<?php

namespace App\Repository;

use App\Entity\Formation;
use App\Entity\Reservation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function countByFormation(Formation $formation): int
    {
        return $this->createQueryBuilder('r')
            ->where('r.formation = :formation')
            ->setParameter('formation', $formation)
            ->select('COUNT(r)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Finds formations reserved by a specific user.
     *
     * @param User $user
     * @return array Returns an array of Formation objects.
     */
    public function findFormationsByUser($user): array
    {
        $query = $this->getEntityManager()->createQuery(
            'SELECT f
             FROM App\Entity\Formation f
             JOIN f.reservations r
             WHERE r.user = :user'
        )->setParameter('user', $user);
        
        return $query->getResult();
        
    }



    //    /**
    //     * @return Reservation[] Returns an array of Reservation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Reservation
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
