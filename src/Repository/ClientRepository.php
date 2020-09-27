<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Client;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Client|null find($id, $lockMode = null, $lockVersion = null)
 * @method Client|null findOneBy(array $criteria, array $orderBy = null)
 * @method Client[]    findAll()
 * @method Client[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }
   /**
    * @return Client[] Returns an array of Client objects
    */
    public function chercherTous(User $user)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.user = :val')
            ->setParameter('val', $user)
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
    * @return Client[] Returns an array of Client objects
    */
    
    public function clientDuJour(User $user)
    {   
        $date = new \DateTime();
        $date = $date->format('Y-m-d');
        $demain = date('Y-m-d', strtotime("$date +1 day"));
        $hier = date('Y-m-d', strtotime("$date -1 day"));
        //dd($demain);
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
        SELECT * FROM `client` 
        WHERE user_id = :user_id_me and created_at < :date_dem and created_at > :date_omaly
        ORDER BY id DESC
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['user_id_me' => $user->getId(), 'date_dem' => $demain, 'date_omaly' => $hier]);
        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
    }

    /**
     * @return Client[] Returns an array of Client objects
     */

    public function valablePointage()
    {
        $date = new \DateTime();
        $date = $date->format('Y-m');
        //dd($demain);
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
        SELECT * FROM `client` 
        WHERE  date_debut <= :date_now and date_fin >= :date_now
        ORDER BY id DESC
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['date_now' => $date]);
        
        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
    }


    public function les_mpandainga(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT * FROM `client` 
        WHERE DATEDIFF(created_at, date_debut) > :marge
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['marge' => 93]);

        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
    }

    public function findOneByIdJoinedToPointage($clientId)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT c, p
            FROM App\Entity\Client c
            INNER JOIN c.pointages p
            WHERE c.id = :id'
        )->setParameter('id', $clientId);

        return $query->getOneOrNullResult();
    }


    /**
     * @return Client[] Returns an array of Client objects
     */
    public function countPresent($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.etat_client = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Client[] Returns an array of Client objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Client
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
