<?php

namespace App\Repository;

use App\Entity\Trick;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Trick|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trick|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trick[]    findAll()
 * @method Trick[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrickRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Trick::class);
    }

    public function getByName(string $trickName)
    {
        return $this->findOneBy(['name' => $trickName]);
    }

    public function countTricks(string $trickName)
    {
        return $this->count(["name" => $trickName]);
    }

    public function hasDuplicate(Trick $trick)
    {
        $count = $this->countTricks($trick->getName());

        if ($count > 1) {
            return true;

        } elseif ($count === 1) {
            $duplicate = $this->getByName($trick->getName());

            if ($trick->getId() !== $duplicate->getId()) {
                return true;
            }
        }

        return false;
    }
}
