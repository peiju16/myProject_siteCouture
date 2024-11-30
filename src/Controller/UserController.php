<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ResetPasswordType;
use App\Form\UserType;
use App\Repository\FormationRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
          
        ]);
    }

    #[Route('/user/edit/{id}', name: 'app_user_edit', methods: ['GET', 'POST'])]
    #[IsGranted(
        new Expression('is_granted("ROLE_USER") and user === subject'),
        subject: 'user',
    )]
    public function edit(Request $request, User $user, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {   
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
                    $this->logger->error('Profile update failed: ' . $e->getMessage());
                    $this->addFlash('error', 'Erreur: votre profile n\'a pas pu être modifié');
                }         
            } 
        }
        
        return $this->render('user/edit.html.twig', [
            'userForm' => $form->createView(),
            'user' => $user
        ]);
    }

    #[Route('/user/reset-password/{id}', name: 'app_user_reset_password', methods: ['GET', 'POST'])]
    #[IsGranted(
        new Expression('is_granted("ROLE_USER") and user === subject'),
        subject: 'user',
    )]
    public function restPassword(Request $request, User $user, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {   
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
                    $this->logger->error('Password reset failed: ' . $e->getMessage());
                    $this->addFlash('error', 'Erreur: votre mot de passe n\'a pas pu être modifié');
                }         
            }
           
        }
        
        return $this->render('user/reset-password.html.twig', [
            'resetPasswordForm' => $form->createView(),
            'user' => $user
        ]);
    }

    /**
     * afficher les produits et les formations que l'utilisateur ont achetés
     *
     * @param  mixed $productRepository
     * @param  mixed $paginator
     * @param  mixed $request
     * @return Response
     */
    #[Route('/user/order', name: 'app_user_order', methods: ['GET'])]    
    public function userOrder(ProductRepository $productRepository, FormationRepository $formationRepository, PaginatorInterface $paginator, Request $request, TokenStorageInterface $token): Response
    {
      
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Veuillez se connecter.');
        }

        $productPage = $request->query->getInt('productPage', 1);
        $formationPage = $request->query->getInt('formationPage', 1);

        $products = $paginator->paginate($productRepository->findProductsByUser($user->getId()), $productPage, 6);
        $formations = $paginator->paginate($formationRepository->findFormationsByUser($user->getId()), $formationPage, 6);

    
        return $this->render('user/order.html.twig', [
            'produits' => $products,
            'formations' => $formations
        ]);
    }
}
