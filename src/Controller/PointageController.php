<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Pointage;
use App\Repository\ClientRepository;
use App\Repository\PointageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PointageController extends AbstractController
{
    /**
     * @Route("/admin/pointer_client" , name="pointer_client")
     */
    public function pointer_client(Request $request, ClientRepository $repoClient, EntityManagerInterface $manager, PointageRepository $repoPointage)
    {
        $response = new Response();
        
        if($request->isXmlHttpRequest()){
            $error = 0;
            $client_id = $request->get('client_id');
            $pointage_id = $request->get('pointage_id');
            $montant_mensuel = $request->get('montant_mensuel');
            $client = new Client();
            $pointage = new Pointage();
            $client = $repoClient->find($client_id);
            $pointage = $repoPointage->find($pointage_id);
            $son_mm = $client->getMontantMensuel();
            if($son_mm != $montant_mensuel){
                // erreur de montant
                $error = 1;
            }
            else{
                
                $pointage->addClient($client);
                $manager->persist($pointage);

                // mettre ce client parmis les pointé
                $client->setEtatClient('pointé');
                $manager->persist($client);
                $manager->flush();
                
               
            }
            $data = json_encode($error); // formater le résultat de la requête en json
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response;
        }

        $items = $repoClient->findAll();
        $now = new \DateTime();
        $year_now = $now->format('Y');
        // dd($year_now);
        $pointages = $repoPointage->findByAnneeActuelle($year_now);
        return $this->render('pointage/pointage.html.twig',[
            "items" => $items,
            "pointages" => $pointages,
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'archived' => $this->count_archived($repoClient),
            'pointed' => $this->count_pointed($repoClient),
        ]);
    }

    /**
     * @Route("/admin/pointage", name="pointage")
     */
    public function pointage(Request $request, ClientRepository $repoClient, EntityManagerInterface $manager, PointageRepository $repoPointage)
    {   
        // creer le nom_pointage  de pointage
        $error = 0;

        if($request->request->count() > 0){
           $m = $request->request->get('mois_pointage');
           $annee = $request->request->get('annee_pointage');
           $nom = $m."-".$annee;
           $mois = $this->generateNameMonth($m);
           $nom_lit = $mois."-".$annee;
            $pointage = $repoPointage->findOneByNom($nom);
            if(!$pointage){
                $created_at = new \DateTime();
                $pointage = new Pointage();
                $pointage->setNom($nom);
                $pointage->setNomLit($nom_lit);
                $pointage->setCreatedAt($created_at);
                $pointage->setAnneeActuelle($annee);
                $manager->persist($pointage);
                $manager->flush();
            }
            else {
                $error = 1;
            }
           
        }
        $now = new \DateTime();
        $year_now = $now->format('Y');
        // dd($year_now);
        $pointages = $repoPointage->findByAnneeActuelle($year_now);
        
        $items = $repoClient->countPresent('présent');
        return $this->render('pointage/pointage.html.twig', [
            "items" => $items,
            "pointages" => $pointages,
            "error" => $error,
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'archived' => $this->count_archived($repoClient),
            'pointed' => $this->count_pointed($repoClient),
        ]);
    }

    public function count_present(ClientRepository $repoClient)
    {
        $tabPresent = $repoClient->countPresent('présent');
        $n = count($tabPresent);
        //dd($n);
        return $n;
    }
    public function count_pointed(ClientRepository $repoClient)
    {
        $tabPaye = $repoClient->countPresent('pointé');
        $n = count($tabPaye);
        //dd($n);
        return $n;
    }
    public function count_suspendu(ClientRepository $repoClient)
    {
        $tabPresent = $repoClient->countPresent('suspendu');
        $n = count($tabPresent);

        return $n;
    }
    public function count_archived(ClientRepository $repoClient)
    {
        $tabPresent = $repoClient->countPresent('archivé');
        $n = count($tabPresent);
        //dd($n);
        return $n;
    }
    public function generateNameMonth($n){
        $tab = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre' ];
       $i = $n - 1 ;
        $mois = $tab[$i];
        return $mois;
    }
    
}
