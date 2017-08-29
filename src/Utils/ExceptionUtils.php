<?php
namespace Webuntis\Utils;

use JsonRPC\Exception\ResponseException;
use Webuntis\Exception\JsonRpcException;
use Webuntis\Exception\UnauthorizedException;

class ExceptionUtils
{
  // Handle a thrown response exception
  public static function handleException(ResponseException $ex)
  {
    if ($ex->getCode() === -8520)
      return new UnauthorizedException("Not authenticated");
    else if ($ex->getCode() === -8506)
      return new UnauthorizedException("Bad credentials");
    else
      return new JsonRpcException($ex->getMessage(),$ex->getCode(),$ex);
  }
}
