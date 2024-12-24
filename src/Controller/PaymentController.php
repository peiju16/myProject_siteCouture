<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Entity\Transport;
use App\Service\CartService;
use App\Service\MailerService;
use App\Service\PayPalService;
use App\Service\PdfGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Stripe\Checkout\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Stripe\Stripe;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PaymentController extends AbstractController
{
    private PayPalService $paypalService;

    public function __construct(PayPalService $paypalService)
    {
        $this->paypalService = $paypalService;
    }


    
    #[Route('/payment/create-session-stripe/{reference}', name: 'app_payment_stripe')]
    public function stripeCheckout($reference, EntityManagerInterface $manager, UrlGeneratorInterface $generator): RedirectResponse
    {
        $productStripe = [];
        $order = $manager->getRepository(Order::class)->findOneBy(['reference' => $reference]);

        if (!$order) {
            return $this->redirectToRoute('app_cart');
        }
        
        foreach ($order->getOrderDetails()->getValues() as $product) {
            $productStripe[] = [
                'price_data' => [
                    'currency' => 'EUR',
                    'unit_amount' => (int)($product->getPrice() * 100), 
                    'product_data' => [
                        'name' => $product->getProductName()
                    ]
                ],
                'quantity' => $product->getQuantity()
            ]; 
        }

        $transportData = $manager->getRepository(Transport::class)->findOneBy(['id' => $order->getTransportway()]);
        $productStripe[] = [
            'price_data' => [
                'currency' => 'EUR',
                'unit_amount' => (int)($order->getTransportPrice() * 100),
                'product_data' => [
                    'name' => $transportData->getTitle()
                ]
            ],
            'quantity' => 1
        ]; 
        
        Stripe::setApiKey('sk_test_51P1O6Z02w5A47MPXpvMmocrsPyqVmDUVjauWzz1QjqwiZZH03fu5W44OrfpPZlDvLCg4cu8h8RcxAnUvOMwmS6nZ00yWPszNYg');
        //header('Content-Type: application/json');
        //$YOUR_DOMAIN = 'http://localhost:4242';
        

        $checkout_session = Session::create([
          'customer_email' => $this->getUser()->getEmail(),
          'line_items' => $productStripe,
          'mode' => 'payment',
          'success_url' => $generator->generate('app_payment_success', [
                'reference' => $order->getReference()
          ], UrlGeneratorInterface::ABSOLUTE_URL),
          'cancel_url' => $generator->generate('app_payment_error', [
                'reference' => $order->getReference()
          ], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        $order->setStripSessionId($checkout_session->Id);
        $manager->flush();

        return new RedirectResponse($checkout_session->url);
        
        // header("HTTP/1.1 303 See Other");
        // header("Location: " . $checkout_session->url);
    }

    #[Route('/payment/create-session-paypal/{reference}', name: 'app_payment_paypal')]
    public function createOrder(Request $request, EntityManagerInterface $manager, $reference, UrlGeneratorInterface $generator): RedirectResponse
    {

         $order = $manager->getRepository(Order::class)->findOneBy(['reference' => $reference]);
 
         if (!$order) {
             return $this->redirectToRoute('app_cart');
         }
 
         $items = [];
         $itemTotal = 0;
 
         foreach ($order->getOrderDetails()->getValues() as $product) {
             $items[] = [
                 'name' => $product->getProductName(),
                 'quantity' => $product->getQuantity(),
                 'unit_amount' => [
                     'currency_code' => 'EUR',
                     'value' => number_format($product->getPrice(), 2, '.', ''),
                 ],
             ];
             $itemTotal += $product->getPrice() * $product->getQuantity();
         }
 
         $totalPrice = $itemTotal + $order->getTransportPrice();
 
         $request = new OrdersCreateRequest();
         $request->body = [
             'intent' => 'CAPTURE',
             'purchase_units' => [
                 [
                     'amount' => [
                         'currency_code' => 'EUR',
                         'value' => number_format($totalPrice, 2, '.', ''),
                         'breakdown' => [
                             'item_total' => [
                                 'currency_code' => 'EUR',
                                 'value' => number_format($itemTotal, 2, '.', ''),
                             ],
                             'shipping' => [
                                 'currency_code' => 'EUR',
                                 'value' => number_format($order->getTransportPrice(), 2, '.', ''),
                             ],
                         ],
                     ],
                     'items' => $items,
                 ],
             ],
             'application_context' => [
                 'return_url' => $generator->generate('app_payment_success', [
                    'reference' => $order->getReference()
                    ], UrlGeneratorInterface::ABSOLUTE_URL),
                
                 'cancel_url' => $generator->generate('app_payment_error', [
                    'reference' => $order->getReference()
                    ], UrlGeneratorInterface::ABSOLUTE_URL),
             ],
         ];

         try {
            $client = $this->paypalService->getClient();
            $response = $client->execute($request);
            $result = $response->result;
            $order->setPaypalOrderId($response->result->Id);
            $manager->flush();
            // Redirect to PayPal checkout page
            return $this->redirect($result->links[1]->href);
        } catch (Exception $e) {
            // Handle exceptions
            return $this->render('order/error.html.twig', ['error' => $e->getMessage()]);
        }
        
     }
 
    
     #[Route('/payment/success/{reference}', name: 'app_payment_success')]
     public function paypalSuccess(
         string $reference,
         CartService $cartService,
         EntityManagerInterface $manager,
         MailerService $mailerService,
         PdfGeneratorService $pdfGenerator
     ): Response {
        $order = $manager->getRepository(Order::class)->findOneBy(['reference' => $reference]);
        if (!$order || $order->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('app_cart');
        }
    
        if (!$order->isPaid()) {
            $cartService->removeCartAll();
            $order->setPaid(true);
            $order->setStatus('En Cours');
        }
        $manager->persist($order);
    
        if (!$order->getInvoice()) {
            $invoice = new Invoice();
            $invoice->setOrderInvoice($order);
            $invoice->setUser($order->getUser());
            $manager->persist($invoice);
            $manager->flush();

            $transportData = $manager->getRepository(Transport::class)->findOneBy(['id' => $order->getTransportway()]);
            $pdfPath = $pdfGenerator->generatePdf(
                'pdf/invoice.html.twig', // Pass the template dynamically
                [
                    'user' => $this->getUser(),
                    'invoiceNumber' => $invoice->getId(),
                    'order' => $order,
                    'orderDetails' => $order->getOrderDetails(),
                    'transport' => $transportData
                ],
                './images/factures',
                sprintf('invoice_%d_%d.pdf', $invoice->getId(), $this->getUser()->getId())
            );
            
    
            $invoice->setPdfPath($pdfPath);
            $invoice->setPdf(true);
    
            $manager->persist($invoice);
    
            $mailerService->send(
                $this->getUser()->getEmail(),
                'Confirmation de votre commande',
                'commande_confirm.html.twig',
                [
                    'user' => $this->getUser(),
                    'order' => $order,
                    'orderDetails' => $order->getOrderDetails(),
                    'invoice' => $invoice
                ],
                [
                    [
                        'path' => $pdfPath,
                        'name' => basename($pdfPath),
                    ]
                ]
            );
            
        }
    
        $manager->flush();
    
        return $this->render('order/success.html.twig', [
            'invoiceNumber' => $order->getInvoice() ? $order->getInvoice()->getId() : null,
            'order' => $order,
            'user' => $this->getUser(),
            'orderDetails' => $order->getOrderDetails(),
        ]);
    }
     
 
     #[Route('/payment/error/{reference}', name: 'app_payment_error')]
     public function paypalError($reference, CartService $cartService, EntityManagerInterface $manager): Response
     {
         $order = $manager->getRepository(Order::class)->findOneBy(['reference' => $reference]);
         if (!$order || $order->getUser() !== $this->getUser()) {
             return $this->redirectToRoute('app_cart');
         }
         return $this->render('order/error.html.twig');
     }

     #[Route('/payment/retry/{reference}', name: 'app_payment_retry')]
    public function retryPayment($reference, EntityManagerInterface $manager): Response
    {
        $order = $manager->getRepository(Order::class)->findOneBy(['reference' => $reference]);

        if (!$order || $order->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('app_cart');
        }

        // Redirect the user to the appropriate payment method (Stripe/PayPal) based on their choice.
        // Assume the payment method is stored in the order entity.

        if ($order->getPaymentMethod() === 'stripe') {
            return $this->redirectToRoute('app_payment_stripe', ['reference' => $reference]);
        } elseif ($order->getPaymentMethod() === 'paypal') {
            return $this->redirectToRoute('app_payment_paypal', ['reference' => $reference]);
        }

        return $this->redirectToRoute('app_cart'); // Fallback to the cart.
    }



}
