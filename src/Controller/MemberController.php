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
    /**
     * @Route("/registration", name="registration_route")
     */
    public function showRegistration(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {
        $session = $this->get("session");
        $session->set("current_page", "registration");

        $newMember = new Member();

        $registrationForm = $this->createForm(RegistrationType::class, $newMember);

        $registrationForm->handleRequest($request);

        if ($registrationForm->isSubmitted() && $registrationForm->isValid()) {
            $hash = $encoder->encodePassword($newMember, $newMember->getPassword());
            $newMember->setPassword($hash);
            $newMember->setRoles([Member::ROLE_USER]);

            $manager->persist($newMember);
            $manager->flush();

            return $this->redirectToRoute("login_route");
        }

        return $this->render('member/registration.html.twig', [
            'registrationForm' => $registrationForm->createView(),
        ]);
    }

    /**
     * @Route("/login", name="login_route")
     */
    public function login()
    {
        $session = $this->get("session");
        $session->set("current_page", "login");

        return $this->render('member/login.html.twig');
    }

    /**
     * @Route("/logout", name="logout_route")
     */
    public function logout()
    {}
}
