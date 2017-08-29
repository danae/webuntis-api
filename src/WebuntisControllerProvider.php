<?php
namespace Webuntis;

use DateTime;
use ICalendar\Component\CalendarComponent;
use ICalendar\Component\EventComponent;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Webuntis\Exception\UnauthorizedException;
use Webuntis\Model\TimetableModel;

class WebuntisControllerProvider implements ControllerProviderInterface
{
  // Variables
  private $serializer;
  
  // Constructor
  public function __construct(SerializerInterface $serializer)
  {
    $this->serializer = $serializer;
  }

  // Get all years
  public function getYears(WebuntisInterface $webuntis)
  {
    // Get the years
    $years = $webuntis->getYears()->findAll();
  
    // Serialize the result and respond
    $json = $this->serializer->serialize(array_values($years),'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Get all departments
  public function getDepartments(WebuntisInterface $webuntis)
  {
    // Get the departments
    $departments = $webuntis->getDepartments()->findAll();
  
    // Serialize the result and respond
    $json = $this->serializer->serialize(array_values($departments),'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Get all classes for a year
  public function getClasses(WebuntisInterface $webuntis, $yearId)
  {
    // Get the year
    $year = $webuntis->getYears()->get((int)$yearId);
    if ($year === null)
      throw new BadRequestHttpException('No year found for yearId ' . $yearId);
  
    // Get the classes
    $classes = $webuntis->getClasses($year)->findAll();
  
    // Serialize the result and respond
    $json = $this->serializer->serialize(array_values($classes),'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Get the iCalendar for a class
  public function getTimetable(WebuntisInterface $webuntis, $yearId, $classIds)
  {
    // Get the year
    $year = $webuntis->getYears()->get((int)$yearId);
    if ($year === null)
      throw new BadRequestHttpException('No year found for yearId ' . $yearId);
  
    // Get the classes
    $classes = array_map(function($classId) use ($webuntis, $year) {
      return $webuntis->getClasses($year)->get((int)$classId);
    },explode(',',$classIds));
    
    var_dump($classes);
  
    // Get the complete timetable
    $timetable = [];
    foreach ($classes as $class)
    {
      if ($class !== null)
        $timetable = array_merge($timetable,$webuntis->getTimetable($class,$year->getStartDate(),$year->getEndDate())->findAll());
    }
    
    // Sort the timetable by time
    usort($timetable,[TimetableModel::class,'compare']);
  
    // Create an iCalendar for the timetable
    $calendar = new CalendarComponent;
  
    // Add all events
    $now = new DateTime;
    foreach ($timetable as $t)
    {
      $event = (new EventComponent)
        ->setUID(sprintf("%s-%s-%d",$server,$school,$t->getId()))
        ->setTimestamp($now)
        ->setStartTime($t->getStartTime())
        ->setEndTime($t->getEndTime())
        ->setSummary(implode(', ',$t->getSubjects()))
        ->setDescription(implode(', ',$t->getClasses()))
       ->setLocation(implode(', ',$t->getRooms()));
      $calendar->add($event);
    }
  
    // Respond the calendar
    $response = new Response($calendar->write());
    $response->headers->set('Content-Type','text/calendar');
    return $response;
  }
  
  // Connect to the application
  public function connect(Application $app): ControllerCollection
  {
    // Create a new controller collection
    $controllers = $app['controllers_factory'];
    
    // Create the endpoint
    $controllers->before(function(Request $request) 
    {
      try
      {
        // Check if credentials are given
        if ($request->getUser() === null)
          throw new UnauthorizedHttpException("Basic",'You must provide your credentials');
        
        // Create a new endpoint
        $webuntis = new Webuntis($request->attributes->get('server'),$request->attributes->get('school'));
        $webuntis->login($request->getUser(),$request->getPassword());
        
        // Add to the request
        $request->attributes->set('webuntis',$webuntis);
      }
      catch (UnauthorizedException $ex)
      {
        if ($ex->getCode() === -8504)
          throw new UnauthorizedHttpException("Basic",'You provided invalid credentials');
        else if ($ex->getCode() === -8520)
          throw new UnauthorizedHttpException("Basic",'You are not authorized');
        else
          throw $ex;
      }
    });
    
    // Log out from the endpoint
    $controllers->after(function(Request $request) 
    {
      // Log out from the endpoint
      if ($request->attributes->has('webuntis'))
        $request->attributes->get('webuntis')->logout();
    });
    
    // Add controllers
    $controllers->get('/years',[$this,'getYears']);
    $controllers->get('/years/{yearId}/classes',[$this,'getClasses']);
    $controllers->get('/departments',[$this,'getDepartments']);
    $controllers->get('/timetable/{yearId}/{classIds}.ics',[$this,'getTimetable']);
    
    // Return the controllers
    return $controllers;
  }
}
