<?php

namespace App\Service;

use App\Entity\Agent;
use App\Entity\Queue;
use App\Repository\AgentActivityLogRepository;
use App\Repository\AgentAvailabilityExceptionRepository;
use App\Repository\AgentRepository;
use App\Repository\ScheduleRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AgentService
{
    public function __construct(
        private AgentRepository $agentRepository,
        private AgentAvailabilityExceptionRepository $agentAvailabilityExceptionRepository,
        private ScheduleRepository $scheduleRepository,
        private AgentActivityLogRepository $agentActivityLogRepository
    ) {
    }

    /**
     * Pobierz agenta po ID lub wyrzuć wyjątek, jeśli nie istnieje
     *
     * @throws NotFoundHttpException jeśli agent nie zostanie znaleziony
     */
    public function getAgentById(int $id): Agent
    {
        $agent = $this->agentRepository->find($id);

        if (!$agent) {
            throw new NotFoundHttpException(sprintf('Agent o ID %d nie został znaleziony', $id));
        }

        return $agent;
    }

    /**
     * Pobierz aktywnych agentów, którzy mogą obsługiwać daną kolejkę
     *
     * @return Agent[]
     */
    public function getActiveAgentsForQueue(Queue $queue): array
    {
        return $this->agentRepository->findActiveAgentsForQueue($queue);
    }

    /**
     * Sprawdź dostępność agenta w danym czasie
     * Bierze pod uwagę domyślny wzorzec dostępności, wyjątki dostępności i istniejące wpisy w grafiku
     */
    public function isAgentAvailableAt(Agent $agent, \DateTimeInterface $dateTime): bool
    {
        // 1. Sprawdź, czy agent jest aktywny
        if (!$agent->isIsActive()) {
            return false;
        }

        // 2. Sprawdź wzorzec dostępności domyślnej
        if (!$this->isAgentAvailableByDefaultPattern($agent, $dateTime)) {
            return false;
        }

        // 3. Sprawdź wyjątki dostępności (urlopy, itp.)
        if ($this->agentAvailabilityExceptionRepository->hasAvailabilityConflict($agent, $dateTime)) {
            return false;
        }

        // 4. Sprawdź, czy agent nie jest już zaplanowany w tym czasie
        $scheduleDate = (clone $dateTime)->setTime(0, 0);
        $timeSlotStart = new \DateTime($dateTime->format('H:i:s'));
        $timeSlotEnd = (clone $timeSlotStart)->modify('+1 hour'); // Zakładamy sloty godzinowe
        
        return !$this->scheduleRepository->hasAgentScheduleConflict($agent, $scheduleDate, $timeSlotStart, $timeSlotEnd);
    }

    /**
     * Sprawdź, czy agent jest dostępny według domyślnego wzorca dostępności
     */
    private function isAgentAvailableByDefaultPattern(Agent $agent, \DateTimeInterface $dateTime): bool
    {
        $pattern = $agent->getDefaultAvailabilityPattern();
        
        if (empty($pattern)) {
            return false; // Brak wzorca oznacza brak dostępności
        }

        $dayOfWeek = $dateTime->format('D');
        $time = $dateTime->format('H:i');

        // Sprawdź, czy dany dzień tygodnia jest w ogóle w schemacie
        if (!isset($pattern[$dayOfWeek]) || !is_array($pattern[$dayOfWeek])) {
            return false;
        }

        // Sprawdź, czy czas mieści się w którymś z przedziałów dla danego dnia
        foreach ($pattern[$dayOfWeek] as $timeRange) {
            if (!is_string($timeRange)) {
                continue;
            }
            
            $parts = explode('-', $timeRange);
            if (count($parts) !== 2) {
                continue;
            }
            
            $start = trim($parts[0]);
            $end = trim($parts[1]);
            
            if ($time >= $start && $time < $end) {
                return true;
            }
        }

        return false;
    }

    /**
     * Pobierz prostą metrykę wydajności agenta dla danej kolejki
     */
    public function getAgentPerformanceMetric(Agent $agent, Queue $queue): string
    {
        return $this->agentActivityLogRepository->getSimplePerformanceMetric($agent, $queue);
    }

    /**
     * Pobierz powód niedostępności agenta w danym czasie (jeśli istnieje)
     * Zwraca null, jeśli agent jest dostępny
     */
    public function getAgentUnavailabilityReason(Agent $agent, \DateTimeInterface $dateTime): ?string
    {
        // 1. Sprawdź, czy agent jest aktywny
        if (!$agent->isIsActive()) {
            return 'Agent nieaktywny';
        }

        // 2. Sprawdź wzorzec dostępności domyślnej
        if (!$this->isAgentAvailableByDefaultPattern($agent, $dateTime)) {
            return 'Poza standardowymi godzinami pracy';
        }

        // 3. Pobierz wyjątki dostępności, które kolidują z danym czasem
        $conflictingExceptions = $this->agentAvailabilityExceptionRepository->findConflictingExceptions(
            $agent, 
            $dateTime,
            $dateTime
        );
        
        if (!empty($conflictingExceptions)) {
            return 'Urlop lub inna nieobecność';
        }

        // 4. Sprawdź, czy agent nie jest już zaplanowany w tym czasie
        $scheduleDate = (clone $dateTime)->setTime(0, 0);
        $timeSlotStart = new \DateTime($dateTime->format('H:i:s'));
        $timeSlotEnd = (clone $timeSlotStart)->modify('+1 hour'); // Zakładamy sloty godzinowe
        
        if ($this->scheduleRepository->hasAgentScheduleConflict($agent, $scheduleDate, $timeSlotStart, $timeSlotEnd)) {
            return 'Już pracuje w tym czasie';
        }

        return null; // Agent jest dostępny
    }
} 
 