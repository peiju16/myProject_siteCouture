<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\Point;
use App\Form\CommentType;
use App\Repository\PointRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PointController extends AbstractController
{
    #[Route('/point', name: 'app_point')]
    public function index(): Response
    {
        return $this->render('point/index.html.twig', [
            'controller_name' => 'PointController',
        ]);
    }

    #[Route('/point/new/{formation}', name: 'app_point_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $manager, Formation $formation, Security $security, PointRepository $pointRepository): Response
    {
          // Check if the user is authenticated
        if (!$security->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->addFlash('error', 'Veuillez se connecter !');
            return $this->redirectToRoute('app_login'); // Adjust to your login route
        }

        // Check if the user has already left a comment for this formation
        $user = $this->getUser();
        $existingComment = $pointRepository->findOneBy(['user' => $user, 'formation' => $formation]);
        if ($existingComment) {
            $form = $this->createForm(CommentType::class, $existingComment);
            $this->addFlash('info', 'Vous avez déjà commenté cette formation. Vous pouvez modifier votre commentaire.');
        } else {
            $point = new Point();
            $point->setFormation($formation);
            $point->setUser($this->getUser());
            $form = $this->createForm(CommentType::class, $point);
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $point = $form->getData();
                $manager->persist($point);
                $manager->flush();
    
                $this->addFlash('success', 'Votre commentaire a bien été enrégistrer');
                return $this->redirectToRoute('app_formation_show', ['id' => $formation->getId()]);
            } catch (\Exception $e) {
                // Log the error if needed (optional)
                // Log error: $e->getMessage()
                $this->addFlash('error', 'Erreur: le commentaire n\'a pas pu être ajoutée');
            }         
        }
        

        return $this->render('point/new.html.twig', [
            'commentForm' => $form->createView(),
            'formation' => $formation
        ]);
    }

    #[Route('/point/delete/{id}', name: 'app_point_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, point $point, EntityManagerInterface $manager): Response
    {
        if (!$point) {
            return new JsonResponse(['status' => 'error', 'message' => 'Erreur: le commentaire n\'a pas pu être trouvée'], 404);
        }
    
        try {
            $manager->remove($point);
            $manager->flush();
    
            // Add success flash message
            $this->addFlash('success', 'Votre commentaire a bien été supprimée');
    
            return new JsonResponse(['status' => 'success']);
        } catch (\Exception $e) {
            // Log the error if necessary
            $this->addFlash('error', 'Erreur: le commentaire n\'a pas pu être supprimée');
            return new JsonResponse(['status' => 'error', 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }
}
