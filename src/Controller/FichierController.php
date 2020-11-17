<?php

    namespace App\Controller;

use App\Entity\Client;
use App\Form\ImportType;
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
        $form_import = $this->createForm(FichierType::class);
        $form_import->handleRequest($request);
        return $this->render('page/fichier.html.twig', [
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'archived' => $this->count_archived($repoClient),
            'pointed' => $this->count_pointed($repoClient),
            'nouveau' => $this->count_nouveau($repoClient),
            'impaye' => $this->count_impaye($repoClient),
            'attente' => $this->count_attente($repoClient),
            'form_import' => $form_import->createView(),
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