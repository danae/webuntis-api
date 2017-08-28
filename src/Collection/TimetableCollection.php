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
  }
}
