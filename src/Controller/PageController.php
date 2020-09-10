<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Client;
use App\Entity\Pointage;
use App\Repository\ClientRepository;
use App\Repository\PointageRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PageController extends AbstractController
{
    /**
     * @Route("/profile/client_present", name="client_present")
     */
    public function client_present(ClientRepository $repoClient)
    {
        $user = new User();
        $items = $repoClient->findAll();
        //dd($items);
        return $this->render('page/client_present.html.twig', [
            'items' => $items
        ]);
    }
    /**
     * @Route("/admin/client_paye", name="client_paye")
     */
    public function client_paye(ClientRepository $repoClient)
    { 
        $user = new User();
        $items = $repoClient->findAll();
        return $this->render('page/client_paye.html.twig', [
            'items' => $items,
        ]);
    }
    /**
     * @Route("/admin/client_suspendu", name="client_suspendu")
     */
    public function client_suspendu(ClientRepository $repoClient)
    {
        $user = new User();
        $items = $repoClient->findAll();
        return $this->render('page/client_suspendu.html.twig', [
            'items' => $items,
        ]);
    }
    /**
     * @Route("/profile/single_page/{id_client}", name="single_page")
     */
    public function single_page($id_client, ClientRepository $repoClient, PointageRepository $repoPointage)
    {   
        $client = new Client();
        $client = $repoClient->find($id_client);
        $pointage = new Pointage();
        //dd($client);
        $montant = $client->getMontant();
        $nbrMois = $client->getNbrVersement();
        $montant_mensuel = $client->getMontantMensuel();
        $pointage = $repoPointage->findByClient($client);
        $nbr_p_effectue = count($pointage);
        //dd($nbr_p_effectue);
        // nombre de mois restant

        $nbr_mois_restant = $nbrMois - $nbr_p_effectue;
        // montant payé
        $montant_paye = $montant_mensuel * $nbr_p_effectue;

        // montant restant
        $montant_restant = $montant_mensuel * $nbr_mois_restant;
        
        return $this->render('page/single_page.html.twig', [
            'item' => $client,
            'montant_paye' => $montant_paye,
            'montant_restant' => $montant_restant,
            'nbr_p_effectue' => $nbr_p_effectue,
            'nbr_paiement_total' => $nbrMois,
        ]);
    }
    /**
     * @Route("/admin/pointage", name="pointage")
     */
    public function pointage()
    {
        return $this->render('page/pointage.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }
    
}
