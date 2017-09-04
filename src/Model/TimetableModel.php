<?php
namespace Webuntis\Model;

use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizableInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Webuntis\Model\Traits\DateTimePeriodTrait;
use Webuntis\Utils\DateTimeUtils;
use Webuntis\WebuntisInterface;

class TimetableModel extends Model implements DenormalizableInterface
{
  use DateTimePeriodTrait;
  
  // Constrants
  const TYPE_CLASS = 1;
  const TYPE_TEACHER = 2;
  const TYPE_SUBJECT = 3;
  const TYPE_ROOM = 4;
  const TYPE_STUDENT = 5;
  
  // Variables
  protected $classes;
  protected $subjects;
  protected $rooms;
  
  // Return the classes
  public function getClasses(): array
  {
    return $this->classes;
  }
  
  // Set the classes
  public function setClasses(array $classes): self
  {
    $this->classes = $classes;
    return $this;
  }
  
  // Return the subjects
  public function getSubjects(): array
  {
    return $this->subjects;
  }
  
  // Set the subjects
  public function setSubjects(array $subjects): self
  {
    $this->subjects = $subjects;
    return $this;
  }
  
  // Return the rooms
  public function getRooms(): array
  {
    return $this->rooms;
  }
  
  // Set the rooms
  public function setRooms(array $rooms): self
  {
    $this->rooms = $rooms;
    return $this;
  }
  
  // Return a merged timetable from this timetable and another timetable
  public function append(TimetableModel $other): TimetableModel
  {
    // Check if the timetables can be merged
    if (!$this->isAppendable($other))
      throw new InvalidArgumentException('The specified timetables cannot be merged');
    
    // Merge the timetables
    return (new TimetableModel)
      ->setId($this->getId())
      ->setStartTime($this->getStartTime())
      ->setEndTime($other->getEndTime())
      ->setClasses($this->getClasses())
      ->setSubjects($this->getSubjects())
      ->setRooms($this->getRooms());
  }
  
  // Return if this timetable can be appended with another timetable
  public function isAppendable(TimetableModel $other): bool
  {
    return $this->getEndTime() == $other->getStartTime()
      && $this->getClasses() == $other->getClasses()
      && $this->getSubjects() == $other->getSubjects()
      && $this->getRooms() == $other->getRooms();
  }
  
  // Denormalize this timetable
  public function denormalize(DenormalizerInterface $denormalizer, $data, $format = null, array $context = []): self
  {
    if (!is_array($data))
      throw new InvalidArgumentException("Data must be an array");
    
    // Check if we can access the database
    if (!is_a($context['database'],WebuntisInterface::class))
      throw new InvalidArgumentException("No database is defined");
    
    // Calculate the start and end times
    $date = DateTimeUtils::parseDate($data['date']);
    $startTime = DateTimeUtils::parseTime($date,$data['startTime']);
    $endTime = DateTimeUtils::parseTime($date,$data['endTime']);
    
    // Get the school year for this schedule
    $year = $context['database']->getYears()->contains($startTime);
    
    // Get the classes
    $classes = [];
    foreach ($data['kl'] as $class)
      $classes[] = $context['database']->getClasses($year)->get($class['id']);
    
    // Get the subjects
    $subjects = [];
    foreach ($data['su'] as $subject)
      $subjects[] = $context['database']->getSubjects()->get($subject['id']);
    
    // Get the rooms
    $rooms = [];
    foreach ($data['ro'] as $room)
      $rooms[] = $context['database']->getRooms()->get($room['id']);
    
    // Return the timetable
    return $this
      ->setId($data['id'])
      ->setStartTime($startTime)
      ->setEndTime($endTime)
      ->setClasses($classes)
      ->setSubjects($subjects)
      ->setRooms($rooms);
  }
  
  // Convert to string
  public function __toString()
  {
    return sprintf("%s - %s: %s – %s",$this->startTime->format('Y-m-d H:i'),$this->endTime->format('Y-m-d H:i'),implode(', ',$this->subjects),implode(', ',$this->rooms));
  }
  
  // Comparator function
  public static function compare($a, $b): int
  {
    if ($a == null)
      return 1;
    else if ($b == null)
      return -1;
    
    return $a->getStartTime()->getTimestamp() - $b->getStartTime()->getTimestamp();
  }
}
