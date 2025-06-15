### GET /queues

pobiera listę dostępnych kolejek

### Response

[
  {
    "id": 1,
    "queue_name": "Sprzedaż VIP",
    "priority": 1,
    "target_handled_calls_per_slot": 15,
    "target_success_rate_percentage": 92.50
  },
  {
    "id": 2,
    "queue_name": "Obsługa Techniczna",
    "priority": 2,
    "target_handled_calls_per_slot": 10,
    "target_success_rate_percentage": 85.00
  },
  {
    "id": 3,
    "queue_name": "Reklamacje",
    "priority": 3,
    "target_handled_calls_per_slot": null,
    "target_success_rate_percentage": 90.00
  }
]