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
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(OrderType::class,  [
            'user' => $this->getUser(),
        ]);

        return $this->render('order/index.html.twig', [
            'orderForm' => $form->createView(),
            'cartData' => $cartService->getTotalCart(),
        ]);
        
        
       
    }

    #[Route('/order/verify', name:'app_order_verify', methods:['POST'])]
    public function verify(Request $request, EntityManagerInterface $entityManager, CartService $cartService): Response 
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $user = $this->getUser(); // Assuming the user is logged in
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
                        $transportAddress->setTitle($formData['addressTitle']);
                        $transportAddress->setAddress($order->getReceiverAddress());
                        $transportAddress->setCity($order->getCity());
                        $transportAddress->setZipCode($order->getZipCode());
                        $transportAddress->setUser($user);
                        $entityManager->persist($transportAddress);
                    }
                }
            }
        
            // Set the order method
            $order->setUser($user);
            $order->setReference($reference);
            $order->setTransportWay($selectedTransport->getTitle);
            $order->setTransportPrice($selectedTransport->getPrice);
            $order->setPaid(0);
            $order->setPaymentMethod("stripe");
            $order->setTotalPrice($cartService->getTotalCart()['cartTotal'] + $selectedTransport->getPrice );
        
            $entityManager->persist($order);

            // set Order details
            foreach ($cartService->getTotalCart() as $product) {
                $orderdetails = new OrderDetails();
                $orderdetails->setOrderNumber($order->getId());
                $orderdetails->setQuantity($product['quantity']);
                $orderdetails->setPrice($product->getPrice());
                $orderdetails->setProductName($product->getName());
            }
            $entityManager->flush();
        
            return $this->redirectToRoute('app_order_success');
        }

        return $this->render('order/index.html.twig', [
            'orderForm' => $form->createView(),
            'cartData' => $cartService->getTotalCart(),
        ]);
        
    }
}
