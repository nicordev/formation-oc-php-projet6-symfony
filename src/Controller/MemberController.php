<?php

namespace App\Controller;

use App\Entity\Member;
use App\Form\RegistrationType;
use App\Repository\MemberRepository;
use App\Security\MemberVoter;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class MemberController extends AbstractController
{
    public const FLASH_ALREADY_CONNECTED = "Vous êtes déjà inscrit. Si vous voulez inscrire un nouveau compte, veuillez vous déconnecter.";

    /**
     * @Route("/registration", name="registration_route")
     */
    public function showRegistration(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {
        // For visitors only
        if ($this->getUser()) {
            throw new AccessDeniedException(self::FLASH_ALREADY_CONNECTED);
        }

        $newMember = new Member();

        $registrationForm = $this->createForm(RegistrationType::class, $newMember);

        $registrationForm->handleRequest($request);

        if ($registrationForm->isSubmitted() && $registrationForm->isValid()) {
            $hash = $encoder->encodePassword($newMember, $newMember->getPassword());
            $newMember->setPassword($hash);
            $newMember->setRoles([Member::ROLE_USER]);

            $manager->persist($newMember);
            $manager->flush();

            return $this->redirectToRoute("app_login");
        }

        return $this->render('member/registration.html.twig', [
            'registrationForm' => $registrationForm->createView(),
        ]);
    }

    /**
     * @Route("/member/{id}", name="member_profile", requirements={"id": "\d+"}))
     */
    public function showProfile(Member $member)
    {
        return $this->render("member/profile.html.twig", [
            "member" => $member
        ]);
    }

    /**
     * @Route("/member-management", name="member_management")
     * @Security("is_granted('ROLE_MANAGER')")
     */
    public function showMemberManagement(MemberRepository $repository)
    {
        $members = $repository->findAll();

        return $this->render("member/management.html.twig", [
            "members" => $members
        ]);
    }

    /**
     * @Route("/delete-member/{id}", name="member_delete", requirements={"id": "\d+"}))
     */
    public function deleteMember(Member $member, EntityManagerInterface $manager, SessionInterface $session)
    {
        $this->denyAccessUnlessGranted(MemberVoter::ADD, $member);

        $manager->remove($member);
        $manager->flush();


        if ($member === $this->getUser()) {
            $session->invalidate();
            $this->addFlash("notice", "Votre compte a bien été supprimé");

            return $this->redirectToRoute("home");

        } else {
            $this->addFlash("notice", "{$member->getName()} a été supprimé");

            return $this->redirectToRoute("member_management");
        }
    }
}
