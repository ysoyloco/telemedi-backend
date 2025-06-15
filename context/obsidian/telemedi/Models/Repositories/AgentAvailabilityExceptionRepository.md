[[AgentAvailabilityException]]

- **findConflictingExceptions(Agent $agent, \DateTimeInterface $startDatetime, \DateTimeInterface $endDatetime)**: Znajduje wyjątki dostępności agenta, które kolidują z podanym zakresem dat
- **hasAvailabilityConflict(Agent $agent, \DateTimeInterface $dateTime)**: Sprawdza, czy agent ma wyjątek dostępności w podanym czasie
- **findByAgentAndDateRange(Agent $agent, \DateTimeInterface $startDate, \DateTimeInterface $endDate)**: Pobiera wszystkie wyjątki dostępności agenta dla podanego okresu 
 