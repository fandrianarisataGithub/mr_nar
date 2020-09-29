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
        $today = new \DateTime();
        $tomoth = $today->format('m-Y');
        foreach($c as $item){

            /** ze nahavita pointage t@ty */

            // ny pointage efa vitany
            $tabPointEff = $this->liste_pointage_du_client($item, $repoClient);
            $tabNom = [];
            if($tabPointEff != "vide"){
                for ($i = 0; $i < count($tabPointEff); $i++) {
                    //dd($tabPointEff);
                    $n = $tabPointEff[$i];
                    array_push($tabNom, $n);
                }
               
            }
          

            if (in_array($tomoth, $tabNom)) {
               // dd($tabNom);
                // nahavita izy zany 
                $item->setEtatClient('pointé');
                $manager->persist($item);
                $manager->flush();
            }


            /** /ze nahavita pointage t@ty */
            
            // si androany no nanomboka ny pointage -ny
            $nextPointage = $item->getNomPointageAv();
            $n = $item->getNumeroPointage();
            // tokony hanao pointage ve izy @ty?
            if($tomoth == $nextPointage){
                // si le pointage est le premier
                if($n == '-1'){
                    // client présent pour le pointage
                    $item->setEtatClient('présent');
                    $manager->persist($item);
                    $manager->flush();
                
                }
                else{
                    // nandoha ve teo aloha ?
                    // on liste les pointage de ce client 
                    $tabPoint = $this->liste_pointage_du_client($item, $repoClient);
                    // le mois dernier 
                    $s = $this->nom_dernier_mois();
                    // nanao pointage ve izy t@io 
                    if(in_array($s, $tabPoint)){
                        //nanao izy
                        //atao client présent
                        $item->setEtatClient('présent');
                        $manager->persist($item);
                        $manager->flush();
                    }
                    else{
                        
                        // suspendu izy
                        /* $item->setEtatClient('suspendu');
                        $manager->persist($item);
                        $manager->flush();*/
                        // impiry izy no tsy nandoha ?
                        // alaina aloha ny liste pointage tokony ho ataony 
                        $list = $item->getTabPointage();
                        $list = explode("__", $list);
                        // tadaviko oe indice firy ao ty mois ty $tomoth
                        $key = array_search($tomoth, $list);
                        if($key >= 3){
                            $item->setEtatClient('impayé');
                            $manager->persist($item);
                            $manager->flush();
                        }
                        else{
                            $item->setEtatClient('suspendu');
                            $manager->persist($item);
                            $manager->flush();
                        }


                    }
                }

                
            }
            if($item->getDateFin() < $today){
                $item->setEtatClient('archivé');
                $manager->persist($item);
                $manager->flush();
            }

            // ireo en attente 
            $etat = $item->getEtatClient();
            // premier jour du mois 
            $tomoth = $today->format('m-Y');
            $p = "1-".$tomoth;
           
            $date1 = new \DateTime($p);
            //dd($date1);
            $date = date_create($date1->format("Y-m-d"));

            // raha ny 1 mois avant no hitsarana azy
            
            date_add($date, date_interval_create_from_date_string(1 . ' months'));
            //dd($date);
            $dd = $item->getDateDebut();
            if($dd >= $date){
                // en attente
                $item->setEtatClient('attente');
                $manager->persist($item);
                $manager->flush();
            }



        }
    }
    /**
     * @Route("/test", name = "test")
     */
    public function liste_pointage_du_client(Client $client, ClientRepository $repoClient)
    {


        $le_client = $repoClient->findOneByIdJoinedToPointage($client->getId());
        // si ce client a  des pointages
        if ($le_client != null) {
            //dd($sesPointages);
            $tab = $le_client->getPointages();

            $tabNom = [];
            for ($i = 0; $i < count($tab); $i++) {
                $n = $tab[$i]->getNom();
                array_push($tabNom, $n);
            }
            //dd($tabNom);
            return $tabNom;
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
