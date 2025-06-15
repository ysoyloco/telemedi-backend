# QueueLoadTrendController

Kontroler obsługujący zarządzanie trendami obciążenia kolejek w systemie Call Center.

## Endpointy

### GET /api/queue-load-trends

Pobierz listę wszystkich trendów obciążenia z opcjonalnym filtrowaniem.

#### Request
```http
GET /api/queue-load-trends
Accept: application/json
```

#### Request z filtrami
```http
GET /api/queue-load-trends?queue_id=1&year=2025&quarter=2&metric_name=average_call_time
Accept: application/json
```

#### Response
```json
Status: 200 OK
Content-Type: application/json

[
  {
    "id": 1,
    "year": 2025,
    "quarter": 2,
    "calculationDate": "2025-06-30T00:00:00+00:00",
    "metricName": "average_call_time",
    "metricValue": "420",
    "additionalDescription": "Średni czas rozmowy w sekundach",
    "queue": {
      "id": 1,
      "queueName": "Obsługa klienta"
    }
  },
  {
    "id": 2,
    "year": 2025,
    "quarter": 2,
    "calculationDate": "2025-06-30T00:00:00+00:00",
    "metricName": "success_rate_percentage",
    "metricValue": "85",
    "additionalDescription": "Procent połączeń zakończonych sukcesem",
    "queue": {
      "id": 1,
      "queueName": "Obsługa klienta"
    }
  }
]
```

### GET /api/queue-load-trends/{id}

Pobierz szczegóły konkretnego trendu obciążenia na podstawie ID.

#### Request
```http
GET /api/queue-load-trends/1
Accept: application/json
```

#### Response
```json
Status: 200 OK
Content-Type: application/json

{
  "id": 1,
  "year": 2025,
  "quarter": 2,
  "calculationDate": "2025-06-30T00:00:00+00:00",
  "metricName": "average_call_time",
  "metricValue": "420",
  "additionalDescription": "Średni czas rozmowy w sekundach",
  "queue": {
    "id": 1,
    "queueName": "Obsługa klienta"
  }
}
```

#### Response jeśli nie znaleziono
```json
Status: 404 Not Found
Content-Type: application/json

{
  "message": "Queue load trend not found"
}
```

### POST /api/queue-load-trends

Utwórz nowy trend obciążenia kolejki.

#### Request
```http
POST /api/queue-load-trends
Content-Type: application/json

{
  "queue_id": 1,
  "year": 2025,
  "quarter": 3,
  "metricName": "calls_per_hour",
  "metricValue": "15",
  "calculationDate": "2025-09-30T00:00:00",
  "additionalDescription": "Średnia liczba połączeń na godzinę"
}
```

#### Response
```json
Status: 201 Created
Content-Type: application/json

{
  "id": 25,
  "year": 2025,
  "quarter": 3,
  "calculationDate": "2025-09-30T00:00:00+00:00",
  "metricName": "calls_per_hour",
  "metricValue": "15",
  "additionalDescription": "Średnia liczba połączeń na godzinę",
  "queue": {
    "id": 1,
    "queueName": "Obsługa klienta"
  }
}
```

#### Response dla błędnych danych
```json
Status: 400 Bad Request
Content-Type: application/json

{
  "message": "Missing required parameters"
}
```

### PUT|PATCH /api/queue-load-trends/{id}

Aktualizuj dane istniejącego trendu obciążenia.

#### Request
```http
PUT /api/queue-load-trends/1
Content-Type: application/json

{
  "metricValue": "380",
  "additionalDescription": "Zaktualizowany średni czas rozmowy w sekundach"
}
```

#### Response
```json
Status: 200 OK
Content-Type: application/json

{
  "id": 1,
  "year": 2025,
  "quarter": 2,
  "calculationDate": "2025-06-30T00:00:00+00:00",
  "metricName": "average_call_time",
  "metricValue": "380",
  "additionalDescription": "Zaktualizowany średni czas rozmowy w sekundach",
  "queue": {
    "id": 1,
    "queueName": "Obsługa klienta"
  }
}
```

### DELETE /api/queue-load-trends/{id}

Usuń trend obciążenia na podstawie ID.

#### Request
```http
DELETE /api/queue-load-trends/1
```

#### Response
```
Status: 204 No Content
```

### GET /api/queue-load-trends/analytics/queue/{id}

Pobierz analitykę metryk dla konkretnej kolejki w czasie.

#### Request
```http
GET /api/queue-load-trends/analytics/queue/1
Accept: application/json
```

#### Response
```json
Status: 200 OK
Content-Type: application/json

{
  "queue": {
    "id": 1,
    "name": "Obsługa klienta"
  },
  "metrics": {
    "average_call_time": {
      "description": "Średni czas rozmowy w sekundach",
      "values": {
        "2024-Q1": "450",
        "2024-Q2": "435",
        "2024-Q3": "425",
        "2024-Q4": "415",
        "2025-Q1": "410",
        "2025-Q2": "380"
      }
    },
    "success_rate_percentage": {
      "description": "Procent połączeń zakończonych sukcesem",
      "values": {
        "2024-Q1": "78",
        "2024-Q2": "80",
        "2024-Q3": "82",
        "2024-Q4": "83",
        "2025-Q1": "84",
        "2025-Q2": "85"
      }
    },
    "calls_per_hour": {
      "description": "Średnia liczba połączeń na godzinę",
      "values": {
        "2024-Q1": "12",
        "2024-Q2": "13",
        "2024-Q3": "14",
        "2024-Q4": "15",
        "2025-Q1": "14",
        "2025-Q2": "15"
      }
    }
  }
}
```

#### Response jeśli nie znaleziono
```json
Status: 404 Not Found
Content-Type: application/json

{
  "message": "Queue not found"
}
``` 