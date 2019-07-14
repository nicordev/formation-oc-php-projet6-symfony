<?php

namespace App\Repository;

use App\Entity\Image;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Image|null find($id, $lockMode = null, $lockVersion = null)
 * @method Image|null findOneBy(array $criteria, array $orderBy = null)
 * @method Image[]    findAll()
 * @method Image[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Image::class);
    }

    public function getUrls()
    {
        $urls = [];
        $connection = $this->getEntityManager()->getConnection();
        $sqlRequest = "SELECT url FROM image";
        $requestUrls = $connection->query($sqlRequest);

        while ($data = $requestUrls->fetch(\PDO::FETCH_NUM)) {
            $urls[] = $data[0];
        }

        return $urls;
    }
}
