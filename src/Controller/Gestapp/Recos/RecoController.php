<?php

namespace App\Controller\Gestapp\Recos;

use App\Repository\Gestapp\Recommandations\RecoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

        return $this->render('gestapp/recos/reco/index.html.twig', [
            'recos' => $recos,
            'gains' => $gains,
        ]);
    }
}
