### argumenty
Request $request

### implementacja

#### getCalendarView

```php
public getCalendarView(Request $request)
{
	$start_date = $request->get['start_date'];
	$end_date = $request->get['end_date'];
	$queueId = $request->get['queue_id'];
}

```

### return jsonResponse

```json
{
  "queue_info": {
    "id": 1,
    "queue_name": "Kolejka 1",
    "priority": 1
  },
  "schedule_entries": [
    {
      "id": 101,
      "agent_id": 1,
      "agent_full_name": "Jan Kowalski",
      "queue_id": 1,
      "schedule_date": "2025-06-10",
      "time_slot_start": "09:00:00",
      "time_slot_end": "10:00:00",
      "entry_status": "Potwierdzony_Przez_Managera",
      "title": "Jan K. (Kolejka 1)",
      "start": "2025-06-10T09:00:00Z",
      "end": "2025-06-10T10:00:00Z",
      "resourceId": 1
    }
}
```
