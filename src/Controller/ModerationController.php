<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Member;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\MemberRepository;
use App\Service\Paginator;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ModerationController extends AbstractController
{
    public const COMMENTS_PER_PAGE = 25;

    /**
     * Show moderation panel
     *
     * @Route("/moderation-panel/{page}", name="moderation_panel", requirements={"page": "\d+"})
     *
     * @param CommentRepository $commentRepository
     * @param MemberRepository $memberRepository
     * @param Paginator $paginator
     * @param int|null $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function moderationPanel(
        CommentRepository $commentRepository,
        MemberRepository $memberRepository,
        Paginator $paginator,
        ?int $page = null)
    {
        // Access control
        $this->denyAccessUnlessGranted(Member::ROLE_MODERATOR);

        $comments = $commentRepository->getComments(
            $memberRepository,
            $paginator,
            CommentRepository::FILTER_NOT_APPROVED,
            $page ?? 1,
            self::COMMENTS_PER_PAGE
        );

        return $this->render('moderation/moderationPanel.html.twig', [
            'comments' => $comments,
            'paginator' => $paginator
        ]);
    }

    /**
     * Edit a comment
     *
     * @Route("/moderation-panel/edit-comment/{id}/page{page}", name="moderation_edit_comment", requirements={"id": "\d+", "page": "\d+"})
     *
     * @param Request $request
     * @param ObjectManager $manager
     * @param Comment $comment
     * @param int|null $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editComment(
        Request $request,
        ObjectManager $manager,
        Comment $comment,
        ?int $page = null
    )
    {
        $this->denyAccessUnlessGranted(Member::ROLE_MODERATOR);

        $editCommentForm = $this->createForm(CommentType::class, $comment);
        $editCommentForm->handleRequest($request);

        if ($editCommentForm->isSubmitted() && $editCommentForm->isValid()) {
            $manager->persist($comment);
            $manager->flush();
            $this->addFlash("notice", "Le commentaire du {$comment->getCreatedAt()->format('d/m/Y H:i')} a été modifié");
        }

        return $this->redirectToRoute("moderation_panel", ['page' => $page]);
    }

    /**
     * Delete a comment
     *
     * @Route("/moderation-panel/delete-comment/{id}/page{page}", name="moderation_delete_comment", requirements={"id": "\d+", "page": "\d+"})
     *
     * @param ObjectManager $manager
     * @param Comment $comment
     * @param int|null $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteComment(
        ObjectManager $manager,
        Comment $comment,
        ?int $page = null
    )
    {
        $this->denyAccessUnlessGranted(Member::ROLE_MODERATOR);

        $manager->remove($comment);
        $manager->flush();

        $this->addFlash("notice", "Le commentaire de {$comment->getAuthor()->getName()} a été supprimé");

        return $this->redirectToRoute("moderation_panel", ['page' => $page]);
    }

    /**
     * Approve or disapprove a comment
     *
     * @Route("/moderation-panel/approve-comment/{id}/approved{approved}/page{page}", name="moderation_approve_comment", requirements={"id": "\d+", "page": "\d+", "approved": "[0-1]{1}"})
     *
     * @param ObjectManager $manager
     * @param Comment $comment
     * @param bool $approved
     * @param int|null $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function approveComment(
        ObjectManager $manager,
        Comment $comment,
        bool $approved,
        ?int $page = null
    )
    {
        $this->denyAccessUnlessGranted(Member::ROLE_MODERATOR);

        $comment->setApproved($approved);
        $manager->flush();

        if ($approved) {
            $this->addFlash("notice", "Le commentaire de {$comment->getAuthor()->getName()} a été approuvé");
        } else {
            $this->addFlash("notice", "Le commentaire de {$comment->getAuthor()->getName()} n'est plus approuvé");
        }

        return $this->redirectToRoute("moderation_panel", ['page' => $page]);
    }
}
