## AgentService

Serwis do zarządzania agentami i ich dostępnością.

### Zależności
- [[AgentRepository]]
- [[AgentAvailabilityExceptionRepository]]
- [[ScheduleRepository]]
- [[AgentActivityLogRepository]]

### Metody
- **getAgentById(int $id)**: Pobiera agenta po ID lub wyrzuca NotFoundHttpException, jeśli nie istnieje
- **getActiveAgentsForQueue(Queue $queue)**: Pobiera aktywnych agentów, którzy mogą obsługiwać daną kolejkę
- **isAgentAvailableAt(Agent $agent, \DateTimeInterface $dateTime)**: Sprawdza dostępność agenta w danym czasie, uwzględniając domyślny wzorzec, wyjątki i grafik
- **getAgentPerformanceMetric(Agent $agent, Queue $queue)**: Pobiera metrykę wydajności agenta dla danej kolejki
- **getAgentUnavailabilityReason(Agent $agent, \DateTimeInterface $dateTime)**: Zwraca powód niedostępności agenta w danym czasie 
 