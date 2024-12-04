<?php

namespace App\Controller\Admin\Security;

use App\Entity\Admin\Security\Employed;
use App\Form\Admin\Security\EmployedType;
use App\Repository\Admin\Security\EmployedRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;


#[Route('/admin/security/employed')]
final class EmployedController extends AbstractController
{
    #[Route(name: 'app_admin_security_employed_index', methods: ['GET'])]
    public function index(EmployedRepository $employedRepository): Response
    {
        return $this->render('admin/security/employed/index.html.twig', [
            'employeds' => $employedRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_security_employed_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
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

    #[Route('/{id}', name: 'app_admin_security_employed_show', methods: ['GET'])]
    public function show(Employed $employed): Response
    {
        return $this->render('admin/security/employed/show.html.twig', [
            'employed' => $employed,
        ]);
    }

    #[Route('/{id}/edit', name: 'paps_security_employed_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Employed $employed, EntityManagerInterface $entityManager, SluggerInterface $slugger, ): Response
    {
        $form = $this->createForm(EmployedType::class, $employed);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // ajout de la carte d'identité
            $ci = $form->get('ciFile')->getData();
            $ciFilename = $employed->getCiFileName();
            // Créer un alias

            if($ci) {
                if ($ciFilename) {
                    $pathheader = $this->getParameter('prescriptors_directory') . '/' . $ciFilename;
                    // On vérifie si l'image existe
                    if (file_exists($pathheader)) {
                        unlink($pathheader);
                    }
                }
                $originalFilename = pathinfo($ci->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '.' . $ci->guessExtension();
                try {
                    $ci->move(
                        $this->getParameter('prescriptors_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $employed->setCiFileName($newFilename);
            }

            $avatar = $form->get('avatarFile')->getData();
            $avatarFilename = $employed->getAvatarName();
            if($avatar) {
                if ($avatarFilename) {
                    $pathheader = $this->getParameter('prescriptors_directory') . '/' . $avatarFilename;
                    // On vérifie si l'image existe
                    if (file_exists($pathheader)) {
                        unlink($pathheader);
                    }
                }
                $originalFilename = pathinfo($avatar->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '.' . $avatar->guessExtension();
                try {
                    $avatar->move(
                        $this->getParameter('prescriptors_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $employed->setAvatarName($newFilename);
            }

            $entityManager->flush();

            return $this->redirectToRoute('paps_security_employed_edit', [
                'id' => $employed->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/security/employed/edit.html.twig', [
            'employed' => $employed,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'paps_admin_security_employed_delete', methods: ['POST'])]
    public function delete(Request $request, Employed $employed, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$employed->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($employed);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_security_employed_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/removeci', name: 'paps_admin_security_employed_removeci', methods: ['POST'])]
    public function removeCi(Request $request, Employed $employed, EntityManagerInterface $em): Response
    {
        $ciFilename = $employed->getCiFileName();
        if ($ciFilename) {
            $path = $this->getParameter('prescriptors_directory') . '/' . $ciFilename;
            // On vérifie si l'image existe
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $form = $this->createForm(EmployedType::class, $employed);
        $form->handleRequest($request);

        $employed->setCiFileName(null);
        $em->flush();

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
    #[Route('/{id}/removeavatar', name: 'paps_admin_security_employed_removeavatar', methods: ['POST'])]
    public function removeAvatar(Request $request, Employed $employed, EntityManagerInterface $em): Response
    {
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

    private function maskRib(string $rib): string
    {
        $masked = substr($rib, 0, -4); // Partie à masquer
        $visible = substr($rib, -4);  // Derniers caractères visibles

        // Remplace les caractères à masquer par 'X' tout en conservant les espaces
        $masked = preg_replace('/[A-Za-z0-9]/', 'X', $masked);

        return $masked . $visible;
    }
}
