<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Service\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public const NO_FILTER = 0;
    public const FILTER_APPROVED = 1;
    public const FILTER_NOT_APPROVED = 2;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * Get the comments of a trick
     *
     * @param Paginator $commentsPaginator
     * @param Trick $trick
     * @param int $commentsPage
     * @param int $commentsPerPage
     * @return array
     */
    public function getTrickComments(
        Paginator $commentsPaginator,
        Trick $trick,
        int $commentsPage = 1,
        int $commentsPerPage = 10
    ): array
    {
        $commentsCount = $this->count(["trick" => $trick]);
        $commentsPaginator->update(
            $commentsPage,
            $commentsPerPage,
            $commentsCount
        );

        return $this->findBy(
            ["trick" => $trick],
            ["createdAt" => "DESC"],
            $commentsPaginator->itemsPerPage,
            $commentsPaginator->pagingOffset
        );
    }

    /**
     * Get comments
     *
     * @param Paginator $paginator
     * @param int $filterApproved
     * @param int $commentsPage
     * @param int $commentsPerPage
     * @return Comment[]
     */
    public function getComments(
        Paginator $paginator,
        int $filterApproved = self::FILTER_NOT_APPROVED,
        int $commentsPage = 1,
        int $commentsPerPage = 10
    )
    {
        $commentsCount = $this->count([]);
        $paginator->update(
            $commentsPage,
            $commentsPerPage,
            $commentsCount
        );

        if ($filterApproved !== self::NO_FILTER) {
            if ($filterApproved === self::FILTER_APPROVED) {
                $approved = true;
            } else {
                $approved = false;
            }
            $comments = $this->findBy(
                ['approved' => $approved],
                ["createdAt" => "DESC"],
                $paginator->itemsPerPage,
                $paginator->pagingOffset
            );
        } else {
            $comments = $this->findBy(
                [],
                ["createdAt" => "DESC"],
                $paginator->itemsPerPage,
                $paginator->pagingOffset
            );
        }

        return $comments;
    }

    /**
     * Get comments from ids
     *
     * @param array $commentIds
     * @return mixed
     */
    public function getCommentsFromIds(array $commentIds)
    {
        $queryBuilder = $this->createQueryBuilder("com")
            ->andWhere("com.id IN (:ids)")
            ->setParameter("ids", array_shift($commentIds)) // array_shift() to get to the ids
            ->getQuery();

        return $queryBuilder->execute();
    }
}
