<?php

    namespace App\Controller;

use App\Entity\Client;
use App\Form\ImportType;
use App\Form\FichierType1;
use App\Form\FichierType2;
use App\Form\FichierType3;
use App\Repository\ClientRepository;
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
    public function fichier1(Request $request, ClientRepository $repoClient)
    {
        $form_import1 = $this->createForm(FichierType1::class);
        $form_import1->handleRequest($request);
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
            $d_aff = [];
            for($i = 1; $i < count($data); $i++){
                array_push($d_aff, $data[$i]);
            }
            //dd($d_aff);
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
            'd_aff1' => $d_aff,
           
        ]);
    }

    /**
     * @Route("/admin/upload/fichier2", name = "fichier2")
     *
     * @param Request $request
     * @param ClientRepository $repoClient
     * @return void
     */
    public function fichier2(Request $request, ClientRepository $repoClient)
    {   
        
        $form_import2 = $this->createForm(FichierType2::class);
        $form_import2->handleRequest($request);
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
            $d_aff = [];
            for ($i = 1; $i < count($data); $i++) {
                array_push($d_aff, $data[$i]);
            }
            //dd($d_aff);
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
            'd_aff2' => $d_aff,

        ]);
    }

    /**
     * @Route("/admin/upload/fichier3", name = "fichier3")
     *
     * @param Request $request
     * @param ClientRepository $repoClient
     * @return void
     */
    public function fichier3(Request $request, ClientRepository $repoClient)
    {
        $form_import3 = $this->createForm(FichierType3::class);
        $form_import3->handleRequest($request);
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
            $d_aff = [];
            for ($i = 1; $i < count($data); $i++) {
                array_push($d_aff, $data[$i]);
            }
            //dd($d_aff);
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
            'd_aff3' => $d_aff,

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