<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\Comment;
use App\Entity\Trick;
use App\Entity\TrickGroup;
use App\Form\CommentType;
use App\Form\ImageUploadType;
use App\Form\TrickType;
use App\Repository\TrickGroupRepository;
use App\Repository\TrickRepository;
use App\Security\CommentVoter;
use App\Security\TrickVoter;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CommentRepository;
use App\Service\HtmlKeys;
use App\Service\Paginator;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends AbstractController
{
    public const COMMENTS_PER_PAGE = 5;

    public const ROUTE_TRICK_SHOW = "trick_show";
    public const ROUTE_ADD_COMMENT = "trick_add_comment";
    public const ROUTE_EDIT_COMMENT = "trick_edit_comment";

    /**
     * Show a trick
     *
     * @Route("/trick/{slug}/comments-page/{commentsPage}", name="trick_show", requirements={"commentsPage": "\d+"})
     *
     * @param Trick $trick
     * @param CommentRepository $commentRepository
     * @param Paginator $commentsPaginator
     * @param int $commentsPage
     * @return Response
     */
    public function show(
        Trick $trick,
        CommentRepository $commentRepository,
        Paginator $commentsPaginator,
        ?int $commentsPage = null
    )
    {
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
     * @Route("/trick-groups", name="show_trick_groups")
     */
    public function showTrickGroups(TrickGroupRepository $repository)
    {
        $groups = $repository->findAll();

        return $this->render("trick/trickGroups.html.twig", [
            "groups" => $groups
        ]);
    }

    /**
     * @Route("/trick-group/{slug}", name="show_trick_group")
     */
    public function showTrickGroup(TrickGroup $trickGroup)
    {
        return $this->render("trick/trickGroup.html.twig", ["group" => $trickGroup]);
    }

    /**
     * Create a trick
     *
     * @Route("/add-trick", name="add_trick")
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return RedirectResponse|Response
     */
    public function addTrick(
        Request $request,
        EntityManagerInterface $manager,
        TrickRepository $repository,
        FileUploader $fileUploader
    )
    {
        $this->denyAccessUnlessGranted(TrickVoter::ADD);

        $trick = new Trick();

        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($repository->getByName($trick->getName())) {
                $this->addFlash(
                    "notice",
                    "Le trick {$trick->getName()} existe déjà"
                );

            } else {
                // Main image
                $mainImageFile = $form['uploadMainImage']->getData();

                if ($mainImageFile) {
                    $trick->setMainImage("/img/tricks/" . $fileUploader->upload($mainImageFile));
                }

                // Images
                $this->removeHttpFromUploadedImages($trick);

                $trick->setAuthor($this->getUser());
                $manager->persist($trick);
                $manager->flush();

                $this->addFlash(
                    "notice",
                    "Le trick {$trick->getName()} a été créé"
                );

                return $this->redirectToRoute(self::ROUTE_TRICK_SHOW, ['slug' => $trick->getSlug()]);
            }
        }

        $imageUploadForm = $this->createForm(ImageUploadType::class);

        return $this->render('trick/trickEditor.html.twig', [
            'trickForm' => $form->createView(),
            'imageUploadForm' => $imageUploadForm->createView(),
            'editMode' => false
        ]);
    }

    /**
     * Edit a trick
     *
     * @Route("/edit-trick/{slug}", name="edit_trick")
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param Trick|null $trick
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function editTrick(
        Request $request,
        EntityManagerInterface $manager,
        TrickRepository $repository,
        FileUploader $fileUploader,
        Trick $trick
    )
    {
        $this->denyAccessUnlessGranted(TrickVoter::EDIT, $trick);

        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($repository->hasDuplicate($trick)) {
                $this->addFlash(
                    "notice",
                    "Le trick {$trick->getName()} existe déjà"
                );

            } else {
                // Main image
                $mainImageFile = $form['uploadMainImage']->getData();

                if ($mainImageFile) {
                    $trick->setMainImage("/img/tricks/" . $fileUploader->upload($mainImageFile));
                }

                // Images
                $this->removeHttpFromUploadedImages($trick);

                $manager->flush();

                $this->addFlash(
                    "notice",
                    "Le trick {$trick->getName()} a été modifié"
                );

                return $this->redirectToRoute(self::ROUTE_TRICK_SHOW, ['slug' => $trick->getSlug()]);
            }
        }

        return $this->render('trick/trickEditor.html.twig', [
            'trickForm' => $form->createView(),
            'editMode' => true,
            'slug' => $trick->getSlug()
        ]);
    }

    /**
     * Delete a trick
     *
     * @Route("/delete-trick/{slug}", name="delete_trick")
     *
     * @param EntityManagerInterface $manager
     * @param Trick $trick
     * @return RedirectResponse
     */
    public function delete(EntityManagerInterface $manager, Trick $trick)
    {
        $this->denyAccessUnlessGranted(TrickVoter::DELETE, $trick);

        $trickName = $trick->getName();
        $manager->remove($trick);
        $manager->flush();

        // Delete trick files
        $this->deleteTrickFiles($trick);

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
     * @param Paginator $commentsPaginator
     * @return bool|RedirectResponse|Response
     */
    public function addComment(
        Request $request,
        EntityManagerInterface $objectManager,
        Trick $trick,
        CommentRepository $commentRepository,
        Paginator $commentsPaginator
    )
    {
        $this->denyAccessUnlessGranted(CommentVoter::ADD);

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
     * @param Paginator $commentsPaginator
     * @param int|null $commentsPage
     * @return Response
     */
    public function editComment(
        Request $request,
        EntityManagerInterface $manager,
        Comment $comment,
        CommentRepository $commentRepository,
        Paginator $commentsPaginator,
        ?int $commentsPage = null
    )
    {
        $this->denyAccessUnlessGranted(CommentVoter::EDIT, $comment);

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
     * @return RedirectResponse
     */
    public function deleteComment(
        EntityManagerInterface $manager,
        Comment $comment,
        ?int $commentsPage = null
    )
    {
        $this->denyAccessUnlessGranted(CommentVoter::DELETE, $comment);

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
     * @return RedirectResponse
     */
    private function redirectToTrickRoute(int $trickId, int $commentPage = 1, string $urlComplements = "")
    {
        $trickUrl = $this->generateUrl(self::ROUTE_TRICK_SHOW, ["id" => $trickId, "commentsPage" => $commentPage]);

        return $this->redirect("{$trickUrl}{$urlComplements}");
    }

    /**
     * Delete images files of a trick
     *
     * @param Trick $trick
     */
    private function deleteTrickFiles(Trick $trick)
    {
        $rootDirectory = dirname(dirname(__DIR__));

        if ($trick->getMainImage() && strpos($trick->getMainImage(), "http") === false) {
            unlink($rootDirectory . "/public" . $trick->getMainImage());
        }

        foreach ($trick->getImages() as $image) {
            if (strpos($image->getUrl(), "http") === false) {
                unlink($rootDirectory . "/public" . $image->getUrl());
            }
        }
    }

    /**
     * Remove http:// on uploaded images
     *
     * @param array $images
     */
    private function removeHttpFromUploadedImages(Trick $trick)
    {
        foreach ($trick->getImages() as $image) {
            $imageUrl = $image->getUrl();
            if (strpos($imageUrl, "http:///") !== false) {
                $image->setUrl(str_replace("http://", "", $imageUrl));
            }
        }
    }
}
