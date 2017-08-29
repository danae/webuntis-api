<?php
require('vendor/autoload.php');

use ICalendar\Component\CalendarComponent;
use ICalendar\Component\EventComponent;
use JDesrosiers\Silex\Provider\CorsServiceProvider;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Webuntis\Model\TimetableModel;
use Webuntis\WebuntisServiceProvider;

// Create an application
$app = new Application();

// Pretty print the JSON response
$app->after(function(Request $request, Response $response) {
  if ($response instanceof JsonResponse)
    $response->setEncodingOptions(JSON_PRETTY_PRINT);
  return $response;
});

// Add exception handling
$app->error(function(Exception $ex) {
  if (is_a($ex,HttpException::class))
    return new JsonResponse(['error' => $ex->getMessage()],$ex->getStatusCode(),$ex->getHeaders());
  else
    return new JsonResponse(['error' => $ex->getMessage(),'exceptionThrown' => get_class($ex)],500);
});

// Add the CORS Service and add support for CORS requests
$app->register(new CorsServiceProvider);
$app->after($app['cors']);

// Add the Webuntis service
$app->register(new WebuntisServiceProvider());

// Add a serializer service
$app['serializer'] = function() {
  return new Serializer([new DateTimeNormalizer(DateTime::ISO8601),new GetSetMethodNormalizer],[new JsonEncoder]);
};

// Get all years
$app->get('/{server}/{school}/years',function($server, $school, Request $request, Application $app)  
{
  // Create an endpoint
  $webuntis = $app['webuntis']($server,$school,$request);
  
  // Get the years
  $years = $webuntis->getYears()->findAll();
  
  // Log out
  $webuntis->logout();
  
  // Serialize the result and respond
  $json = $app['serializer']->serialize(array_values($years),'json');
  return JsonResponse::fromJsonString($json);
});

// Get all departments
$app->get('/{server}/{school}/departments',function($server, $school, Request $request, Application $app)  
{
  // Create an endpoint
  $webuntis = $app['webuntis']($server,$school,$request);
  
  // Get the departments
  $departments = $webuntis->getDepartments()->findAll();
  
  // Log out
  $webuntis->logout();
  
  // Serialize the result and respond
  $json = $app['serializer']->serialize(array_values($departments),'json');
  return JsonResponse::fromJsonString($json);
});

// Get all classes of a year
$app->get('/{server}/{school}/years/{yearId}/classes',function($server, $school, $yearId, Request $request, Application $app)  
{
  // Create an endpoint
  $webuntis = $app['webuntis']($server,$school,$request);
  
  // Get the year
  $year = $webuntis->getYears()->get((int)$yearId);
  if ($year === null)
    $app->abort(400,'Found no year for id ' . $yearId);
  
  // Get the classes
  $classes = $webuntis->getClasses($year)->findAll();
  
  // Log out
  $webuntis->logout();
  
  // Serialize the result and respond
  $json = $app['serializer']->serialize(array_values($classes),'json');
  return JsonResponse::fromJsonString($json);
});

// Get the timetable for a class
$app->get('/{server}/{school}/years/{yearId}/timetable/{classesIds}',function($server, $school, $yearId, $classesIds, Request $request, Application $app)  
{
  // Create an endpoint
  $webuntis = $app['webuntis']($server,$school,$request);
  
  // Get the year
  $year = $webuntis->getYears()->get((int)$yearId);
  
  // Get the classes
  $classes = array_map(function($classId) use ($webuntis, $year) {
    return $webuntis->getClasses($year)->get((int)$classId);
  },explode(',',$classesIds));
  
  // Get the complete timetable and sort it
  $timetable = [];
  foreach ($classes as $class)
    $timetable = array_merge($timetable,$webuntis->getTimetable($class,$year->getStartDate(),$year->getEndDate())->findAll());
  usort($timetable,[TimetableModel::class,'compare']);
  
  // Log out
  $webuntis->logout();
  
  // Serialize the result and respond
  $json = $app['serializer']->serialize($timetable,'json');
  return JsonResponse::fromJsonString($json);
});

// Get the iCalendar for a class
$app->get('/{server}/{school}/years/{yearId}/export/{classesIds}.ics',function($server, $school, $yearId, $classesIds, Request $request, Application $app)  
{
  // Create an endpoint
  $webuntis = $app['webuntis']($server,$school,$request);
  
  // Get the year
  $year = $webuntis->getYears()->get($yearId);
  
  // Get the classes
  $classes = array_map(function($classId) use ($webuntis, $year) {
    return $webuntis->getClasses($year)->get((int)$classId);
  },explode(',',$classesIds));
  
  // Get the complete timetable and sort it
  $now = new DateTime;
  $timetable = [];
  foreach ($classes as $class)
    $timetable = array_merge($timetable,$webuntis->getTimetable($class,$year->getStartDate(),$year->getEndDate())->findAll());
  usort($timetable,[TimetableModel::class,'compare']);
  
  // Log out
  $webuntis->logout();
  
  // Create an iCalendar for the timetable
  $calendar = new CalendarComponent;
  
  // Add all events
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
});

// Run the application
$app->run();