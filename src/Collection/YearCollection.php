<?php
namespace Webuntis\Collection;

use DateTime;
use Webuntis\Model\YearModel;

class YearCollection extends Collection
{
  // Constructor
  public function __construct(array $elements = array())
  {
    parent::__construct($elements);
    
    // Sort the years
    uasort($this->map,[YearModel::class,'compare']);
  }
  
  // Return the year that contains a given date
  public function contains(DateTime $dateTime)
  {
    // Iterate over the years
    foreach ($this->map as $year)
    {
      if ($year->getStartDate()->getTimestamp() <= $dateTime->getTimestamp() && $dateTime->getTimestamp() <= $year->getEndDate()->getTimestamp())
        return $year;
    }
    
    // Nothing found
    return null;
  }
}
