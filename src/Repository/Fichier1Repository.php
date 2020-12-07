<?php

namespace App\Repository;

use App\Entity\Fichier1;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Fichier1|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fichier1|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fichier1[]    findAll()
 * @method Fichier1[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class Fichier1Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fichier1::class);
    }

    // /**
    //  * @return Fichier1[] Returns an array of Fichier1 objects
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
    public function findOneBySomeField($value): ?Fichier1
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
