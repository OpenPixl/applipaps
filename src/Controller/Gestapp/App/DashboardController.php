<?php

namespace App\Controller\Gestapp\App;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'paps_gestapp_app_dashboard')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_PRESCRIBER');
        $user = $this->getUser();

        return $this->redirectToRoute('paps_gestapp_recos_index');
    }
}
