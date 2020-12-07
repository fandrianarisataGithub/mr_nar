<?php

namespace App\Repository;

use App\Entity\Fichier3;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Fichier3|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fichier3|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fichier3[]    findAll()
 * @method Fichier3[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class Fichier3Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fichier3::class);
    }

    // /**
    //  * @return Fichier3[] Returns an array of Fichier3 objects
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
    public function findOneBySomeField($value): ?Fichier3
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
