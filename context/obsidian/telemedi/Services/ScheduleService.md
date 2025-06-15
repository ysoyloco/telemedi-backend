## ScheduleService

Serwis do zarządzania grafikiem.

### Zależności
- [[ScheduleRepository]]
- [[AgentService]]
- EntityManagerInterface

### Metody
- **getScheduleEntries(Queue $queue, \DateTimeInterface $startDate, \DateTimeInterface $endDate)**: Pobiera wpisy grafiku dla danej kolejki i zakresu dat
- **getScheduleEntryById(int $id)**: Pobiera wpis grafiku po ID lub wyrzuca NotFoundHttpException
- **createScheduleEntry(Agent $agent, Queue $queue, \DateTimeInterface $scheduleDate, \DateTimeInterface $timeSlotStart, \DateTimeInterface $timeSlotEnd, string $entryStatus)**: Tworzy nowy wpis w grafiku z walidacją
- **updateScheduleEntry(int $scheduleId, ?Agent $agent, ?Queue $queue, ?\DateTimeInterface $scheduleDate, ?\DateTimeInterface $timeSlotStart, ?\DateTimeInterface $timeSlotEnd, ?string $entryStatus)**: Aktualizuje istniejący wpis grafiku
- **deleteScheduleEntry(int $scheduleId)**: Usuwa wpis grafiku
- **canAssignAgentToSlot(Agent $agent, Queue $queue, \DateTimeInterface $scheduleDate, \DateTimeInterface $timeSlotStart, \DateTimeInterface $timeSlotEnd)**: Sprawdza, czy agent może być przypisany do slotu 
 