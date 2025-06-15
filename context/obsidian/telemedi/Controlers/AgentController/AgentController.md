# AgentController

Kontroler obsługujący zarządzanie agentami w systemie Call Center.

## Endpointy

### GET /api/agents

Pobierz listę wszystkich agentów.

#### Request
```http
GET /api/agents
Accept: application/json
```

#### Response
```json
Status: 200 OK
Content-Type: application/json

[
  {
    "id": 1,
    "fullName": "Jan Kowalski",
    "email": "jan.kowalski@example.com",
    "defaultAvailabilityPattern": {
      "Mon": ["08:00-16:00"],
      "Tue": ["08:00-16:00"],
      "Wed": ["08:00-16:00"],
      "Thu": ["08:00-16:00"],
      "Fri": ["08:00-16:00"]
    },
    "isActive": true,
    "queues": [
      {
        "id": 1,
        "queueName": "Obsługa klienta",
        "priority": 1
      },
      {
        "id": 2,
        "queueName": "Wsparcie techniczne",
        "priority": 2
      }
    ]
  },
  {
    "id": 2,
    "fullName": "Anna Nowak",
    "email": "anna.nowak@example.com",
    "defaultAvailabilityPattern": {
      "Mon": ["09:00-17:00"],
      "Tue": ["09:00-17:00"],
      "Wed": ["09:00-17:00"],
      "Thu": ["09:00-17:00"],
      "Fri": ["09:00-17:00"]
    },
    "isActive": true,
    "queues": [
      {
        "id": 3,
        "queueName": "Reklamacje",
        "priority": 3
      }
    ]
  }
]
```

### GET /api/agents/{id}

Pobierz szczegóły konkretnego agenta na podstawie ID.

#### Request
```http
GET /api/agents/1
Accept: application/json
```

#### Response
```json
Status: 200 OK
Content-Type: application/json

{
  "id": 1,
  "fullName": "Jan Kowalski",
  "email": "jan.kowalski@example.com",
  "defaultAvailabilityPattern": {
    "Mon": ["08:00-16:00"],
    "Tue": ["08:00-16:00"],
    "Wed": ["08:00-16:00"],
    "Thu": ["08:00-16:00"],
    "Fri": ["08:00-16:00"]
  },
  "isActive": true,
  "queues": [
    {
      "id": 1,
      "queueName": "Obsługa klienta",
      "priority": 1
    },
    {
      "id": 2,
      "queueName": "Wsparcie techniczne",
      "priority": 2
    }
  ]
}
```

#### Response jeśli nie znaleziono
```json
Status: 404 Not Found
Content-Type: application/json

{
  "message": "Agent not found"
}
```

### POST /api/agents

Utwórz nowego agenta.

#### Request
```http
POST /api/agents
Content-Type: application/json

{
  "fullName": "Piotr Wiśniewski",
  "email": "piotr.wisniewski@example.com",
  "defaultAvailabilityPattern": {
    "Mon": ["08:00-16:00"],
    "Tue": ["08:00-16:00"],
    "Wed": ["08:00-16:00"],
    "Thu": ["08:00-16:00"],
    "Fri": ["08:00-16:00"]
  },
  "isActive": true,
  "queues": [1, 3]
}
```

#### Response
```json
Status: 201 Created
Content-Type: application/json

{
  "id": 5,
  "fullName": "Piotr Wiśniewski",
  "email": "piotr.wisniewski@example.com",
  "defaultAvailabilityPattern": {
    "Mon": ["08:00-16:00"],
    "Tue": ["08:00-16:00"],
    "Wed": ["08:00-16:00"],
    "Thu": ["08:00-16:00"],
    "Fri": ["08:00-16:00"]
  },
  "isActive": true,
  "queues": [
    {
      "id": 1,
      "queueName": "Obsługa klienta",
      "priority": 1
    },
    {
      "id": 3,
      "queueName": "Reklamacje",
      "priority": 3
    }
  ]
}
```

### PUT|PATCH /api/agents/{id}

Aktualizuj dane istniejącego agenta.

#### Request
```http
PUT /api/agents/1
Content-Type: application/json

{
  "fullName": "Jan Kowalski Zmodyfikowany",
  "email": "jan.kowalski.mod@example.com",
  "isActive": false
}
```

#### Response
```json
Status: 200 OK
Content-Type: application/json

{
  "id": 1,
  "fullName": "Jan Kowalski Zmodyfikowany",
  "email": "jan.kowalski.mod@example.com",
  "defaultAvailabilityPattern": {
    "Mon": ["08:00-16:00"],
    "Tue": ["08:00-16:00"],
    "Wed": ["08:00-16:00"],
    "Thu": ["08:00-16:00"],
    "Fri": ["08:00-16:00"]
  },
  "isActive": false,
  "queues": [
    {
      "id": 1,
      "queueName": "Obsługa klienta",
      "priority": 1
    },
    {
      "id": 2,
      "queueName": "Wsparcie techniczne",
      "priority": 2
    }
  ]
}
```

### DELETE /api/agents/{id}

Usuń agenta na podstawie ID.

#### Request
```http
DELETE /api/agents/1
```

#### Response
```
Status: 204 No Content
```

### GET /api/agents/{id}/queues

Pobierz kolejki, do których przypisany jest agent.

#### Request
```http
GET /api/agents/1/queues
Accept: application/json
```

#### Response
```json
Status: 200 OK
Content-Type: application/json

[
  {
    "id": 1,
    "queueName": "Obsługa klienta",
    "priority": 1,
    "description": "Ogólne pytania klientów",
    "targetHandledCallsPerSlot": 10,
    "targetSuccessRatePercentage": 85
  },
  {
    "id": 2,
    "queueName": "Wsparcie techniczne",
    "priority": 2,
    "description": "Problemy techniczne i wsparcie",
    "targetHandledCallsPerSlot": 8,
    "targetSuccessRatePercentage": 80
  }
]
```

### GET /api/agents/available/for-period

Pobierz dostępnych agentów dla danego okresu, opcjonalnie z filtrowaniem po kolejce.

#### Request
```http
GET /api/agents/available/for-period?start_date=2025-06-20T09:00:00&end_date=2025-06-20T10:00:00&queue_id=1
Accept: application/json
```

#### Response
```json
Status: 200 OK
Content-Type: application/json

[
  {
    "id": 1,
    "fullName": "Jan Kowalski",
    "email": "jan.kowalski@example.com",
    "defaultAvailabilityPattern": {
      "Mon": ["08:00-16:00"],
      "Tue": ["08:00-16:00"],
      "Wed": ["08:00-16:00"],
      "Thu": ["08:00-16:00"],
      "Fri": ["08:00-16:00"]
    },
    "isActive": true
  },
  {
    "id": 3,
    "fullName": "Katarzyna Dąbrowska",
    "email": "katarzyna.dabrowska@example.com",
    "defaultAvailabilityPattern": {
      "Mon": ["08:00-16:00"],
      "Tue": ["08:00-16:00"],
      "Wed": ["08:00-16:00"],
      "Thu": ["08:00-16:00"],
      "Fri": ["08:00-16:00"]
    },
    "isActive": true
  }
]
``` 