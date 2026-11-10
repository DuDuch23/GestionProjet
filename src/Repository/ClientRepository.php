<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Client>
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    /**
     * Clients with their projects loaded in the same query.
     *
     * Returns a QueryBuilder (not results) so a paginator can add LIMIT/OFFSET in SQL.
     */
    public function createWithProjectsQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.projects', 'p')
            ->addSelect('p')
            ->orderBy('c.lastName', 'ASC')
            ->addOrderBy('c.id', 'ASC'); // stable order between pages when last names are equal
    }
}
