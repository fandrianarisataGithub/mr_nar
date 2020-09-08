<?php

namespace App\Controller;

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
        
        return $this->render('client/register.html.twig');
    }
}
