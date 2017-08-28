<?php
namespace Webuntis\Collection;

use Webuntis\Model\HolidayModel;

class HolidayCollection extends Collection
{
  // Constructor
  public function __construct(array $elements = array())
  {
    parent::__construct($elements);
    
    // Sort the holidays
    uasort($this->map,[HolidayModel::class,'compare']);
  }
}
