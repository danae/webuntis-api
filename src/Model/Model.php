<?php
namespace Webuntis\Model;

abstract class Model implements ModelInterface
{
  // Variables
  protected $id;
  
  // Return the identifier
  public function getId(): int
  {
    return $this->id;
  }
  
  // Set the identifier
  public function setId(int $id): self
  {
    $this->id = $id;
    return $this;
  }
}
