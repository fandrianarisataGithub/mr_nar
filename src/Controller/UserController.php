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
        //dd($items);
        return $this->render('user/editeurs.html.twig', [
            'items' => $items,
            'form' => $form->createView(),
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
