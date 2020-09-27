<?php

namespace App\Repository;


use App\Entity\Client;
use App\Entity\Pointage;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Pointage|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pointage|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pointage[]    findAll()
 * @method Pointage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PointageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pointage::class);
    }
    /**
     * @return Pointage[] Returns an array of Pointage objects findOneByNom
     */
    public function tous_les_pointages($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.client_id = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Pointage[] Returns an array of Pointage objects 
     */
    public function findOneByNom($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.nom = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
    public function findLastDatePointageFor($client_id): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT MAX(created_at) 
        FROM pointage 
        WHERE client_id = :client_id
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['client_id' => $client_id]);

        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
    }

    // SELECT MAX(created_at) FROM `pointage` 

    public function lastPointagePourUnClient(Client $client)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT * 
        FROM pointage 
        WHERE client_id = :client_id
        ORDER BY id DESC
        LIMIT 1
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['client_id' => $client->getId()]);

        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetch();
    }
    
     /**
     * @return Pointage[] Returns an array of Pointage objects
     */
    public function findByAnneeActuelle($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.annee_actuelle = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    /**
     * @return Pointage[] Returns an array of Pointage objects  
     */
    
    public function lastPointage()
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Pointage[] Returns an array of Pointage objects  
     */

    public function findlesTwelveLastPointage()
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(36)
            ->getQuery()
            ->getResult();
    }



    // /**
    //  * @return Pointage[] Returns an array of Pointage objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Pointage
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
