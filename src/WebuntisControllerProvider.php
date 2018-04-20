<?php
namespace Webuntis;

use DateTime;
use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Webuntis\Exception\UnauthorizedException;
use Webuntis\Model\YearModel;

class WebuntisControllerProvider implements ControllerProviderInterface
{
  // Variables
  private $serializer;
  
  // Constructor
  public function __construct(SerializerInterface $serializer)
  {
    $this->serializer = $serializer;
  }
  
  // Respond a single object
  private function respond($object): Response
  {
    $json = $this->serializer->serialize($object,'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Respond multiple objects
  private function respondArray($objects): Response
  {
    $json = $this->serializer->serialize(array_values($objects),'json');
    return JsonResponse::fromJsonString($json);
  }
  
  // Get a timetable
  private function getTimetable(array $objects, YearModel $year, WebuntisInterface $webuntis, Request $request)
  {
    // Get the start and end date if specified
    if ($request->query->has('startDate'))
      $startDate = DateTime::createFromFormat('Y-m-d',$request->query->get('startDate'));
    else
      $startDate = $year->getStartDate();
    if ($request->query->has('endDate'))
      $endDate = DateTime::createFromFormat('Y-m-d',$request->query->get('endDate'));
    else
      $endDate = $year->getEndDate();
    
    // Return the timetable
    return $webuntis->getMultipleTimetables($objects,$startDate,$endDate)->findAll();
  }
  
  // Get a timetable and respond it
  private function respondTimetable(array $objects, YearModel $year, WebuntisInterface $webuntis, Request $request)
  {
    // Get the timetable
    $timetable = $this->getTimetable($objects,$year,$webuntis,$request);
    
    // Serialize the result and respond
    return $this->respondArray($timetable);
  }
  
  // Get a timetable and respond its calendar
  private function respondTimetableAsCalendar(array $objects, YearModel $year, WebuntisInterface $webuntis, Request $request)
  {
    // Get the timetable
    $timetable = $this->getTimetable($objects,$year,$webuntis,$request);
    
    // Create an iCalendar for the timetable
    $calendar = new Calendar("-//dengsn//webuntis-api");
  
    // Add all events
    $now = new DateTime;
    foreach ($timetable as $t)
    {
      $eventUid = sprintf("%s-%s-%d",$request->attributes->get('server'),$request->attributes->get('school'),$t->getId());
      
      $event = new Event($eventUid);
      $event->setDtStamp($now);
      $event->setDtStart($t->getStartTime());
      $event->setDtEnd($t->getEndTime());
      $event->setSummary(implode(', ',$t->getSubjects()));
      $event->setDescription(implode(', ',$t->getClasses()));
      $event->setLocation(implode(', ',$t->getRooms()));
        
      $calendar->addComponent($event);
    }
  
    // Respond the calendar
    $response = new Response($calendar->render());
    $response->headers->set('Content-Type','text/calendar; charset=utf-8');
    return $response;
  }

  // Get all years
  public function getYears(WebuntisInterface $webuntis, Request $request)
  {
    // Get the years
    $years = $webuntis->getYears()->findAll();
  
    // Serialize the result and respond
    return $this->respondArray($years);
  }
  
  // Get a single year
  public function getYear(WebuntisInterface $webuntis, $yearId)
  {
    // Get the year
    $year = $webuntis->getYears()->get((int)$yearId);
    if ($year === null)
      throw new NotFoundHttpException('The specified year does not exist');
  
    // Serialize the result and respond
    return $this->respond($year);
  }
  
  // Get all holidays
  public function getHolidays(WebuntisInterface $webuntis, Request $request)
  {
    // Get the holidays
    $holidays = $webuntis->getHolidays()->findAll();
    
    // Serialize the result and respond
    return $this->respondArray($holidays);
  }
  
  // Get a single holiday
  public function getHoliday(WebuntisInterface $webuntis, $holidayId)
  {
    // Get the holiday
    $holiday = $webuntis->getHolidays()->get((int)$holidayId);
    if ($holiday === null)
      throw new NotFoundHttpException('The specified holiday does not exist');
  
    // Serialize the result and respond
    return $this->respond($holiday);
  }
  
  // Get all departments
  public function getDepartments(WebuntisInterface $webuntis, Request $request)
  {
    // Get the departments
    $departments = $webuntis->getDepartments()->findAll();
  
    // Serialize the result and respond
    return $this->respondArray($departments);
  }
  
  // Get a single department
  public function getDepartment(WebuntisInterface $webuntis, $departmentId)
  {
    // Get the department
    $department = $webuntis->getDepartments()->get((int)$departmentId);
    if ($department === null)
      throw new NotFoundHttpException('The department room does not exist');
  
    // Serialize the result and respond
    return $this->respond($department);
  }
  
  // Get all classes for a year
  public function getClasses(WebuntisInterface $webuntis, $yearId, Request $request)
  {
    // Get the year
    $year = $webuntis->getYears()->get((int)$yearId);
    if ($year === null)
      throw new NotFoundHttpException('The specified year does not exist');
  
    // Get the classes
    $classes = $webuntis->getClasses($year)->findAll();
  
    // Serialize the result and respond
    return $this->respondArray($classes);
  }
  
  // Get a single class for a year
  public function getClass(WebuntisInterface $webuntis, $yearId, $classId)
  {
    // Get the year
    $year = $webuntis->getYears()->get((int)$yearId);
    if ($year === null)
      throw new NotFoundHttpException('The specified year does not exist');
  
    // Get the class
    $class = $webuntis->getClasses($year)->get((int)$classId);
    if ($class === null)
      throw new NotFoundHttpException('The specified class does not exist');
  
    // Serialize the result and respond
    return $this->respond($class);
  }
  
  // Get all subjects
  public function getSubjects(WebuntisInterface $webuntis, Request $request)
  {
    // Get the subjects
    $subjects = $webuntis->getSubjects()->findAll();
    
    // Serialize the result and respond
    return $this->respondArray($subjects);
  }
  
  // Get a single subject
  public function getSubject(WebuntisInterface $webuntis, $subjectId)
  {
    // Get the subject
    $subject = $webuntis->getSubjects()->get((int)$subjectId);
    if ($subject === null)
      throw new NotFoundHttpException('The specified subject does not exist');
  
    // Serialize the result and respond
    return $this->respond($subject);
  }
  
  // Get all rooms
  public function getRooms(WebuntisInterface $webuntis, Request $request)
  {
    // Get the rooms
    $rooms = $webuntis->getRooms()->findAll();
    
    // Serialize the result and respond
    return $this->respondArray($rooms);
  }
  
  // Get a single room
  public function getRoom(WebuntisInterface $webuntis, $roomId)
  {
    // Get the room
    $room = $webuntis->getRooms()->get((int)$roomId);
    if ($room === null)
      throw new NotFoundHttpException('The specified room does not exist');
  
    // Serialize the result and respond
    return $this->respond($room);
  }
  
  // Get the timetable for classes
  public function getClassesTimetable(WebuntisInterface $webuntis, $yearId, $classIdList, Request $request)
  {
    // Get the year
    $year = $webuntis->getYears()->get((int)$yearId);
    if ($year === null)
      throw new NotFoundHttpException('The specified year does not exist');
    
    // Get the classes
    $classes = array_map(function($classId) use ($webuntis, $year)
    {
      $class = $webuntis->getClasses($year)->get((int)$classId);
      if ($class === null)
        throw new NotFoundHttpException('The specified class does not exist');
      return $class;
    },explode(',',$classIdList));
    
    // Respond the corresponding timetable
    return $this->respondTimetable($classes,$year,$webuntis,$request);
  }
  
  // Get the timetable for subjects
  public function getSubjectsTimetable(WebuntisInterface $webuntis, $subjectIdList, Request $request)
  {
    // Get the current year
    $year = $webuntis->getYears()->contains(new DateTime);
    
    // Get the subjects
    $subjects = array_map(function($subjectId) use ($webuntis)
    {
      $subject = $webuntis->getSubjects()->get((int)$subjectId);
      if ($subject === null)
        throw new NotFoundHttpException('The specified subject does not exist');
      return $subject;
    },explode(',',$subjectIdList));
    
    // Respond the corresponding timetable
    return $this->respondTimetable($subjects,$year,$webuntis,$request);
  }
  
  // Get the timetable for rooms
  public function getRoomsTimetable(WebuntisInterface $webuntis, $roomIdList, Request $request)
  {
    // Get the current year
    $year = $webuntis->getYears()->contains(new DateTime);
    
    // Get the rooms
    $rooms = array_map(function($roomId) use ($webuntis)
    {
      $room = $webuntis->getRooms()->get((int)$roomId);
      if ($room === null)
        throw new NotFoundHttpException('The specified room does not exist');
      return $room;
    },explode(',',$roomIdList));
    
    // Respond the corresponding timetable
    return $this->respondTimetable($rooms,$year,$webuntis,$request);
  }
  
  // Get the calendar for classes
  public function getClassesCalendar(WebuntisInterface $webuntis, $yearId, $classIdList, Request $request)
  {    
    // Get the year
    $year = $webuntis->getYears()->get((int)$yearId);
    if ($year === null)
      throw new NotFoundHttpException('The specified year does not exist');
    
    // Get the classes
    $classes = array_map(function($classId) use ($webuntis, $year)
    {
      $class = $webuntis->getClasses($year)->get((int)$classId);
      if ($class === null)
        throw new NotFoundHttpException('The specified class does not exist');
      return $class;
    },explode(',',$classIdList));
    
    // Respond the corresponding calendar
    return $this->respondTimetableAsCalendar($classes,$year,$webuntis,$request);
  }
  
  // Get the calendar for subjects
  public function getSubjectsCalendar(WebuntisInterface $webuntis, $subjectIdList, Request $request)
  {
    // Get the current year
    $year = $webuntis->getYears()->contains(new DateTime);
    
    // Get the subjects
    $subjects = array_map(function($subjectId) use ($webuntis)
    {
      $subject = $webuntis->getSubjects()->get((int)$subjectId);
      if ($subject === null)
        throw new NotFoundHttpException('The specified subject does not exist');
      return $subject;
    },explode(',',$subjectIdList));
    
    // Respond the corresponding calendar
    return $this->respondTimetableAsCalendar($subjects,$year,$webuntis,$request);
  }
  
  // Get the calendar for rooms
  public function getRoomsCalendar(WebuntisInterface $webuntis, $roomIdList, Request $request)
  {
    // Get the current year
    $year = $webuntis->getYears()->contains(new DateTime);
    
    // Get the rooms
    $rooms = array_map(function($roomId) use ($webuntis)
    {
      $room = $webuntis->getRooms()->get((int)$roomId);
      if ($room === null)
        throw new NotFoundHttpException('The specified room does not exist');
      return $room;
    },explode(',',$roomIdList));
    
    // Respond the corresponding calendar
    return $this->respondTimetableAsCalendar($rooms,$year,$webuntis,$request);
  }
  
  // Connect to the application
  public function connect(Application $app): ControllerCollection
  {
    // Create a new controller collection
    $controllers = $app['controllers_factory'];
    
    // Create the endpoint
    $controllers->before(function(Request $request, Application $app) 
    {
      try
      {
        // Check if credentials are given
        if ($request->getUser() === null)
          throw new UnauthorizedHttpException('Basic','You must provide your credentials');
        
        // Create a new endpoint and log in to the endpoint
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
    $controllers->get('/holidays/{holidayId}',[$this,'getHoliday']);
    
    $controllers->get('/departments/',[$this,'getDepartments']);
    $controllers->get('/departments/{departmentId}',[$this,'getDepartment']);
    
    $controllers->get('/years/{yearId}/classes/',[$this,'getClasses']);
    $controllers->get('/years/{yearId}/classes/{classId}',[$this,'getClass']);
    
    $controllers->get('/subjects/',[$this,'getSubjects']);
    $controllers->get('/subjects/{subjectId}',[$this,'getSubject']);
    
    $controllers->get('/rooms/',[$this,'getRooms']);
    $controllers->get('/rooms/{roomId}',[$this,'getRoom']);
    
    $controllers->get('/years/{yearId}/classes/{classIdList}/timetable',[$this,'getClassesTimetable']);
    $controllers->get('/subjects/{subjectIdList}/timetable',[$this,'getSubjectsTimetable']);
    $controllers->get('/rooms/{roomIdList}/timetable',[$this,'getRoomsTimetable']);
    
    $controllers->get('/years/{yearId}/classes/{classIdList}/calendar.ics',[$this,'getClassesCalendar']);
    $controllers->get('/subjects/{subjectIdList}/calendar.ics',[$this,'getSubjectsCalendar']);
    $controllers->get('/rooms/{roomIdList}/calendar.ics',[$this,'getRoomsCalendar']);
    
    // Return the controllers
    return $controllers;
  }
}
