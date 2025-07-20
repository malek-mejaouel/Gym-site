<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AboutusController extends AbstractController
{
    #[Route('/abous-us', name: 'about-us')]   // Your homepage route
    public function index(): Response
    {
        return $this->render('aboutus.html.twig');  // Render your base template
    }
}
