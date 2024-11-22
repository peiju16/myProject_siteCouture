<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/connexion', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    #[IsGranted('ROLE_USER')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/inscription', name: 'app_registration', methods: ['GET', 'POST'])]
    public function registration(Request $request, EntityManagerInterface $manager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        // turbo.js problem
        // if ($form->isSubmitted() && !$form->isValid()) {
        //     // Return 422 status for form errors, allowing Turbo to interpret the response correctly
        //     return $this->render('security/registration.html.twig', [
        //         'registrationForm' => $form->createView(),
        //     ], new Response('', 422));
        // }

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $user = $form->getData();
                $user->setRoles(['ROLE_USER']);
                $manager->persist($user);
                $manager->flush();
    
                $this->addFlash('success', 'Votre compte a bien été créé');
                return $this->redirectToRoute('app_login');
            } catch (\Exception $e) {
                // Log the error if needed (optional)
                // Log error: $e->getMessage()
                $this->addFlash('error', 'Erreur: Votre compte n\'a pas pu être enregistré.');
            }         
        }
        
        return $this->render('security/registration.html.twig', [
            'registrationForm' => $form->createView()
        ]);
    }

}
