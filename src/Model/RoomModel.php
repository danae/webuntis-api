<?php
namespace Webuntis\Model;

use Webuntis\Model\Traits\ActiveTrait;
use Webuntis\Model\Traits\LongNameTrait;
use Webuntis\Model\Traits\NameTrait;

class RoomModel extends Model
{
  use NameTrait;
  use LongNameTrait;
  use ActiveTrait;
  
  // Variables
  protected $building;
  
  // Return the building
  public function getBuilding(): string
  {
    return $this->building;
  }
  
  // Set the building
  public function setBuilding(string $building): self
  {
    $this->building = $building;
    return $this;
  }
  
  // Convert to string
  public function __toString()
  {
    return $this->getName();
  }
}
