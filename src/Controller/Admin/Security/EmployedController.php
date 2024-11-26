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

    #[Route('/{id}/edit', name: 'app_admin_security_employed_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Employed $employed, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EmployedType::class, $employed);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_security_employed_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/security/employed/edit.html.twig', [
            'employed' => $employed,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_security_employed_delete', methods: ['POST'])]
    public function delete(Request $request, Employed $employed, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$employed->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($employed);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_security_employed_index', [], Response::HTTP_SEE_OTHER);
    }
}
