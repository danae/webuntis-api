<?php
namespace Webuntis\Model\Traits;

use DateTime;

trait DateTimePeriodTrait
{
  // Variables
  protected $startTime;
  protected $endTime;
  
  // Return the start date
  public function getStartTime(): DateTime
  {
    return $this->startTime;
  }
  
  // Set the start time
  public function setStartTime(DateTime $startTime): self
  {
    $this->startTime = $startTime;
    return $this;
  }
  
  // Return the end time
  public function getEndTime(): DateTime
  {
    return $this->endTime;
  }
  
  // Set the end time
  public function setEndTime(DateTime $endTime): self
  {
    $this->endTime = $endTime;
    return $this;
  }
}
