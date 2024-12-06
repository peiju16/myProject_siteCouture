<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use function Symfony\Component\Clock\now;

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
    public function registration(Request $request, EntityManagerInterface $manager, MailerService $mailerService, TokenGeneratorInterface $tokenGeneratorInterface): Response
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
            // Token
            $tokenregistration = $tokenGeneratorInterface->generateToken();
            try {
                $user = $form->getData();
                $user->setRoles(['ROLE_USER']);
                // User token
                $user->setTokenRegistration($tokenregistration);
                $manager->persist($user);
                $manager->flush();

                // Mail send
                $mailerService->send(
                    $user->getEmail(),
                    'Confirmation du compt utilisateur',
                    'registration_confirm.html.twig',
                    [
                        'user' => $user,
                        'token' => $tokenregistration,
                        'lifeTimeToken' => $user->getTokenRegistrationLifeTime()->format('d-m-Y, H:i')
                    ]
                    );
    
                $this->addFlash('success', 'Votre compte a bien été créé. Veuillez vérifier votre e-mail pour activer votre compte.');
                return $this->redirectToRoute('app_login');
            } catch (\Exception $e) {
                // Log the error if needed (optional)
                // Log error: $e->getMessage()
                $this->addFlash('error', 'Votre compte n\'a pas pu enrégistré.');
            }         
        }
        
        return $this->render('security/registration.html.twig', [
            'registrationForm' => $form->createView()
        ]);
    }

    #[Route('/verify/{token}/{id<\d+>}', name: 'account_verify', methods: ['GET'])]
    public function verifyUse(string $token, User $user, EntityManagerInterface $manger) :Response {
        if ($user->getTokenRegistration() !== $token) {
            throw new AccessDeniedException();
        }
        if ($user->getTokenRegistration() === null) {
            throw new AccessDeniedException();
        }
        if (new \DateTime('now') > $user->getTokenRegistrationLifeTime()) {
            throw new AccessDeniedException();
        }

        $user->setVerifed(true);
        $user->setTokenRegistration('null');
        $manger->flush();

        $this->addFlash('success', 'Votre compte a bien été activé. Vous pouvez désormais vous connecter sur votre compte.');
        return $this->redirectToRoute('app_login');
    }

 

}
