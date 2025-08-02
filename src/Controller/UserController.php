<?php

namespace App\Controller;
use App\Repository\UserRepository;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
                    'profile_image' => $user->getProfileImage(),
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
            $session->invalidate();
            $this->addFlash('success', 'Logged out successfully!');
            return $this->redirectToRoute('home');
        }
      
        #[Route('/delete-user/{id}', name: 'delete_user', methods: ['DELETE'])]
        public function deleteUser(int $id, UserRepository $userRepository, EntityManagerInterface $em): JsonResponse
        {
            $user = $userRepository->find($id);
        
            if (!$user) {
                return new JsonResponse(['success' => false, 'message' => 'User not found'], 404);
            }
        
            $em->remove($user);
            $em->flush();
        
            return new JsonResponse(['success' => true]);
        }
        
     
#[Route('/update', name: 'update', methods: ['GET', 'POST'])]
public function update(Request $request, UserRepository $userRepository, EntityManagerInterface $em): Response
{
   
    $sessionUser = $request->getSession()->get('user');
    if (!$sessionUser || empty($sessionUser['id'])) {
        $this->addFlash('error', 'User not authenticated.');
        return $this->render('profile.html.twig');
    }
    $user = $userRepository->find((int) $sessionUser['id']);
    if (!$user) {
        $this->addFlash('error', 'User not found.');
        return $this->render('profile.html.twig');
    }
    if ($request->isMethod('POST')) {
        try {
           
            $newEmail    = trim((string) $request->request->get('email'));
            $newUsername = trim((string) $request->request->get('name')); 
            if ($newEmail === '' || $newUsername === '') {
                $this->addFlash('error', 'All fields are required.');
                return $this->render('profile.html.twig', ['user' => $user]);
            }

         
            $existingUser = $userRepository->findOneBy(['email' => $newEmail]);
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                $this->addFlash('error', 'Email already used by another user!');
                return $this->render('profile.html.twig', ['user' => $user]);
            }
            $user->setEmail($newEmail);
            $user->setUsername($newUsername);
            $em->flush();
            $request->getSession()->set('user', [
                'id'       => $user->getId(),
                'username' => $user->getUsername(),
                'email'    => $user->getEmail(),
                'profile_image' => $user->getProfileImage(),
            ]);

            $this->addFlash('success', 'Profile updated!');
        } catch (\Throwable $e) {
            // You can log $e->getMessage()
            $this->addFlash('error', 'An error occurred. Please try again.');
        }
    }

    
    return $this->render('profile.html.twig', ['user' => $user]);
}
#[Route('/image', name: 'image', methods: ['POST'])]
public function image(Request $request, UserRepository $userRepository, EntityManagerInterface $em): Response
{
    $sessionUser = $request->getSession()->get('user');

    if (!$sessionUser || empty($sessionUser['id'])) {
        $this->addFlash('error', 'User not authenticated.');
        return $this->redirectToRoute('profile');
    }

    // Find user in DB
    $user = $userRepository->find((int) $sessionUser['id']);
    if (!$user) {
        $this->addFlash('error', 'User not found.');
        return $this->redirectToRoute('profile');
    }

    /** @var UploadedFile $avatarFile */
    $avatarFile = $request->files->get('avatar');

    if ($avatarFile) {
        $newFilename = uniqid() . '.' . $avatarFile->guessExtension();

        // Delete old image if exists
        if ($user->getProfileImage()) {
            $oldImagePath = $this->getParameter('avatars_directory') . '/' . $user->getProfileImage();
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }

        // Upload new file
        $avatarFile->move(
            $this->getParameter('avatars_directory'),
            $newFilename
        );

        // Save to DB
        $user->setProfileImage($newFilename);
        $em->flush();

        // Update session
        $request->getSession()->set('user', [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'profileImage' => $newFilename
        ]);

        $this->addFlash('success', 'Profile image updated!');
    }

    return $this->redirectToRoute('profile');
}

} 