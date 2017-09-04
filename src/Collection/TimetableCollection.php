<?php
namespace Webuntis\Collection;

use Webuntis\Model\TimetableModel;

class TimetableCollection extends Collection
{
  // Constructor
  public function __construct(array $elements = array())
  {
    parent::__construct($elements);
    
    // Sort the timetables
    uasort($this->map,[TimetableModel::class,'compare']);
    
    // Search for appendable classes and merge them
    foreach ($this->map as $id => $timetable)
    {
      // Check if the timetable equals the last (except time)
      if ($lastTimetable !== null && $lastTimetable->isAppendable($timetable))
      {
        // Merge the two timetables
        $newTimetable = $lastTimetable->append($timetable);
        $this->map[$newTimetable->getId()] = $newTimetable;
        
        // Remove the current timetable
        unset($this->map[$id]);
      }
      
      // Set the last timetable
      $lastTimetable = $timetable;
    }
  }
}
