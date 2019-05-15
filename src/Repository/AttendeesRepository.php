<?php

namespace App\Repository;

use App\Entity\Attendees;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Attendees|null find($id, $lockMode = null, $lockVersion = null)
 * @method Attendees|null findOneBy(array $criteria, array $orderBy = null)
 * @method Attendees[]    findAll()
 * @method Attendees[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AttendeesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Attendees::class);
    }

    // /**
    //  * @return Attendees[] Returns an array of Attendees objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Attendees
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
