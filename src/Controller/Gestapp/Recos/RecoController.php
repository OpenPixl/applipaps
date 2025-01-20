<?php

namespace App\Controller\Gestapp\Recos;

use App\Entity\Admin\Comm\Contact;
use App\Entity\Gestapp\Recommandations\Reco;
use App\Form\Gestapp\Recos\RecoType;
use App\Repository\Gestapp\Recommandations\RecoRepository;
use App\Repository\Gestapp\Recommandations\StatutRecoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;

class RecoController extends AbstractController
{
    #[Route('/app/recos/', name: 'paps_gestapp_recos_index')]
    public function index(RecoRepository $recoRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_PRESCRIBER');
        $user = $this->getUser();

        $recos = $recoRepository->findBy(['refPrescripteur' => $user->getId()]);
        $gains = 0;

        foreach ($recos as $reco){
            $gain = $reco->getCommission();
            $gains = $gains + $gain;
        }

        return $this->render('gestapp/recommandations/reco/index.html.twig', [
            'recos' => $recos,
            'gains' => $gains,
        ]);
    }

    #[Route('/new', name: 'paps_gestapp_recos_new', methods: ['GET', 'POST'])]
    public function newOnPublic(
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        StatutRecoRepository $statutRecoRepository
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_PRESCRIBER');
        $user = $this->getUser();
        $startReco = $statutRecoRepository->findOneBy(['id' => 1 ]);

        $reco = new Reco();
        $reco->setRefPrescripteur($user);
        $reco->setStatutReco($startReco);
        $reco->setRefEmployed($user->getReferent());
        $reco->setAnnounceFirstName($user->getFirstName());
        $reco->setAnnounceLastName($user->getLastName());
        $reco->setAnnounceEmail($user->getEmail());
        $reco->setAnnouncePhone($user->getGsm());
        $form = $this->createForm(RecoType::class, $reco, [
            'action' => $this->generateUrl('paps_gestapp_recos_new') ,
            'method' => 'POST',
            'attr' => [
                'id' => 'formReco'
            ]
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $reco->setOpenRecoAt(new \DateTime('now'));
            $reco->setAuthCustomer(0);
            $reco->setAuthRGPD(0);

            $entityManager->persist($reco);

            $email = (new TemplatedEmail())
                ->from($user->getEmail())
                ->to($user->getReferent()->getEmail())
                //->cc('cc@example.com')
                //->bcc('bcc@example.com')
                //->replyTo('fabien@example.com')
                //->priority(Email::PRIORITY_HIGH)
                ->subject('[APPLIPAPS] : Recommandation')
                ->htmlTemplate('composants/mails/messageNewReco.html.twig')
                ->context([
                    'reco' => $reco,
                    'user' => $user
                ]);
            try {
                $mailer->send($email);
                $contact = new Contact;
                $contact->setContent('Une nouvelle recommandation vous attend dans votre espace recommandation.');
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

            } catch (TransportExceptionInterface $e) {
                // some error prevented the email sending; display an
                // error message or try to resend the message
                dd($e);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Un email va être envoyé au mandataire pour lui signaler votre recommandation.');

            return $this->redirectToRoute('paps_gestapp_recos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gestapp/recommandations/reco/new.html.twig', [
            'reco' => $reco,
            'form' => $form,
        ]);

    }

    #[Route('/{id}/edit/', name: 'paps_gestapp_recos_edit', methods: ['GET', 'POST'])]
    public function editOnPublic(Request $request, Reco $reco, EntityManagerInterface $entityManager, StatutRecoRepository $statutRecoRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_PRESCRIBER');
        $user = $this->getUser();
        $startReco = $statutRecoRepository->findOneBy(['id' => 1 ]);

        $form = $this->createForm(RecoType::class, $reco, [
            'action' => $this->generateUrl('paps_gestapp_recos_edit', [
                'id' => $reco->getId()
            ]) ,
            'method' => 'POST',
            'attr' => [
                'id' => 'formReco'
            ]
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($reco);
            $entityManager->flush();

            $this->addFlash('success', 'La mise à jour de la recommandation effectuée.');

            return $this->redirectToRoute('paps_gestapp_recos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gestapp/recommandations/reco/edit.html.twig', [
            'reco' => $reco,
            'form' => $form,
        ]);
    }



}
