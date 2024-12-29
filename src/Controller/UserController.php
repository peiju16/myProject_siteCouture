<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Entity\Transport;
use App\Entity\User;
use App\Form\ResetPasswordType;
use App\Form\UserType;
use App\Repository\FormationRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
     * @param  mixed $orderRepository
     * @param  mixed $paginator
     * @param  mixed $request
     * @return Response
     */
    #[Route('/user/order', name: 'app_user_order', methods: ['GET'])]    
    public function userOrder(PaginatorInterface $paginator, Request $request, OrderRepository $orderRepository): Response
    {
      
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Veuillez se connecter.');
        }


        $orders = $paginator->paginate(
            $orderRepository->findBy(['user' => $user]), 
            $request->query->getInt('page', 1),
            12 
        );

        return $this->render('user/order.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/user/formation', name: 'app_user_formation', methods: ['GET'])]    
    public function userFormation(PaginatorInterface $paginator, Request $request, ReservationRepository $reservationRepository): Response
    {
      
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Veuillez se connecter.');
        }

        $formations = $paginator->paginate(
            $reservationRepository->findFormationsByUser($user), 
            $request->query->getInt('page', 1), 
            12
        );

        return $this->render('user/formation.html.twig', [
            'formations' => $formations,
        ]);
    }

    #[Route('/user/order_detail/{id}', name: 'app_user_orderDetail', methods: ['GET'])]    
    public function userOrderDetail(Order $order, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');;
        }

        $orderDetails = $manager->getRepository(OrderDetails::class)->findBy(criteria: ['orderNumber' => $order->getId()]);
        $invoice = $manager->getRepository(Invoice::class)->findOneBy(criteria: ['orderInvoice' => $order->getId()]);
        $transportway = $manager->getRepository(Transport::class)->findOneBy(criteria: ['id' => $order->getTransportWay()]);
 

        return $this->render('user/orderDetail.html.twig', [
            'user' => $user,
            'order' => $order,
            'orderDetails' => $orderDetails,
            'invoice' => $invoice,
            'transportway' => $transportway
        ]);
    }

    #[Route('/user/order_detail/{id}/download-invoice', name: 'app_user_orderDetail_invoice', methods: ['GET'])]
    public function downloadInvoice(Order $order, EntityManagerInterface $manager): Response
    {
        if (!$this->getUser() || $order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Access Denied.');
        }

        $invoice = $manager->getRepository(Invoice::class)->findOneBy(criteria: ['orderInvoice' => $order->getId()]);
        // **Get the existing invoice path**
        $invoicePath = $invoice->getPdfPath();

        // Create a BinaryFileResponse to directly download the file
        $response = new BinaryFileResponse($invoicePath);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="invoice_' . $order->getId() . '.pdf"'); 

        return $response;
    }

}
