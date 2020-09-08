<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PageController extends AbstractController
{
    /**
     * @Route("/profile/client_present", name="client_present")
     */
    public function client_present()
    {
        return $this->render('page/client_present.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }
    /**
     * @Route("/admin/client_paye", name="client_paye")
     */
    public function client_paye()
    {
        return $this->render('page/client_paye.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }
    /**
     * @Route("/admin/client_suspendu", name="client_suspendu")
     */
    public function client_suspendu()
    {
        return $this->render('page/client_suspendu.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }
    /**
     * @Route("/profile/single_page", name="single_page")
     */
    public function single_page()
    {
        return $this->render('page/single_page.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }
    /**
     * @Route("/admin/pointage", name="pointage")
     */
    public function pointage()
    {
        return $this->render('page/pointage.html.twig', [
            'controller_name' => 'PageController',
        ]);
    }
    
}
