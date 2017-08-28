<?php
namespace Webuntis\Model\Traits;

trait LongNameTrait
{
  // Variables
  protected $longName;
  
  // Return the long name
  public function getLongName(): string
  {
    return $this->longName;
  }
  
  // Set the long name
  public function setLongName(string $longName): self
  {
    $this->longName = $longName;
    return $this;
  }
}
