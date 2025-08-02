<?php

namespace App\Controller;

use App\Entity\Coach;
use App\Repository\CoachRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

#[Route('/dashboard/coach', name: 'coach_')]
class CoachController extends AbstractController
{
    #[Route('/add', name: 'add')]
    public function add(Request $request, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        $coach = new Coach();
        $form = $this->createForm(CoachType::class, $coach);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pdfFile = $form->get('degreePdf')->getData();

            if ($pdfFile) {
                $pdfName = uniqid() . '.' . $pdfFile->guessExtension();
                try {
                    $pdfFile->move($this->getParameter('degrees_directory'), $pdfName);
                    $coach->setDegreePdf($pdfName);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload degree file.');
                    return $this->redirectToRoute('coach_add');
                }
            }

            $em->persist($coach);
            $em->flush();

            // Send verification email
            $email = (new Email())
                ->from(new Address('admin@gym.com', 'Gym Admin'))
                ->to($coach->getEmail())
                ->subject('Welcome Coach - Application Received')
                ->html("
                    <p>Hello {$coach->getFirstName()} {$coach->getLastName()},</p>
                    <p>Thank you for applying as a <strong>{$coach->getSpecialty()}</strong> coach.</p>
                    <p>Age: {$coach->getAge()}</p>
                    <p>We received your application and degree. Please wait while we review your profile.</p>
                    <p>Best regards,<br>Gym Team</p>
                ");

            $mailer->send($email);

            $this->addFlash('success', 'Coach added and verification email sent!');
            return $this->redirectToRoute('coach_list');
        }

        return $this->render('coachform.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/list', name: 'list')]
    public function list(CoachRepository $repo): Response
    {
        return $this->render('coachform.html.twig', [
            'coaches' => $repo->findAll()
        ]);
    }
}

// Inline form type
class CoachType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('age')
            ->add('email', EmailType::class)
            ->add('degreePdf', FileType::class, [
                'label' => 'Upload Degree (PDF only)',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new File([
                        'mimeTypes' => ['application/pdf'],
                        'mimeTypesMessage' => 'Please upload a valid PDF',
                    ])
                ],
            ])
            ->add('specialty', ChoiceType::class, [
                'choices' => [
                    'Bodybuilding' => 'bodybuilding',
                    'Cardio' => 'cardio',
                    'Yoga' => 'yoga',
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Add Coach']);
    }
}
