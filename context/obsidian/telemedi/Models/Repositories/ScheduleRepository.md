[[Schedule]]

- **findByQueueAndDateRange(Queue $queue, \DateTimeInterface $startDate, \DateTimeInterface $endDate)**: Pobiera wpisy grafiku dla danej kolejki i zakresu dat
- **hasAgentScheduleConflict(Agent $agent, \DateTimeInterface $scheduleDate, \DateTimeInterface $timeSlotStart, \DateTimeInterface $timeSlotEnd, ?int $excludeScheduleId = null)**: Sprawdza, czy agent ma już wpis w grafiku w danym czasie
- **findAgentSchedulesByDateRange(Agent $agent, \DateTimeInterface $startDate, \DateTimeInterface $endDate)**: Pobiera wszystkie wpisy grafiku agenta w podanym zakresie dat 
 