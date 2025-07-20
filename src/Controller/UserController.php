<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('login.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

   

    #[Route('/register', name: 'register', methods: ['GET', 'POST'])]
    public function register(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            try {
                $email = $request->request->get('email');
                $username = $request->request->get('username');
                $password = $request->request->get('password');
    
                if (!$email || !$username || !$password) {
                    $this->addFlash('error', 'All fields are required.');
                    return $this->render('login.html.twig'); // No redirect
                }
    
                $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $email]);
                if ($existingUser) {
                    $this->addFlash('error', 'Email already used!');
                    return $this->render('login.html.twig'); // No redirect
                }
    
                $user = new User();
                $user->setEmail($email);
                $user->setUsername($username);
                $user->setPassword($password);
                $em->persist($user);
                $em->flush();
    
                $this->addFlash('success', 'Account created!');
                return $this->render('login.html.twig'); // Show message on same page
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred. Please try again.');
                return $this->render('login.html.twig');
            }
        }
    
        return $this->render('login.html.twig');
    }
    
    

    
        #[Route('/login', name: 'login', methods: ['POST'])]
        public function login(Request $request, EntityManagerInterface $em): Response
        {
            $email = $request->request->get('email');
            $password = $request->request->get('password');
        
            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        
            if (!$user || $user->getPassword() !== $password) {
                $this->addFlash('error', 'Invalid credentials!');
                return $this->render('login.html.twig');
            } else {
                // Store user info in session
                $session = $request->getSession();
                $session->set('user', [
                    'id' => $user->getId(),
                    'username' => $user->getUsername(),
                    'email' => $user->getEmail(),
                ]);
        
                $this->addFlash('success', 'Welcome ' . $user->getUsername() . '!');
            }
        
            return $this->redirectToRoute('home');
        }
        #[Route('/logout', name: 'logout')]
        public function logout(Request $request): Response
        {
            $session = $request->getSession();
            $session->remove('user');
            $session->invalidate(); // optional: clear everything
        
            $this->addFlash('success', 'Logged out successfully!');
            return $this->redirectToRoute('home');
        }
                
}
