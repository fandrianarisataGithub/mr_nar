<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Client;
use App\Entity\Pointage;
use App\Form\ClientType;
use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use App\Repository\PointageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ClientController extends AbstractController
{
    /**
     * @Route("/profile/register_client", name="register_client")
     * @Route("/profile/edit_client/{id}", name="edit_client")
     */
    public function register_client(Client $client = null, Request $request, ClientRepository $repoClient, UserRepository $repos, EntityManagerInterface $manager,SessionInterface $session)
    {
       
        $this->triage_principal($repoClient, $manager);
        $misy = "oui";
        if(!$client){
            $client = new Client();
            $misy = "non";
        }
        $date_du_jour = new \Datetime();
        $user = new User();
        $session  = $request->getSession();
        $session_user = $session->get('session_user', []);
        $user = $repos->find($session_user['id']);        
        $form_register = $this->createForm(ClientType::class, $client);
        $form_register->handleRequest($request);
       
       // dd($clients_du_jour);
        if($form_register->isSubmitted() && $form_register->isValid()){
            $client = $form_register->getData();
            $image_cin_1 = $form_register->get('image_1')->getData();
            $image_cin_2 = $form_register->get('image_2')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($image_cin_1 && $image_cin_2) {
                $originalFilename1 = pathinfo($image_cin_1->getClientOriginalName(), PATHINFO_FILENAME);
                $originalFilename2 = pathinfo($image_cin_2->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename1 = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename1);
                $newFilename1 = $safeFilename1 . '-' . uniqid() . '.' . $image_cin_1->guessExtension();

                $safeFilename2 = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename2);
                $newFilename2 = $safeFilename2 . '-' . uniqid() . '.' . $image_cin_2->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $image_cin_1->move(
                        $this->getParameter('image_cin_directory'),
                        $newFilename1
                    );
                    $image_cin_2->move(
                        $this->getParameter('image_cin_directory'),
                        $newFilename2
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'image_cin_1name' property to store the PDF file name
                // instead of its contents
               
                if (!$client->getId()) {
                    $client->setUser($user);
                    $client->setVerifier("non");
                    $client->setCreatedAt(new \DateTime());
                    $client->setImage1($newFilename1);
                    $client->setImage2($newFilename2);
                    $client->setEtatClient("nouveau");
                    $client->setNumeroPointage('-1');
                    $s = $client->tab_mois();
                    $client->setTabPointage($s);
                    $dd = $form_register->get('date_debut')->getData();
                    $date_fp = $dd->format('m-Y');
                    $client->setNomPointageAv($date_fp);
                    //dd($client);
                }
                // calcul de la date 
                $dd = $form_register->get('date_debut')->getData();
                // consvertissena hon string lo zany
                $dd_s = $dd->format('Y-m-d');
                // $dd = date($dd);
                // nombre de versement
                $nbr = $form_register->get('nbr_versement')->getData();
                $nbr--;
                // date de fin en string

                $df = date("Y-m-d", strtotime("$dd_s + $nbr month"));

                $df_dt = new \DateTime($df);
                //dd($df_dt);
                $client->setDateFin($df_dt);
                $etat = $client->getEtatClient();
                $client->setEtatClient($etat);
                $date_fp = $dd->format('m-Y');
                $client->setNomPointageAv($date_fp);
                $client->setEtatClient("nouveau");
                $client->setNumeroPointage('-1');
              
            }
            //  dd($client);
            $manager->persist($client);
            $manager->flush();
            return $this->redirectToRoute("register_client");

        }
        // calcul des montant journalier

       
        $allClient = $repoClient->findByUser($user);
        //dd($allClient);
        $tab1 = [];
        foreach($allClient as $cu){
            $createdAt = $cu->getCreatedAt();
            $createdAt = $createdAt->format("d-m-Y");
            //dd($createdAt);
            // raha vao androany
            $now = new \DateTime();
            $now = $now->format("d-m-Y");
           // dd($now);
            if($now == $createdAt){
                array_push($tab1, $cu);
              
            }  
        }
        //dd($tab1);
        $mj = 0;
        $mmj = 0;
        foreach ($tab1 as $item) {
            $mj += $item->getMontant();
            $mmj += $item->getMontantMensuel();
        }
        

        return $this->render('client/register.html.twig', [
            "form" => $form_register->createView(),
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'archived' => $this->count_archived($repoClient),
            'pointed' => $this->count_pointed($repoClient),
            "date_du_jour" => $date_du_jour,
            "misy" => $misy,
            "clients_du_jour" => $tab1,
            "mmj" => $mmj,
            "mj" => $mj,
            "nbr_client_jour" => count($tab1),
            'nouveau' => $this->count_nouveau($repoClient),
            'impaye' => $this->count_impaye($repoClient),
            'attente' => $this->count_attente($repoClient),
        ]);
    }
    
    /**
     * @Route("/profile/lister_tout_client/{user_id}", name="liste_client_de_user")
     */
    public function liste_client_de_user(EntityManagerInterface $manager, ClientRepository $repoClient, $user_id, Request $request, UserRepository $repos)
    {
        $user = new User();
        $session  = $request->getSession();
        $session_user = $session->get('session_user', []);

        $user = $repos->find($user_id);
        $nom = $user->getNom();
        $clients_du_jour = [];
        $items = $repoClient->findByUser($user);
        foreach ($items as $cu) {
            $createdAt = $cu->getCreatedAt();
            $createdAt = $createdAt->format("d-m-Y");
            //dd($createdAt);
            // raha vao androany
            $now = new \DateTime();
            $now = $now->format("d-m-Y");
            // dd($now);
            if ($now == $createdAt) {
                array_push($clients_du_jour, $cu);
            }
        }
        $mj = 0;
        $mmj = 0;
        foreach ($clients_du_jour as $item) {
            $mj += $item->getMontant();
            $mmj += $item->getMontantMensuel();
        }
        //Total be 
        $m = 0;
        $mm = 0;
        foreach ($items as $item) {
            $m += $item->getMontant();
            $mm += $item->getMontantMensuel();
        }
        
        //dd($items);
        return $this->render("client/listeClientUser.html.twig",[
            "items" => $items,
            "nom_user" => $nom,
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'archived' => $this->count_archived($repoClient),
            'pointed' => $this->count_pointed($repoClient),
            "clients_du_jour" => $clients_du_jour,
            "mmj" => $mmj,
            "mj" => $mj,
            "nbr_client_jour" => count($clients_du_jour),
            "date_du_jour" => new \DateTime(),
            "m" =>$m,
            "mm" =>$mm,
            'nouveau' => $this->count_nouveau($repoClient),
            'impaye' => $this->count_impaye($repoClient),
            'attente' => $this->count_attente($repoClient),
        ]);
    }
    public function triage_principal(ClientRepository $repoClient, EntityManagerInterface $manager)
    {
        $c = $repoClient->findAll();
        // initialisation 
        foreach ($c as $cl) {
            $cl->setEtatClient("présent");
            $manager->persist($cl);
            $manager->flush();
        }
        
        $Tous_les_nc = $repoClient->findAll();
        // ajourd'huhi
        $today = new \DateTime();
        $today_day = $today->format('d-m-Y');
        $today_moth = $today->format('m-Y');
        // last month
        $p = "1-" . $today_moth;
        $date1 = new \DateTime($p);
        //dd($date1);
        $date = date_create($date1->format("Y-m-d"));
        // raha ny 1 mois avant no hitsarana azy
        date_add($date, date_interval_create_from_date_string(-1 . ' months'));
        $last_moth = $date->format('m-Y');
        
        // last 3 moths
        $date2 = date_create($date1->format("Y-m-d"));
        // raha ny 1 mois avant no hitsarana azy
        date_add($date2, date_interval_create_from_date_string(-3 . ' months'));
        $last_3moth = $date2->format('m-Y');

        // 1er mois après
        $date3 = date_create($date1->format("Y-m-d"));
        // raha ny 1 mois avant no hitsarana azy
        date_add($date3, date_interval_create_from_date_string(+1 . ' months'));
        $next_moth = $date3;
        //dd($next_moth);
         
        foreach ($Tous_les_nc as $item) {
           
            // les données du client
            // sa liste de pointage à faire
            $sa_liste_p_a_faire = $item->getTabPointage();
            $sa_liste_p_a_faire = explode("__", $sa_liste_p_a_faire);
            // sa date de debut
            $sa_date_debut = $item->getDateDebut();
            // son mois de debut 
            $son_mois_debut = $sa_date_debut->format('m-Y');
            // sa date de fin
            $sa_date_fin = $item->getDateFin();
            // sa date d'ajout
            $sa_date_ajout = $item->getCreatedAt();
            // son numero de pointage
            $son_numero_p = $item->getNumeroPointage();
            // ses pointages faits
            $ses_p_fait = $this->liste_pointage_du_client($item, $repoClient); // return "vide" si tsisy
            // les liste m-Y de ses pointages fait
            
            // avoaka aloha ze nouveau
            if($sa_date_ajout == $today_day){
                $item->setEtatClient("nouveau");
            }

            // avoaka ze efa archivé
            if($sa_date_fin <= $today){
                $item->setEtatClient('archivé');
            }
            // avoaka ze en attente
            if ($next_moth <= $sa_date_debut) {
                // $item->setEtatClient('attente');
                $item->setEtatClient('présent');
            }
            // avoaka ze pointé
            if($ses_p_fait != "vide"){
                $item->setEtatClient('pointé');
            }

            // ireo ho atao pointage
            if(in_array($today_moth, $sa_liste_p_a_faire)){
                // io ve ny pointage-ny voalohany
                if($son_numero_p == -1){
                    // oviana izy no tokony nanao pointage
                    if($sa_date_debut <= $today){
                       
                       // raha @ty mois ty izy no tokony hanao
                       if($son_mois_debut == $today_moth){
                           
                            $item->setEtatClient('présent');
                       }
                       else{
                           // jerena le decalage
                           // fahafiry @ moid de pointage à faire-ny ty mois ty 
                           $indice = array_search($today_moth, $sa_liste_p_a_faire);
                           if($indice>=2){
                                // impayé
                                //dd("nisy impayé");
                                $item->setEtatClient('impayé');
                           }
                           else{
                                //dd("nisy suspendu");
                                $item->setEtatClient('suspendu');
                           }
                       }
                    }
                    
                }
                else{
                    //dd('misy diff de 1');
                    // ses nom de liste de pointage fait
                    $t = count($ses_p_fait);
                    $tab = [];
                    foreach($ses_p_fait as $spf){
                        array_push($tab, $spf->getNom());
                    }
                    // fahafiry @ pointage-ny ty mois ty 
                    $key = array_search($today_moth, $sa_liste_p_a_faire);
                    if($key>=2){
                        //dd('misy key >=2');
                        // raha efa nanao in-telo izy
                        // nandoha ve izy t@ 3 volana lasa 
                        // tokony nanao ve lo izy t@io 3 mois io 
                        //dd($last_3moth);
                        if(in_array($last_3moth, $sa_liste_p_a_faire)){
                            // raha tokony nanao izy 
                            //dd("tokony nanao izy t@ 3 mois");
                            // nahavita ve izy
                            if(!in_array($last_3moth, $tab)){
                                // raha tsy nahavita
                                $item->setEtatClient("impayé");
                            }
                            else{
                                
                                // raha nahavita 
                                // nahavita ve izy t@mois teo aloha 
                                if (!in_array($last_moth, $tab)) {
                                   
                                    // raha tsy nahavita
                                    $item->setEtatClient("suspendu");
                                }
                                else{
                                    $item->setEtatClient("pointé");
                                }
                            }
                        }
                        else{
                            
                            // raha tsy tokony nanao izy t@ 3 mois
                            // nahavita ve izy t@mois teo aloha 
                            if (!in_array($last_moth, $tab)) {
                                // raha tsy nahavita
                                $item->setEtatClient("suspendu");
                            } else {
                                $item->setEtatClient("pointé");
                            }
                        }
                       
                    }
                    else{
                        // tsy mbola nahavita pointage + de 2 izy
                        // nahavita ve izy t@mois teo aloha 
                        if (!in_array($last_moth, $tab)) {
                            // raha tsy nahavita
                           
                            $item->setEtatClient("suspendu");
                        } else {
                            $item->setEtatClient("pointé");
                        }
                    }
                    
                }
            }
            $manager->persist($item);
            $manager->flush();
           
           
        }
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
     * @Route("/admin/count", name="count")
     */
    public function count(ClientRepository $repoClient)
    {
        // nombre de client présent 
        $client_presents = $repoClient->findAll();
        $nbr_client_present  = count($client_presents);
        //dd($nbr_client_present);

        

        return $this->render('base.html.twig');
    }

    /**
     * @Route("/admin/supprimer_client/{id}", name="supprimer_client")
     */
    public function supprimer_client($id, Request $request, EntityManagerInterface $manager, PointageRepository $repoPointage, ClientRepository $repoClient)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){
            
            $action = $request->get('action');
            if($action== "suppression"){
                $client = $repoClient->find($id);
                $manager->remove($client);
                $manager->flush();
                $data = json_encode("ok"); // formater le résultat de la requête en json
                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            }
           
        }
        return $this->redirectToRoute("client_present");
    }

    /**
     * @Route("/admin/suspendre_client/{id}", name="suspendre_client")
     */
    public function suspendre_client($id, ClientRepository $repoClient, EntityManagerInterface $manager)
    {
        $client = new Client();
        $client =$repoClient->find($id);
        $client->setEtatClient('suspendu');
        $manager->flush();
        return $this->redirectToRoute('client_suspendu', [
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
     * @Route("/admin/classe_impaye/{id}", name="classe_impaye_client")
     */
    public function classe_impaye_client($id, ClientRepository $repoClient, EntityManagerInterface $manager)
    {
        $client = new Client();
        $client = $repoClient->find($id);
        $client->setEtatClient('impayé');
        $manager->flush();
        return $this->redirectToRoute('client_impaye', [
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
        $clients = $repoClient->findAll();
        $today = new \DateTime();
        $today_moth = $today->format('m-Y');
        $n = 0;
        foreach ($clients as $client) {
            $ses_pointages_fait = $this->liste_pointage_du_client($client, $repoClient);
            $tab = array();
            if ($ses_pointages_fait != 'vide') {
                foreach ($ses_pointages_fait as $pointage) {
                    array_push($tab, $pointage->getNom());
                }
            }
            if (in_array($today_moth, $tab)) {
                $n++;
            }
        }
        return $n;
    }
    public function count_suspendu(ClientRepository $repoClient)
    {
        $tabPresent = $repoClient->countPresent('suspendu');
        $n = count($tabPresent);
        //dd($n);
        return $n;
    }
    public function count_archived(ClientRepository $repoClient)
    {
        $tabPresent = $repoClient->countPresent('archivé');
        $n = count($tabPresent);
        //dd($n);
        return $n;
    }

    /**
     * @Route("/admin/verifier/{id}", name ="verifier_client")
     */
    public function verifier_client($id, Request $request, ClientRepository $repoClient, EntityManagerInterface $manager)
    {
        $response = new Response();
        $client = new Client();
        if($request->isXmlHttpRequest()){
            
            $client = $repoClient->find($id);
            $client->setVerifier("oui");
            $manager->persist($client);
            $manager->flush();

            $data = json_encode("ok"); // formater le résultat de la requête en json

            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response;
        }
    }

    /**
     * @Route("/admin/load_pointage", name="load_pointage")
     */
    public function load_pointage(Request $request, ClientRepository $repoClient)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){

            $id = $request->get('id');
            $client = $repoClient->find($id);
            $retour = "matricule__nbrPointageFait__nbrVersement__nomDernierPointage__montantRestant__moisRestant";
            $n = $client->getNumeroPointage();
            $matricule = $client->getMatricule();
            $nbrPointageFait = $this->nbrPointageFait($client);
            $nbrVersement = $client->getNbrVersement();
            $nomDernierPointage = $this->lastPointageName($client);
            $montantRestant = $this->montantRestant($client);
            $moisRestant = $this->getNombreMoisRestant($client);
            $retour = [$matricule, $nbrPointageFait, $nbrVersement, $nomDernierPointage, $montantRestant, $moisRestant];
           
            
            $data = json_encode($retour); // formater le résultat de la requête en json

            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response;
        }
    }

    // nombre poinatge fait
    public function nbrPointageFait(Client $client)
    {
        $num = $client->getNumeroPointage();
        return ($num + 1);
    }
    // nom du dernier poinatge fait
    public function lastPointageName(Client $client)
    {
        $num = $client->getNumeroPointage();
        $tab = $client->getTabPointage();
        $tabP = explode("__", $tab);
        if($num != -1){
            return $tab[$num];
        }
       else{
            return "vide";
       }
    }

    // nombrfe de mois restant
    public function getNombreMoisRestant(Client $client)
    {
        $n = $this->nbrPointageFait($client);
        $nbrV = $client->getNbrVersement();
       if($n != -1){
       
        return ($nbrV - $n);
       }
       else{
        return $nbrV ;
       }
    }

    // Montant restant

    public function montantRestant(Client $client)
    {
        $n = $this->getNombreMoisRestant($client);
        $montant_m = $client->getMontantMensuel();
        return ($n * $montant_m);
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

    
    
    
}
