# ScheduleController

Kontroler obsługujący zarządzanie grafikami w systemie Call Center.

## Endpointy

### GET /api/schedules

Pobierz listę wszystkich wpisów grafiku z opcjonalnym filtrowaniem.

#### Request
```http
GET /api/schedules
Accept: application/json
```

#### Request z filtrami
```http
GET /api/schedules?agent_id=1&queue_id=2&start_date=2025-06-01&end_date=2025-06-30
Accept: application/json
```

#### Response
```json
Status: 200 OK
Content-Type: application/json

[
  {
    "id": 1,
    "scheduleDate": "2025-06-15",
    "timeSlotStart": "09:00:00",
    "timeSlotEnd": "10:00:00",
    "entryStatus": "scheduled",
    "agent": {
      "id": 1,
      "fullName": "Jan Kowalski",
      "email": "jan.kowalski@example.com"
    },
    "queue": {
      "id": 2,
      "queueName": "Wsparcie techniczne"
    }
  },
  {
    "id": 2,
    "scheduleDate": "2025-06-16",
    "timeSlotStart": "09:00:00",
    "timeSlotEnd": "10:00:00",
    "entryStatus": "scheduled",
    "agent": {
      "id": 1,
      "fullName": "Jan Kowalski",
      "email": "jan.kowalski@example.com"
    },
    "queue": {
      "id": 2,
      "queueName": "Wsparcie techniczne"
    }
  }
]
```

### GET /api/schedules/{id}

Pobierz szczegóły konkretnego wpisu grafiku na podstawie ID.

#### Request
```http
GET /api/schedules/1
Accept: application/json
```

#### Response
```json
Status: 200 OK
Content-Type: application/json

{
  "id": 1,
  "scheduleDate": "2025-06-15",
  "timeSlotStart": "09:00:00",
  "timeSlotEnd": "10:00:00",
  "entryStatus": "scheduled",
  "agent": {
    "id": 1,
    "fullName": "Jan Kowalski",
    "email": "jan.kowalski@example.com"
  },
  "queue": {
    "id": 2,
    "queueName": "Wsparcie techniczne"
  }
}
```

#### Response jeśli nie znaleziono
```json
Status: 404 Not Found
Content-Type: application/json

{
  "message": "Schedule entry not found"
}
```

### POST /api/schedules

Utwórz nowy wpis grafiku.

#### Request
```http
POST /api/schedules
Content-Type: application/json

{
  "agent_id": 1,
  "queue_id": 2,
  "schedule_date": "2025-06-20",
  "time_slot_start": "14:00:00",
  "time_slot_end": "15:00:00",
  "entry_status": "scheduled"
}
```

#### Response
```json
Status: 201 Created
Content-Type: application/json

{
  "id": 25,
  "scheduleDate": "2025-06-20",
  "timeSlotStart": "14:00:00",
  "timeSlotEnd": "15:00:00",
  "entryStatus": "scheduled",
  "agent": {
    "id": 1,
    "fullName": "Jan Kowalski",
    "email": "jan.kowalski@example.com"
  },
  "queue": {
    "id": 2,
    "queueName": "Wsparcie techniczne"
  }
}
```

#### Response dla konfliktu
```json
Status: 409 Conflict
Content-Type: application/json

{
  "message": "Agent Jan Kowalski ma już zaplanowane zadanie w tym czasie"
}
```

### PUT|PATCH /api/schedules/{id}

Aktualizuj dane istniejącego wpisu grafiku.

#### Request
```http
PUT /api/schedules/1
Content-Type: application/json

{
  "entry_status": "completed",
  "time_slot_end": "10:15:00"
}
```

#### Response
```json
Status: 200 OK
Content-Type: application/json

{
  "id": 1,
  "scheduleDate": "2025-06-15",
  "timeSlotStart": "09:00:00",
  "timeSlotEnd": "10:15:00",
  "entryStatus": "completed",
  "agent": {
    "id": 1,
    "fullName": "Jan Kowalski",
    "email": "jan.kowalski@example.com"
  },
  "queue": {
    "id": 2,
    "queueName": "Wsparcie techniczne"
  }
}
```

### DELETE /api/schedules/{id}

Usuń wpis grafiku na podstawie ID.

#### Request
```http
DELETE /api/schedules/1
```

#### Response
```
Status: 204 No Content
```

### GET /api/calendar-view

Pobierz widok kalendarza dla konkretnej kolejki i okresu czasu.

#### Request
```http
GET /api/calendar-view?queue_id=1&start_date=2025-06-10&end_date=2025-06-16
Accept: application/json
```

#### Response
```json
Status: 200 OK
Content-Type: application/json

{
  "queue_info": {
    "id": 1,
    "queueName": "Obsługa klienta",
    "priority": 1,
    "targetHandledCallsPerSlot": 10
  },
  "date_range": {
    "start_date": "2025-06-10",
    "end_date": "2025-06-16"
  },
  "schedule_entries": [
    {
      "id": 5,
      "scheduleDate": "2025-06-10",
      "timeSlotStart": "09:00:00",
      "timeSlotEnd": "10:00:00",
      "entryStatus": "scheduled",
      "agent": {
        "id": 1,
        "fullName": "Jan Kowalski"
      }
    },
    {
      "id": 6,
      "scheduleDate": "2025-06-10",
      "timeSlotStart": "10:00:00",
      "timeSlotEnd": "11:00:00",
      "entryStatus": "scheduled",
      "agent": {
        "id": 3,
        "fullName": "Katarzyna Dąbrowska"
      }
    },
    {
      "id": 7,
      "scheduleDate": "2025-06-11",
      "timeSlotStart": "09:00:00",
      "timeSlotEnd": "10:00:00",
      "entryStatus": "scheduled",
      "agent": {
        "id": 1,
        "fullName": "Jan Kowalski"
      }
    }
  ]
}
```

### GET /api/slot-proposals

Pobierz propozycje agentów dla określonego slotu czasowego.

#### Request
```http
GET /api/slot-proposals?queue_id=1&slot_start_datetime=2025-06-20T09:00:00&slot_end_datetime=2025-06-20T10:00:00
Accept: application/json
```

#### Response
```json
Status: 200 OK
Content-Type: application/json

{
  "slot_info": {
    "queue_id": 1,
    "queueName": "Obsługa klienta",
    "start_datetime": "2025-06-20T09:00:00",
    "end_datetime": "2025-06-20T10:00:00"
  },
  "suggested_agents": [
    {
      "id": 1,
      "fullName": "Jan Kowalski",
      "email": "jan.kowalski@example.com",
      "performance_data": {
        "success_rate": 85.5,
        "average_call_time": 420,
        "activity_count_last_month": 45
      }
    },
    {
      "id": 3,
      "fullName": "Katarzyna Dąbrowska",
      "email": "katarzyna.dabrowska@example.com",
      "performance_data": {
        "success_rate": 88.2,
        "average_call_time": 380,
        "activity_count_last_month": 38
      }
    }
  ]
}
```

### POST /api/schedules/generate

Wygeneruj propozycje grafiku dla podanego zakresu dat i kolejek.

#### Request
```http
POST /api/schedules/generate
Content-Type: application/json

{
  "start_date": "2025-06-20",
  "end_date": "2025-06-22",
  "queue_ids": [1, 3],
  "shift_start_hour": "09:00:00",
  "shift_end_hour": "17:00:00",
  "slot_duration_minutes": 60
}
```

#### Response
```json
Status: 200 OK
Content-Type: application/json

[
  {
    "id": 30,
    "scheduleDate": "2025-06-20",
    "timeSlotStart": "09:00:00",
    "timeSlotEnd": "10:00:00",
    "entryStatus": "Zaproponowany_Systemowo",
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
    "id": 31,
    "scheduleDate": "2025-06-20",
    "timeSlotStart": "10:00:00",
    "timeSlotEnd": "11:00:00",
    "entryStatus": "Zaproponowany_Systemowo",
    "agent": {
      "id": 3,
      "fullName": "Katarzyna Dąbrowska"
    },
    "queue": {
      "id": 1,
      "queueName": "Obsługa klienta"
    }
  },
  // Więcej wygenerowanych wpisów grafiku...
]
```
