<?php

namespace App\Controller;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(CartService $cartService): Response
    {
        return $this->render('cart/index.html.twig', [
            'cartData' => $cartService->getTotalCart(),
        ]);
    }

    #[Route('/cart/add/{id<\d+>}', name: 'app_cart_add' )]
    public function add(CartService $cartService, int $id): Response
    {
        $cartService->addCart($id);
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/decrease/{id<\d+>}', name: 'app_cart_decrease' )]
    public function decrease(CartService $cartService, int $id): RedirectResponse
    {
        $cartService->decrease($id);
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove/{id<\d+>}', name: 'app_cart_remove' )]
    public function removeItem(CartService $cartService, int $id): Response
    {
        $cartService->removeItem($id);
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/removeAll', name: 'app_cart_removeAll')]
    public function removeAll(CartService $cartService): Response
    {
        $cartService->removeCartAll();
        return $this->redirectToRoute('app_product');
    }
}
