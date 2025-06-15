# AgentActivityLogController

Kontroler obsługujący zarządzanie logami aktywności agentów w systemie Call Center.

## Endpointy

### GET /api/agent-activity-logs

Pobierz listę wszystkich logów aktywności agentów z opcjonalnym filtrowaniem.

#### Request
```http
GET /api/agent-activity-logs
Accept: application/json
```

#### Request z filtrami
```http
GET /api/agent-activity-logs?agent_id=1&queue_id=2&start_date=2025-05-01&end_date=2025-05-31
Accept: application/json
```

#### Response
```json
Status: 200 OK
Content-Type: application/json

[
  {
    "id": 1,
    "activityStartDatetime": "2025-05-15T09:30:00+00:00",
    "activityEndDatetime": "2025-05-15T09:45:12+00:00",
    "wasSuccessful": true,
    "activityReferenceId": "CALL-12345",
    "agent": {
      "id": 1,
      "fullName": "Jan Kowalski"
    },
    "queue": {
      "id": 1,
      "queueName": "Obsługa klienta"
    }
  },
  {
    "id": 2,
    "activityStartDatetime": "2025-05-15T10:05:00+00:00",
    "activityEndDatetime": "2025-05-15T10:20:45+00:00",
    "wasSuccessful": false,
    "activityReferenceId": "CALL-12346",
    "agent": {
      "id": 1,
      "fullName": "Jan Kowalski"
    },
    "queue": {
      "id": 1,
      "queueName": "Obsługa klienta"
    }
  }
]
```

### GET /api/agent-activity-logs/{id}

Pobierz szczegóły konkretnego logu aktywności na podstawie ID.

#### Request
```http
GET /api/agent-activity-logs/1
Accept: application/json
```

#### Response
```json
Status: 200 OK
Content-Type: application/json

{
  "id": 1,
  "activityStartDatetime": "2025-05-15T09:30:00+00:00",
  "activityEndDatetime": "2025-05-15T09:45:12+00:00",
  "wasSuccessful": true,
  "activityReferenceId": "CALL-12345",
  "agent": {
    "id": 1,
    "fullName": "Jan Kowalski"
  },
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
  "message": "Activity log not found"
}
```

### POST /api/agent-activity-logs

Utwórz nowy log aktywności agenta.

#### Request
```http
POST /api/agent-activity-logs
Content-Type: application/json

{
  "agent_id": 1,
  "queue_id": 2,
  "activityStartDatetime": "2025-05-16T09:30:00",
  "activityEndDatetime": "2025-05-16T09:42:23",
  "wasSuccessful": true,
  "activityReferenceId": "CALL-12350"
}
```

#### Response
```json
Status: 201 Created
Content-Type: application/json

{
  "id": 31,
  "activityStartDatetime": "2025-05-16T09:30:00+00:00",
  "activityEndDatetime": "2025-05-16T09:42:23+00:00",
  "wasSuccessful": true,
  "activityReferenceId": "CALL-12350",
  "agent": {
    "id": 1,
    "fullName": "Jan Kowalski"
  },
  "queue": {
    "id": 2,
    "queueName": "Wsparcie techniczne"
  }
}
```

#### Response dla błędnych danych
```json
Status: 400 Bad Request
Content-Type: application/json

{
  "message": "Missing required parameters: agent_id, queue_id"
}
```

### PUT|PATCH /api/agent-activity-logs/{id}

Aktualizuj dane istniejącego logu aktywności.

#### Request
```http
PUT /api/agent-activity-logs/1
Content-Type: application/json

{
  "wasSuccessful": false,
  "activityEndDatetime": "2025-05-15T09:50:00"
}
```

#### Response
```json
Status: 200 OK
Content-Type: application/json

{
  "id": 1,
  "activityStartDatetime": "2025-05-15T09:30:00+00:00",
  "activityEndDatetime": "2025-05-15T09:50:00+00:00",
  "wasSuccessful": false,
  "activityReferenceId": "CALL-12345",
  "agent": {
    "id": 1,
    "fullName": "Jan Kowalski"
  },
  "queue": {
    "id": 1,
    "queueName": "Obsługa klienta"
  }
}
```

### DELETE /api/agent-activity-logs/{id}

Usuń log aktywności na podstawie ID.

#### Request
```http
DELETE /api/agent-activity-logs/1
```

#### Response
```
Status: 204 No Content
```

### GET /api/agent-activity-logs/analytics/agent/{id}

Pobierz analitykę aktywności dla konkretnego agenta z opcjonalnym filtrowaniem po okresie.

#### Request
```http
GET /api/agent-activity-logs/analytics/agent/1?start_date=2025-01-01&end_date=2025-05-31
Accept: application/json
```

#### Response
```json
Status: 200 OK
Content-Type: application/json

{
  "agent": {
    "id": 1,
    "fullName": "Jan Kowalski"
  },
  "period": {
    "startDate": "2025-01-01",
    "endDate": "2025-05-31"
  },
  "overall": {
    "totalActivities": 120,
    "successfulActivities": 98,
    "successRate": 81.67,
    "avgDuration": 483
  },
  "queues": {
    "Obsługa klienta": {
      "totalActivities": 75,
      "successfulActivities": 65,
      "successRate": 86.67,
      "avgDuration": 450
    },
    "Wsparcie techniczne": {
      "totalActivities": 45,
      "successfulActivities": 33,
      "successRate": 73.33,
      "avgDuration": 540
    }
  }
}
``` 