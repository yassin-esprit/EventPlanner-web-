<?php

 namespace App\Controller;

use App\Entity\Equipement;
use App\Form\EquipementType;
use App\Repository\EquipementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Service\GoogleCalendarService;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Calendar\Calendar;



use App\Controller\EquipmentQrCodeController;

use Dompdf\Dompdf;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

#[Route('/equipement')]
final class EquipementController extends AbstractController{
    #[Route(name: 'app_equipement_index', methods: ['GET'])]

    public function index(EquipementRepository $equipementRepository): Response
{
    return $this->render('equipement/index.html.twig', [
        'equipements' => $equipementRepository->findAll(),
        
    ]);
}



  #[Route(name:'app_equipement_search', methods:['GET'])]
 
public function search(Request $request): Response
{
    $searchTerm = $request->query->get('q');
    $status = $request->query->get('statut');

    $queryBuilder = $this->getDoctrine()
        ->getRepository(Equipement::class)
        ->createQueryBuilder('e');

    if ($searchTerm) {
        $queryBuilder->andWhere('e.nom LIKE :term OR e.description LIKE :term')
            ->setParameter('term', '%'.$searchTerm.'%');
    }

    if ($status && $status !== 'all') {
        $queryBuilder->andWhere('e.statut = :status')
            ->setParameter('status', $status);
    }

    $equipements = $queryBuilder->getQuery()->getResult();

    return $this->render('dashboard/admin/admin.html.twig', [
        'equipements' => $equipements
    ]);
}
    #[Route('/new', name: 'app_equipement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $equipement = new Equipement();
        $form = $this->createForm(EquipementType::class, $equipement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($equipement);
            $entityManager->flush();

            return $this->redirectToRoute('admin_dashboard', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('equipement/new.html.twig', [
            'equipement' => $equipement,
            'form' => $form,
        ]);
    }

    #[Route('/{id_eq}', name: 'app_equipement_show', methods: ['GET'])]
    public function show(Equipement $equipement): Response
    {
        return $this->render('equipement/show.html.twig', [
            'equipement' => $equipement,
        ]);
    }

    #[Route('/{id_eq}/edit', name: 'app_equipement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Equipement $equipement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EquipementType::class, $equipement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_dashboard', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('equipement/edit.html.twig', [
            'equipement' => $equipement,
            'form' => $form,
        ]);
    }

    #[Route('/{id_eq}', name: 'app_equipement_delete', methods: ['POST'])]
    public function delete(Request $request, Equipement $equipement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$equipement->getId_eq(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($equipement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_dashboard', [], Response::HTTP_SEE_OTHER);
    }



    //bundle pdf 
    #[Route('/equipement/pdf', name: 'app_equipement_pdf')]
    public function generatePdf(EquipementRepository $equipementRepository): Response
    {
        // Fetch all equipment from the repository
        $equipements = $equipementRepository->findAll();

        // Render the Twig template as HTML
        $html = $this->renderView('equipement/pdf.html.twig', [
            'equipements' => $equipements,
        ]);

        // Initialize Dompdf
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Generate the PDF content
        $pdfContent = $dompdf->output();

        // Create a response with the PDF
        $response = new Response($pdfContent);
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'equipment_list.pdf'
        );
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }


}
