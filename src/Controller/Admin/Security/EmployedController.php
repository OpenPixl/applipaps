<?php

namespace App\Controller\Admin\Security;

use App\Entity\Admin\Security\Employed;
use App\Form\Admin\Security\EmployedType;
use App\Repository\Admin\Security\EmployedRepository;
use App\Service\EncryptionService;
use App\Service\pathService;
use App\Service\QrcodeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class EmployedController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $httpClient
    ){}

    #[Route('/admin/security/employed/index', name: 'app_admin_security_employed_index', methods: ['GET'])]
    public function index(EmployedRepository $employedRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('admin/security/employed/index.html.twig', [
            'employeds' => $employedRepository->findAll(),
        ]);
    }

    #[Route('/admin/security/employed/new', name: 'app_admin_security_employed_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $employed = new Employed();
        $form = $this->createForm(EmployedType::class, $employed);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($employed);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_security_employed_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/security/employed/new.html.twig', [
            'employed' => $employed,
            'form' => $form,
        ]);
    }

    #[Route('/admin/security/employed/{id}', name: 'app_admin_security_employed_show', methods: ['GET'])]
    public function show(Employed $employed): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('admin/security/employed/show.html.twig', [
            'employed' => $employed,
        ]);
    }

    #[Route('/admin/security/employed/{id}/qr', name: 'app_admin_security_employed_showqr', methods: ['GET'])]
    public function showqr(Employed $employed, QrcodeService $qrcodeService, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYED');

        if($employed->getQrcodePwa() == null){
            $url = $this->generateUrl('paps_security_register_byqr', ['idEmployed' => $employed->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

            $name = $qrcodeService->qrcode_share_register($url, $employed);
            $employed->setQrcodePwa($name);
            $em->flush();
        }

        return $this->render('admin/security/employed/show_qr.html.twig', [
            'employed' => $employed,
        ]);
    }

    #[Route('/app/prescriptor/{id}/edit', name: 'paps_security_employed_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Employed $employed, EntityManagerInterface $entityManager, SluggerInterface $slugger, EncryptionService $encryptionService, pathService $pathService): Response
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('ROLE_PRESCRIBER');
        $form = $this->createForm(EmployedType::class, $employed);
        $form->handleRequest($request);

        $scheme = $pathService->getScheme();
        $port = $pathService->getPort();
        $host = $pathService->getHost();

        if ($form->isSubmitted() && $form->isValid()) {
            // ajout de la carte d'identité
            $ci = $form->get('ciFile')->getData();
            $ciFilename = $employed->getCiFileName();
            if($ci) {
                if ($ciFilename) {
                    $pathheader = $this->getParameter('prescriptors_directory').'/'. $employed->getSlug(). '/' . $ciFilename;
                    // On vérifie si l'image existe
                    if (file_exists($pathheader)) {
                        unlink($pathheader);
                    }
                }
                $newFilename = 'ci-'. $user->getSlug() . '.' . $ci->guessExtension();
                try {
                    $ci->move(
                        $this->getParameter('prescriptors_directory').'/'. $employed->getSlug(),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $employed->setCiFileName($newFilename);

                $token = $encryptionService->getToken($user);
                if(!$port){
                    $url = $scheme.'://papsimmo.openpixl.fr/opadmin/employed/ciTransfertApp/'.$newFilename;
                }else{
                    $url = $scheme.'://papsimmo.openpixl.fr:'.$port.'/opadmin/employed/ciTransfertApp/'.$newFilename;
                }

                try {
                    $response = $this->httpClient->request('GET', $url, [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $token,
                            'Accept' => 'application/json',
                        ],
                    ]);

                    $data = $response->toArray();
                } catch (\Exception $e) {
                    return $this->json(['error' => 'Erreur : ' . $e->getMessage()], 500);
                }

            }

            $avatar = $form->get('avatarFile')->getData();
            $avatarFilename = $employed->getAvatarName();
            if($avatar) {
                if ($avatarFilename) {
                    $pathheader = $this->getParameter('prescriptors_directory') .'/'. $employed->getSlug().  '/' . $avatarFilename;
                    // On vérifie si l'image existe
                    if (file_exists($pathheader)) {
                        unlink($pathheader);
                    }
                }
                $originalFilename = pathinfo($avatar->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = 'avatar-'.$user->getSlug().'.'.$avatar->guessExtension();
                try {
                    $avatar->move(
                        $this->getParameter('prescriptors_directory').'/'. $employed->getSlug(),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $employed->setAvatarName($newFilename);

                $user = $this->getUser();
                $token = $encryptionService->getToken($user);

                if(!$port){
                    $url = $scheme.'://papsimmo.openpixl.fr/opadmin/employed/avatarTransfertApp/'.$newFilename;
                }else{
                    $url = $scheme.'://papsimmo.openpixl.fr:'.$port.'/opadmin/employed/avatarTransfertApp/'.$newFilename;
                }

                try {
                    $response = $this->httpClient->request('GET', $url, [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $token,
                            'Accept' => 'application/json',
                        ],
                    ]);

                    $data = $response->toArray();
                } catch (\Exception $e) {
                    return $this->json(['error' => 'Erreur : ' . $e->getMessage()], 500);
                }
            }

            $iban = $form->get('iban')->getData();
            if($iban !== null) {
                $ibanEncrypted = $encryptionService->encrypt($iban);
                $employed->setIban($ibanEncrypted);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été correctement mis à jour');

            return $this->redirectToRoute('paps_security_employed_edit', [
                'id' => $employed->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        if($employed->getIban()) {
            $iban = $encryptionService->decrypt($employed->getIban());
            $iban = $this->maskRib($iban);
            return $this->render('admin/security/employed/edit.html.twig', [
                'employed' => $employed,
                'form' => $form,
                'iban' => $iban,
            ]);
        }else{
            return $this->render('admin/security/employed/edit.html.twig', [
                'employed' => $employed,
                'form' => $form,
            ]);
        }
    }

    #[Route('/admin/security/employed/{id}', name: 'paps_security_employed_delete', methods: ['POST'])]
    public function delete(Request $request, Employed $employed, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if ($this->isCsrfTokenValid('delete'.$employed->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($employed);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_security_employed_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/app/prescriptor/{id}/removeci', name: 'paps_admin_security_employed_removeci', methods: ['POST'])]
    public function removeCi(Request $request, Employed $employed,EncryptionService $encryptionService, EntityManagerInterface $em, pathService $pathService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_PRESCRIBER');
        $ciFilename = $employed->getCiFileName();
        if ($ciFilename) {
            $path = $this->getParameter('prescriptors_directory').'/'.$employed->getSlug().'/'. $ciFilename;
            // On vérifie si l'image existe
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $form = $this->createForm(EmployedType::class, $employed);
        $form->handleRequest($request);

        $employed->setCiFileName(null);
        $em->flush();

        $token = $encryptionService->getToken($employed);
        $scheme = $pathService->getScheme();
        $port = $pathService->getPort();
        $host = $pathService->getHost();

        $viewForm = $this->createForm(EmployedType::class, $employed);

        return $this->json([
            'type' => 'ci',
            'message' => "La pièce d'identité a été correctement supprimée.",
            'view' => $this->renderView('admin/security/employed/include/_addci.html.twig', [
                'employed' => $employed,
                'form' => $viewForm
            ]),
        ], 200);
    }

    #[Route('/app/prescriptor/{id}/removeavatar', name: 'paps_admin_security_employed_removeavatar', methods: ['POST'])]
    public function removeAvatar(Request $request, Employed $employed, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_PRESCRIBER');
        $AvatarName = $employed->getAvatarName();
        if ($AvatarName) {
            $path = $this->getParameter('prescriptors_directory') . '/' . $AvatarName;
            // On vérifie si l'image existe
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $form = $this->createForm(EmployedType::class, $employed);
        $form->handleRequest($request);

        $employed->setAvatarName(null);
        $em->flush();

        $viewForm = $this->createForm(EmployedType::class, $employed);

        return $this->json([
            'type' => 'avatar',
            'message' => "La photo de profil a été correctement supprimée.",
            'view' => $this->renderView('admin/security/employed/include/_addavatar.html.twig', [
                'employed' => $employed,
                'form' => $viewForm
            ]),
        ], 200);
    }

    #[Route('/app/prescriptor/{id}/removeiban', name: 'paps_admin_security_employed_removeiban', methods: ['POST'])]
    public function removeIban(Request $request, Employed $employed, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_PRESCRIBER');
        $form = $this->createForm(EmployedType::class, $employed);
        $form->handleRequest($request);

        $employed->setIban(null);
        $em->flush();

        $viewForm = $this->createForm(EmployedType::class, $employed);

        return $this->json([
            'type' => 'iban',
            'message' => "IBAN réinitialisé.",
            'view' => $this->renderView('admin/security/employed/include/_iban.html.twig', [
                'employed' => $employed,
                'form' => $viewForm
            ]),
        ], 200);
    }

    private function maskRib(string $rib): string
    {
        $visStart = substr($rib, 0, 5);
        $masked = substr($rib, 5, -4); // Partie à masquer
        $visEnd = substr($rib, -4);  // Derniers caractères visibles

        // Remplace les caractères à masquer par 'X' tout en conservant les espaces
        $masked = preg_replace('/[A-Za-z0-9]/', 'X', $masked);

        return $visStart . $masked . $visEnd;
    }
}
