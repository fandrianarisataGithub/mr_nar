<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Pointage;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PointageController extends AbstractController
{
    /**
     * @Route("/admin/pointer", name="pointer")
     */
    public function index(Request $request, ClientRepository $repoClient, EntityManagerInterface $manager)
    {
       
        $response = new Response();
        $client = new Client();
        $erreur = 0;
        if($request->isXmlHttpRequest()){
            $client_id = $request->get('client_id');
            $p_annee = $request->get('p_annee');
            $p_mois = $request->get('p_mois');
            $montant_mensuel = $request->get('montant_mensuel');
            
            // testons si le client ment :D 

            $client = $repoClient->find($client_id);
            $mm_client = $client->getMontantMensuel();
            if($montant_mensuel != $mm_client){
                $erreur = "Le montant mensuel concerné est incorrecte";
            }
            else{
                
                $pointage = new Pointage();
                $pointage->setPMois($p_mois);
                $pointage->setPAnnee($p_annee);
                $pointage->addClient($client);
                $pointage->setCreatedAt(new \DateTime());
                $manager->persist($pointage);
                $manager->flush();
            }
            
            $data = json_encode($erreur); // formater le résultat de la requête en json

            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response;
        }
        
        return $this->render('pointage/index.html.twig', [
            'controller_name' => 'PointageController',
        ]);
    }
}
