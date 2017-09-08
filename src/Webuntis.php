<?php
namespace Webuntis;

use DateTime;
use InvalidArgumentException;
use JsonRPC\Client;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Webuntis\Collection\Collection;
use Webuntis\Collection\HolidayCollection;
use Webuntis\Collection\TimetableCollection;
use Webuntis\Collection\YearCollection;
use Webuntis\Model\ClassModel;
use Webuntis\Model\DepartmentModel;
use Webuntis\Model\HolidayModel;
use Webuntis\Model\ModelInterface;
use Webuntis\Model\RoomModel;
use Webuntis\Model\SubjectModel;
use Webuntis\Model\TimetableModel;
use Webuntis\Model\YearModel;
use Webuntis\Utils\DateTimeUtils;
use Webuntis\Utils\ExceptionUtils;

class Webuntis implements WebuntisInterface
{
  // Constants
  const endpoint_url = "https://%s/WebUntis/jsonrpc.do?school=%s";
  
  // Variables
  private $client;
  private $serializer;
  private $session;
  
  // Cache variables
  private $years;
  private $holidays;
  private $departments;
  private $classes;
  private $subjects;
  private $rooms;
  private $timetables;
  
  // Constructor
  public function __construct(string $server, string $school)
  {
    $url = sprintf(self::endpoint_url,$server,$school);
    
    $this->client = new Client($url);
    $this->serializer = new Serializer([new CustomNormalizer, new GetSetMethodNormalizer]);
  }
  
  // Authenticate to the API
  public function login(string $user, string $password)
  {
    try
    {
      // Try to authenticate to the API
      $result = $this->client->execute('authenticate',[$user,$password]);
      
      // Set the session
      $this->session = $result['sessionId'];
    }
    catch (Exception $ex)
    {
      throw ExceptionUtils::handleException($ex);
    }
  }
  
  // Log out the current session
  public function logout()
  {
    if (is_null($this->session))
      throw new UnauthorizedHttpException('Basic','You are not logged in');
    
    try
    {
      // Try to log out
      $this->client->execute('logout');
      
      // Reset the session
      $this->session = null;
    }
    catch (Exception $ex)
    {
      throw ExceptionUtils::handleException($ex);
    }
  }
  
  // Return an object by identifier and type
  public function getObject(int $id, $class): ModelInterface
  {
    if ($class === YearModel::class)
      return $this->getYears()->get($id);
    else if ($class === HolidayModel::class)
      return $this->getHolidays()->get($id);
    else if ($class === DepartmentModel::class)
      return $this->getDepartments()->get($id);
    else if ($class === SubjectModel::class)
      return $this->getSubjects()->get($id);
    else if ($class === Room::class)
      return $this->getRooms()->get($id);
    else
      throw new InvalidArgumentException("Cannot fetch objects with class {$class}");
  }
  
  // Get years
  public function getYears(): YearCollection
  {
    try
    {
      // If already fetched, then return the cache
      if ($this->years !== null)
        return $this->years;
      
      // Get the results
      $results = $this->client->execute('getSchoolyears');
      
      // Create a collection of the results
      return $this->years = new YearCollection(array_map(function($result) {
        return $this->serializer->denormalize($result,YearModel::class,null,['database' => $this]);
      },$results));
    }
    catch (Exception $ex)
    {
      throw ExceptionUtils::handleException($ex);
    }
  }
  
  // Get holidays
  public function getHolidays(): HolidayCollection
  {
    try
    {
      // If already fetched, then return the cache
      if ($this->holidays !== null)
        return $this->holidays;
      
      // Get the results
      $results = $this->client->execute('getHolidays');
      
      // Create a collection of the results
      return $this->holidays = new HolidayCollection(array_map(function($result) {
        return $this->serializer->denormalize($result,HolidayModel::class,null,['database' => $this]);
      },$results));
    }
    catch (Exception $ex)
    {
      throw ExceptionUtils::handleException($ex);
    }
  }
  
  // Get departments
  public function getDepartments(): Collection
  {
    try
    {
      // If already fetched, then return the cache
      if ($this->departments !== null)
        return $this->departments;
      
      // Get the results
      $results = $this->client->execute('getDepartments');
      
      // Create a collection of the results
      return $this->departments = new Collection(array_map(function($result) {
        return $this->serializer->denormalize($result,DepartmentModel::class,null,['database' => $this]);
      },$results));
    }
    catch (Exception $ex)
    {
      throw ExceptionUtils::handleException($ex);
    }
  }
  
  // Get classes
  public function getClasses(YearModel $year): Collection
  {
    try
    {
      // If the classes array isn't an array, create it
      if ($this->classes === null)
        $this->classes = [];
      
      // If already fetched, then return the cache
      if ($this->classes[$year->getId()] !== null)
        return $this->classes[$year->getId()];
      
      // Get the results
      $results = $this->client->execute('getKlassen',['schoolyearId' => $year->getId()]);
      
      // Create a collection of the results
      return $this->classes[$year->getId()] = new Collection(array_map(function($result) {
        return $this->serializer->denormalize($result,ClassModel::class,null,['database' => $this]);
      },$results));
    }
    catch (Exception $ex)
    {
      throw ExceptionUtils::handleException($ex);
    }
  }
  
  // Get subjects
  public function getSubjects(): Collection
  {
    try
    {
      // If already fetched, then return the cache
      if ($this->subjects !== null)
        return $this->subjects;
      
      // Get the results
      $results = $this->client->execute('getSubjects');
      
      // Create a collection of the results
      return $this->subjects = new Collection(array_map(function($result) {
        return $this->serializer->denormalize($result,SubjectModel::class,null,['database' => $this]);
      },$results));
    }
    catch (Exception $ex)
    {
      throw ExceptionUtils::handleException($ex);
    }
  }
  
  // Get rooms
  public function getRooms(): Collection
  {
    try
    {
      // If already fetched, then return the cache
      if ($this->rooms !== null)
        return $this->rooms;
      
      // Get the results
      $results = $this->client->execute('getRooms');
      
      // Create a collection of the results
      return $this->rooms = new Collection(array_map(function($result) {
        return $this->serializer->denormalize($result,RoomModel::class,null,['database' => $this]);
      },$results));
    }
    catch (Exception $ex)
    {
      throw ExceptionUtils::handleException($ex);
    }
  }
  
  // Get timetable
  public function getTimetable(ModelInterface $object, DateTime $startDate, DateTime $endDate): TimetableCollection
  {
    try
    {      
      // Get the type of the object
      if (is_a($object,ClassModel::class))
        $type = TimetableModel::TYPE_CLASS;
      else if (is_a($object,SubjectModel::class))
        $type = TimetableModel::TYPE_SUBJECT;
      else if (is_a($object,RoomModel::class))
        $type = TimetableModel::TYPE_ROOM;
      else
        throw new InvalidArgumentException("Object must be a class, subject or room");
      
      // If the timetables array isn't an array, create it
      if ($this->timetables === null)
        $this->timetables = [];
      if ($this->timetable[$type] === null)
        $this->timetables[$type] = [];
      
      // If already fetched, then return the cache
      if ($this->timetables[$type][$object->getId()] !== null)
        return $this->timetables[$type][$object->getId()];
    
      // Get the results
      $results = $this->client->execute('getTimetable',[
        'startDate' => DateTimeUtils::formatDate($startDate),
        'endDate' => DateTimeUtils::formatDate($endDate),
        'id' => $object->getId(),
        'type' => $type
      ]);
      
      // Create a collection of the results
      return $this->timetables[$type][$object->getId()] = new TimetableCollection(array_map(function($result) {
        return $this->serializer->denormalize($result,TimetableModel::class,null,['database' => $this]);
      },$results));
    }
    catch (Exception $ex)
    {
      throw ExceptionUtils::handleException($ex);
    }
  }
  
  // Return multiple timetables
  public function getMultipleTimetables(array $objects, DateTime $startDate, DateTime $endDate): TimetableCollection
  {
    // Create an empty timetable
    $timetable = [];
    
    // Iterate over the objects
    foreach ($objects as $object)
    {
      // Get the timetable
      $objectTimetable = $this->getTimetable($object,$startDate,$endDate)->findAll();
      
      // Merge it with the total timetable
      $timetable = array_merge($timetable,$objectTimetable);
    }
    
    // Create a collection of the results
    $collection = new TimetableCollection($timetable);
    
    // Return the collection
    return $collection;
  }
}
