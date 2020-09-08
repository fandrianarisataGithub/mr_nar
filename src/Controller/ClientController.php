<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClientController extends AbstractController
{
    /**
     * @Route("/profile/register_client", name="register_client")
     */
    public function register_client(Request $request)
    {   
        $client = new Client();
        $form_register = $this->createForm(ClientType::class, $client);
        return $this->render('client/register.html.twig', [
            "form" => $form_register->createView()
        ]);
    }
    /**
     * @Route("/profile/lister_client_by_user/{id}", name="lister_client_by_user")
     */
    public function lister_client_by_user(Request $request, ClientRepository $repo, $id)
    {
       $client = $repo->find_client_byId($id); 
        return $this->render('client/lister_client_by_user.html.twig');
    }
}
