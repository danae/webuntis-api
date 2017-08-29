<?php
namespace Webuntis\Exception;

use Exception;

class UnauthorizedException extends Exception
{
  // Constructor
  public function __construct(string $message = "", int $code = 0, \Throwable $previous = null)
  {
    parent::__construct($message,$code,$previous);
  }
}
