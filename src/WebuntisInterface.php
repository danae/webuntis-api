<?php
namespace Webuntis;

use DateTime;
use Webuntis\Collection\Collection;
use Webuntis\Collection\HolidayCollection;
use Webuntis\Collection\TimetableCollection;
use Webuntis\Collection\YearCollection;
use Webuntis\Model\ModelInterface;
use Webuntis\Model\YearModel;

interface WebuntisInterface
{
  // Return an object by identifier and type
  public function getObject(int $id, $class): ModelInterface;
  
  // Return the years
  public function getYears(): YearCollection;
  
  // Return the holidays
  public function getHolidays(): HolidayCollection;
  
  // Return the departments
  public function getDepartments(): Collection;
  
  // Return the classes
  public function getClasses(YearModel $year): Collection;
  
  // Return the subjects
  public function getSubjects(): Collection;
  
  // Return the rooms
  public function getRooms(): Collection;
  
  // Return the timetable for a class, subject or room
  public function getTimetable(ModelInterface $object, DateTime $startDate, DateTime $endDate): TimetableCollection;
  
  // Return multiple timetables
  public function getMultipleTimetables(array $objects, DateTime $startDate, DateTime $endDate): TimetableCollection;
}
