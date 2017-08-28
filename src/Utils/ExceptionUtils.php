<?php
namespace Webuntis\Utils;

use JsonRPC\Exception\ResponseException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ExceptionUtils
{
  // Handle a thrown response exception
  public static function handleException(ResponseException $ex)
  {
    if ($ex->getCode() === -8520)
      return new AccessDeniedHttpException("Not authenticated");
    else if ($ex->getCode() === -8506)
      return new AccessDeniedHttpException("Bad credentials");
    else
      return $ex;
  }
}
