<?php

namespace App\Controller;

use App\Entity\ReservationEquipement;
use App\Form\ReservationEquipementType;
use App\Repository\ReservationEquipementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reservation/equipement')]
final class ReservationEquipementController extends AbstractController{
    #[Route(name: 'app_reservation_equipement_index', methods: ['GET'])]
    public function index(ReservationEquipementRepository $reservationEquipementRepository): Response
    {
        return $this->render('reservation_equipement/index.html.twig', [
            'reservation_equipements' => $reservationEquipementRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_reservation_equipement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reservationEquipement = new ReservationEquipement();
        $form = $this->createForm(ReservationEquipementType::class, $reservationEquipement);
        $form->handleRequest($request);
    
        // ✅ Debug temporaire pour confirmer la soumission
        if ($form->isSubmitted()) {
            dump('Soumis: ✅');
            dump('Valide: ', $form->isValid());
    
            if ($form->isValid()) {
                try {
                    $entityManager->persist($reservationEquipement);
                    $entityManager->flush();
    
                    $this->addFlash('success', 'Réservation créée avec succès');
                    return $this->redirectToRoute('app_reservation_equipement_index');
                } catch (ForeignKeyConstraintViolationException $e) {
                    $this->addFlash('error', 'L\'équipement ou l\'événement sélectionné n\'existe pas ou n\'est plus disponible');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de la création de la réservation');
                }
            }
        }
    
        return $this->render('reservation_equipement/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    

    #[Route('/{id}', name: 'app_reservation_equipement_show', methods: ['GET'])]
    public function show(ReservationEquipement $reservationEquipement): Response
    {
        return $this->render('reservation_equipement/show.html.twig', [
            'reservation_equipement' => $reservationEquipement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reservation_equipement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ReservationEquipement $reservationEquipement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReservationEquipementType::class, $reservationEquipement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reservation_equipement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation_equipement/edit.html.twig', [
            'reservation_equipement' => $reservationEquipement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_equipement_delete', methods: ['POST'])]
    public function delete(Request $request, ReservationEquipement $reservationEquipement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservationEquipement->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reservationEquipement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reservation_equipement_index', [], Response::HTTP_SEE_OTHER);
    }
}
