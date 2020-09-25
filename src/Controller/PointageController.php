<?php

namespace App\Controller;

use DateInterval;
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
        //dd($pointages);
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


    /**
     * @Route("/admin/trier", name="trier")
     */
    public function tri_pointage(Request $request, PointageRepository $repoPointage, ClientRepository $repoClient)
    {
        $pointage = new Pointage();
        $client_present = $repoClient->countPresent('présent');
        $client_a_pointer = [];
        if($request->request->count()>0){
             $id_pointage = $request->request->get('id_pointage_trier');
             $pointage = $repoPointage->find($id_pointage);
             //dd($pointage);
             $nom_pointage = $pointage->getNom();
            
             foreach($client_present as $item){
                /** test */
                $date_debut = $item->getDateDebut();
                $date_debut_s = $date_debut->format("Y-m-d");
                $next = $date_debut->add(new \DateInterval('P10D'));
                //dd($date_debut);
                
                /*$date_debut->add(new \DateInterval('P1M')); //Où 'P12M' indique 'Période de 12 Mois'
                $date_debut->format('Y-m-d');
                dd($date_debut);*/
                $tab = [];
                for($i = 1; $i<$item->getNbrVersement(); $i++){
                    $date_debut->add(new \DateInterval('P1M'));
                    array_push($tab, $date_debut);
                }
                dd($tab);
                $tab_date_complet = [$date_debut_s];
                $tab_pointage = [];
                $tab_pointage_s="";
                $date = date($date_debut_s, strtotime('+1 month'));
                //array_push($tab_date_complet, $date);
                dd($date);

                /** test */
               
                 //si ce client devrait faire ce pointage là
                 $son_liste_pointage = $item->getTabPointage();
                 $tab_pointage = explode("__", $son_liste_pointage);
                 //dd($son_liste_pointage);
             }

        }
    }
    
}
