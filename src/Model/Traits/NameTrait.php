<?php
namespace Webuntis\Model\Traits;

trait NameTrait
{
  // Variables
  protected $name;
  
  // Return the name
  public function getName(): string
  {
    return $this->name;
  }
  
  // Set the name
  public function setName(string $name): self
  {
    $this->name = $name;
    return $this;
  }
}
