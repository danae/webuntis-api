<?php
require('vendor/autoload.php');

use JDesrosiers\Silex\Provider\CorsServiceProvider;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Webuntis\WebuntisControllerProvider;

// Create an application
$app = new Application();

// Add exception handling
$app->error(function(Exception $ex) {
  if (is_a($ex,HttpException::class))
  {
    if ($ex->getMessage() === '')
      $message = Response::$statusTexts[$ex->getStatusCode()];
    else
      $message = $ex->getMessage();
    return new JsonResponse(['error' => $message ?? $ex->getStatusCode()],$ex->getStatusCode(),$ex->getHeaders());
  }
  else
    return new JsonResponse(['error' => $ex->getMessage(),'exceptionThrown' => get_class($ex)],500);
});

// Pretty print the JSON response
$app->after(function(Request $request, Response $response) {
  if ($response instanceof JsonResponse)
    $response->setEncodingOptions(JSON_PRETTY_PRINT);
  return $response;
});

// Add the CORS Service and add support for CORS requests
$app->register(new CorsServiceProvider);
$app['cors.allowCredentials'] = true;
$app->after($app['cors']);

// Add a serializer service
$app['serializer'] = function() {
  return new Serializer([new DateTimeNormalizer(DateTime::ISO8601),new GetSetMethodNormalizer],[new JsonEncoder]);
};

// Mount the controllers
$app->mount('/{server}/{school}',new WebuntisControllerProvider($app['serializer']));

// Run the application
$app->run();