<?php
namespace AlthingiAggregator;

use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $app = $e->getApplication();
        $eventManager = $app->getEventManager();
        $sharedEventManager  = $eventManager->getSharedManager();
        $serviceManager = $app->getServiceManager();

        /** @var  $logger \Psr\Log\LoggerInterface */
        $logger = $serviceManager->get('Psr\Log');

        set_error_handler(function ($level, $message, $file, $line) use ($logger) {
            $minErrorLevel = error_reporting();
            if ($minErrorLevel & $level) {
                throw new \ErrorException($message, $code = 0, $level, $file, $line);
            }
            // return false to not continue native handler
            return false;
        });

        $sharedEventManager->attach('Zend\Mvc\Application', MvcEvent::EVENT_DISPATCH_ERROR, function (MvcEvent $event) use ($logger) {
             if (!$event->isError()) {
                 return;
             }

             $exception = $event->getParam('exception');
             if ($exception instanceof \Exception) {
                 $logger->error($exception->getMessage(), [
                     'code' => $exception->getCode(),
                     'file' => $exception->getFile(),
                     'line' => $exception->getLine(),
                 ]);
             }

             $event->stopPropagation(true);
             $message = $event->getError();
             $logger->error($message);

         }, 1000);

        register_shutdown_function(function () use ($logger) {
            // get error
            $error = error_get_last();
            // check and allow only errors
            if (null === $error || $error['type'] !== E_ERROR) {
                return;
            }

            // clean any previous output from buffer
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            // generate unique reference for this error
            $chars = md5(uniqid('', true));
            $errorReference = substr($chars, 2, 2) . substr($chars, 12, 2) . substr($chars, 26, 2);

            $extras = [
                'reference' => $errorReference,
                'file' => $error['file'],
                'line' => $error['line']
            ];

            $logger->error($error['message'], $extras);
            die(1);
        });
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return include __DIR__ . '/config/service.config.php';
    }
}
