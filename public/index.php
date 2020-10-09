<?php

chdir(dirname(__DIR__));
set_error_handler("exception_error_handler");
require_once './vendor/autoload.php';

use App\Event\ErrorEvent;
use App\Event\ExceptionEvent;
use Laminas\Diactoros\ServerRequest;
use App\Lib\CommandLine;
use Laminas\Diactoros\Uri;
use App\Lib\Router\RouteCollection;
use Laminas\ServiceManager\ServiceManager;
use Psr\EventDispatcher\EventDispatcherInterface;

try {
    $arguments = CommandLine::parseArgs($argv);
    $command = array_shift($arguments);

    $serviceManager = new ServiceManager(require_once './config/service.php');
    $request = (new ServerRequest())->withUri(new Uri("/{$command}"));
    foreach($arguments as $key => $value) {
        $request = $request->withAttribute($key, $value);
    }

    $routerCollection = new RouteCollection();
    $routerCollection->setRouteConfig(require_once './config/routes.php');
    $response = $routerCollection->match($request);

    try {
        $response = $serviceManager->get($response->getHandler())->handle($request);
    } catch (App\Extractor\Exception $exception) {
        $serviceManager->get(EventDispatcherInterface::class)
            ->dispatch(new ExceptionEvent($request, $exception));
    } catch(Throwable $exception) {
        $serviceManager->get(EventDispatcherInterface::class)
            ->dispatch(new ErrorEvent($request, $exception));
    }
    exit(0);
} catch (Throwable $exception) { //@todo
    echo $exception->getMessage();
    exit(1);
}


function exception_error_handler($severity, $message, $file, $line)
{
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
}
