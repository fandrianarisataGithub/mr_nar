<?php

namespace App\Controller;

use DateTime;
use DateInterval;
use App\Entity\Client;
use App\Entity\Pointage;
use App\Form\PointageType;
use App\Repository\ClientRepository;
use App\Repository\PointageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PointageController extends AbstractController
{


    public function tableau_pointage(Client $client)
    {
        $n = $client->getNumeroPointage();
        $s = $client->getTabPointage();
        $tab = explode("__", $s);
        return $tab;
    }

    /**
     * @Route("/admin/autrepointage/{id_client}", name="autrepointage")
     */
    public function pointer_dehors($id_client, Request $request, ClientRepository $repoClient, PointageRepository $repoPointage, EntityManagerInterface $manager)
    {
       
        $client = $repoClient->find($id_client);
        // liste des pointage prévu
        $tabPointage = $this->tableau_pointage($client);
        $listeP_tsotra = $tabPointage;
        $mois = [
				'Janvier',
				'Février',
				 'Mars',
				 'Avril',
				'Mai',
				'Juin',
				'Juillet',
				'Août',
				'Septembre',
				'Octobre',
				'Novembre',
                'Décembre'];
           //dd($tabPointage[0]);  
           $tab = [];   
        for($i = 0; $i<count($tabPointage); $i++){
            $s = explode("-", $tabPointage[$i]);
            $n = $s[0] - 1;

           $tabPointage[''.$i.''] = $mois[$n]."-".$s[1];
            
        }
       //dd($tabPointage);
        // liste des poinatge eff
        $liste_p_eff = $this->liste_pointage_du_client($client, $repoClient);
        
        $nouveau = "";
        if (count($request->request) > 0) {
           $nom_pointage = $request->request->get('pointage');
           $montant =  $request->request->get('montant_mensuel');
           $nom_user =  $request->request->get('nom_user');
            $pointage1 = new Pointage();
            $pointage1->setNom($nom_pointage);
            $sl = explode("-", $nom_pointage);
            $n = $sl[0] - 1;
            $sl =  $mois[$n] . "-" . $sl[1];
            $pointage1->setNomLit($sl);
            $pointage1->setNomUser($nom_user);
            $pointage1->setCreatedAt(new \Datetime());
           
           if($montant != $client->getMontantMensuel()){
                return $this->render("pointage/autrepointage.html.twig", [
                    "liste_p_p" => $tabPointage,
                    'liste_tsotra' => $listeP_tsotra,
                    "client" => $client,
                    'montant' => $client->getMontantMensuel(),
                    "liste_p_eff" => $liste_p_eff,
                    'present' => $this->count_present($repoClient),
                    'suspendu' => $this->count_suspendu($repoClient),
                    'archived' => $this->count_archived($repoClient),
                    'pointed' => $this->count_pointed($repoClient),
                    'nouveau' => $this->count_nouveau($repoClient),
                    'impaye' => $this->count_impaye($repoClient),
                    'attente' => $this->count_attente($repoClient),
                    'erreur2' => "Le montant renseigné est inexact",
                ]);
           }
               
                // on fait le pointage
                //si ce client n'a pas encore ce pointage 
                $point_eff = $this->liste_pointage_du_client($client, $repoClient);
                $sonNom_pointage = [];
                if($point_eff != "vide"){
                    for ($i = 0; $i < count($point_eff); $i++) {
                        array_push($sonNom_pointage, $point_eff[$i]->getNom());
                    }
                    //dd($sonNom_pointage);
                    if (in_array($nom_pointage, $sonNom_pointage)) {
                        // efa nahavita an'io izy
                        return $this->render("pointage/autrepointage.html.twig", [
                            "liste_p_p" => $tabPointage,
                            'liste_tsotra' => $listeP_tsotra,
                            "client" => $client,
                            "liste_p_eff" => $liste_p_eff,
                            'present' => $this->count_present($repoClient),
                            'suspendu' => $this->count_suspendu($repoClient),
                            'archived' => $this->count_archived($repoClient),
                            'pointed' => $this->count_pointed($repoClient),
                            'nouveau' => $this->count_nouveau($repoClient),
                            'impaye' => $this->count_impaye($repoClient),
                            'attente' => $this->count_attente($repoClient),
                            'erreur2' => "Ce client a déjà fait ce pointage",
                            'montant' => $client->getMontantMensuel(),
                        ]);
                    } else {
                       
                        $client->addPointage($pointage1);
                        
                        $n = $client->getNumeroPointage();
                        $n++;
                        $client->setNumeroPointage($n);
                        $n++;

                        if ($n >= count($tabPointage)) {
                            // pointé
                        } else {
                            $next = $tabPointage[$n];
                            $client->setNomPointageAv($next);
                        }

                        $client->setEtatClient('pointé');
                    } 
                }
                else{
                    $client->addPointage($pointage1);
                    $n = $client->getNumeroPointage();
                    $n++;
                    $client->setNumeroPointage($n);
                    $n++;

                    if ($n >= count($tabPointage)) {
                        // pointé
                    } else {
                        $next = $tabPointage[$n];
                        $client->setNomPointageAv($next);
                    }

                    $client->setEtatClient('pointé');
                    
                }
               
                    $manager->persist($pointage1);
                    $manager->persist($client);
                    $manager->flush();
                    return $this->redirectToRoute("autrepointage", ["id_client" => $id_client]);
                
               
               
          
            return $this->render("pointage/autrepointage.html.twig", [
                "liste_p_p" => $tabPointage,
                'liste_tsotra' => $listeP_tsotra,
                "client" => $client,
                "liste_p_eff" => $liste_p_eff,
                'present' => $this->count_present($repoClient),
                'suspendu' => $this->count_suspendu($repoClient),
                'archived' => $this->count_archived($repoClient),
                'pointed' => $this->count_pointed($repoClient),
                'nouveau' => $this->count_nouveau($repoClient),
                'impaye' => $this->count_impaye($repoClient),
                'attente' => $this->count_attente($repoClient),
                'montant' => $client->getMontantMensuel(),

            ]);
        }

       
        return $this->render("pointage/autrepointage.html.twig", [
            "liste_p_p"=>$tabPointage,
            'liste_tsotra' => $listeP_tsotra,
            "client" =>$client,
            "liste_p_eff" => $liste_p_eff,
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'archived' => $this->count_archived($repoClient),
            'pointed' => $this->count_pointed($repoClient),
            'nouveau' => $this->count_nouveau($repoClient),
            'impaye' => $this->count_impaye($repoClient),
            'attente' => $this->count_attente($repoClient),
            'montant' => $client->getMontantMensuel(),
            
        ]);
    }

    /**
     * @Route("/test", name = "test")
     */
    public function liste_pointage_du_client(Client $client, ClientRepository $repoClient)
    {


        $le_client = $repoClient->findOneByIdJoinedToPointage($client->getId());
        //dd($le_client);
        // si ce client a  des pointages
        if ($le_client != null) {
            //dd($sesPointages);
            $tab = $le_client->getPointages();
            // $tabNom = [];
            // for($i=0; $i<count($tab); $i++){
            //     $n = $tab[$i]->getNom();
            //     array_push($tabNom, $n);
            // }
            //dd($tabNom);
            return $tab;
        } else {
            return 'vide';
        }
    }

    public function nom_dernier_mois()
    {
        $today = new \DateTime();
        $tomoth = $today->format('m-Y');
        $p = "1-" . $tomoth;

        $date1 = new \DateTime($p);
        //dd($date1);
        $date = date_create($date1->format("Y-m-d"));

        // raha ny 1 mois avant no hitsarana azy

        date_add($date, date_interval_create_from_date_string(-1 . ' months'));
        $s = $date->format("m-Y");

        return $s;
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
           
        
        
       
        $items = $repoClient->countPresent('présent');
       
        $now = new \DateTime();
        $mois_actus = $this->getMoisText($now);
       
       // $mois_actus = $pointage2->getNomLit();
        $pointages = $repoPointage->findlesTwelveLastPointage();
        $last_p = $repoPointage->lastPointage();
        //dd(count($last_p));
        if(count($last_p)>0){
            $lit = $last_p[0]->getNomLit();
        }
       
        
       // dd($lit);
        return $this->render('pointage/pointage.html.twig', [
            "items" => $items,
           
            "pointages" => $pointages,
            "error" => $error,
            "mois_actus" => $mois_actus,
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'archived' => $this->count_archived($repoClient),
            'pointed' => $this->count_pointed($repoClient),
            'nouveau' => $this->count_nouveau($repoClient),
            'impaye' => $this->count_impaye($repoClient),
            'attente' => $this->count_attente($repoClient),
        ]);
    }

    /**
     * @Route("/admin/create_pointage", name="create_pointage")
     */
    public function create_pointage(Request $request, ClientRepository $repoClient, EntityManagerInterface $manager)
    {
        $pointage = new Pointage();
        $form = $this->createForm(PointageType::class, $pointage);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $pointage = $form->getData();
            $pointage->setCreatedAt(new \DateTime());
            $manager->persist($pointage);
            $manager->flush();
            return $this->redirectToRoute("pointage");
        }
        return $this->render('pointage/create_pointage.html.twig', [
            "form"=> $form->createView(),
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'archived' => $this->count_archived($repoClient),
            'pointed' => $this->count_pointed($repoClient),
            'nouveau' => $this->count_nouveau($repoClient),
            'impaye' => $this->count_impaye($repoClient),
            'attente' => $this->count_attente($repoClient),
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
        $tous = $repoClient->findAll();
        $today = new \DateTime();
        $tomoth = $today->format('m-Y');

        $cp = [];
        foreach ($tous as $item) {
            $ses_p = $this->liste_pointage_du_client($item, $repoClient);

            if ($ses_p != "vide") {
                foreach ($ses_p as $p) {
                    $nom = $p->getNom();
                    if ($nom == $tomoth) {
                        $item->setEtatClient('pointé');
                        array_push($cp, $item);
                    }
                }
            }
        }
        return count($cp);
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
    public function count_impaye(ClientRepository $repoClient)
    {
        $tabPresent = $repoClient->countPresent('impayé');
        $n = count($tabPresent);
        //dd($n);
        return $n;
        
    }
    public function count_attente(ClientRepository $repoClient)
    {
        $tabPresent = $repoClient->countPresent('attente');
        $n = count($tabPresent);
        //dd($n);
        return $n;
        
    }
    public function count_nouveau(ClientRepository $repoClient)
    {
        $tabPresent = $repoClient->findAll();
        $tab = [];
        foreach($tabPresent as $item){
            $c = $item->getCreatedAt();
            $today = new \DateTime();
            $today_s = $today->format('d-m-Y');
            $c_s = $c->format("d-m-Y");
            if($c_s == $today_s){
               array_push($tab, $item);
            }
        }
        $n = count($tab);
        //dd($n);
        return $n;
        
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
