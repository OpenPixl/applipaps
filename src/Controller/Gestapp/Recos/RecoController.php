<?php

namespace App\Controller\Gestapp\Recos;

use App\Entity\Gestapp\Recommandations\Reco;
use App\Form\Gestapp\Recos\RecoType;
use App\Repository\Gestapp\Recommandations\RecoRepository;
use App\Repository\Gestapp\Recommandations\StatutRecoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    public function newOnPublic(Request $request, EntityManagerInterface $entityManager, StatutRecoRepository $statutRecoRepository): Response
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
            $entityManager->flush();

            return $this->redirectToRoute('paps_gestapp_recos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gestapp/recommandations/reco/new.html.twig', [
            'reco' => $reco,
            'form' => $form,
        ]);

    }
}
