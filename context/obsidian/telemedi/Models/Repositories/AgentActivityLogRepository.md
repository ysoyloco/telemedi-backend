[[AgentActivityLog]]

- **findRecentActivitiesForAgentAndQueue(Agent $agent, Queue $queue, int $limit = 10)**: Pobiera ostatnie aktywności agenta dla danej kolejki
- **calculateAgentPerformanceForQueue(Agent $agent, Queue $queue)**: Oblicza procentową wydajność agenta (procent udanych połączeń)
- **getSimplePerformanceMetric(Agent $agent, Queue $queue)**: Zwraca prostą tekstową ocenę wydajności agenta ('Świetny', 'Dobry', 'OK', 'Wymaga poprawy') 
 