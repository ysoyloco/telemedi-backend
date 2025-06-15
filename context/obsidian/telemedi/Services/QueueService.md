## QueueService

Serwis do zarządzania kolejkami.

### Zależności
- [[QueueRepository]]

### Metody
- **getAllQueuesSortedByPriority()**: Pobiera wszystkie kolejki posortowane według priorytetu
- **getQueueById(int $id)**: Pobiera kolejkę po ID lub wyrzuca NotFoundHttpException, jeśli nie istnieje
- **getQueueWithAgents(int $id)**: Pobiera kolejkę po ID wraz z przypisanymi agentami (wykorzystuje join) 
 