<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 0],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        /*$request = $event->getRequest();
        
        // Obsługuj tylko żądania do /api
        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $exception = $event->getThrowable();
        $response = new JsonResponse();

        // Ustaw kod statusu
        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $reasonCode = $this->getReasonCodeFromException($exception);
        } else {
            $response->setStatusCode(JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
            $reasonCode = 'INTERNAL_SERVER_ERROR';
        }

        // Ustaw dane odpowiedzi
        $response->setData([
            'error' => $this->getErrorMessage($exception),
            'reason_code' => $reasonCode,
            'message' => $exception->getMessage()
        ]);

        $event->setResponse($response);*/
        return;
    }

    private function getErrorMessage(\Throwable $exception): string
    {
        $errorClass = get_class($exception);
        $errorClass = substr($errorClass, strrpos($errorClass, '\\') + 1);
        
        switch ($errorClass) {
            case 'BadRequestHttpException':
                return 'Nieprawidłowe żądanie';
            case 'NotFoundHttpException':
                return 'Nie znaleziono zasobu';
            case 'ConflictHttpException':
                return 'Konflikt danych';
            case 'AccessDeniedHttpException':
                return 'Dostęp zabroniony';
            default:
                return 'Wystąpił błąd';
        }
    }

    private function getReasonCodeFromException(HttpExceptionInterface $exception): string
    {
        $errorClass = get_class($exception);
        $errorClass = substr($errorClass, strrpos($errorClass, '\\') + 1);
        
        switch ($errorClass) {
            case 'BadRequestHttpException':
                return 'INVALID_REQUEST';
            case 'NotFoundHttpException':
                return 'RESOURCE_NOT_FOUND';
            case 'ConflictHttpException':
                return 'DATA_CONFLICT';
            case 'AccessDeniedHttpException':
                return 'ACCESS_DENIED';
            default:
                return 'UNKNOWN_ERROR';
        }
    }
} 
 