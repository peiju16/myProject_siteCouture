<?php

namespace App\Repository;

use App\Entity\Formation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Formation>
 */
class FormationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Formation::class);
    }

     /**
     * Get products for the current user.
     *
     * @param int $userId The ID of the user to filter by.
     * @return array
     */
    public function findFormationsByUser($userId): array
    {
        $qb = $this->createQueryBuilder('formation')
            ->innerJoin('formation.users', 'u')  // Join the 'users' relation in Product entity
            ->where('u.id = :userId')    // Filter by the user's ID
            ->setParameter('userId', $userId)  // Set the parameter for user ID
         ;      
        $query = $qb->getQuery();
        $results = $query->getResult();
        return $results;
        
    }


    //    /**
    //     * @return Formation[] Returns an array of Formation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Formation
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
