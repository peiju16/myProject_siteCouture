<?php

namespace App\Service;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Config\Framework\RequestConfig;

class CartService 
{
    private RequestStack $requestStack;
    private EntityManagerInterface $manager;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $manager) {
        $this->requestStack = $requestStack;
        $this->manager = $manager;
    }

    public function addCart(int $id, int $quantity): void
    {
        $cart = $this->getSession()->get('cart', []);

        // Fetch the product to check stock availability
        $product = $this->manager->getRepository(Product::class)->find($id);

        if ($product === null) {
            throw new \InvalidArgumentException('Produit n\'exsist pas.');
        }

        if (!empty($cart[$id])) {
            // Increment quantity only if it's within stock limits
            $newQuantity = $cart[$id] + $quantity;

            if ($newQuantity > $product->getStock()) {
                throw new \InvalidArgumentException('La quantity commndée dépasse le stock.');
            }

            $cart[$id] = $newQuantity;
        } else {
            // Add product with the specified quantity if within stock limits
            if ($quantity > $product->getStock()) {
                throw new \InvalidArgumentException('La quantity commndée dépasse le stock.');
            }

            $cart[$id] = $quantity;
        }

        $this->getSession()->set('cart', $cart);
    }

    
    
    public function decrease(int $id): void {
        $cart = $this->getSession()->get('cart', []);
        if (isset($cart[$id]) && $cart[$id] > 1) { // Safely check quantity
            $cart[$id]--;
        } else {
            unset($cart[$id]); // Remove if quantity reaches 0
        }
        $this->getSession()->set('cart', $cart);
    }
    
    public function getTotalCart(): array
    {
        $cart = $this->getSession()->get('cart', []); // Ensure default is an empty array
        $cartData = [];
        $cartTotal = 0;

        if (!empty($cart)) {
            foreach ($cart as $id => $quantity) {
                $product = $this->manager->getRepository(Product::class)->findOneBy(['id' => $id]);

                if (!$product) {
                    // Skip if product doesn't exist
                    $this->removeItem($id);
                    continue;
                }

                // Calculate the total for this product
                $productTotal = (float)$product->getPrice() * $quantity;

                // Add to overall cart total
                $cartTotal += $productTotal;

                // Add product details to the cart data
                $cartData[] = [
                    'product' => $product, // Ensure this is the full product object
                    'quantity' => $quantity,
                    'productTotal' => $productTotal, // Total for this specific product
                ];
            }
        }

        return [
            'items' => $cartData, // Product data array
            'cartTotal' => $cartTotal, // Total amount
        ];
        
    }

    public function removeItem(int $id) {
        $cart = $this->requestStack->getSession()->get('cart', []);
        unset($cart[$id]);
        return $this->getSession()->set('cart', $cart);
    }

    public function removeCartAll() {
        return $this->getSession()->remove('cart');
    }

    public function getSession(): SessionInterface {
        return $this->requestStack->getSession();
    }




}