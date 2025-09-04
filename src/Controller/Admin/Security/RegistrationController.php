<?php

namespace App\Controller\Admin\Security;

use App\Entity\Admin\Comm\Contact;
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
        $user -> setCivility(1);
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

                // generate a signed url and email it to the user
                $this->emailVerifier->sendEmailConfirmation('paps_security_verify_email', $user,
                    (new TemplatedEmail())
                        ->from(new Address('contact@papsimmo40.fr', 'Contact Paps Immo 40'))
                        ->to($user->getEmail())
                        ->subject('Please Confirm your Email')
                        ->htmlTemplate('admin/security/registration/confirmation_email2.html.twig')
                );

                $contact = new Contact;
                $contact->setContent('Une nouvelle inscription avec votre référence est enregistrée dans AppliPAPs.');
                $contact->setForEmployed($user->getReferent());
                $contact->setIsRGPD(1);
                if($user->getHome()){
                    $contact->setPhoneHome($user->getHome());
                }
                if($user->getGsm()){
                    $contact->setPhoneGsm($user->getGsm());
                }
                $contact->setName('APPLIPAPS : Message ');
                $contact->setContactBy('applipaps');
                $contact->setEmail($user->getEmail());
                $contact->setFromApp('applipaps');

                $entityManager->persist($contact);
                $entityManager->flush();

                $this->addFlash('success', 'Votre compte est crée. Toutefois, un lien de confirmation va être envoyé à votre e-mail pour validation.');
                // do anything else you need here, like send an email
                return $this->redirectToRoute('paps_gestapp_app_dashboard');
            }else{

                return $this->render('admin/security/registration/register2.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }
        }

        return $this->render('admin/security/registration/register2.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/security/register/byqr/{idEmployed}', name: 'paps_security_register_byqr')]
    public function registerByQr(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        EmployedRepository $employedRepository,
        $idEmployed
    ): Response
    {
        $collaborateur = $employedRepository->findOneBy(['id'=> $idEmployed]);
        $user = new Employed();

        $form = $this->createForm(RegistrationForm2Type::class, $user);
        $form->handleRequest($request);

        $form->get('numCollaborator')->setData($collaborateur->getNumCollaborator());

        if ($form->isSubmitted() && $form->isValid()) {
            $code = $form->get('numCollaborator')->getData();

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

                // generate a signed url and email it to the user
                $this->emailVerifier->sendEmailConfirmation('paps_security_verify_email', $user,
                    (new TemplatedEmail())
                        ->from(new Address('contact@papsimmo40.fr', 'Contact Paps Immo 40'))
                        ->to($user->getEmail())
                        ->subject('Please Confirm your Email')
                        ->htmlTemplate('admin/security/registration/confirmation_email2.html.twig')
                );

                $contact = new Contact;
                $contact->setContent('Une nouvelle inscription avec votre référence est enregistrée dans AppliPAPs.');
                $contact->setForEmployed($user->getReferent());
                $contact->setIsRGPD(1);
                if($user->getHome()){
                    $contact->setPhoneHome($user->getHome());
                }
                if($user->getGsm()){
                    $contact->setPhoneGsm($user->getGsm());
                }
                $contact->setName('APPLIPAPS : Message ');
                $contact->setContactBy('applipaps');
                $contact->setEmail($user->getEmail());
                $contact->setFromApp('applipaps');

                $entityManager->persist($contact);
                $entityManager->flush();

                $this->addFlash('success', 'Votre compte est crée. Toutefois, un lien de confirmation va être envoyé à l\'adresse indiquée pour confirmation. <br>L\'inscription sera définitive après validation de votre part.');
                // do anything else you need here, like send an email
                return $this->redirectToRoute('paps_gestapp_app_dashboard');
            }else{

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
