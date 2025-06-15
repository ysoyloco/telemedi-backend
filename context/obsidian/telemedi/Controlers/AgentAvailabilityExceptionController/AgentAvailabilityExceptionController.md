# AgentAvailabilityExceptionController

Kontroler obsługujący zarządzanie wyjątkami dostępności agentów (urlopy, nieobecności) w systemie Call Center.

## Endpointy

### GET /api/agent-availability-exceptions

Pobierz listę wszystkich wyjątków dostępności z opcjonalnym filtrowaniem.

#### Request
```http
GET /api/agent-availability-exceptions
Accept: application/json
```

#### Request z filtrami
```http
GET /api/agent-availability-exceptions?agent_id=1&start_date=2025-06-01&end_date=2025-08-31
Accept: application/json
```

#### Response
```json
Status: 200 OK
Content-Type: application/json

[
  {
    "id": 1,
    "unavailableDatetimeStart": "2025-07-01T00:00:00+00:00",
    "unavailableDatetimeEnd": "2025-07-14T23:59:59+00:00",
    "agent": {
      "id": 1,
      "fullName": "Jan Kowalski"
    }
  },
  {
    "id": 2,
    "unavailableDatetimeStart": "2025-08-10T00:00:00+00:00",
    "unavailableDatetimeEnd": "2025-08-12T23:59:59+00:00",
    "agent": {
      "id": 1,
      "fullName": "Jan Kowalski"
    }
  }
]
```

### GET /api/agent-availability-exceptions/{id}

Pobierz szczegóły konkretnego wyjątku dostępności na podstawie ID.

#### Request
```http
GET /api/agent-availability-exceptions/1
Accept: application/json
```

#### Response
```json
Status: 200 OK
Content-Type: application/json

{
  "id": 1,
  "unavailableDatetimeStart": "2025-07-01T00:00:00+00:00",
  "unavailableDatetimeEnd": "2025-07-14T23:59:59+00:00",
  "agent": {
    "id": 1,
    "fullName": "Jan Kowalski"
  }
}
```

#### Response jeśli nie znaleziono
```json
Status: 404 Not Found
Content-Type: application/json

{
  "message": "Availability exception not found"
}
```

### POST /api/agent-availability-exceptions

Utwórz nowy wyjątek dostępności agenta.

#### Request
```http
POST /api/agent-availability-exceptions
Content-Type: application/json

{
  "agent_id": 2,
  "unavailableDatetimeStart": "2025-09-01T00:00:00",
  "unavailableDatetimeEnd": "2025-09-14T23:59:59"
}
```

#### Response
```json
Status: 201 Created
Content-Type: application/json

{
  "id": 11,
  "unavailableDatetimeStart": "2025-09-01T00:00:00+00:00",
  "unavailableDatetimeEnd": "2025-09-14T23:59:59+00:00",
  "agent": {
    "id": 2,
    "fullName": "Anna Nowak"
  }
}
```

#### Response dla błędnych danych
```json
Status: 400 Bad Request
Content-Type: application/json

{
  "message": "Missing required parameter: agent_id"
}
```

### PUT|PATCH /api/agent-availability-exceptions/{id}

Aktualizuj dane istniejącego wyjątku dostępności.

#### Request
```http
PUT /api/agent-availability-exceptions/1
Content-Type: application/json

{
  "unavailableDatetimeEnd": "2025-07-21T23:59:59"
}
```

#### Response
```json
Status: 200 OK
Content-Type: application/json

{
  "id": 1,
  "unavailableDatetimeStart": "2025-07-01T00:00:00+00:00",
  "unavailableDatetimeEnd": "2025-07-21T23:59:59+00:00",
  "agent": {
    "id": 1,
    "fullName": "Jan Kowalski"
  }
}
```

### DELETE /api/agent-availability-exceptions/{id}

Usuń wyjątek dostępności na podstawie ID.

#### Request
```http
DELETE /api/agent-availability-exceptions/1
```

#### Response
```
Status: 204 No Content
```

### GET /api/agent-availability-exceptions/check/availability

Sprawdź dostępność agenta w konkretnym czasie.

#### Request
```http
GET /api/agent-availability-exceptions/check/availability?agent_id=1&datetime=2025-07-05T10:00:00
Accept: application/json
```

#### Response
```json
Status: 200 OK
Content-Type: application/json

{
  "agent_id": 1,
  "datetime": "2025-07-05T10:00:00",
  "is_available": false
}
```

#### Response dla dostępnego agenta
```json
Status: 200 OK
Content-Type: application/json

{
  "agent_id": 1,
  "datetime": "2025-08-01T10:00:00",
  "is_available": true
}
```

#### Response dla błędnych danych
```json
Status: 400 Bad Request
Content-Type: application/json

{
  "message": "Missing required parameters: agent_id, datetime"
}
``` 