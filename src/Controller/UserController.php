<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ResetPasswordType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/user/edit/{id}', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        if ($this->getUser() !== $user) {
            return $this->redirectToRoute('app_home');
        }
        
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($hasher->isPasswordValid($user, $form->getData()->getPleinPassword())) {
                try {
                    $user = $form->getData();
                    $manager->persist($user);
                    $manager->flush();
        
                    $this->addFlash('success', 'Votre profile a bien été modifié');
                    return $this->redirectToRoute('app_user');
                } catch (\Exception $e) {
                    // Log the error if needed (optional)
                    // Log error: $e->getMessage()
                    $this->addFlash('error', 'Erreur: votre profile n\'a pas pu être modifié');
                }         
            } else {
                $this->addFlash('error', 'Le mot de passe renseigné est incorrect!');
            }
           
        }
        
        return $this->render('user/edit.html.twig', [
            'userForm' => $form->createView(),
            'user' => $user
        ]);
    }

    #[Route('/user/reset-password/{id}', name: 'app_user_reset_password', methods: ['GET', 'POST'])]
    public function restPassword(Request $request, User $user, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        if ($this->getUser() !== $user) {
            return $this->redirectToRoute('app_home');
        }
        
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($hasher->isPasswordValid($user, $form->getData()['pleinPassword'])) {
                try {
                    $user->setUpdatedAt(new \DateTimeImmutable());
                    $user->setPleinPassword($form->getData()['newPassword']);
                    $manager->persist($user);
                    $manager->flush();
        
                    $this->addFlash('success', 'Votre mot de passe a bien été modifié');
                    return $this->redirectToRoute('app_user');
                } catch (\Exception $e) {
                    // Log the error if needed (optional)
                    // Log error: $e->getMessage()
                    $this->addFlash('error', 'Erreur: votre mot de passe n\'a pas pu être modifié');
                }         
            } else {
                $this->addFlash('error', 'Le mot de passe renseigné est incorrect!');
            }
           
        }
        
        return $this->render('user/reset-password.html.twig', [
            'resetPasswordForm' => $form->createView(),
            'user' => $user
        ]);
    }
}
