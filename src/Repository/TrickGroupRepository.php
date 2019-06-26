<?php

namespace App\Repository;

use App\Entity\TrickGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TrickGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method TrickGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method TrickGroup[]    findAll()
 * @method TrickGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrickGroupRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TrickGroup::class);
    }
}
