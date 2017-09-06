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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
  public function getYears(WebuntisInterface $webuntis, Request $request)
  {
    // Get the years
    $years = $webuntis->getYears()->findAll();
  
    // Serialize the result and respond
    $json = $this->serializer->serialize(array_values($years),'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Get a single year
  public function getYear(WebuntisInterface $webuntis, $yearId)
  {
    // Get the year
    $year = $webuntis->getYears()->get((int)$yearId);
    if ($year === null)
      throw new NotFoundHttpException('The specified year does not exist');
  
    // Serialize the result and respond
    $json = $this->serializer->serialize($year,'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Get all holidays
  public function getHolidays(WebuntisInterface $webuntis, Request $request)
  {
    // Get the holidays
    $holidays = $webuntis->getHolidays()->findAll();
    
    // Serialize the result and respond
    $json = $this->serializer->serialize(array_values($holidays),'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Get a single holiday
  public function getHoliday(WebuntisInterface $webuntis, $holidayId)
  {
    // Get the holiday
    $holiday = $webuntis->getHolidays()->get((int)$holidayId);
    if ($holiday === null)
      throw new NotFoundHttpException('The specified holiday does not exist');
  
    // Serialize the result and respond
    $json = $this->serializer->serialize($holiday,'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Get all departments
  public function getDepartments(WebuntisInterface $webuntis, Request $request)
  {
    // Get the departments
    $departments = $webuntis->getDepartments()->findAll();
  
    // Serialize the result and respond
    $json = $this->serializer->serialize(array_values($departments),'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Get a single department
  public function getDepartment(WebuntisInterface $webuntis, $departmentId)
  {
    // Get the department
    $department = $webuntis->getDepartments()->get((int)$departmentId);
    if ($department === null)
      throw new NotFoundHttpException('The department room does not exist');
  
    // Serialize the result and respond
    $json = $this->serializer->serialize($department,'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Get all classes for a year
  public function getClasses(WebuntisInterface $webuntis, $yearId, Request $request)
  {
    // Get the year
    $year = $webuntis->getYears()->get((int)$yearId);
    if ($year === null)
      throw new BadRequestHttpException('The specified year does not exist');
  
    // Get the classes
    $classes = $webuntis->getClasses($year)->findAll();
  
    // Serialize the result and respond
    $json = $this->serializer->serialize(array_values($classes),'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Get a single class for a year
  public function getClass(WebuntisInterface $webuntis, $yearId, $classId)
  {
    // Get the year
    $year = $webuntis->getYears()->get((int)$yearId);
    if ($year === null)
      throw new BadRequestHttpException('The specified year does not exist');
  
    // Get the class
    $class = $webuntis->getClasses($year)->get((int)$classId);
    if ($class === null)
      throw new NotFoundHttpException('The specified class does not exist');
  
    // Serialize the result and respond
    $json = $this->serializer->serialize($class,'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Get all subjects
  public function getSubjects(WebuntisInterface $webuntis, Request $request)
  {
    // Get the subjects
    $subjects = $webuntis->getSubjects()->findAll();
    
    // Serialize the result and respond
    $json = $this->serializer->serialize(array_values($subjects),'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Get a single subject
  public function getSubject(WebuntisInterface $webuntis, $subjectId)
  {
    // Get the subject
    $subject = $webuntis->getSubjects()->get((int)$subjectId);
    if ($subject === null)
      throw new NotFoundHttpException('The specified subject does not exist');
  
    // Serialize the result and respond
    $json = $this->serializer->serialize($subject,'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Get all rooms
  public function getRooms(WebuntisInterface $webuntis, Request $request)
  {
    // Get the rooms
    $rooms = $webuntis->getRooms()->findAll();
    
    // Serialize the result and respond
    $json = $this->serializer->serialize(array_values($rooms),'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Get a single room
  public function getRoom(WebuntisInterface $webuntis, $roomId)
  {
    // Get the room
    $room = $webuntis->getRooms()->get((int)$roomId);
    if ($room === null)
      throw new NotFoundHttpException('The specified room does not exist');
  
    // Serialize the result and respond
    $json = $this->serializer->serialize($room,'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Get the iCalendar for a class
  public function getTimetable(WebuntisInterface $webuntis, $yearId, $classIds, Request $request)
  {
    // Get the year
    $year = $webuntis->getYears()->get((int)$yearId);
    if ($year === null)
      throw new BadRequestHttpException('No year found for yearId ' . $yearId);
  
    // Get the classes
    $classes = array_map(function($classId) use ($webuntis, $year) {
      return $webuntis->getClasses($year)->get((int)$classId);
    },explode(',',$classIds));
    
    // Get the start and end date if specified
    if ($request->query->has('startDate'))
      $startDate = DateTime::createFromFormat('Y-m-d',$request->query->get('startDate'));
    else
      $startDate = $year->getStartDate();
    
    if ($request->query->has('endDate'))
      $endDate = DateTime::createFromFormat('Y-m-d',$request->query->get('endDate'));
    else
      $endDate = $year->getEndDate();
  
    // Get the complete timetable
    $timetable = [];
    foreach ($classes as $class)
    {
      if ($class !== null)
        $timetable = array_merge($timetable,$webuntis->getTimetable($class,$startDate,$endDate)->findAll());
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
        ->setUID(sprintf("%s-%s-%d",$request->attributes->get('server'),$request->attributes->get('school'),$t->getId()))
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
    $controllers->get('/years/',[$this,'getYears']);
    $controllers->get('/years/{yearId}',[$this,'getYear']);
    
    $controllers->get('/holidays/',[$this,'getHolidays']);
    $controllers->get('/holidays/{holidayId',[$this,'getHoliday']);
    
    $controllers->get('/departments/',[$this,'getDepartments']);
    $controllers->get('/departments/{departmentId}',[$this,'getDepartment']);
    
    $controllers->get('/classes/{yearId}/',[$this,'getClasses']);
    $controllers->get('/classes/{yearId}/{classId}',[$this,'getClass']);
    
    $controllers->get('/subjects/',[$this,'getSubjects']);
    $controllers->get('/subjects/{subjectId}',[$this,'getSubject']);
    
    $controllers->get('/rooms/',[$this,'getRooms']);
    $controllers->get('/rooms/{roomId}',[$this,'getRoom']);
    
    $controllers->get('/timetable/{yearId}/{classIds}.ics',[$this,'getTimetable']);
    
    // Return the controllers
    return $controllers;
  }
}
