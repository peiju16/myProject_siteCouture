<?php
namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Entity\TransportAddress;
use App\Form\OrderType;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    #[Route('/order', name: 'app_order')]
    public function index(CartService $cartService): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('No logged-in user found.');
        }

        $orderForm = $this->createForm(OrderType::class, null, [
            'user' => $user, // Pass the user to the form
        ]);


        return $this->render('order/index.html.twig', [
            'orderForm' => $orderForm->createView(),
            'cartData' => $cartService->getTotalCart(),
            'isModify' => false,
        ]);
            
        
       
    }

    #[Route('/order/verify', name:'app_order_verify', methods:['POST'])]
    public function verify(Request $request, EntityManagerInterface $entityManager, CartService $cartService): Response 
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
    
        $user = $this->getUser(); 
        $datetime = new \DateTime('now');
        $order = new Order();
        $reference = $datetime->format('dmY') . "-" . uniqid();

        $form = $this->createForm(OrderType::class, $order, [
            'user' => $user,
        ]);

        $form->handleRequest($request);
 
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $selectedTransport = $form->get('transportWay')->getData();
    
            if ($selectedTransport->getIsPickup()) {
                // Boutique address
                $order->setReceiverAddress('45 Boulvarde de la Croisette');
                $order->setCity('Cannes');
                $order->setZipCode('06400');
            } else {
                if ($form->get('useSavedAddress')->getData()) {
                    $savedAddress = $form->get('savedAddress')->getData();
                    $order->setReceiverAddress($savedAddress->getAddress());
                    $order->setCity($savedAddress->getCity());
                    $order->setZipCode($savedAddress->getZipCode());
                } else {
                    $order->setReceiverAddress($form->get('receiverAddress')->getData());
                    $order->setCity($form->get('city')->getData());
                    $order->setZipCode($form->get('zipCode')->getData());
    
                    if ($form->get('saveAddress')->getData()) {
                        $transportAddress = new TransportAddress();
                        $transportAddress->setTitle($form->get('addressTitle')->getData());
                        $transportAddress->setAddress($order->getReceiverAddress());
                        $transportAddress->setCity($order->getCity());
                        $transportAddress->setZipCode($order->getZipCode());
                        $transportAddress->setUser($user);
                        $transportAddress->setTelephone($order->getReceiverPhone());
                        $entityManager->persist($transportAddress);
                    }
                }
            }
    
            // Set the order method
            $order->setUser($user);
            $order->setReference($reference);
            $order->setTransportWay($selectedTransport);
            $order->setTransportPrice($selectedTransport->getPrice());
            $order->setPaid(0);
            $order->setTotalPrice($cartService->getTotalCart()['cartTotal'] + $selectedTransport->getPrice());
            $order->setStatus('Attends Payment');
    
            $paymentMethod = $form->get('paymentMethod')->getData();
            $order->setPaymentMethod($paymentMethod);
            
            $entityManager->persist($order);

             // Check stock and adjust quantities
            foreach ($cartService->getTotalCart()['items'] as $product) {
                $productEntity = $product['product'];
                $orderedQuantity = $product['quantity'];

                if ($productEntity->getStock() < $orderedQuantity) {
                    // Insufficient stock, handle this case
                    $this->addFlash(
                        'error',
                        sprintf('Stock insuffisant pour le produit %s.', $productEntity->getName())
                    );
                    return $this->redirectToRoute('app_cart');
                }

                // Decrease stock
                $productEntity->setStock($productEntity->getStock() - $orderedQuantity);

                // Set order details
                $orderdetails = new OrderDetails();
                $orderdetails->setOrderNumber($order);
                $orderdetails->setQuantity($orderedQuantity);
                $orderdetails->setPrice($productEntity->getPrice());
                $orderdetails->addProduct($productEntity);
                $orderdetails->setProductName($productEntity->getName());
                $entityManager->persist($orderdetails);
            }

            $entityManager->flush();

            return $this->render('order/recap.html.twig', [
                'method' => $order->getPaymentMethod(),
                'cartData' => $cartService->getTotalCart(),
                'transporter' => $selectedTransport,
                'deliveryAddress' => [
                    $order->getReceiverName(),
                    $order->getReceiverAddress(),
                    $order->getCity(),
                    $order->getZipCode(),
                ],
                'reference' => $reference,
            ]);
        }
    
        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash(
                'error',
                'Il y a un problème, lors de validation de la commande. Veuillez vérifier le formulaire!'
            );
    
            return $this->redirectToRoute('app_order');
        }
    
        // Add a default return statement
        return $this->redirectToRoute('app_cart');
    }

     #[Route('/order/modify/{reference}', name: 'app_order_modify', methods: ['GET', 'POST'])]
    public function modifyOrder(
        CartService $cartService,
        EntityManagerInterface $manager,
        $reference
    ): Response {
        $order = $manager->getRepository(Order::class)->findOneBy(['reference' => $reference]);
        $orderDetails = $manager->getRepository(OrderDetails::class)->findBy(criteria: ['orderNumber' => $order->getId()]);

        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if (!$order || $order->getUser() !== $user) {
            $this->addFlash('error', 'Commande introuvable ou vous n\'avez pas accès.');
            return $this->redirectToRoute('app_order');
        }

        if ($order->isPaid()) {
            $this->addFlash('error', 'Cette commande a déjà été payée et ne peut être modifiée.');
            return $this->redirectToRoute('app_order');
        }

        $form = $this->createForm(OrderType::class, $order, [
            'user' => $user,
        ]);

      

        return $this->render('order/index.html.twig', [
            'orderForm' => $form->createView(),
            'cartData' => $cartService->getTotalCart(),
            'order' => $order,
            'isModify' => true,
            'orderDetails' => $orderDetails,
        ]);
    }

    #[Route('/order/delete/{id}', name: 'app_order_delete', methods: ['GET', 'DELETE'])]
    public function deleteOrder(Order $order, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($order->getUser() !== $user) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer cette commande.');
            return $this->redirectToRoute('app_user_order');
        }

        if ($order->isPaid()) {
            $this->addFlash('error', 'Impossible de supprimer une commande déjà payée.');
            return $this->redirectToRoute('app_user_order');
        }

        // Remove related order details
        foreach ($order->getOrderDetails() as $orderDetail) {
            $entityManager->remove($orderDetail);
        }

        $entityManager->remove($order);
        $entityManager->flush();

        $this->addFlash('success', 'Commande supprimée avec succès.');
        return $this->redirectToRoute('app_user_order');
    }

    
}
