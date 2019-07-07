<?php

namespace App\Controller;

use App\Entity\Member;
use App\Form\MemberType;
use App\Form\RegistrationType;
use App\Repository\MemberRepository;
use App\Security\MemberVoter;
use App\Service\SecurityHelper;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class MemberController extends AbstractController
{
    public const FLASH_ALREADY_CONNECTED = "Vous êtes déjà inscrit. Si vous voulez inscrire un nouveau compte, veuillez vous déconnecter.";
    public const PASSWORD_REQUIREMENTS = "Le mot de passe doit comporter au moins 8 caractères dont une lettre minuscule, une lettre majuscule, un chiffre et un caractère spécial. Bon courage ! ☺";

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
            if (!SecurityHelper::hasStrongPassword($newMember->getPassword())) {
                $this->addFlash("warning", self::PASSWORD_REQUIREMENTS);

            } else {
                $hash = $encoder->encodePassword($newMember, $newMember->getPassword());
                $newMember->setPassword($hash);
                $newMember->setRoles([Member::ROLE_USER]);

                $manager->persist($newMember);
                $manager->flush();

                $this->addFlash("notice", "Vous êtes enregistré");

                return $this->redirectToRoute("app_login");
            }

        }

        return $this->render('member/registration.html.twig', [
            'registrationForm' => $registrationForm->createView(),
        ]);
    }

    /**
     * @Route("/member/{id}", name="member_profile", requirements={"id": "\d+"}))
     * @Route("/member/{id}/edit-password{editPassword}", name="member_profile_password", requirements={"id": "\d+", "editPassword": "[0-1]"}))
     */
    public function showProfile(Request $request, EntityManagerInterface $manager, Member $member, UserPasswordEncoderInterface $encoder, int $editPassword = 0)
    {
        $user = $this->getUser();

        // Edition form
        if ($user) {
            $userIsManager = in_array(Member::ROLE_MANAGER, $user->getRoles()) || in_array(Member::ROLE_ADMIN, $user->getRoles());

            if ($user === $member || $userIsManager) {
                $form = $this->createForm(MemberType::class, $member, [
                    MemberType::KEY_EDIT_ROLES => $userIsManager,
                    MemberType::KEY_EDIT_PASSWORD => $editPassword
                ]);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    if ($editPassword) {
                        $hash = $encoder->encodePassword($member, $member->getPassword());
                        $member->setPassword($hash);
                    }
                    $member->addRole(Member::ROLE_USER);
                    $manager->persist($member);
                    $manager->flush();

                    $this->addFlash(
                        "notice",
                        "Le profil de {$member->getName()} a été modifié"
                    );

                    return $this->redirectToRoute("member_profile", ['id' => $member->getId()]);
                }
            }
        }

        return $this->render("member/profile.html.twig", [
            "member" => $member,
            "memberForm" => isset($form) ? $form->createView() : null
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
    public function deleteMember(
        Member $member,
        EntityManagerInterface $manager,
        SessionInterface $session,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->denyAccessUnlessGranted(MemberVoter::ADD, $member);

        $manager->remove($member);
        $manager->flush();

        if ($member === $this->getUser()) {
            $session->invalidate();
            $tokenStorage->setToken(null);
            $this->addFlash("notice", "Votre compte a bien été supprimé");

            return $this->redirectToRoute("home");

        } else {
            $this->addFlash("notice", "{$member->getName()} a été supprimé");

            return $this->redirectToRoute("member_management");
        }
    }
}
