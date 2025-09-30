<?php

namespace App\Controller;

use App\Entity\Facture;
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
            return $this->redirectToRoute('facture_success');
        }

        // Affiche un formulaire simple (à remplacer par ton propre template)
        return $this->render('facture/new.html.twig', [
            'facture' => $facture
        ]);
    }
}
