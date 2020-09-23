<?php

namespace App\Controller;

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
use Symfony\Component\Routing\Annotation\Route;
// Include Dompdf required namespaces
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PageController extends AbstractController
{
    
    /**
     * @Route("/profile/client_present", name="client_present")
     */
    public function client_present(ClientRepository $repoClient, UserRepository $repoUser)
    {
        $user = new User();
        $items = $repoClient->countPresent('présent');
         $users = $repoUser->findAll();
       $total_mj = 0;
       $total_mmj = 0;
       $nbr_client_du_jour = 0;
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
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'archived' => $this->count_archived($repoClient),
            'pointed' => $this->count_pointed($repoClient),
             "total_mj" => $total_mj,
             "total_mmj" => $total_mmj,
            "nbr_client_du_jour" => $nbr_client_du_jour,
            "date_du_jour" => new \DateTime(),
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
     * @Route("/admin/client_impaye", name="client_impaye")
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
        ]);
    }

    /**
     * @Route("/admin/client_paye", name="client_paye")
     */
    public function client_pointed(ClientRepository $repoClient)
    {
        $user = new User();
        $items = $repoClient->countPresent('pointé');
        return $this->render('page/client_pointed.html.twig', [
            'items' => $items,
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'archived' => $this->count_archived($repoClient),
            'pointed' => $this->count_pointed($repoClient),
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
        //$pointage = $repoPointage->findByClient($client);
       // $nbr_p_effectue = count($pointage);
        //dd($nbr_p_effectue);
        // nombre de mois restant

        /*$nbr_mois_restant = $nbrMois - $nbr_p_effectue;
        
        // montant payé
        $montant_paye = $montant_mensuel * $nbr_p_effectue;

        // montant restant
        $montant_restant = $montant - $montant_paye;*/
        
        return $this->render('page/single_page.html.twig', [
            'item' => $client,
           /* 'montant_paye' => $montant_paye,
            'montant_restant' => $montant_restant,
            'nbr_p_effectue' => $nbr_p_effectue,
            'nbr_paiement_total' => $nbrMois,
            'nbr_mois_restant' => $nbr_mois_restant,
            "nbr_pointage" => $nbr_p_effectue,*/
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'archived' => $this->count_archived($repoClient),
            'pointed' => $this->count_pointed($repoClient),
        ]);
    }
    /**
     * @Route("/admin/pointage", name="pointage")
     */
    public function pointage(ClientRepository $repoClient, PointageRepository $repoPointage, EntityManagerInterface $manager)
    {
        $this->retournAllToPresent();
        
        $user = new User();
        $client = new Client();
        $pointage = new Pointage();
        $now = new \DateTime();
        $now_s = $now->format("m-Y");
        $last_moth = 0;
        $fifteen_prec1 = date('Y-m-d', strtotime(date('Y-m-15')." -2 month"));
        //dd($fifteen_prec);
        $fifteen_prec2 = date_create($fifteen_prec1);
        // le mois courant
        $month = date("m", strtotime($now->format("Y-m-d")));
        //dd($month);
        $year = date("Y", strtotime($now->format("Y-m-d")));
       // dd($year);
        
        $items = [];
        $liste_a_pointer = [];
        $liste_a_nepas_pointer = [];
        $clients_have_pointage = [];
       
        $clients_present = $repoClient->countPresent('présent'); // objet
        
        /*foreach($clients_present as $item){
            $son_liste_pointage = $this->liste_m_p($item);
            // si le mois actuel est dans son pointage
            if(in_array($now_s, $son_liste_pointage)){
                array_push($items, $item);
                $item->setEtatClient("présent");
                $manager->flush();
            }
           
        }*/
        
        // jerena ze tsy nandoha talohan'ty mois ty
       /* foreach($items as $item){
           // $pointage = $repoPointage->lastPointagePourUnClient($item);
            
           if(!$pointage){ // tsy manana pointage nefa tokony hanao
                array_push($liste_a_pointer, $item);
                
           }
           else{
                array_push($clients_have_pointage, $item);
               
                $t = $pointage["created_at"];

                // si ce client a le pointage courant
                if($pointage["p_mois"] == $month && $pointage["p_annee"] == $year){
                    $item->setEtatClient("pointé");
                    $manager->flush();
                    array_push($clients_have_pointage, $item);            
                }

                // if ($t < $fifteen_prec2) { // si sa dernier date de pointage est avant le 15em du mois precédent 
                //    // dd($t); ce client sera suspendu
                //    $item->setEtatClient("suspendu");
                //    $manager->flush();
                //     array_push($liste_a_nepas_pointer, $item);
                //    //dd($item); ok (lasa suspendu le client)
                // }
                // else{
                //     $item->setEtatClient("présent");
                //     $manager->flush();
                //     array_push($liste_a_pointer, $item);
                // }
           }
           
            
        }*/

        //dd($clients_have_pointage);   
        
        //dd($pointage[0]["created_at"]);
        return $this->render('page/pointage.html.twig', [
           "items" => $clients_present,
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'archived' => $this->count_archived($repoClient),
            'pointed' => $this->count_pointed($repoClient),
        ]);
    }
    // famerenana ny client rehetra ho présent

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
