<?php

namespace App\Service;

use App\Entity\Agent;
use App\Entity\Queue;
use App\Entity\Schedule;
use App\Repository\ScheduleRepository;
use App\Service\AgentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ScheduleService
{
    public function __construct(
        private ScheduleRepository $scheduleRepository,
        private AgentService $agentService,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Pobierz wpisy grafiku z opcjonalnym filtrowaniem
     *
     * @param int|null $agentId ID agenta (opcjonalne)
     * @param int|null $queueId ID kolejki (opcjonalne)
     * @param \DateTimeInterface|null $startDate Data początkowa (opcjonalna)
     * @param \DateTimeInterface|null $endDate Data końcowa (opcjonalna)
     * @return Schedule[]
     */
    public function getSchedules(
        ?int $agentId = null,
        ?int $queueId = null,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null
    ): array {
        return $this->scheduleRepository->findByFilters($agentId, $queueId, $startDate, $endDate);
    }

    /**
     * Pobierz wpisy grafiku dla danej kolejki i zakresu dat
     *
     * @return Schedule[]
     */
    public function getScheduleEntries(Queue $queue, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->scheduleRepository->findByQueueAndDateRange($queue, $startDate, $endDate);
    }

    /**
     * Pobierz wpis grafiku po ID lub wyrzuć wyjątek, jeśli nie istnieje
     *
     * @throws NotFoundHttpException jeśli wpis nie zostanie znaleziony
     */
    public function getScheduleEntryById(int $id): Schedule
    {
        $schedule = $this->scheduleRepository->find($id);

        if (!$schedule) {
            throw new NotFoundHttpException(sprintf('Wpis grafiku o ID %d nie został znaleziony', $id));
        }

        return $schedule;
    }

    /**
     * Utwórz nowy wpis grafiku
     *
     * @throws BadRequestHttpException jeśli dane są niepoprawne
     * @throws ConflictHttpException jeśli agent jest niedostępny lub już zaplanowany
     */
    public function createScheduleEntry(
        Agent $agent,
        Queue $queue,
        \DateTimeInterface $scheduleDate,
        \DateTimeInterface $timeSlotStart,
        \DateTimeInterface $timeSlotEnd,
        string $entryStatus
    ): Schedule {
        // Walidacja
        $this->validateScheduleData($agent, $queue, $scheduleDate, $timeSlotStart, $timeSlotEnd);

        // Sprawdź, czy agent jest dostępny
        $slotStartDateTime = $this->combineDateTime($scheduleDate, $timeSlotStart);
        if (!$this->agentService->isAgentAvailableAt($agent, $slotStartDateTime)) {
            $reason = $this->agentService->getAgentUnavailabilityReason($agent, $slotStartDateTime);
            throw new ConflictHttpException(sprintf(
                'Agent %s jest niedostępny w tym czasie. Powód: %s',
                $agent->getFullName(),
                $reason ?? 'Nieznany'
            ));
        }

        // Sprawdź, czy agent ma już wpis w grafiku w tym czasie
        if ($this->scheduleRepository->hasAgentScheduleConflict($agent, $scheduleDate, $timeSlotStart, $timeSlotEnd)) {
            throw new ConflictHttpException(sprintf(
                'Agent %s ma już zaplanowane zadanie w tym czasie',
                $agent->getFullName()
            ));
        }

        // Utwórz nowy wpis
        $schedule = new Schedule();
        $schedule->setAgent($agent);
        $schedule->setQueue($queue);
        $schedule->setScheduleDate($scheduleDate);
        $schedule->setTimeSlotStart($timeSlotStart);
        $schedule->setTimeSlotEnd($timeSlotEnd);
        $schedule->setEntryStatus($entryStatus);

        // Zapisz
        $this->entityManager->persist($schedule);
        $this->entityManager->flush();

        return $schedule;
    }

    /**
     * Aktualizuj istniejący wpis grafiku
     *
     * @throws NotFoundHttpException jeśli wpis nie zostanie znaleziony
     * @throws BadRequestHttpException jeśli dane są niepoprawne
     * @throws ConflictHttpException jeśli agent jest niedostępny lub już zaplanowany
     */
    public function updateScheduleEntry(
        int $scheduleId,
        ?Agent $agent = null,
        ?Queue $queue = null,
        ?\DateTimeInterface $scheduleDate = null,
        ?\DateTimeInterface $timeSlotStart = null,
        ?\DateTimeInterface $timeSlotEnd = null,
        ?string $entryStatus = null
    ): Schedule {
        $schedule = $this->getScheduleEntryById($scheduleId);

        // Określ nowe wartości lub zachowaj istniejące
        $newAgent = $agent ?? $schedule->getAgent();
        $newQueue = $queue ?? $schedule->getQueue();
        $newScheduleDate = $scheduleDate ?? $schedule->getScheduleDate();
        $newTimeSlotStart = $timeSlotStart ?? $schedule->getTimeSlotStart();
        $newTimeSlotEnd = $timeSlotEnd ?? $schedule->getTimeSlotEnd();
        
        // Jeśli zmieniono agenta, datę lub czas - zwaliduj
        if ($agent !== null || $scheduleDate !== null || $timeSlotStart !== null || $timeSlotEnd !== null) {
            $this->validateScheduleData($newAgent, $newQueue, $newScheduleDate, $newTimeSlotStart, $newTimeSlotEnd);
            
            // Sprawdź, czy agent jest dostępny (tylko jeśli zmieniono agenta, datę lub czas)
            $slotStartDateTime = $this->combineDateTime($newScheduleDate, $newTimeSlotStart);
            
            if (!$this->agentService->isAgentAvailableAt($newAgent, $slotStartDateTime)) {
                $reason = $this->agentService->getAgentUnavailabilityReason($newAgent, $slotStartDateTime);
                throw new ConflictHttpException(sprintf(
                    'Agent %s jest niedostępny w tym czasie. Powód: %s',
                    $newAgent->getFullName(),
                    $reason ?? 'Nieznany'
                ));
            }
            
            // Sprawdź, czy agent ma już wpis w grafiku w tym czasie (z pominięciem aktualnego wpisu)
            if ($this->scheduleRepository->hasAgentScheduleConflict(
                $newAgent, 
                $newScheduleDate, 
                $newTimeSlotStart, 
                $newTimeSlotEnd,
                $scheduleId
            )) {
                throw new ConflictHttpException(sprintf(
                    'Agent %s ma już zaplanowane zadanie w tym czasie',
                    $newAgent->getFullName()
                ));
            }
        }

        // Aktualizuj dane
        $schedule->setAgent($newAgent);
        $schedule->setQueue($newQueue);
        $schedule->setScheduleDate($newScheduleDate);
        $schedule->setTimeSlotStart($newTimeSlotStart);
        $schedule->setTimeSlotEnd($newTimeSlotEnd);
        
        if ($entryStatus !== null) {
            $schedule->setEntryStatus($entryStatus);
        }

        // Zapisz
        $this->entityManager->flush();

        return $schedule;
    }

    /**
     * Usuń wpis grafiku
     *
     * @throws NotFoundHttpException jeśli wpis nie zostanie znaleziony
     */
    public function deleteScheduleEntry(int $scheduleId): void
    {
        $schedule = $this->getScheduleEntryById($scheduleId);
        
        $this->entityManager->remove($schedule);
        $this->entityManager->flush();
    }

    /**
     * Sprawdź, czy agent może być przypisany do danego slotu
     */
    public function canAssignAgentToSlot(
        Agent $agent,
        Queue $queue,
        \DateTimeInterface $scheduleDate,
        \DateTimeInterface $timeSlotStart,
        \DateTimeInterface $timeSlotEnd
    ): bool {
        try {
            $this->validateScheduleData($agent, $queue, $scheduleDate, $timeSlotStart, $timeSlotEnd);
            
            $slotStartDateTime = $this->combineDateTime($scheduleDate, $timeSlotStart);
            if (!$this->agentService->isAgentAvailableAt($agent, $slotStartDateTime)) {
                return false;
            }
            
            if ($this->scheduleRepository->hasAgentScheduleConflict($agent, $scheduleDate, $timeSlotStart, $timeSlotEnd)) {
                return false;
            }
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Waliduj dane wpisu grafiku
     *
     * @throws BadRequestHttpException jeśli dane są niepoprawne
     */
    private function validateScheduleData(
        Agent $agent,
        Queue $queue,
        \DateTimeInterface $scheduleDate,
        \DateTimeInterface $timeSlotStart,
        \DateTimeInterface $timeSlotEnd
    ): void {
        // Sprawdź, czy agent jest aktywny
        if (!$agent->isIsActive()) {
            throw new BadRequestHttpException(sprintf('Agent %s jest nieaktywny', $agent->getFullName()));
        }

        // Sprawdź, czy agent ma umiejętność obsługi tej kolejki
        if (!$agent->getQueues()->contains($queue)) {
            throw new BadRequestHttpException(sprintf(
                'Agent %s nie ma umiejętności obsługi kolejki %s',
                $agent->getFullName(),
                $queue->getQueueName()
            ));
        }

        // Sprawdź, czy czas rozpoczęcia jest przed czasem zakończenia
        if ($timeSlotStart >= $timeSlotEnd) {
            throw new BadRequestHttpException('Czas rozpoczęcia musi być przed czasem zakończenia');
        }
    }

    /**
     * Połącz datę i czas w jeden obiekt DateTime
     */
    private function combineDateTime(\DateTimeInterface $date, \DateTimeInterface $time): \DateTime
    {
        $dateTime = new \DateTime($date->format('Y-m-d') . ' ' . $time->format('H:i:s'));
        return $dateTime;
    }
} 
 