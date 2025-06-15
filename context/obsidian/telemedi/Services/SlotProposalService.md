## SlotProposalService

Serwis do generowania propozycji agentów dla slotów.

### Zależności
- [[AgentRepository]]
- [[AgentService]]
- [[AgentActivityLogRepository]]
- [[ScheduleRepository]]

### Metody
- **generateProposalsForSlot(Queue $queue, \DateTimeInterface $slotStartDatetime, \DateTimeInterface $slotEndDatetime)**: Generuje listę propozycji agentów dla danego slotu z rankingiem opartym na dostępności i wydajności 
 