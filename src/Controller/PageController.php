<?php

namespace App\Controller;

use DateInterval;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\User;
use App\Entity\Client;
use App\Form\UserType;
use App\Entity\Pointage;
use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use App\Repository\PointageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
// Include Dompdf required namespaces
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PageController extends AbstractController
{

    /**
     * @Route("/", name="firstRedirect")
     */
    public function home(ClientRepository $repoClient, PointageRepository $repoPointage, EntityManagerInterface $manager)
    {
        
       return $this->redirectToRoute("app_login");
        
    }
    /**
     * @Route("/test", name = "test")
     */
    public function liste_pointage_du_client(Client $client, ClientRepository $repoClient)
    {   

        
        $le_client = $repoClient->findOneByIdJoinedToPointage($client->getId());
        //dd($le_client);
        // si ce client a  des pointages
        if($le_client != null){
            //dd($sesPointages);
            $tab = $le_client->getPointages();
            // $tabNom = [];
            // for($i=0; $i<count($tab); $i++){
            //     $n = $tab[$i]->getNom();
            //     array_push($tabNom, $n);
            // }
            //dd($tabNom);
            return $tab;
        }
        else{
            return 'vide';
        }
       
    }

    public function nom_dernier_mois()
    {
        $today = new \DateTime();
        $tomoth = $today->format('m-Y');
            $p = "1-".$tomoth;
           
            $date1 = new \DateTime($p);
            //dd($date1);
            $date = date_create($date1->format("Y-m-d"));

            // raha ny 1 mois avant no hitsarana azy
            
            date_add($date, date_interval_create_from_date_string(-1 . ' months'));
            $s = $date->format("m-Y");
            
            return $s;
    }

    /**
     * @Route("/admin/search", name = "search")
     */
    public function search(Request $request, ClientRepository $repoClient)
    {   
        $result = [];
        $client = new Client();
        if(count($request->request)>0){
            $text = $request->request->get('matricule');
            $result = $repoClient->createQueryBuilder('c')
            ->where('c.matricule LIKE :text')
            ->setParameter('text', '%'.$text.'%')
            ->getQuery()
            ->getResult();
           // dd($result);
            $client = $repoClient->findOneByMatricule($text);
            if (!$client) {
                return $this->redirectToRoute("search");
            } else {
                return $this->redirectToRoute("single_page", ["id_client" => $client->getId()]);
            }
        }
        
        return $this->render("page/search.html.twig",[
            "items" => $result,
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
     * @Route("/profile/client_present", name="client_present")
     */
    public function client_present(ClientRepository $repoClient, UserRepository $repoUser, PointageRepository $repoPointage)
    {
        $user = new User();
        $items = $repoClient->findAll();
        $users = $repoUser->findAll();
       $total_mj = 0;
       $total_mmj = 0;
       $nbr_client_du_jour = 0;
        $pointages = $repoPointage->findlesTwelveLastPointage();
        foreach($users as $itemUser){
            $clients_du_jour_user = $repoClient->clientDuJour($itemUser);
            $mj = 0;
            $mmj = 0;
            foreach ($clients_du_jour_user as $item) {
                $mj += $item['montant'];
                $mmj += $item['montant_mensuel'];  
                $nbr_client_du_jour++;
            }
            $total_mj += $mj;
            $total_mmj += $mmj;
            
        }
        $les_clients = $repoClient->findAll();
        $montant = 0;
        foreach($les_clients as $c){
            $montant += $c->getMontant();
        }
        return $this->render('page/client_present.html.twig', [
            'items' => $items,
            'montant' => $montant,
            'pointages' => $pointages,
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'archived' => $this->count_archived($repoClient),
            'pointed' => $this->count_pointed($repoClient),
             "total_mj" => $total_mj,
             "total_mmj" => $total_mmj,
            "nbr_client_du_jour" => $nbr_client_du_jour,
            "date_du_jour" => new \DateTime(),
            'nouveau' => $this->count_nouveau($repoClient),
            'impaye' => $this->count_impaye($repoClient),
            'attente' => $this->count_attente($repoClient),
        ]);
    }
    /**
     * @Route("/profile/imprimer/client_present", name="imprimer_client_present")
     */
    public function imprimer_client_present(ClientRepository $repoClient)
    {
        $user = new User();
        $items = $repoClient->countPresent('présent');
        
        // dompdf
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        
        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('page/listePresentImpr.html.twig', [
            'items' => $items,
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'archived' => $this->count_archived($repoClient),
            'pointed' => $this->count_pointed($repoClient),
            'nouveau' => $this->count_nouveau($repoClient),
            'impaye' => $this->count_impaye($repoClient),
            'attente' => $this->count_attente($repoClient),
        ]);
        
        // Load HTML to Dompdf
        $dompdf->loadHtml($html);
        
        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => true
        ]);
    }

    /**
     * @Route("/profile/imprimer/client_impaye", name="imprimer_client_impaye")
     */
    public function imprimer_client_impaye(ClientRepository $repoClient)
    {
        $user = new User();
        $items = $repoClient->countPresent('archivé');
        // dompdf
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('page/listeImpayeImpr.html.twig', [
            'items' => $items,
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'archived' => $this->count_archived($repoClient),
            'pointed' => $this->count_pointed($repoClient),
            'nouveau' => $this->count_nouveau($repoClient),
            'impaye' => $this->count_impaye($repoClient),
            'attente' => $this->count_attente($repoClient),
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => true
        ]);
    }

    /**
     * @Route("/profile/imprimer/client_suspendu", name="imprimer_client_suspendu")
     */
    public function imprimer_client_suspendu(ClientRepository $repoClient)
    {
        $user = new User();
        $items = $repoClient->countPresent('suspendu');
        // dompdf
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('page/listeSuspenduImpr.html.twig', [
            'items' => $items,
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'archived' => $this->count_archived($repoClient),
            'pointed' => $this->count_pointed($repoClient),
            'nouveau' => $this->count_nouveau($repoClient),
            'impaye' => $this->count_impaye($repoClient),
            'attente' => $this->count_attente($repoClient),
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => true
        ]);
    }


    /**
     * @Route("/admin/client_archived", name="client_archived")
     */
    public function client_archived(ClientRepository $repoClient)
    { 
        $user = new User();
        $items = $repoClient->countPresent('archivé');
        return $this->render('page/client_archived.html.twig', [
            'items' => $items,
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
     * @Route("/admin/client_suspendu", name="client_suspendu")
     */
    public function client_suspendu(ClientRepository $repoClient)
    {
        $user = new User();
        $items = $repoClient->countPresent('suspendu'); 
        return $this->render('page/client_suspendu.html.twig', [
            'items' => $items,
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
     * @Route("/admin/client_paye", name="client_pointed")
     */
    public function client_pointed(ClientRepository $repoClient, PointageRepository $repoPointage, EntityManagerInterface $manager)
    {
        
        $tous = $repoClient->findAll();
        $today = new \DateTime();
        $tomoth = $today->format('m-Y');
        
        $cp = [];
        foreach($tous as $item){
            $ses_p = $this->liste_pointage_du_client($item, $repoClient);
           
            if($ses_p != "vide"){
               foreach($ses_p as $p){
                   $nom = $p->getNom();
                   if($nom == $tomoth){
                        $item->setEtatClient('pointé');
                        $manager->persist($item);
                        $manager->flush();
                        array_push($cp, $item);

                   }
               }
            }
        }
        //dd($cp);

        return $this->render('page/client_pointed.html.twig', [
            'items' => $cp,
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'archived' => $this->count_archived($repoClient),
            'pointed' => $this->count_pointed($repoClient),
            'nouveau' => $this->count_nouveau($repoClient),
            'impaye' => $this->count_impaye($repoClient),
            'attente' => $this->count_attente($repoClient),
            'liste' => 'autre'
        ]);
    }

     /**
     * @Route("/admin/client_nouveau", name="client_nouveau")
     */
    public function client_nouveau(ClientRepository $repoClient)
    {
        $user = new User();
        $tabPresent = $repoClient->findAll();
        $items = [];
        foreach($tabPresent as $item){
            $c = $item->getCreatedAt();
            $today = new \DateTime();
            $today_s = $today->format('d-m-Y');
            $c_s = $c->format("d-m-Y");
            if($c_s == $today_s){
               array_push($items, $item);
            }
        }

        return $this->render('page/client_nouveau.html.twig', [
            'items' => $items,
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
     * @Route("/admin/client_attente", name="client_attente")
     */
    public function client_attente(ClientRepository $repoClient)
    {
        $user = new User();
        $items = $repoClient->countPresent('attente');
        return $this->render('page/client_attente.html.twig', [
            'items' => $items,
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
     * @Route("/admin/client_impaye", name="client_impaye")
     */
    public function client_impaye(ClientRepository $repoClient)
    {
        $user = new User();
        $items = $repoClient->countPresent('impayé');
        return $this->render('page/client_impaye.html.twig', [
            'items' => $items,
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
     * @Route("/profile/single_page/{id_client}", name="single_page")
     */
    public function single_page($id_client, ClientRepository $repoClient, PointageRepository $repoPointage)
    {   
        $client = new Client();
       $client = $repoClient->find($id_client);
        
       $pointage = new Pointage();
        $montant = $client->getMontant();
        $nbrMois = $client->getNbrVersement();
        $montant_mensuel = $client->getMontantMensuel();
        // liste des pointages t0kony atao
        $liste_point_lit = $client->getTabPointage();
        //dd($liste_point_lit);
        $tableauPointageAeff = explode("__", $liste_point_lit);
        //dd($tableauPointageAeff);
        //liste pointage effectué
        $liste_pointage_e = [];
        $pointages = $this->liste_pointage_du_client($client, $repoClient);
        if($pointages != "vide"){
                for($i=0; $i<count($pointages); $i++){
                    array_push($liste_pointage_e, $pointages[$i]);
                }
        }
       //dd(count($liste_pointage_e));
      
        //dd($pointages);
        //dd($pointages['nom']);
        $nbr_p_effectue = count($liste_pointage_e);
        //dd($nbr_p_effectue);
        //nombre de mois restant

        $nbr_mois_restant = $nbrMois - $nbr_p_effectue;
        
        // montant payé
        $montant_paye = $montant_mensuel * $nbr_p_effectue;

        // montant restant
        $montant_restant = $montant - $montant_paye;
        
        return $this->render('page/single_page.html.twig', [
            'item' => $client,
            'montant_paye' => $montant_paye,
            'montant_restant' => $montant_restant,
            'nbr_p_effectue' => $nbr_p_effectue,
            'nbr_paiement_total' => $nbrMois,
            'nbr_mois_restant' => $nbr_mois_restant,
            "nbr_pointage" => $nbr_p_effectue,
            "numero_bl" => $client->getNumeroBl(),
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'archived' => $this->count_archived($repoClient),
            'pointed' => $this->count_pointed($repoClient),
            'nouveau' => $this->count_nouveau($repoClient),
            'impaye' => $this->count_impaye($repoClient),
            'attente' => $this->count_attente($repoClient),
            'tableauAEff' => $tableauPointageAeff,
            "liste_pointage_e" => $pointages,
        ]);
    }
        
     //famerenana ny client rehetra ho présent

    public function retournAllToPresent()
    {
        $repoClient = $this->getDoctrine()->getRepository(Client::class);
        foreach($repoClient->findAll() as $item)
        {
            $item->setEtatClient("présent");
            $this->getDoctrine()->getManager()->flush();
        }
    }

   
    /**
     * tableau de pointage pour un client
     */
    public function liste_m_p(Client $client)
    {
        $liste_m_p = [];
        $dd = $client->getDateDebut();
        $df = $client->getDateFin();
        $dd_s = $dd->format('d-m-Y');
        $df_s = $df->format('d-m-Y');
        $dd_sm = $dd->format('m-Y');
        $df_sm = $df->format('m-Y');
        // dif en mois de deux dates
        $diff = $this->get_month_diff($dd_s, $df_s);
        $diff ++;

        $dtdebut = date_create($dd_s);
        $dtfin = date_create($df_s);

        foreach (new \DatePeriod($dtdebut, \DateInterval::createFromDateString('1 months'), $dtfin) as $dt) {
            //echo $dt->format('m-Y')."  "; liste des mois
            array_push($liste_m_p, $dt->format('m-Y'));
        }
        array_push($liste_m_p, $df_sm);
        
        return $liste_m_p;
    }

    /**
     * Calculates how many months is past between two timestamps.
     *
     * @param  int $start Start timestamp.
     * @param  int $end   Optional end timestamp.
     *
     * @return int
     */
    public function get_month_diff($start, $end = FALSE)
    {
        $end or $end = time();

        $start = new \DateTime($start);
        $end   = new \DateTime($end);
        $diff  = $start->diff($end);

        return $diff->format('%y') * 12 + $diff->format('%m');
    }
    

    /**
     * @Route("/admin/graph/", name="graph")
     */
    public function graph(ClientRepository $repoClient, Request $request)
    {
        // les client présent
        $all_client = $repoClient->findAll();
        $tab_client_mois = [];
        $tab_client_montant = [];
        $jan = 0;
        $fev = 0;
        $mars = 0;
        $avr = 0;
        $mai = 0;
        $juin = 0;
        $jul = 0;
        $aou = 0;
        $sep = 0;
        $oct = 0;
        $nov = 0;
        $dec  = 0;
        // montant
        $m_jan = 0;
        $m_fev = 0;
        $m_mars = 0;
        $m_avr = 0;
        $m_mai = 0;
        $m_juin = 0;
        $m_jul = 0;
        $m_aou = 0;
        $m_sep = 0;
        $m_oct = 0;
        $m_nov = 0;
        $m_dec  = 0;

        $annee = '';
        $today = new \DateTime();
        $ppa = $today->format('Y');
        if($request->request->count()>0){
            $annee = $request->request->get('annee');
        }
        else{

            $today = new \DateTime();
            $annee = $today->format('Y');
            
        }
        foreach ($all_client as $item) {
            // les clients de cet année là
            $sa_created_at = $item->getCreatedAt();
            $sa_created_at_mois = $sa_created_at->format('m-Y');
            $son_num_mois =  $sa_created_at->format('m');
            $sa_created_at_annee = $sa_created_at->format('Y');
            if ($sa_created_at_annee == $annee) {
                if ($son_num_mois == '01') {
                    $jan++;
                    $m_jan+= $item->getMontant();
                }
                if ($son_num_mois == '02') {
                    $fev++;
                    $m_fev+=$item->getMontant();
                }
                if ($son_num_mois == '03') {
                    $mars++;
                    $m_mars+= $item->getMontant();
                }
                if ($son_num_mois == '04') {
                    $avr++;
                    $m_avr += $item->getMontant();
                }
                if ($son_num_mois == '05') {
                    $mai++;
                    $m_mai += $item->getMontant();
                }
                if ($son_num_mois == '06') {
                    $juin++;
                    $m_juin += $item->getMontant();
                }
                if ($son_num_mois == '07') {
                    $jul++;
                    $m_jul += $item->getMontant();
                }
                if ($son_num_mois == '08') {
                    $aou++;
                    $m_aou += $item->getMontant();
                }
                if ($son_num_mois == '09') {
                    $sep++;
                    $m_sep += $item->getMontant();
                }
                if ($son_num_mois == '10') {
                    $oct++;
                    $m_oct += $item->getMontant();
                }
                if ($son_num_mois == '11') {
                    $nov++;
                    $m_nov += $item->getMontant();
                }
                if ($son_num_mois == '12') {
                    $dec++;
                    $m_dec += $item->getMontant();
                }
            }
        }
        foreach($all_client as $c){
            $sa_created_at = $item->getCreatedAt();
            $sa_created_at_mois = $sa_created_at->format('m-Y');
            $son_num_mois =  $sa_created_at->format('m');
            $sa_created_at_annee = $sa_created_at->format('Y');
            if($sa_created_at_annee < $ppa){
                $ppa = $sa_created_at_annee;
            }
        }
        array_push($tab_client_mois, $jan, $fev, $mars, $avr, $mai, $juin, $jul, $aou, $sep, $oct, $nov, $dec);
        array_push($tab_client_montant, $m_jan, $m_fev, $m_mars, $m_avr, $m_mai, $m_juin, $m_jul, $m_aou, $m_sep, $m_oct, $m_nov, $m_dec);
        // liste d'annéé exixtante
        $annee_actus = $today->format('Y');
        $tab = [];
        for($i = $annee_actus; $i >= $ppa; $i--){
            array_push($tab, $i);
        }
        
        //dd($tab);
        return $this->render('page/graph.html.twig', [
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'archived' => $this->count_archived($repoClient),
            'pointed' => $this->count_pointed($repoClient),
            'nouveau' => $this->count_nouveau($repoClient),
            'impaye' => $this->count_impaye($repoClient),
            'attente' => $this->count_attente($repoClient),
            'tab_client_mois' => $tab_client_mois,
            'tab_client_montant' => $tab_client_montant,
            'annee' => $annee,
            'tab_annee' => $tab,
        ]);
    }
    
    /**
     * @Route("/io/{mois}", name="io")
     */
    public function client_enregistre_mois($mois, ClientRepository $repoClient)
    {
        $clients = $repoClient->findAll();
        $items = [];
        foreach($clients as $item){
            $createdAt = $item->getCreatedAt();
            $ddm = '1-'.$mois;
            $ddm = new \DateTime($ddm);
            $dfm = '31-'.$mois;
            $dfm = new \DateTime($dfm);
           dd($dfm);
        }
        
        return $this->render('page/graph.html.twig', [
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
        $tabPresent = $repoClient->findAll();
        $n = count($tabPresent);
        //dd($n);
        return $n;
        
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
    /**
     * @Route("/admin/all_pointed/", name="all_pointed")
     */
    public function all_pointed(ClientRepository $repoClient)
    {
        $clients = $repoClient->findAll();
        $today_moth = new \DateTime();
        $today_moth = $today_moth->format('m-Y');
        $tabClientPointed = [];
        foreach($clients as $client){
            // ses pointages fait
            $sesPF = $this->liste_pointage_du_client($client, $repoClient);
            if($sesPF != "vide"){
                $tab = [];
                foreach ($sesPF as $s) {
                    array_push($tab, $s->getNom());
                }
                if(in_array($today_moth, $tab)){
                    array_push($tabClientPointed, $client);
                }
            } 
            
        }
        return $this->render('page/client_pointed.html.twig', [
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'archived' => $this->count_archived($repoClient),
            'pointed' => $this->count_pointed($repoClient),
            'nouveau' => $this->count_nouveau($repoClient),
            'impaye' => $this->count_impaye($repoClient),
            'attente' => $this->count_attente($repoClient),
            'items' => $tabClientPointed,
            'liste' => 'all_pointed',
        ]);
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

    
}
