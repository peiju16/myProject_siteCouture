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
    
            // Set Order details
            foreach ($cartService->getTotalCart()['items'] as $product) {
                $orderdetails = new OrderDetails();
                $orderdetails->setOrderNumber($order);
                $orderdetails->setQuantity($product['quantity']);
                $orderdetails->setPrice($product['product']->getPrice());
                $orderdetails->addProduct($product['product']);
                $orderdetails->setProductName($product['product']->getName());
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
        ]);
    }

    
}
