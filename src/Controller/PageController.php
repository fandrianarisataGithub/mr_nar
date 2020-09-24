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
     * @Route("/", name="triage")
     */
    public function triage(ClientRepository $repoClient, PointageRepository $repoPointage, EntityManagerInterface $manager)
    {
        //$clients_presents = $repoClient->countPresent('présent');
        $clients_presents = $repoClient->findAll();
        // le mois du jour 
        /** test */

            $date1 = "2021-02-15";
            $date2 = "2021-02-16";
            if ($date1 < $date2)
            echo "$date1 est inférieur à $date2";
            else
            echo "$date1 est supérieur à $date2";

        /** / test */
        $now = new \Datetime();
        $now_s = $now->format('d-m-Y');
       // $now_s = date_parse_from_format("d-m-Y", $now);
        $tab = explode("-",$now_s);
        $now_month = $tab[1]."-".$tab[2];

        $last_m_pointage = "";

        if($tab[1]==1){
            $last_m_pointage = "12-".($tab[2] - 1);
        }
        else{
            $last_m_pointage =  ($tab[1] - 1) . "-" . $tab[2];
        }

        $client_a_pointer = [];
        $client_a_nepas_pointer = [];
        foreach($clients_presents as $item){
            $liste_m_p = $this->liste_m_p($item);
            if(in_array($now_month, $liste_m_p)){
               
               // jerena hoe nandoha t@ray volana sa tsia
                  
                if(in_array($last_m_pointage, $liste_m_p)){

                    // on select le pointage pour ce nom là
                    
                    $pointage  = $repoPointage->findOneByNom($last_m_pointage); 
                    $id_lastP = $pointage->getId();
                    // est-ce que ce client là possede ce pointage ?
                    $conn = $manager->getConnection();

                    $sql = '
                    SELECT * FROM `pointage_client` WHERE client_id =:id
                    ';
                    $stmt = $conn->prepare($sql);
                    $stmt->execute(['id' => $item->getId()]);

                    // returns an array of arrays (i.e. a raw data set)
                    $tab =  $stmt->fetchAll();
                    $last_indice = count($tab) - 1;
                    // last pointage est id = $tab[$last_indice]['pointage_id'];
                   if( $tab[$last_indice]['pointage_id'] >= $id_lastP){

                        // ce client peut etre parmis les clients présent nandoha izy
                        array_push($client_a_pointer, $item);
                   }
                   
                   else{
                       // jerena aloha ny date de debut any sao @ty izy vao tokony handoha
                       $son_data_debut = $item->getDateDebut();
                       $stringDate = $son_data_debut->format('d-m-Y');
                        $tab = explode("-", $stringDate);
                        $stringDate_mois_annee = $tab[1] . "-" . $tab[2];
                        
                        if($now_month == $stringDate_mois_annee){
                            array_push($client_a_pointer, $item);
                        }
                        else{
                            $item->setEtatClient("suspendu");
                            $manager->persist($item);
                            $manager->flush();
                        }
                        
                   }
                   
                } 
               
            }
            else{
                // liste des client qui n'ont pas de pointage pour ce mois
                array_push($client_a_nepas_pointer, $item);
            }
            
        }
        dd($client_a_pointer);
        
    }
    
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
   /* public function single_page($id_client, ClientRepository $repoClient, PointageRepository $repoPointage)
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

        // $nbr_mois_restant = $nbrMois - $nbr_p_effectue;
        
        // // montant payé
        // $montant_paye = $montant_mensuel * $nbr_p_effectue;

        // // montant restant
        // $montant_restant = $montant - $montant_paye;
        
        return $this->render('page/single_page.html.twig', [
            'item' => $client,
           /* 'montant_paye' => $montant_paye,
            'montant_restant' => $montant_restant,
            'nbr_p_effectue' => $nbr_p_effectue,
            'nbr_paiement_total' => $nbrMois,
            'nbr_mois_restant' => $nbr_mois_restant,
            "nbr_pointage" => $nbr_p_effectue,
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'archived' => $this->count_archived($repoClient),
            'pointed' => $this->count_pointed($repoClient),
        ]);
    }*/
        
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
