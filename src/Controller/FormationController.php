<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\Point;
use App\Entity\Reservation;
use App\Form\FormationType;
use App\Repository\FormationRepository;
use App\Repository\PointRepository;
use App\Repository\ReservationRepository;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class FormationController extends AbstractController
{
    #[Route('/formation', name: 'app_formation')]
    public function index(PaginatorInterface $paginator, FormationRepository $formationRepository, Request $request, PointRepository $pointRepository): Response
    {
        $formations = $paginator->paginate(
            $formationRepository->findAll(), /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            3 /* limit per page */
        );

        $averagePoints = [];
        foreach ($formations as $formation) {
            $averagePoints[$formation->getId()] = $pointRepository->getAveragePointForFormation($formation);
        }
    
        return $this->render('formation/index.html.twig', [
            'formations' => $formations,
            'averagePoints' => $averagePoints
        ]);
    }

    #[Route('/formation/{id}', name: 'app_formation_show', methods: ['GET'])]
    public function show(Formation $formation, PointRepository $pointRepository, PaginatorInterface $paginator, Request $request, ReservationRepository $reservationRepository): Response
    {
        $user = $this->getUser();
        $hasReservation = false;

        if ($user) {
            $existingReservation = $reservationRepository->findOneBy(['formation' => $formation, 'user' => $user]);
            if ($existingReservation) {
                $hasReservation = true;
            }
        }
        // Get comments for the given formation, ordered by ID in descending order (most recent first)
        $comments = $paginator->paginate(
            $pointRepository->findBy(
                ['formation' => $formation], // Filter by formation ID
                ['id' => 'DESC'] // Order by id in descending order (most recent first)
            ),
            $request->query->getInt('page', 1), // Page number
            3 // Limit per page
        );

        return $this->render('formation/show.html.twig', [
            'formation' => $formation,
            'comments' => $comments,
            'hasReservation' => $hasReservation,     
        ]);
    }

    #[Route('/new/formation', name: 'app_formation_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
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
    #[IsGranted('ROLE_ADMIN')]
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
    #[IsGranted('ROLE_ADMIN')]
    public function deleteFormation(Formation $formation, EntityManagerInterface $manager): Response
    {
        // Check if the user has the admin role
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous n\'avez pas les droits pour supprimer cette formation.');
        }
        if (!$formation) {
            return $this->json(['status' => 'error', 'message' => 'Erreur: la formation n\'a pas pu être trouvé'], 404);
        }

        try {
            $manager->remove($formation);
            $manager->flush();
            $this->addFlash('success', 'Votre formation a bien été supprimé');
            
            // Send a success response back to the frontend
            return new JsonResponse(['status' => 'success']);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur: la formation n\'a pas pu être supprimée');
            return new JsonResponse(['status' => 'error', 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }


    #[Route('/formation/reserve/{id}', name: 'app_formation_reserve', methods: ['GET', 'POST'])]
    public function reserve(Formation $formation, EntityManagerInterface $entityManager, ReservationRepository $reservationRepository, MailerService $mailerService): Response
    {
        $user = $this->getUser();

        // Check if the user is logged in
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Check if the user has already reserved a place
        $existingReservation = $reservationRepository->findOneBy(['formation' => $formation, 'user' => $user]);
        if ($existingReservation) {
            $hasReservation = true;
            $this->addFlash('error', 'Vous avez déjà réservé une place pour cette formation.');
            return $this->redirectToRoute('app_formation_show', ['id' => $formation->getId()]);
        }

        // // Check if the user has reached the maximum number of reservations (e.g., 3 reservations per user)
        // $userReservations = $reservationRepository->findBy(['user' => $user]);
        // if (count($userReservations) >= 3) { // Adjust the limit as needed
        //     $this->addFlash('error', 'Vous avez atteint le nombre maximum de réservations.');
        //     return $this->redirectToRoute('app_formation_show', ['id' => $formation->getId()]);
        // }

        // Check if the formation has available places
        $currentReservations = $reservationRepository->countByFormation($formation);
        if ($currentReservations >= $formation->getNbrPlace()) {
            $this->addFlash('error', 'Cette formation est complète.');
            return $this->redirectToRoute('app_formation_show', ['id' => $formation->getId()]);
        }

        // Create a new reservation
        $reservation = new Reservation();
        $reservation->setFormation($formation);
        $reservation->setUser($user);
        $entityManager->persist($reservation);
        $entityManager->flush();

        $mailerService->send(
            $this->getUser()->getEmail(),
            'Confirmation de votre Réservation',
            'reservation_confirm.html.twig',
            [
                'user' => $this->getUser(),
                'formation' => $formation,
            ],
        );

        $this->addFlash('success', 'Vous avez réservé une place pour cette formation avec succès.');

        return $this->redirectToRoute('app_formation_show', ['id' => $formation->getId()]);
    }

}
