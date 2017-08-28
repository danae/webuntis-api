<?php
namespace Webuntis\Model\Traits;

use DateTime;

trait DatePeriodTrait
{
  // Variables
  protected $startDate;
  protected $endDate;
  
  // Return the start date
  public function getStartDate(): DateTime
  {
    return $this->startDate;
  }
  
  // Set the start date
  public function setStartDate(DateTime $startDate): self
  {
    $this->startDate = $startDate;
    return $this;
  }
  
  // Return the end date
  public function getEndDate(): DateTime
  {
    return $this->endDate;
  }
  
  // Set the end date
  public function setEndDate(DateTime $endDate): self
  {
    $this->endDate = $endDate;
    return $this;
  }
}
