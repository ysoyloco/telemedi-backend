## ApiExceptionSubscriber

Klasa obsługująca wyjątki dla endpointów API.

### Implementowane interfejsy
- EventSubscriberInterface

### Funkcjonalności
- Przekształca wyjątki HTTP w ustandaryzowane odpowiedzi JSON
- Obsługuje różne kody błędów (400, 404, 409, 500)
- Format odpowiedzi: `{error: string, reason_code: string, message: string}`
- Obsługiwane wyjątki:
  - BadRequestHttpException -> INVALID_REQUEST (400)
  - NotFoundHttpException -> RESOURCE_NOT_FOUND (404)
  - ConflictHttpException -> DATA_CONFLICT (409)
  - Inne wyjątki -> UNKNOWN_ERROR (500) 
 