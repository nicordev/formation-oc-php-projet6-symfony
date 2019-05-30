<?php

namespace App\Controller;

use App\Entity\Member;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
            $this->addFlash(
                "notice",
                self::FLASH_ALREADY_CONNECTED
            );
            return $this->redirectToRoute("home");
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
}
