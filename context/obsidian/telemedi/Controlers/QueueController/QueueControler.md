# QueueController

Kontroler obsługujący zarządzanie kolejkami w systemie Call Center.

## Endpointy

### GET /api/queues

Pobierz listę wszystkich kolejek, opcjonalnie z sortowaniem.

#### Request
```http
GET /api/queues
Accept: application/json
```

#### Request z sortowaniem
```http
GET /api/queues?sort_by=priority:desc
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
    "targetSuccessRatePercentage": 85,
    "agents": [
      {
        "id": 1,
        "fullName": "Jan Kowalski"
      },
      {
        "id": 3,
        "fullName": "Katarzyna Dąbrowska"
      }
    ]
  },
  {
    "id": 2,
    "queueName": "Wsparcie techniczne",
    "priority": 2,
    "description": "Problemy techniczne i wsparcie",
    "targetHandledCallsPerSlot": 8,
    "targetSuccessRatePercentage": 80,
    "agents": [
      {
        "id": 1,
        "fullName": "Jan Kowalski"
      },
      {
        "id": 2,
        "fullName": "Anna Nowak"
      }
    ]
  },
  {
    "id": 3,
    "queueName": "Reklamacje",
    "priority": 3,
    "description": "Obsługa reklamacji klientów",
    "targetHandledCallsPerSlot": 5,
    "targetSuccessRatePercentage": 90,
    "agents": [
      {
        "id": 2,
        "fullName": "Anna Nowak"
      }
    ]
  }
]
```

### GET /api/queues/{id}

Pobierz szczegóły konkretnej kolejki na podstawie ID.

#### Request
```http
GET /api/queues/1
Accept: application/json
```

#### Response
```json
Status: 200 OK
Content-Type: application/json

{
  "id": 1,
  "queueName": "Obsługa klienta",
  "priority": 1,
  "description": "Ogólne pytania klientów",
  "targetHandledCallsPerSlot": 10,
  "targetSuccessRatePercentage": 85,
  "agents": [
    {
      "id": 1,
      "fullName": "Jan Kowalski"
    },
    {
      "id": 3,
      "fullName": "Katarzyna Dąbrowska"
    }
  ]
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

### POST /api/queues

Utwórz nową kolejkę.

#### Request
```http
POST /api/queues
Content-Type: application/json

{
  "queueName": "VIP Support",
  "priority": 1,
  "description": "Obsługa klientów VIP",
  "targetHandledCallsPerSlot": 5,
  "targetSuccessRatePercentage": 95
}
```

#### Response
```json
Status: 201 Created
Content-Type: application/json

{
  "id": 4,
  "queueName": "VIP Support",
  "priority": 1,
  "description": "Obsługa klientów VIP",
  "targetHandledCallsPerSlot": 5,
  "targetSuccessRatePercentage": 95,
  "agents": []
}
```

### PUT|PATCH /api/queues/{id}

Aktualizuj dane istniejącej kolejki.

#### Request
```http
PUT /api/queues/1
Content-Type: application/json

{
  "queueName": "Obsługa klienta Premium",
  "targetHandledCallsPerSlot": 12
}
```

#### Response
```json
Status: 200 OK
Content-Type: application/json

{
  "id": 1,
  "queueName": "Obsługa klienta Premium",
  "priority": 1,
  "description": "Ogólne pytania klientów",
  "targetHandledCallsPerSlot": 12,
  "targetSuccessRatePercentage": 85,
  "agents": [
    {
      "id": 1,
      "fullName": "Jan Kowalski"
    },
    {
      "id": 3,
      "fullName": "Katarzyna Dąbrowska"
    }
  ]
}
```

### DELETE /api/queues/{id}

Usuń kolejkę na podstawie ID.

#### Request
```http
DELETE /api/queues/1
```

#### Response
```
Status: 204 No Content
```

### GET /api/queues/{id}/agents

Pobierz listę agentów przypisanych do konkretnej kolejki.

#### Request
```http
GET /api/queues/1/agents
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
    "isActive": true
  },
  {
    "id": 3,
    "fullName": "Katarzyna Dąbrowska",
    "email": "katarzyna.dabrowska@example.com",
    "isActive": true
  }
]
```

### POST /api/queues/{id}/agents

Przypisz agenta do kolejki.

#### Request
```http
POST /api/queues/1/agents
Content-Type: application/json

{
  "agent_id": 2
}
```

#### Response
```json
Status: 200 OK
Content-Type: application/json

{
  "id": 1,
  "queueName": "Obsługa klienta",
  "agents": [
    {
      "id": 1,
      "fullName": "Jan Kowalski"
    },
    {
      "id": 2,
      "fullName": "Anna Nowak"
    },
    {
      "id": 3,
      "fullName": "Katarzyna Dąbrowska"
    }
  ]
}
```

### DELETE /api/queues/{id}/agents/{agentId}

Usuń przypisanie agenta do kolejki.

#### Request
```http
DELETE /api/queues/1/agents/3
```

#### Response
```json
Status: 200 OK
Content-Type: application/json

{
  "id": 1,
  "queueName": "Obsługa klienta",
  "agents": [
    {
      "id": 1,
      "fullName": "Jan Kowalski"
    }
  ]
}
```






