<?php

namespace App\Repository;

use App\Entity\ParticipantResponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ParticipantResponse|null find($id, $lockMode = null, $lockVersion = null)
 * @method ParticipantResponse|null findOneBy(array $criteria, array $orderBy = null)
 * @method ParticipantResponse[]    findAll()
 * @method ParticipantResponse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParticipantResponseRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ParticipantResponse::class);
    }

    // /**
    //  * @return AttendeeResponse[] Returns an array of AttendeeResponse objects
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
    public function findOneBySomeField($value): ?AttendeeResponse
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
