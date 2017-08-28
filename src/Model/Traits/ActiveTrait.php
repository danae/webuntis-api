<?php
namespace Webuntis\Model\Traits;

trait ActiveTrait
{
  // Variables
  protected $active;
  
    // Return the active state
  public function isActive(): bool
  {
    return $this->active;
  }
  
  // Set the active state
  public function setActive(bool $active): self
  {
    $this->active = $active;
    return $this;
  }
}
