<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductImage;
use App\Form\ImageType;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product', methods: ['GET'])]
    public function index(ProductRepository $productRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $products = $paginator->paginate(
            $productRepository->findAll(), /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            3 /* limit per page */
        );
    
        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
            'produits' => $products
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product, EntityManagerInterface $manager, int $id): Response
    {
       
        return $this->render('product/show.html.twig', [
            'product' => $product,
           
        ]);
    }

    #[Route('/new/product', name: 'app_product_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $product = $form->getData();
                 // Filter out images with null imageFile before persisting
                foreach ($product->getProductImages() as $productImage) {
                    if ($productImage->getImageFile() === null) {
                        $product->removeProductImage($productImage);
                    }
                }
                $manager->persist($product);
                $manager->flush();
    
                $this->addFlash('success', 'Votre produit a bien été ajouté');
                return $this->redirectToRoute('app_product');
            }  catch (\Doctrine\DBAL\Exception $e) {
                // Handle database-specific errors here
                $this->addFlash('error', 'Database error: '.$e->getMessage());
            }
            
               
        }
        
        return $this->render('product/new.html.twig', [
            'productForm' => $form->createView(),
            'context' => 'new',
        ]);
    }

    #[Route('/product/edit/{id}', name: 'app_product_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Product $product, EntityManagerInterface $manager): Response
    {
        $originalimages = new ArrayCollection();

        // Create an ArrayCollection of the current Tag objects in the database
        foreach ($product->getProductImages() as $image) {
            $originalimages->add($image);
        }
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $product = $form->getData();
                foreach ($originalimages as $image) {
                    if (!$product->getProductImages()->contains($image)) {
                        $image->setProduct(null); // Break relationship
                        $manager->remove($image); // Optionally delete the entity
                    }
                }
                  // Filter out invalid images
                foreach ($product->getProductImages() as $productImage) {
                    if (!$productImage->getImageFile() && !$productImage->getImageName()) {
                        $product->removeProductImage($productImage);
                    }
                }

                $manager->persist($product);
                $manager->flush();
    
                $this->addFlash('success', 'Votre produit a bien été modifié');
                return $this->redirectToRoute('app_product');
            } catch (\Exception $e) {
                // Log the error if needed (optional)
                // Log error: $e->getMessage()
                $this->addFlash('error', 'Erreur: le produit n\'a pas pu être trouvé');
            }         
        }
        
        return $this->render('product/edit.html.twig', [
            'productForm' => $form->createView(),
            'product' => $product,
            'images' => $originalimages,
            'context' => 'edit',
        ]);
    }
    
    #[Route('/product/delete/{id}', name: 'app_product_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteProduct(Product $product, EntityManagerInterface $manager): Response
    {
        // Check if the user has the admin role
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous n\'avez pas les droits pour supprimer ce produit.');
        }
        if (!$product) {
            return $this->json(['status' => 'error', 'message' => 'Erreur: le produit n\'a pas pu être trouvé'], 404);
        }
          
        try {
            $manager->remove($product);
            $manager->flush();
            $this->addFlash('success', 'Votre produit a bien été supprimé');
            // Send a success response back to the frontend
            return new JsonResponse(['status' => 'success']);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur: le produit n\'a pas pu être supprimé');
            return new JsonResponse(['status' => 'error', 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    } 

}
