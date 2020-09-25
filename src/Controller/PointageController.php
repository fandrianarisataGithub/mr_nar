<?php

namespace App\Controller;

use DateTime;
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

               
                $n = $client->getNumeroPointage();
                $n++;
                $client->setNumeroPointage($n);
                // si son num point correspond au mois actuels
                $tab = $this->tabbleau_pointage($client);
                $mois_actus = $this->getMoisText(new \DateTime());
                if($mois_actus == $client->getNumeroPointage()){
                    // mettre ce client parmis les pointé
                    $client->setEtatClient('pointé');
                }
                else{
                    // mettre ce client parmis les présent
                    $client->setEtatClient('présent');
                }


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

    public function tabbleau_pointage(Client $client)
    {
        $n = $client->getNumeroPointage();
        $s = $client->getTabPointage();
        $tab = explode("__", $s);
        return $tab;
    }

    /**
     * @Route("/admin/pointage", name="pointage")
     * 
     */
    public function pointage(Request $request, ClientRepository $repoClient, EntityManagerInterface $manager, PointageRepository $repoPointage)
    {   
        // creer le nom_pointage  de pointage
        $error = 0;
        $nom_lit = "";
            if ($request->request->count() > 0) {
                $m = $request->request->get('mois_pointage');
                $annee = $request->request->get('annee_pointage');
                $nom = $m . "-" . $annee;
                $mois = $this->generateNameMonth($m);
                $nom_lit = $mois . "-" . $annee;
                $pointage = $repoPointage->findOneByNom($nom);
                
                if (!$pointage) {
                    $created_at = new \DateTime();
                    $pointage = new Pointage();
                    $pointage->setNom($nom);
                    $pointage->setNomLit($nom_lit);
                    $pointage->setCreatedAt($created_at);
                    $pointage->setAnneeActuelle($annee);
                    $manager->persist($pointage);
                    $manager->flush();
                } else {
                    $error = 1;
                }
            }
        
        
       
        $presents = $repoClient->countPresent('présent');
        // le tokony ho pointena
        $items = [];
        foreach($presents as $item){
            $res = $this->test_etat($item, new \DateTime());
            if($res=="pointable"){
                array_push($items, $item);
            }
        }
        $now = new \DateTime();
        $mois_actus = $this->getMoisText($now);
       
       // $mois_actus = $pointage2->getNomLit();
        $pointages = $repoPointage->findlesTenLastPointage();
        $last_p = $repoPointage->lastPointage();
        
        $lit = $last_p[0]->getNomLit();
       // dd($lit);
        return $this->render('pointage/pointage.html.twig', [
            "items" => $items,
            "nom_last_p" => $lit,
            "pointages" => $pointages,
            "error" => $error,
            "mois_actus" => $mois_actus,
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


    
    public function getMoisText(\Datetime $date)
    {
        $date_s = $date->format("d-m-Y");
        $tn = explode("-", $date_s);
        $mois_du_date = $tn[1] . "-" . $tn[2];
        return $mois_du_date;
    }
    
    /**
     * @Route("/admin/trier", name="trier")
     */
    public function trier_pointage(Request $request, ClientRepository $repoClient, PointageRepository $repoPointage){
        $response = new Response();
        if($request->isXmlHttpRequest()){

            // tab pour les clients à selectionner
            $tab = [];
            $present = $repoClient->countPresent("présent");
            $pointed = $repoClient->countPresent("pointé");
            foreach($present as $item){
                array_push($tab, $item);
            }
            foreach ($pointed as $item) {
                array_push($tab, $item);
            }

            $client_afficher = [];
            $rep = "";
            //  mois condition 
            $pointage = $repoPointage->find($request->get('id_pointage'));
            $nom = $pointage->getNom();
            $nom_lit = $pointage->getNomLit();
            foreach($tab as $item){
                $result = $this->test_etat($item, new \Datetime("01-".$nom));
                if($result=="pointable" || $result == "après" || $result == "retard"){
                    // $rep = "misy";
                    array_push($client_afficher, $item);
                }
                else{
                    $rep = "tsisy pointable";
                }
            }
            $html = "";
            foreach($client_afficher as $item){
                
                $html.='<tr>
						<td class="td-check">
							<span> '.$item->getMatricule() .'</span>
						</td>
						<td>
							<span>'.$item->getNom() . '</span><br>
							<span>  ' . $item->getPrenom() . '</span>
						</td>
						<td>
							<span> '.$item->getUser()->getNom(). ' </span><br>
							(<span> ' . $item->getUser()->getUsername() . ' </span>)
						</td>
						<td>
							<span>N BL:</span>
							<span class="unite"> '.$item->getNumeroBl().' </span>
							<br>
							<span>MONTANT:</span><b class="unite"> '.$item->getMontant().' </b><b class="unite">Ar</b>
							<br>
							<span>MONTANT MENS:</span><b class="unite"> '.$item->getMontantMensuel().' </b><b class="unite">Ar</b>
							<br>
							<span>MONTANT MENS:</span><b class="unite"> '.$item->getMontantMensuel().' </b><b class="unite">Ar</b>
							<br>
							<span>NBR VERSEMENT:</span><b class="unite"> '.$item->getNbrVersement().' </b>
						</td>
						<td>
							<span>DATE DE DEBUT:</span><span class="unite">'. $item->getDateDebut()->format("d-m-Y") .' </span>
							<br>
							<span>DATE DE DEBUT:</span><span class="unite"> '. $item->getDatefin()->format("d-m-Y") .'</span>
							<br>
							<span>POINTAGE DU MOIS : </span><sapn class="unite"> '. $nom_lit .'</sapn>
						</td>
						<td>
							<a href="#" data-id-client="'. $item->getId().' " class="pointer btn btn-primary btn-xs">
								<span>Pointer</span>
								<span class="fa fa-check"></span>
							</a>
						</td>

					</tr>' ;
            }



            $data = json_encode($html); // formater le résultat de la requête en json
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response;
        }
    }
    
    public function test_etat(Client $client, \Datetime $x)
    {   
        $repoPointage = $this->getDoctrine()->getRepository(Pointage::class);
        $repoClient = $this->getDoctrine()->getRepository(Client::class);
        //$client = $repoClient->find($client_id);
        $son_tab_pointage = $client->getTabPointage();
        $t = explode("__", $son_tab_pointage);
        $now_s = $x->format("d-m-Y");
        $tn = explode("-", $now_s);
        $mois_actus = $tn[1]."-".$tn[2];
        //dd($mois_actus);
        //dd($son_tab_pointage);
        //dd($t);
        // si ce client doit faire le pointage du mois
        if(in_array($mois_actus, $t)){
            //return "dedans";
            // nandoha ve izy teo aloha sa tsy nandoha
            $today = new \DateTime();
            $date = date_create($today->format("Y-m-d"));

            // raha ny 1 mois avant no hitsarana azy
            
            date_add($date, date_interval_create_from_date_string(-1 . ' months'));
            $date_s = $date->format("m-Y");
            // tokony nanao pointage ve izy t@io? 
            if(in_array($date_s, $t)){
                // nandoha ve izy t@io farany io
                $key = array_search($date_s, $t);
                // si $key == numero pointage ok 
                if($key <= $client->getNumeroPointage()){
                    return "pointable";
                }
                else{
                    return "retard";
                }
            }
            else{
                return "pointable" ;
            }
            
        }
        else{
            //dd('non');
            $son_date_debut = $client->getDateDebut();
            $son_date_fin = $client->getDateFin();
            // si date debut mbola any aoriana 
            if($son_date_debut > $x){
               return "après";
            }
            else{
                if($son_date_fin < $x){
                   //raha nahaloha de archivena
                   // zany hoe num pointage + 1 = nbr_versement
                   $numPlus = $client->getNumeroPointage() + 1;
                   $n = $client->getNbrVersement();
                   if($n == $numPlus){
                        return "archiver";   
                   }
                   else{
                        return "suspendre"; 
                   }
                                
                }
            }
        }
    }
    
}
