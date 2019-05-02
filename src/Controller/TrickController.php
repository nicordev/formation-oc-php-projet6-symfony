<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Form\CommentType;
use App\Form\TrickType;
use App\Repository\CommentRepository;
use App\Repository\MemberRepository;
use App\Service\Paginator;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends AbstractController
{
    /**
     * Show a trick
     *
     * @Route("/trick/{id}/{commentsPage}", name="trick_show_id", requirements={"id": "\d+"})
     *
     * @param Request $request
     * @param ObjectManager $objectManager
     * @param Trick $trick
     * @param CommentRepository $commentRepository
     * @param MemberRepository $memberRepository
     * @param Paginator $commentsPaginator
     * @param int $commentsPage
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(
        Request $request,
        ObjectManager $objectManager,
        Trick $trick,
        CommentRepository $commentRepository,
        MemberRepository $memberRepository,
        Paginator $commentsPaginator,
        int $commentsPage = 1)
    {
        // Add a new comment

        $newComment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $newComment);
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $newComment->setTrick($trick);
            $objectManager->persist($newComment);
            $objectManager->flush();
        }

        // Existing comments

        $commentsCount = $commentRepository->count(["trick" => $trick]);
        $commentsPaginator->update(
            $commentsPage,
            3,
            $commentsCount
        );

        $comments = $commentRepository->findBy(
            ["trick" => $trick],
            ["createdAt" => "DESC"],
            $commentsPaginator->itemsPerPage,
            $commentsPaginator->pagingOffset
        );

        foreach ($comments as $comment) {
            if ($comment->getAuthor()) {
                $comment->setAuthor($memberRepository->findOneBy(["id" => $comment->getAuthor()->getId()]));
            }
        }

        return $this->render('trick/trick.html.twig', [
            'trick' => $trick,
            'comments' => $comments,
            'commentsPaginator' => $commentsPaginator,
            'commentForm' => $commentForm->createView()
        ]);
    }

    /**
     * Create or edit a trick
     *
     * @Route("/create-trick", name="create_trick")
     * @Route("/edit-trick/{id}", name="edit_trick", requirements={"id": "\d+"})
     *
     * @param Request $request
     * @param ObjectManager $manager
     * @param Trick|null $trick
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function createOrEdit(Request $request, ObjectManager $manager, Trick $trick = null)
    {
        $trick = $trick ?? new Trick();

        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($trick);
            $manager->flush();

            if ($trick->getId()) {
                $this->addFlash(
                    "notice",
                    "Le trick {$trick->getName()} a été modifié"
                );
            } else {
                $this->addFlash(
                    "notice",
                    "Le trick {$trick->getName()} a été créé"
                );
            }

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
     * @param ObjectManager $manager
     * @param Trick $trick
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(ObjectManager $manager, Trick $trick)
    {
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
}
