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
                    $client->setEtatClient("présent");
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
              
            }
            //  dd($client);
            $manager->persist($client);
            $manager->flush();
            return $this->redirectToRoute("register_client");

        }
        $clients_du_jour = $repoClient->clientDuJour($user);
        $items = $repoClient->chercherTous($user);
        
        // calcul des montant journalier

        $mj = 0 ;
        $mmj = 0;
        foreach($clients_du_jour as $item){
            $mj += $item['montant'];
            $mmj += $item['montant_mensuel'];
        }
       

        return $this->render('client/register.html.twig', [
            "form" => $form_register->createView(),
            "items" => $items,
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'impaye' => $this->count_impaye($repoClient),
            'paye' => $this->count_impaye($repoClient),
            "date_du_jour" => $date_du_jour,
            "misy" => $misy,
            "clients_du_jour" => $clients_du_jour,
            "mmj" => $mmj,
            "mj" => $mj,
            "nbr_client_jour" => count($clients_du_jour),
        ]);
    }
    
    /**
     * @Route("/profile/lister_tout_client/{user_id}", name="liste_client_de_user")
     */
    public function liste_client_de_user(ClientRepository $repoClient, $user_id, Request $request, UserRepository $repos)
    {
        $user = new User();
        $session  = $request->getSession();
        $session_user = $session->get('session_user', []);

        $user = $repos->find($user_id);
        $nom = $user->getNom();
        $clients_du_jour = $repoClient->clientDuJour($user);
        $items = $repoClient->findByUser($user);
        $mj = 0;
        $mmj = 0;
        foreach ($clients_du_jour as $item) {
            $mj += $item['montant'];
            $mmj += $item['montant_mensuel'];
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
            'impaye' => $this->count_impaye($repoClient),
            'paye' => $this->count_impaye($repoClient),
            "clients_du_jour" => $clients_du_jour,
            "mmj" => $mmj,
            "mj" => $mj,
            "nbr_client_jour" => count($clients_du_jour),
            "date_du_jour" => new \DateTime(),
            "m" =>$m,
            "mm" =>$mm,
        ]);
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
    public function supprimer_client($id, Request $request, EntityManagerInterface $manager, PointageRepository $repoPointage)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){
            
            $action = $request->get('action');
            if($action== "suppression"){
                // on select le client
                $client = new Client();
                $client = $this->getDoctrine()->getRepository(Client::class)->find($id);
                // on supprime le client
                    // mais d'abord il faaut enlever les champs des tables fille
                    // suppr de son pointage
                    $pointage = new Pointage(); 
                    $pointages = $repoPointage->findByClient($client);
                    foreach ($pointages as $pointage) {
                        $manager->remove($pointage);
                    }
                // maintenant le client
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
        $manager->flush();
        return $this->redirectToRoute('client_suspendu', [
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'impaye' => $this->count_impaye($repoClient),
            'paye' => $this->count_impaye($repoClient),
        ]);

    }
    /**
     * @Route("/admin/classe_impaye/{id}", name="classe_impaye_client")
     */
    public function classe_impaye_client($id, ClientRepository $repoClient, EntityManagerInterface $manager)
    {
        $client = new Client();
        $client = $repoClient->find($id);
        $manager->flush();
        return $this->redirectToRoute('client_impaye', [
            'present' => $this->count_present($repoClient),
            'suspendu' => $this->count_suspendu($repoClient),
            'impaye' => $this->count_impaye($repoClient),
            'paye' => $this->count_impaye($repoClient),
        ]);
    }


    public function count_present(ClientRepository $repoClient)
    {
        $tabPresent = $repoClient->countPresent('présent');
        $n = count($tabPresent);
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
    public function count_impaye(ClientRepository $repoClient)
    {
        $tabPresent = $repoClient->countPresent('impayé');
        $n = count($tabPresent);
        //dd($n);
        return $n;
    }

    public function count_paye(ClientRepository $repoClient)
    {
        $tabPaye = $repoClient->countPresent('payé');
        $n = count($tabPaye);
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
    
    
    
}
