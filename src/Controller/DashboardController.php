<?php

namespace App\Controller;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
public function dashboard(UserRepository $userRepository): Response
{
    $userCount = $userRepository->count([]);

    return $this->render('dashboard.html.twig', [
        'userCount' => $userCount
    ]);
}

    #[Route('/tables', name: 'tables')] 
    public function tables(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        
        return $this->render('tables.html.twig', [
            'users' => $users
        ]);
    }
    #[Route(path: '/profile', name: 'profile')] 
    public function profile(UserRepository $userRepository):Response{
        $users = $userRepository->findAll();
        return $this->render('profile.html.twig', [
            'users' => $users
        ]);
    }
    
}
