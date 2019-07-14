<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Member;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Service\Paginator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ModerationController extends AbstractController
{
    public const COMMENTS_PER_PAGE = 25;

    public const ROUTE_MODERATION_PANEL = "moderation_panel";
    public const ROUTE_EDIT_COMMENT = "moderation_edit_comment";
    public const ROUTE_DELETE_COMMENT = "moderation_delete_comment";

    public const TASK_APPROVE = "approve";
    public const TASK_DISAPPROVE = "disapprove";
    public const TASK_DELETE = "delete";

    /**
     * Show moderation panel
     *
     * @Route("/moderation-panel", name="moderation_panel_simple", requirements={"page": "\d+"})
     * @Route("/moderation-panel/page{page}", name="moderation_panel", requirements={"page": "\d+"})
     * @Route("/moderation-panel/page{page}/filter{filter}", name="moderation_panel_filter", requirements={"page": "\d+"})
     *
     * @param CommentRepository $commentRepository
     * @param Paginator $paginator
     * @param int|null $page
     * @param int|null $filter
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function moderationPanel(
        CommentRepository $commentRepository,
        Paginator $paginator,
        ?int $page = null,
        ?int $filter = null
    ) {
        // Access control
        $this->denyAccessUnlessGranted(Member::ROLE_MODERATOR);

        // Filter
        $session = $this->get("session");
        if ($filter !== null) {
            $session->set("moderation_panel_filter", $filter);
        }

        $comments = $commentRepository->getComments(
            $paginator,
            $session->get('moderation_panel_filter') ?? CommentRepository::FILTER_NOT_APPROVED,
            $page ?? 1,
            self::COMMENTS_PER_PAGE
        );

        $commentFormsViews = $this->createCommentFormsViews($comments);

        return $this->render('moderation/moderationPanel.html.twig', [
            'comments' => $comments,
            'paginator' => $paginator,
            'commentEditForms' => $commentFormsViews
        ]);
    }

    /**
     * Edit a comment
     *
     * @Route("/moderation-panel/edit-comment/{id}/page{page}", name="moderation_edit_comment", requirements={"id": "\d+", "page": "\d+"})
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param Comment $comment
     * @param int|null $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editComment(
        Request $request,
        EntityManagerInterface $manager,
        Comment $comment,
        ?int $page = null
    ) {
        $this->denyAccessUnlessGranted(Member::ROLE_MODERATOR);

        $editCommentForm = $this->createForm(CommentType::class, $comment);
        $editCommentForm->handleRequest($request);

        if ($editCommentForm->isSubmitted() && $editCommentForm->isValid()) {
            $manager->persist($comment);
            $manager->flush();
            $this->addFlash("notice", "Le commentaire du {$comment->getCreatedAt()->format('d/m/Y H:i')} a été modifié");
        }

        return $this->redirectToRoute(self::ROUTE_MODERATION_PANEL, ['page' => $page]);
    }

    /**
     * Delete a comment
     *
     * @Route("/moderation-panel/delete-comment/{id}/page{page}", name="moderation_delete_comment", requirements={"id": "\d+", "page": "\d+"})
     *
     * @param EntityManagerInterface $manager
     * @param Comment $comment
     * @param int|null $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteComment(
        EntityManagerInterface $manager,
        Comment $comment,
        ?int $page = null
    ) {
        $this->denyAccessUnlessGranted(Member::ROLE_MODERATOR);

        $manager->remove($comment);
        $manager->flush();

        $this->addFlash("notice", "Le commentaire de {$comment->getAuthor()->getName()} a été supprimé");

        return $this->redirectToRoute(self::ROUTE_MODERATION_PANEL, ['page' => $page]);
    }

    /**
     * Approve or disapprove a comment
     *
     * @Route("/moderation-panel/approve-comment/{id}/approved{approved}/page{page}", name="moderation_approve_comment", requirements={"id": "\d+", "page": "\d+", "approved": "[0-1]{1}"})
     *
     * @param EntityManagerInterface $manager
     * @param Comment $comment
     * @param bool $approved
     * @param int|null $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function approveComment(
        EntityManagerInterface $manager,
        Comment $comment,
        bool $approved,
        ?int $page = null
    ) {
        $this->denyAccessUnlessGranted(Member::ROLE_MODERATOR);

        $comment->setApproved($approved);
        $manager->flush();

        if ($approved) {
            $this->addFlash("notice", "Le commentaire de {$comment->getAuthor()->getName()} a été approuvé");
        } else {
            $this->addFlash("notice", "Le commentaire de {$comment->getAuthor()->getName()} n'est plus approuvé");
        }

        return $this->redirectToRoute(self::ROUTE_MODERATION_PANEL, ['page' => $page]);
    }

    /**
     * Handle selected comments in the moderation panel
     *
     * @Route("/moderation-panel/handle-selection/{task}/page{page}", name="moderation_handle_comments")
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param string|null $task
     * @param int|null $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */
    public function handleSelectedComments(
        Request $request,
        EntityManagerInterface $manager,
        ?string $task = null,
        ?int $page = null
    ) {
        $this->denyAccessUnlessGranted(Member::ROLE_MODERATOR);

        $commentIds = $request->request->all();
        $commentRepository = $manager->getRepository(Comment::class);
        $comments = $commentRepository->getCommentsFromIds($commentIds);
        $flashMessage = null;

        switch ($task) {
            case self::TASK_APPROVE:
                foreach ($comments as $comment) {
                    $comment->setApproved(true);
                }
                $flashMessage = "Les commentaires sélectionnés ont été approuvés";
                break;

            case self::TASK_DISAPPROVE:
                foreach ($comments as $comment) {
                    $comment->setApproved(false);
                }
                $flashMessage = "Les commentaires sélectionnés ont été mis en attente";
                break;

            case self::TASK_DELETE:
                foreach ($comments as $comment) {
                    $manager->remove($comment);
                }
                $flashMessage = "Les commentaires sélectionnés ont été supprimés";
                break;

            default:
                throw new \InvalidArgumentException("The task $task does not exist", 500);
                break;
        }

        $manager->flush();
        if ($flashMessage) {
            $this->addFlash("notice", $flashMessage);
        }

        return $this->redirectToRoute(self::ROUTE_MODERATION_PANEL, ["page" => $page]);
    }

    // Private

    /**
     * Build CommentType forms and return their views
     *
     * @param array $comments
     * @param int|null $page
     * @return array
     */
    private function createCommentFormsViews(array $comments, ?int $page = null)
    {
        $commentFormsViews = [];

        foreach ($comments as $comment) {
            $commentForm = $this->createForm(CommentType::class, $comment, [
                'action' => $this->generateUrl(self::ROUTE_EDIT_COMMENT, [
                    'id' => $comment->getId(),
                    'page' => $page ?? 1
                ])
            ]);
            $commentFormsViews[] = $commentForm->createView();
        }

        return $commentFormsViews;
    }
}
