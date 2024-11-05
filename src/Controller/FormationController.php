<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Form\FormationType;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FormationController extends AbstractController
{
    #[Route('/formation', name: 'app_formation')]
    public function index(PaginatorInterface $paginator, FormationRepository $formationRepository, Request $request): Response
    {
        $formations = $paginator->paginate(
            $formationRepository->findAll(), /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            3 /* limit per page */
        );
    
        return $this->render('formation/index.html.twig', [
            'formations' => $formations
        ]);
    }

    #[Route('/formation/{id}', name: 'app_formation_show', methods: ['GET'])]
    public function show(Formation $formation): Response
    {
       
        return $this->render('formation/show.html.twig', [
            'formation' => $formation,
           
        ]);
    }

    #[Route('/new/formation', name: 'app_formation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $formation = new Formation();
        $form = $this->createForm(formationType::class, $formation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $formation = $form->getData();
                $manager->persist($formation);
                $manager->flush();
    
                $this->addFlash('success', 'Votre formation a bien été ajoutée');
                return $this->redirectToRoute('app_formation');
            } catch (\Exception $e) {
                // Log the error if needed (optional)
                // Log error: $e->getMessage()
                $this->addFlash('error', 'Erreur: la formation n\'a pas pu être ajoutée');
            }         
        }
        
        return $this->render('formation/new.html.twig', [
            'formationForm' => $form->createView()
        ]);
    }

    #[Route('/formation/edit/{id}', name: 'app_formation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Formation $formation, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(formationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $formation = $form->getData();
                $manager->persist($formation);
                $manager->flush();
    
                $this->addFlash('success', 'Votre formation a bien été modifié');
                return $this->redirectToRoute('app_formation');
            } catch (\Exception $e) {
                // Log the error if needed (optional)
                // Log error: $e->getMessage()
                $this->addFlash('error', 'Erreur: la formation n\'a pas pu être trouvée');
            }         
        }
        
        return $this->render('formation/edit.html.twig', [
            'formationForm' => $form->createView(),
            'formation' => $formation
        ]);
    }

    #[Route('/formation/delete/{id}', name: 'app_formation_delete', methods: ['POST'])]
    public function delete(Request $request, Formation $formation, EntityManagerInterface $manager): JsonResponse
    {
        if (!$formation) {
            return $this->json(['status' => 'error', 'message' => 'Erreur: la formation n\'a pas pu être trouvée'], 404);
        }
    
        try {
            $manager->remove($formation);
            $manager->flush();
    
            // Add success flash message
            $this->addFlash('success', 'Votre formation a bien été supprimée');
    
            return $this->json([
                'status' => 'success',
                'message' => 'Votre formation a bien été supprimée',
                'redirect' => $this->generateUrl('app_formation')
            ]);
        } catch (\Exception $e) {
            // Log the error if necessary
            $this->addFlash('error', 'Erreur: la formation n\'a pas pu être supprimée');
            return $this->json(['status' => 'error', 'message' => 'Erreur: la formation n\'a pas pu être supprimée'], 500);
        }
    }

}
