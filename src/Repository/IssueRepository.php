<?php

namespace App\Repository;

use App\Entity\Enum\IssueState;
use App\Entity\Issue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Issue>
 */
class IssueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Issue::class);
    }

    public function findByTitleLike(?string $search)
    {
        $qb = $this->createQueryBuilder('issue');

        if ($search) {
            $qb->andWhere('issue.title LIKE :pattern')
                ->setParameter('pattern', '%'.$search.'%');
        }

        return $qb->andWhere('issue.state = :state')
            ->setParameter('state', IssueState::open)
            ->addOrderBy('issue.createdAt', 'DESC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult()
        ;
    }
    public function findByTitleLikeBis(?string $search)
    {
        return $this->getEntityManager()->createQuery(
            'SELECT issue FROM '.Issue::class.' WHERE 1 '.
            'AND issue.state = \''.IssueState::open->value.'\' '.
            'ORDER BY issue.createdAt DESC'
        )->setMaxResults(20)->getResult();
    }

//    /**
//     * @return Issue[] Returns an array of Issue objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Issue
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
