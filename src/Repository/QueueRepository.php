<?php

namespace App\Repository;

use App\Entity\Queue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Queue>
 *
 * @method Queue|null find($id, $lockMode = null, $lockVersion = null)
 * @method Queue|null findOneBy(array $criteria, array $orderBy = null)
 * @method Queue[]    findAll()
 * @method Queue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QueueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Queue::class);
    }

    /**
     * Znajdź wszystkie kolejki posortowane według priorytetu
     *
     * @return Queue[]
     */
    public function findAllSortedByPriority(): array
    {
        return $this->createQueryBuilder('q')
            ->orderBy('q.priority', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Znajdź kolejkę po ID wraz z agentami, którzy mają umiejętność jej obsługi
     */
    public function findWithAgents(int $id): ?Queue
    {
        return $this->createQueryBuilder('q')
            ->leftJoin('q.agents', 'a')
            ->addSelect('a')
            ->where('q.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
} 
 