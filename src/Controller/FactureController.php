<?php

namespace App\Controller;

use App\Entity\Facture;
use App\Form\FactureType;
use App\Repository\FactureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FactureController extends AbstractController
{
    #[Route('/facture/new', name: 'facture_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $facture = new Facture();

        if ($request->isMethod('POST')) {
            // Remplir l'entité avec les données reçues (exemple POST brut, à adapter selon ton formulaire)
            $facture->setNumeroFacture($request->request->get('numeroFacture'));
            $facture->setDateFacture(new \DateTime($request->request->get('dateFacture')));
            $facture->setTypeFacture($request->request->get('typeFacture'));
            $facture->setDevise($request->request->get('devise'));
            $facture->setNomFournisseur($request->request->get('nomFournisseur'));
            $facture->setSirenFournisseur($request->request->get('sirenFournisseur'));
            $facture->setSiretFournisseur($request->request->get('siretFournisseur'));
            $facture->setTvaFournisseur($request->request->get('tvaFournisseur'));
            $facture->setCodePaysFournisseur($request->request->get('codePaysFournisseur'));
            $facture->setEmailFournisseur($request->request->get('emailFournisseur'));
            $facture->setNomAcheteur($request->request->get('nomAcheteur'));
            $facture->setSirenAcheteur($request->request->get('sirenAcheteur'));
            $facture->setEmailAcheteur($request->request->get('emailAcheteur'));
            $facture->setCommandeAcheteur($request->request->get('commandeAcheteur'));
            $facture->setTotalHT($request->request->get('totalHT'));
            $facture->setTotalTVA($request->request->get('totalTVA'));
            $facture->setTotalTTC($request->request->get('totalTTC'));
            $facture->setNetAPayer($request->request->get('netAPayer'));
            $facture->setDateEcheance(
                $request->request->get('dateEcheance') ? new \DateTime($request->request->get('dateEcheance')) : null
            );
            $facture->setDateLivraison(
                $request->request->get('dateLivraison') ? new \DateTime($request->request->get('dateLivraison')) : null
            );
            $facture->setModePaiement($request->request->get('modePaiement'));
            $facture->setReferencePaiement($request->request->get('referencePaiement'));
            $facture->setTvaDetails(json_decode($request->request->get('tvaDetails'), true));
            $facture->setRemisePied($request->request->get('remisePied'));
            $facture->setChargesPied($request->request->get('chargesPied'));
            $facture->setReferenceContrat($request->request->get('referenceContrat'));
            $facture->setReferenceBonLivraison($request->request->get('referenceBonLivraison'));
            $facture->setProfilFacturX($request->request->get('profilFacturX'));

            // Persistance
            $em->persist($facture);
            $em->flush();

            // Redirection ou message de succès
            return $this->redirectToRoute('facture_index');
        }

        // Affiche un formulaire simple (à remplacer par ton propre template)
        return $this->render('facture/new.html.twig', [
            'facture' => $facture
        ]);
    }

    #[Route('/facture/index', name: 'facture_index', methods: ['GET'])]
    public function index(FactureRepository $factureRepository): Response
    {
        $factures = $factureRepository->findAll();

        return $this->render('facture/index.html.twig', [
            'factures' => $factures,
        ]);
    }

        #[Route('/{id}', name: 'facture_show', methods: ['GET'])]
    public function show(Facture $facture): Response
    {
        return $this->render('facture/show.html.twig', [
            'facture' => $facture,
        ]);
    }

    #[Route('/{id}/edit', name: 'facture_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Facture $facture, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(FactureType::class, $facture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Facture mise à jour avec succès ✅');
            return $this->redirectToRoute('facture_index');
        }

        return $this->render('facture/edit.html.twig', [
            'facture' => $facture,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'facture_delete', methods: ['POST'])]
    public function delete(Request $request, Facture $facture, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$facture->getId(), $request->request->get('_token'))) {
            $em->remove($facture);
            $em->flush();
            $this->addFlash('danger', 'Facture supprimée ❌');
        }

        return $this->redirectToRoute('facture_index');
    }
}
