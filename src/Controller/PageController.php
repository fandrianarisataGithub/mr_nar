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

    public function triage_principal(ClientRepository $repoClient, EntityManagerInterface $manager)
    {
        $c = $repoClient->findAll();
        $today = new \DateTime();
        $tomoth = $today->format('m-Y');
        foreach ($c as $item) {

            /** ze nahavita pointage t@ty */

            // ny pointage efa vitany
            $tabPointEff = $this->liste_pointage_du_client($item, $repoClient);
            $tabNom = [];
            for ($i = 0; $i < count($tabPointEff); $i++) {
                $n = $tabPointEff[$i]->getNom();
                array_push($tabNom, $n);
            }


            if (in_array($tomoth, $tabNom)) {
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
            if ($tomoth == $nextPointage) {
                // si le pointage est le premier
                if ($n == '-1') {
                    // client présent pour le pointage
                    $item->setEtatClient('présent');
                    $manager->persist($item);
                    $manager->flush();
                } else {
                    // nandoha ve teo aloha ?
                    // on liste les pointage effectué par ce client 
                    $tabPoint = $this->liste_pointage_du_client($item, $repoClient);
                    // le mois dernier 
                    $s = $this->nom_dernier_mois();
                    // nanao pointage ve izy t@io 
                    if (in_array($s, $tabPoint)) {
                        //nanao izy
                        //atao client présent
                        $item->setEtatClient('présent');
                        $manager->persist($item);
                        $manager->flush();
                    } else {

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
                        if ($key >= 3) {
                            $item->setEtatClient('impayé');
                            $manager->persist($item);
                            $manager->flush();
                        } else {
                            $item->setEtatClient('suspendu');
                            $manager->persist($item);
                            $manager->flush();
                        }
                    }
                }
            }
            if ($item->getDateFin() < $today) {
                $item->setEtatClient('archivé');
                $manager->persist($item);
                $manager->flush();
            }

            // ireo en attente 
            $etat = $item->getEtatClient();
            // premier jour du mois 
            $tomoth = $today->format('m-Y');





            $p = "1-" . $tomoth;

            $date1 = new \DateTime($p);
            //dd($date1);
            $date = date_create($date1->format("Y-m-d"));

            // raha ny 1 mois avant no hitsarana azy

            date_add($date, date_interval_create_from_date_string(1 . ' months'));
            //dd($date);
            $dd = $item->getDateDebut();
            if ($dd >= $date) {
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
        $items = $repoClient->countPresent('présent');
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

        return $this->render('page/client_present.html.twig', [
            'items' => $items,
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
     * @Route("/admin/graph", name="graph")
     */
    public function graph(ClientRepository $repoClient)
    {
        
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
        $tabPresent = $repoClient->countPresent('présent');
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

    
}
