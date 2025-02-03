<?php

namespace App\Controller\Admin\Comm;

use App\Service\QrcodeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class QrController extends AbstractController
{
    #[Route('/admin/comm/qr', name: 'app_admin_comm_qr')]
    public function index(
        Request $request,
        QrcodeService $qrcodeService,
    ): Response
    {
        return $this->render('admin/comm/qr/index.html.twig', [
            'controller_name' => 'QrController',
        ]);
    }
}
