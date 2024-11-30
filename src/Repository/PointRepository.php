<?php

namespace App\Repository;

use App\Entity\Formation;
use App\Entity\Point;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Point>
 */
class PointRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Point::class);
    }

     /**
     * Get average points for a given formation
     *
     * @param Formation $formation
     * @return float|null Average points or null if no points exist
     */
    public function getAveragePointForFormation(Formation $formation): ?float
    {
        $qb = $this->createQueryBuilder('point p')
            ->select('AVG(p.point) as avgPoint')
            ->andWhere('p.formation = :formation')
            ->setParameter('formation', $formation);

        $result = $qb->getQuery()->getSingleScalarResult();

        return $result ? (float)$result : null;
    }

    //    /**
    //     * @return Point[] Returns an array of Point objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Point
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
