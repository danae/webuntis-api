<?php
namespace Webuntis\Model;

use Webuntis\Model\Traits\ActiveTrait;
use Webuntis\Model\Traits\LongNameTrait;
use Webuntis\Model\Traits\NameTrait;

class SubjectModel extends Model
{
  use NameTrait;
  use LongNameTrait;
  use ActiveTrait;
  
  // Convert to string
  public function __toString()
  {
    return $this->getLongName();
  }
}
