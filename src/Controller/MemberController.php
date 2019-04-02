<?php

namespace App\Controller;

use App\Entity\Member;
use App\Form\RegistrationType;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MemberController extends AbstractController
{
    /**
     * @Route("/registration", name="registration")
     */
    public function showRegistration(Request $request, ObjectManager $manager)
    {
        $newMember = new Member();

        $registrationForm = $this->createForm(RegistrationType::class, $newMember);

        $registrationForm->handleRequest($request);

        if ($registrationForm->isSubmitted() && $registrationForm->isValid()) {
            $manager->persist($newMember);
            $manager->flush();
        }

        return $this->render('member/registration.html.twig', [
            'registrationForm' => $registrationForm->createView(),
        ]);
    }
}
