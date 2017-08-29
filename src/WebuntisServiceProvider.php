<?php
namespace Webuntis;

use JsonRPC\Exception\ResponseException;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class WebuntisServiceProvider implements ServiceProviderInterface
{
  // Register to the application
  public function register(Container $app)
  {
    // Return a new endpoint
    $app['webuntis'] = $app->protect(function(string $server, string $school, Request $request) 
    {
      // Check if credentials are given
      if ($request->getUser() === null)
        throw new UnauthorizedHttpException("Basic realm={$server}:{$school}",'You must provide your credentials');
      
        // Create a new endpoint
      try
      {
        $webuntis = new Webuntis($server,$school);
        $webuntis->login($request->getUser(),$request->getPassword());
        return $webuntis;
      }
      catch (ResponseException $ex)
      {
        if ($ex->getCode() === -8504)
          throw new UnauthorizedHttpException("Basic realm={$server}:{$school}",'You provided invalid credentials');
        else if ($ex->getCode() === -8520)
          throw new UnauthorizedHttpException("Basic realm={$server}:{$school}",'You are not authorized');
        else
          throw $ex;
      }
    });
  }
}
