<?php
namespace Webuntis;

use DateTime;
use InvalidArgumentException;
use JsonRPC\Client;
use Symfony\Component\Cache\Simple\FilesystemCache;
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
  private $cache;
  private $session;
  
  // Constructor
  public function __construct(string $server, string $school)
  {
    $url = sprintf(self::endpoint_url,$server,$school);
    
    $this->client = new Client($url);
    $this->serializer = new Serializer([new CustomNormalizer, new GetSetMethodNormalizer]);
    $this->cache = new FilesystemCache("webuntis.{$server}.{$school}",300,'cache');
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
  
  // Get years
  public function getYears(): YearCollection
  {
    try
    {
      // Check if the collection is cached
      if ($this->cache->hasItem("years"))
        return $this->cache->get("years");
    
      // Get the results
      $results = $this->client->execute('getSchoolyears');
      
      // Create a collection of the results
      $collection = new YearCollection(array_map(function($result) {
        return $this->serializer->denormalize($result,YearModel::class,null,['database' => $this]);
      },$results));
    
      // Cache the collection
      $this->cache->set("years",$collection);
    
      // Return the collection
      return $collection;
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
      // Check if the collection is cached
      if ($this->cache->hasItem("holidays"))
        return $this->cache->get("holidays");
    
      // Get the results
      $results = $this->client->execute('getHolidays');
      
      // Create a collection of the results
      $collection = new HolidayCollection(array_map(function($result) {
        return $this->serializer->denormalize($result,HolidayModel::class,null,['database' => $this]);
      },$results));
      
      // Cache the collection
      $this->cache->set("holidays",$collection);
      
      // Return the collection
      return $collection;
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
      // Check if the collection is cached
      if ($this->cache->hasItem("departments"))
        return $this->cache->get("departments");
    
      // Get the results
      $results = $this->client->execute('getDepartments');
      
      // Create a collection of the results
      $collection = new Collection(array_map(function($result) {
        return $this->serializer->denormalize($result,DepartmentModel::class,null,['database' => $this]);
      },$results));
      
      // Cache the collection
      $this->cache->set("departments",$collection);
      
      // Return the collection
      return $collection;
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
      // Check if the collection is cached
      if ($this->cache->hasItem("classes.{$year->getId()}"))
        return $this->cache->get("classes.{$year->getId()}");
    
      // Get the results
      $results = $this->client->execute('getKlassen',['schoolyearId' => $year->getId()]);
      
      // Create a collection of the results
      $collection = new Collection(array_map(function($result) {
        return $this->serializer->denormalize($result,ClassModel::class,null,['database' => $this]);
      },$results));
      
      // Cache the collection
      $this->cache->set("classes.{$year->getId()}",$collection);
      
      // Return the collection
      return $collection;
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
      // Check if the collection is cached
      if ($this->cache->hasItem("subjects"))
        return $this->cache->get("subjects");
    
      // Get the results
      $results = $this->client->execute('getSubjects');
      
      // Create a collection of the results
      $collection = new Collection(array_map(function($result) {
        return $this->serializer->denormalize($result,SubjectModel::class,null,['database' => $this]);
      },$results));
      
      // Cache the collection
      $this->cache->set("subjects",$collection);
      
      // Return the collection
      return $collection;
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
      // Check if the collection is cached
      if ($this->cache->hasItem("rooms"))
        return $this->cache->get("rooms");
    
      // Get the results
      $results = $this->client->execute('getRooms');
      
      // Create a collection of the results
      $collection = new Collection(array_map(function($result) {
        return $this->serializer->denormalize($result,RoomModel::class,null,['database' => $this]);
      },$results));
      
      // Cache the collection
      $this->cache->set("rooms",$collection);
      
      // Return the collection
      return $collection;
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
      // Check if the collection is cached
      //if ($this->cache->hasItem("timetable"))
        //return $this->cache->get("timetable");
    
      // Get the type of the object
      if (is_a($object,ClassModel::class))
        $type = TimetableModel::TYPE_CLASS;
      else if (is_a($object,SubjectModel::class))
        $type = TimetableModel::TYPE_SUBJECT;
      else if (is_a($object,RoomModel::class))
        $type = TimetableModel::TYPE_ROOM;
      else
        throw new InvalidArgumentException("Object must be a class, subject or room");
    
      // Get the results
      $results = $this->client->execute('getTimetable',[
        'startDate' => DateTimeUtils::formatDate($startDate),
        'endDate' => DateTimeUtils::formatDate($endDate),
        'id' => $object->getId(),
        'type' => $type
      ]);
      
      // Create a collection of the results
      $collection = new TimetableCollection(array_map(function($result) {
        return $this->serializer->denormalize($result,TimetableModel::class,null,['database' => $this]);
      },$results));
      
      // Cache the collection
      //$this->cache->set("timetable",$collection);
      
      // Return the collection
      return $collection;
    }
    catch (Exception $ex)
    {
      throw ExceptionUtils::handleException($ex);
    }
  }
}
