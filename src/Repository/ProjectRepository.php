<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    /**
     * Projects that are not soft-deleted, with their client, most urgent first
     * (closest due date first, projects without a due date last).
     *
     * Returns a QueryBuilder (not results) so a paginator can add LIMIT/OFFSET in SQL.
     */
    public function createActiveQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->addSelect('c')
            ->join('p.client', 'c')
            ->addSelect('CASE WHEN p.dueDate IS NULL THEN 1 ELSE 0 END AS HIDDEN noDueDate')
            ->andWhere('p.deletedAt IS NULL')
            ->orderBy('noDueDate', 'ASC')
            ->addOrderBy('p.dueDate', 'ASC')
            ->addOrderBy('p.id', 'ASC'); // stable order between pages
    }
}
