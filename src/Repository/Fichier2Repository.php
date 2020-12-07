<?php

namespace App\Repository;

use App\Entity\Fichier2;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Fichier2|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fichier2|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fichier2[]    findAll()
 * @method Fichier2[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class Fichier2Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fichier2::class);
    }

    // /**
    //  * @return Fichier2[] Returns an array of Fichier2 objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Fichier2
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
