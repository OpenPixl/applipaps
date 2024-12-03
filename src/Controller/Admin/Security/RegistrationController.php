<?php

namespace App\Controller\Admin\Security;

use App\Entity\Admin\Security\Employed;
use App\Form\Admin\Security\RegistrationFormType;
use App\Form\Admin\Security\RegistrationForm2Type;
use App\Repository\Admin\Security\EmployedRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new Employed();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('contact@openpixl.fr', 'Contact PAP\'s immo'))
                    ->to((string) $user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('admin/security/registration/confirmation_email.html.twig')
            );

            // do anything else you need here, like send an email

            return $this->redirectToRoute('paps_gestapp_app_dashboard');
        }

        return $this->render('admin/security/registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/security/register/prescriber', name: 'paps_security_register_prescriber')]
    public function registerPrescriber(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, EmployedRepository $employedRepository): Response
    {
        $user = new Employed();
        $form = $this->createForm(RegistrationForm2Type::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $code = $form->get('numCollaborator')->getData();

            $collaborateur = $employedRepository->findOneBy(['numCollaborator'=> $code]);
            //dd($collaborateur);
            if($collaborateur){
                // encode the plain password
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
                $user->setReferent($collaborateur);
                $user->setGenre('prescripteur');
                $user->setRoles(['ROLE_PRESCRIBER']);

                $entityManager->persist($user);
                $entityManager->flush();

                // generate a signed url and email it to the user
                $this->emailVerifier->sendEmailConfirmation('op_webapp_security_verifyemail', $user,
                    (new TemplatedEmail())
                        ->from(new Address('contact@papsimmo40.fr', 'Contact Paps Immo 40'))
                        ->to($user->getEmail())
                        ->subject('Please Confirm your Email')
                        ->htmlTemplate('admin/security/registration/confirmation_email2.html.twig')
                );
                $this->addFlash('success', 'Votre compte est crée. Toutefois, nous controlons si cette inscription est issu d\'un être humain et nom d\'un robot informatique en vous envoyant un e-mail de confirmation à l\'adresse indiquée. L\'inscription sera définitive après validation de ce mail de votre part.');
                // do anything else you need here, like send an email
                return $this->redirectToRoute('paps_gestapp_app_dashboard');
            }else{
                $this->addFlash('error_collaborator', "Le collaborateur n'existe pas.");
                return $this->render('admin/security/registration/register2.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }
        }

        return $this->render('admin/security/registration/register2.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'paps_security_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            /** @var Employed $user */
            $user = $this->getUser();
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }
}
