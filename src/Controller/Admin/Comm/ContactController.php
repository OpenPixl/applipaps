<?php

namespace App\Controller\Admin\Comm;

use App\Entity\Admin\Comm\Contact;
use App\Form\Admin\Comm\Contact\contactEmployedType;
use App\Repository\Admin\Comm\ContactRepository;
use App\Repository\Admin\Security\EmployedRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

final class ContactController extends AbstractController
{
    #[Route('/admin/comm/contact/index', name: 'app_admin_comm_contact_index', methods: ['GET'])]
    public function index(ContactRepository $contactRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_PRESCRIBER');
        $user = $this->getUser();
        return $this->render('admin/comm/contact/index.html.twig', [
            'contacts' => $contactRepository->findBy(['forEmployed'=>$user]),
        ]);
    }

    #[Route('/admin/comm/contact/newmessforemployed/{idEmployed}', name: 'app_admin_comm_contact_newmessforemployed', methods: ['GET', 'POST'])]
    public function newMessForEmployed(Request $request, EmployedRepository $employedRepository, $idEmployed, EntityManagerInterface $em)
    {
        $user = $this->getUser();
        $employed = $employedRepository->find($idEmployed);
        $contact = new Contact();
        $form = $this->createForm(contactEmployedType::class, $contact, [
            'action' => $this->generateUrl('app_admin_comm_contact_newmessforemployed', [
                'idEmployed' => $employed->getId()
            ]) ,
            'method' => 'POST',
            'attr' => [
                'id' => 'formReco'
            ]
        ]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $contact->setForEmployed($employed);
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

            $em->persist($contact);
            $em->flush();
            
            // return
            return $this->json([
                'code' => 200,
                'message' => 'Le message vient d\'être envoyé à votre mandataire.' ,
            ], 200);
        }

        // view
        $view = $this->render('admin/comm/contact/include/_formForEmployed.html.twig', [
            'contact' => $contact,
            'form' => $form
        ]);

        // return
        return $this->json([
            'code' => 200,
            'formView' => $view->getContent()
        ], 200);
    }
}