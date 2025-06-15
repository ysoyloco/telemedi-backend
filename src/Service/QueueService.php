<?php

namespace App\Service;

use App\Entity\Queue;
use App\Repository\QueueRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class QueueService
{
    public function __construct(
        private QueueRepository $queueRepository
    ) {
    }

    /**
     * Pobierz wszystkie kolejki posortowane wg priorytetu
     *
     * @return Queue[]
     */
    public function getAllQueuesSortedByPriority(): array
    {
        return $this->queueRepository->findAllSortedByPriority();
    }

    /**
     * Pobierz kolejkę po ID lub wyrzuć wyjątek, jeśli nie istnieje
     *
     * @throws NotFoundHttpException jeśli kolejka nie zostanie znaleziona
     */
    public function getQueueById(int $id): Queue
    {
        $queue = $this->queueRepository->find($id);

        if (!$queue) {
            throw new NotFoundHttpException(sprintf('Kolejka o ID %d nie została znaleziona', $id));
        }

        return $queue;
    }

    /**
     * Pobierz kolejkę po ID wraz z przypisanymi agentami (wykorzystuje join)
     *
     * @throws NotFoundHttpException jeśli kolejka nie zostanie znaleziona
     */
    public function getQueueWithAgents(int $id): Queue
    {
        $queue = $this->queueRepository->findWithAgents($id);

        if (!$queue) {
            throw new NotFoundHttpException(sprintf('Kolejka o ID %d nie została znaleziona', $id));
        }

        return $queue;
    }
}
 