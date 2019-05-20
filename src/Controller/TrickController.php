<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\Comment;
use App\Entity\Trick;
use App\Form\CommentType;
use App\Form\TrickType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CommentRepository;
use App\Repository\MemberRepository;
use App\Service\HtmlKeys;
use App\Service\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends AbstractController
{
    public const COMMENTS_PER_PAGE = 5;

    public const ROUTE_TRICK = "trick_show";
    public const ROUTE_ADD_COMMENT = "trick_add_comment";
    public const ROUTE_EDIT_COMMENT = "trick_edit_comment";

    /**
     * Show a trick
     *
     * @Route("/trick/{id}/comments-page/{commentsPage}", name="trick_show", requirements={"id": "\d+"})
     * @Route("/trick/{id}", name="trick_show_simple", requirements={"id": "\d+"})
     *
     * @param Trick $trick
     * @param CommentRepository $commentRepository
     * @param MemberRepository $memberRepository
     * @param Paginator $commentsPaginator
     * @param int $commentsPage
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(
        Trick $trick,
        CommentRepository $commentRepository,
        MemberRepository $memberRepository,
        Paginator $commentsPaginator,
        ?int $commentsPage = null
    )
    {
        $session = $this->get("session");
        $session->set("current_page", "trick_page");

        // Add a new comment form

        if ($this->isGranted(Member::ROLE_USER)) {
            $newComment = new Comment();
            $commentForm = $this->createForm(CommentType::class, $newComment, [
                'action' => $this->generateUrl(self::ROUTE_ADD_COMMENT, [
                    'id' => $trick->getId()
                ])
            ]);
        }

        // Existing comments

        $comments = $commentRepository->getTrickComments(
            $memberRepository,
            $commentsPaginator,
            $trick,
            $commentsPage ?? 1
        );

        return $this->render('trick/trick.html.twig', [
            'trick' => $trick,
            'comments' => $comments,
            'commentsPaginator' => $commentsPaginator,
            'commentForm' => isset($commentForm) ? $commentForm->createView() : null
        ]);
    }

    /**
     * Create a trick
     *
     * @Route("/create-trick", name="create_trick")
     *
     * @param Request $request
     * @param ObjectManager $manager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addTrick(Request $request, ObjectManager $manager)
    {
        $this->denyAccessUnlessGranted(Member::ROLE_USER);

        $trick = new Trick();

        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($trick);
            $manager->flush();

            $this->addFlash(
                "notice",
                "Le trick {$trick->getName()} a été créé"
            );

            return $this->redirectToRoute("trick_show_id", ['id' => $trick->getId()]);
        }

        return $this->render('trick/trickEditor.html.twig', [
            'trickForm' => $form->createView(),
            'editMode' => $trick->getId() !== null
        ]);
    }

    /**
     * Edit a trick
     *
     * @Route("/edit-trick/{id}", name="edit_trick", requirements={"id": "\d+"})
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param Trick|null $trick
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function editTrick(Request $request, EntityManagerInterface $manager, Trick $trick = null)
    {
        $user = $this->getUser();

        if (!$user || !$this->isGranted(Member::ROLE_EDITOR) && !$user->isAuthor($trick)) {
            $this->denyAccessUnlessGranted([]);
        }

        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($trick);
            $manager->flush();

            $this->addFlash(
                "notice",
                "Le trick {$trick->getName()} a été modifié"
            );

            return $this->redirectToRoute("trick_show_id", ['id' => $trick->getId()]);
        }

        return $this->render('trick/trickEditor.html.twig', [
            'trickForm' => $form->createView(),
            'editMode' => $trick->getId() !== null
        ]);
    }

    /**
     * Delete a trick
     *
     * @Route("/delete-trick/{id}", name="delete_trick", requirements={"id": "\d+"})
     *
     * @param EntityManagerInterface $manager
     * @param Trick $trick
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(EntityManagerInterface $manager, Trick $trick)
    {
        $user = $this->getUser();

        if (!$user || !$this->isGranted(Member::ROLE_EDITOR) && !$user->isAuthor($trick)) {
            $this->denyAccessUnlessGranted([]);
        }

        $trickName = $trick->getName();
        $manager->remove($trick);
        $manager->flush();

        $this->addFlash(
            "notice",
            "Le trick $trickName a été supprimé"
        );

        $homeUrl = $this->generateUrl("home");

        return $this->redirect("$homeUrl#main-content");
    }

    // Comments

    /**
     * Add a comment
     *
     * @Route("/trick/{id}/add-comment", name="trick_add_comment", requirements={"id": "\d+"})
     *
     * @param Request $request
     * @param EntityManagerInterface $objectManager
     * @param Trick $trick
     * @param CommentRepository $commentRepository
     * @param MemberRepository $memberRepository
     * @param Paginator $commentsPaginator
     * @return bool|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addComment(
        Request $request,
        EntityManagerInterface $objectManager,
        Trick $trick,
        CommentRepository $commentRepository,
        MemberRepository $memberRepository,
        Paginator $commentsPaginator
    )
    {
        $this->denyAccessUnlessGranted(Member::ROLE_USER);

        $newComment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $newComment);
        $commentForm->handleRequest($request);

        // Save comment in database
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $newComment->setTrick($trick);
            $newComment->setAuthor($this->getUser());
            $objectManager->persist($newComment);
            $objectManager->flush();
            $this->addFlash(
                "notice",
                "Votre commentaire a été publié"
            );

            return $this->redirectToTrickRoute($trick->getId(), 1, HtmlKeys::ID_TRICK_COMMENTS);
        }

        // Existing comments

        $comments = $commentRepository->getTrickComments(
            $memberRepository,
            $commentsPaginator,
            $trick,
            1
        );

        return $this->render('trick/trick.html.twig', [
            'trick' => $trick,
            'comments' => $comments,
            'commentsPaginator' => $commentsPaginator,
            'commentForm' => $commentForm->createView()
        ]);
    }

    /**
     * Edit a comment
     *
     * @Route("/edit-comment/{id}/comments-page/{commentsPage}", name="trick_edit_comment", requirements={"id": "\d+", "commentsPage": "\d+"})
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param Comment $comment
     * @param CommentRepository $commentRepository
     * @param MemberRepository $memberRepository
     * @param Paginator $commentsPaginator
     * @param int|null $commentsPage
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editComment(
        Request $request,
        EntityManagerInterface $manager,
        Comment $comment,
        CommentRepository $commentRepository,
        MemberRepository $memberRepository,
        Paginator $commentsPaginator,
        ?int $commentsPage = null
    )
    {
        $this->denyAccessUnlessGranted(Member::ROLE_USER);

        $editCommentForm = $this->createForm(CommentType::class, $comment);
        $editCommentForm->handleRequest($request);
        $trick = $comment->getTrick();

        if ($editCommentForm->isSubmitted() && $editCommentForm->isValid()) {
            $manager->persist($comment);
            $manager->flush();
            $this->addFlash("notice", "Le commentaire du {$comment->getCreatedAt()->format('d/m/Y H:i')} a été modifié");

            return $this->redirectToTrickRoute($trick->getId(), $commentsPage, HtmlKeys::ID_TRICK_COMMENTS);
        }

        // Existing comments

        $comments = $commentRepository->getTrickComments(
            $memberRepository,
            $commentsPaginator,
            $comment->getTrick(),
            $commentsPage
        );

        $commentForm = $this->createForm(CommentType::class, new Comment());

        return $this->render('trick/trick.html.twig', [
            'trick' => $trick,
            'comments' => $comments,
            'commentsPaginator' => $commentsPaginator,
            'commentForm' => $commentForm->createView(),
            'editCommentForm' => $editCommentForm->createView(),
            'commentToEditId' => $comment->getId()
        ]);
    }

    /**
     * Delete a comment
     *
     * @Route("/delete-comment/{id}/comments-page/{commentsPage}", name="trick_delete_comment", requirements={"id": "\d+", "commentsPage": "\d+"})
     *
     * @param EntityManagerInterface $manager
     * @param Comment $comment
     * @param int $commentsPage
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteComment(
        EntityManagerInterface $manager,
        Comment $comment,
        ?int $commentsPage = null
    )
    {
        $manager->remove($comment);
        $manager->flush();

        $this->addFlash("notice", "Le commentaire de {$comment->getAuthor()->getName()} a été supprimé");

        return $this->redirectToTrickRoute($comment->getTrick()->getId(), $commentsPage, HtmlKeys::ID_TRICK_COMMENTS);
    }

    // Private

    /**
     * Redirect to the trick page
     *
     * @param int $trickId
     * @param int $commentPage
     * @param string $urlComplements
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function redirectToTrickRoute(int $trickId, int $commentPage = 1, string $urlComplements = "")
    {
        $trickUrl = $this->generateUrl(self::ROUTE_TRICK, ["id" => $trickId, "commentsPage" => $commentPage]);

        return $this->redirect("{$trickUrl}{$urlComplements}");
    }
}
