<?php

namespace Webuntis\Model;

use Webuntis\Model\Traits\LongNameTrait;
use Webuntis\Model\Traits\NameTrait;

class DepartmentModel extends Model
{
  use NameTrait;
  use LongNameTrait;
  
  // Convert to string
  public function __toString()
  {
    return $this->getLongName();
  }
}
