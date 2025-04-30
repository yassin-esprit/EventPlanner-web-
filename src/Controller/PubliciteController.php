<?php

namespace App\Controller;


use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

use App\Entity\Publicite;
use App\Form\PubliciteType;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Repository\PubliciteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/publicite')]
final class PubliciteController extends AbstractController{
    #[Route(name: 'app_publicite_index', methods: ['GET'])]
    public function index(PubliciteRepository $publiciteRepository): Response
    {
        return $this->render('publicite/index.html.twig', [
            'publicites' => $publiciteRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_publicite_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $publicite = new Publicite();
        $form = $this->createForm(PubliciteType::class, $publicite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($publicite);
            $entityManager->flush();

            return $this->redirectToRoute('app_publicite_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('publicite/new.html.twig', [
            'publicite' => $publicite,
            'form' => $form,
            
        ]);
    }

    #[Route('/{id_pub}', name: 'app_publicite_show', methods: ['GET'])]
    public function show(Publicite $publicite): Response
    {
        return $this->render('publicite/show.html.twig', [
            'publicite' => $publicite,
        ]);
    }

    #[Route('/{id_pub}/edit', name: 'app_publicite_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Publicite $publicite, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PubliciteType::class, $publicite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_publicite_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('publicite/edit.html.twig', [
            'publicite' => $publicite,
            'form' => $form,
        ]);
    }

    #[Route('/{id_pub}', name: 'app_publicite_delete', methods: ['POST'])]
    public function delete(Request $request, Publicite $publicite, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$publicite->getId_pub(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($publicite);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_publicite_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id_pub}/pdf', name: 'app_publicite_pdf' ,methods: ['GET'])]
public function generatePdf(int $id_pub, PubliciteRepository $publiciteRepository): Response
{
    // 1. Récupérer la publicité via le repository injecté
    $publicite = $publiciteRepository->find($id_pub);

    if (!$publicite) {
        throw $this->createNotFoundException("Cette publicité n'existe pas.");
    }

    // 2. Configurer Dompdf
    $pdfOptions = new Options();
    $pdfOptions->set('defaultFont', 'Arial');
    $pdfOptions->set('isRemoteEnabled', true); // Permet d'afficher les images
    $pdfOptions->set('isHtml5ParserEnabled', true);

    // 3. Initialiser Dompdf
    $dompdf = new Dompdf($pdfOptions);

    // 4. Générer le HTML depuis Twig
    $html = $this->renderView('publicite/pdf_template.html.twig', [
        'publicite' => $publicite,
    ]);

    // 5. Charger le HTML dans Dompdf
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');

    // 6. Générer le PDF
    $dompdf->render();

    // 7. Nom du fichier PDF (ex: "publicite-45-2024-03-15.pdf")
    $filename = "publicite-" . $publicite->getIdPub() . "-" . date('Y-m-d') . ".pdf";

    // 8. Retourner le PDF en réponse
    return new Response(
        $dompdf->output(),
        200,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]
    );

}

}
