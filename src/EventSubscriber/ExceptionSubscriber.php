<?php

namespace App\EventSubscriber;

use App\Exception\DefaultExceptionInterface;
use App\Service\Helper\ApplicationEnvironment;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

/**
 * This subscriber handles exceptions thrown by the application.
 * It returns a JSON response for API requests and a nice error page for other requests.
 * 
 * In production environment, it does not expose any exception messages to the user.
 * This is really critical, thus controlled through our own exception interface DefaultExceptionInterface (which must be implemented by all custom exceptions and must implement a prod env error message).
 */
class ExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Environment $twig,
    ) { }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $exceptionMessage = $exception->getMessage();
        $statusCode = $exception instanceof HttpException ? $exception->getStatusCode() : 500;
        $request = $event->getRequest();
        $isAPIRequest = \str_starts_with($request->get('_route'), 'api_') || \str_starts_with($request->getPathInfo(), '/api/');
        
        // in prod environment we do not want to expose any exception messages as they could reveal sensitive information;
        // if we want to display production error messages (e.g. for debugging / displaying to the user) throw an exception which implements DefaultExceptionInterface.
        if (ApplicationEnvironment::isProdEnv()) {
            $exceptionMessage = $exception instanceof DefaultExceptionInterface ? $exception->getProductionMessage() : 'An error occurred';
        }

        $exceptionPayload = [
            'message' => $exceptionMessage,
            'code' => $statusCode,
        ];

        if (ApplicationEnvironment::isDevEnv()) {
            $exceptionPayload['trace'] = $exception->getTrace();
            $exceptionPayload['class'] = \get_class($exception);
        }

        if ($isAPIRequest) {
            // if it is an API request we return a JSON response
            $event->setResponse(new JsonResponse($exceptionPayload, status: $statusCode));
        } else {
            // if it is not an API request show a nice error page, made with Twig
            $twigRenderResponse = $this->twig->render('error/error.html.twig', $exceptionPayload);
            $event->setResponse(new Response($twigRenderResponse, status: $statusCode));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
