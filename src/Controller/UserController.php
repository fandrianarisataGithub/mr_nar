<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Client;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/admin/editeurs", name="editeurs")
     * @Route("/admin/edit_editeurs/{id}", name="edit_user")
     */
    public function editeurs(User $user = null, ClientRepository $repoClient, UserRepository $repoUser, Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {
       
        $form = $this->createForm(UserType::class, $user);
        $items = $repoUser->findAll();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // dd($user);
            if (!$user) {
                $user = new User();
            }
            $user = $form->getData();
            $pass = $user->getPassword();
            $user->setPassword($encoder->encodePassword($user, $pass));
            // manamboatra rôle à partir anle type
            $type = $user->getType();
            if($type == "admin"){
                $user->setRoles(["ROLE_ADMIN"]);
            }
            else if($type=="editor"){
                $user->setRoles(["ROLE_EDITOR"]);
            }
            $manager->persist($user);
            $manager->flush();
            return $this->redirectToRoute("editeurs");
        }
        $tabPresent = $repoClient->findAll();
        $nouveau = [];
        foreach ($tabPresent as $item) {
            $c = $item->getCreatedAt();
            $today = new \DateTime();
            $today_s = $today->format('d-m-Y');
            $c_s = $c->format("d-m-Y");
            if ($c_s == $today_s) {
                array_push($nouveau, $item);
            }
        }
        
        return $this->render('user/editeurs.html.twig', [
            'items' => $items,
            'nouveau' => $nouveau,
            'form' => $form->createView(),
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'archived' => $this->count_archived($repoClient),
            'pointed' => $this->count_pointed($repoClient),
            'nouveau' => $this->count_nouveau($repoClient),
            'impaye' => $this->count_impaye($repoClient),
            'attente' => $this->count_attente($repoClient),
        ]);
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


    public function count_present(ClientRepository $repoClient)
    {
        $tabPresent = $repoClient->findAll();
        $n = count($tabPresent);
        //dd($n);
        return $n;
    }
   
    public function count_pointed(ClientRepository $repoClient)
    {
        $clients = $repoClient->findAll();
        $today = new \DateTime();
        $today_moth = $today->format('m-Y');
        $n=0;
        foreach($clients as $client){
            $ses_pointages_fait = $this->liste_pointage_du_client($client, $repoClient);
            $tab = array();
           if($ses_pointages_fait != 'vide'){
                foreach ($ses_pointages_fait as $pointage) {
                    array_push($tab, $pointage->getNom());
                }
           }
           if(in_array($today_moth, $tab)){
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
     * @Route("/admin/client_de/{user_id}", name="supprimer_client_de")
     */
    public function import_client_de_user($user_id, ClientRepository $repoClient, UserRepository $repoUser, Request $request, EntityManagerInterface $manager)
    {
        $userOrigin = new User();
        $userOrigin = $repoUser->find($user_id);
        $clients = $repoClient->findByUser($userOrigin);
        $session  = $request->getSession();
        $session_user = $session->get('session_user', []);
        $userCourant = $repoUser->find($session_user['id']); 

        // on insère les clients de userOrigin dans cel de userCourant

        foreach($clients as $client){
            $client->setUser($userCourant);
             $manager->persist($client);
             $manager->flush();
        }
        // on peut maintenant supprimer le userOrigin
        $manager->remove($userOrigin);
        $manager->flush();

        return $this->redirectToRoute("editeurs");
    }

    
}
