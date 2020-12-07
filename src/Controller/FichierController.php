<?php

    namespace App\Controller;

use App\Entity\Client;
use App\Entity\Fichier1;
use App\Entity\Fichier2;
use App\Entity\Fichier3;
use App\Form\ImportType;
use App\Form\FichierType1;
use App\Form\FichierType2;
use App\Form\FichierType3;
use App\ClientServices\MyService;
use App\Repository\ClientRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FichierController extends AbstractController
{
    /**
     * @Route("/admin/upload/fichier1", name = "fichier1")
     *
     * @param Request $request
     * @param ClientRepository $repoClient
     * @return void
     */
    public function fichier1(Request $request, ClientRepository $repoClient, EntityManagerInterface $manager)
    {
        $form_import1 = $this->createForm(FichierType1::class);
        $form_import1->handleRequest($request);
        $repoF1 = $this->getDoctrine()->getRepository(Fichier1::class);
        $d_aff = [];
        if($form_import1->isSubmitted() && $form_import1->isValid()){
            $fichier = $form_import1->get('fichier')->getData();
            $originalFilename1 = pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename1 = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename1);
            $newFilename1 = $safeFilename1 . '.' . $fichier->guessExtension();
            $fileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($fichier->getRealPath()); // d'après dd($fichier)
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($fileType); // ty le taloha
            /* $sheetname = "FOURNISSEURS";
            $reader->setLoadSheetsOnly($sheetname);*/
            $spreadsheet = $reader->load($fichier->getRealPath()); // le nom temporaire
            $data = $spreadsheet->getActiveSheet()->toArray();
            for($i = 1; $i < count($data); $i++){
                array_push($d_aff, $data[$i]);
                $num_pens = $data[$i][0];
                $nom = $data[$i][1];
                $arr_ssd = $data[$i][2];
                $date = $data[$i][3];
                $ben = $data[$i][4];
                $ord = $data[$i][5];
                $fichier1 = new Fichier1();
                $fichier1->setNumPens($num_pens);
                $fichier1->setNom($nom);
                $fichier1->setArrSsd($arr_ssd);
                $fichier1->setDate($date);
                $fichier1->setBen($ben);
                $fichier1->setOrd($ord);
                $manager->persist($fichier1);
            }
            $manager->flush();
        }

        $form_import2 = $this->createForm(FichierType2::class);
        $form_import2->handleRequest($request);

        $form_import3 = $this->createForm(FichierType3::class);
        $form_import3->handleRequest($request);
        return $this->render('page/fichier.html.twig', [
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'archived' => $this->count_archived($repoClient),
            'pointed' => $this->count_pointed($repoClient),
            'nouveau' => $this->count_nouveau($repoClient),
            'impaye' => $this->count_impaye($repoClient),
            'attente' => $this->count_attente($repoClient),
            'form_import1' => $form_import1->createView(),
            'form_import2' => $form_import2->createView(),
            'form_import3' => $form_import3->createView(),
            'd_aff1' => $repoF1->findAll(),
           
        ]);
    }

    /**
     * @Route("/admin/upload/fichier2", name = "fichier2")
     *
     * @param Request $request
     * @param ClientRepository $repoClient
     * @return void
     */
    public function fichier2(Request $request, ClientRepository $repoClient, EntityManagerInterface $manager, MyService $service)
    {   
        
        $form_import2 = $this->createForm(FichierType2::class);
        $form_import2->handleRequest($request);
        $repoF2 = $this->getDoctrine()->getRepository(Fichier2::class);
        $d_aff = [];
        if ($form_import2->isSubmitted() && $form_import2->isValid()) {
            $fichier = $form_import2->get('fichier')->getData();
            $originalFilename1 = pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename1 = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename1);
            $newFilename1 = $safeFilename1 . '.' . $fichier->guessExtension();
            $fileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($fichier->getRealPath()); // d'après dd($fichier)
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($fileType); // ty le taloha
            /* $sheetname = "FOURNISSEURS";
            $reader->setLoadSheetsOnly($sheetname);*/
            $spreadsheet = $reader->load($fichier->getRealPath()); // le nom temporaire
            $data = $spreadsheet->getActiveSheet()->toArray();
                     
            for ($i = 1; $i < count($data); $i++) {
                array_push($d_aff, $data[$i]);
                $num_pens = $data[$i][0];
                $nom = $data[$i][1];
                $rub = $data[$i][2];
                $montant = $data[$i][3];
                $montant = $service->getAmount($montant);
                $date_debut = $data[$i][4];
                $date_fin = $data[$i][5];
                $fichier2 = new Fichier2();
                $fichier2->setNumPens($num_pens);
                $fichier2->setNom($nom);
                $fichier2->setRub($rub);
                $fichier2->setDateDebut($date_debut);
                $fichier2->setDateFin($date_fin);
                $fichier2->setMontant($montant);
                $manager->persist($fichier2);
            }
            $manager->flush();
        }

        $form_import1 = $this->createForm(FichierType1::class);
        $form_import1->handleRequest($request);

        $form_import3 = $this->createForm(FichierType3::class);
        $form_import3->handleRequest($request);
        return $this->render('page/fichier.html.twig', [
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'archived' => $this->count_archived($repoClient),
            'pointed' => $this->count_pointed($repoClient),
            'nouveau' => $this->count_nouveau($repoClient),
            'impaye' => $this->count_impaye($repoClient),
            'attente' => $this->count_attente($repoClient),
            'form_import1' => $form_import1->createView(),
            'form_import2' => $form_import2->createView(),
            'form_import3' => $form_import3->createView(),
            'd_aff2' => $repoF2->findAll(),

        ]);
    }

    /**
     * @Route("/admin/upload/fichier3", name = "fichier3")
     *
     * @param Request $request
     * @param ClientRepository $repoClient
     * @return void
     */
    public function fichier3(Request $request, ClientRepository $repoClient, EntityManagerInterface $manager, MyService $service)
    {
        $form_import3 = $this->createForm(FichierType3::class);
        $form_import3->handleRequest($request);
        $repoF3 = $this->getDoctrine()->getRepository(Fichier3::class);
        $d_aff = [];
        if ($form_import3->isSubmitted() && $form_import3->isValid()) {
            $fichier = $form_import3->get('fichier')->getData();
            $originalFilename1 = pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename1 = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename1);
            $newFilename1 = $safeFilename1 . '.' . $fichier->guessExtension();
            $fileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($fichier->getRealPath()); // d'après dd($fichier)
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($fileType); // ty le taloha
            /* $sheetname = "FOURNISSEURS";
            $reader->setLoadSheetsOnly($sheetname);*/
            $spreadsheet = $reader->load($fichier->getRealPath()); // le nom temporaire
            $data = $spreadsheet->getActiveSheet()->toArray();
            for ($i = 1; $i < count($data); $i++) {
                array_push($d_aff, $data[$i]);
                $num_pens = $data[$i][0];
                $code_rub = $data[$i][1];
                $ben = $data[$i][2];
                $montant = $data[$i][3];
                $montant = $service->getAmount($montant);
                $date_fin = $data[$i][4];
                $nom = $data[$i][5];
                $fichier3 = new Fichier3();
                $fichier3->setNumPens($num_pens);
                $fichier3->setNom($nom);
                $fichier3->setCodeRub($code_rub);
                $fichier3->setDateFin($date_fin);
                $fichier3->setMontant($montant);
                $fichier3->setBen($ben);
                $manager->persist($fichier3);
            }
            $manager->flush();
        }

        $form_import1 = $this->createForm(FichierType1::class);
        $form_import1->handleRequest($request);

        $form_import2 = $this->createForm(FichierType2::class);
        $form_import2->handleRequest($request);
        return $this->render('page/fichier.html.twig', [
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'archived' => $this->count_archived($repoClient),
            'pointed' => $this->count_pointed($repoClient),
            'nouveau' => $this->count_nouveau($repoClient),
            'impaye' => $this->count_impaye($repoClient),
            'attente' => $this->count_attente($repoClient),
            'form_import1' => $form_import1->createView(),
            'form_import2' => $form_import2->createView(),
            'form_import3' => $form_import3->createView(),
            'd_aff3' => $repoF3->findAll(),

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
        foreach ($tabPresent as $item) {
            $c = $item->getCreatedAt();
            $today = new \DateTime();
            $today_s = $today->format('d-m-Y');
            $c_s = $c->format("d-m-Y");
            if ($c_s == $today_s) {
                array_push($tab, $item);
            }
        }
        $n = count($tab);
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