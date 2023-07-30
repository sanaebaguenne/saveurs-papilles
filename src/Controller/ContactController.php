<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;


class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact.index')]
    public function index(HttpFoundationRequest $request, EntityManagerInterface $manager, MailerInterface $mailer): Response
    {

        $contact = new Contact();

        if( $this->getUser()){
            $contact->setFirstname($this->getUser()->getFirstname())
                    ->setLastname($this->getUser()->getLastname())
                    ->setEmail($this->getUser()->getEmail());
        }
        $form = $this->createForm(ContactType::class, $contact);

        $form->handleRequest($request);
        if ( $form->isSubmitted() && $form->isValid()){
            // dd($form->getData());
            $contact = $form->getData();

            $manager->persist($contact);
            $manager->flush();

            // Email

            $email = (new TemplatedEmail())
            ->from($contact->getEmail())
            ->to('no-reply@patisserie.fr')
            ->subject($contact->getSubject())
            ->htmlTemplate('emails/contact.html.twig')

            //pass variables (name => value) to the template

            ->context([
                'contact' => $contact 
                 
            ]);

        $mailer->send($email);

            $this->addFlash(
                'success', 
                'Votre demande a  été envoyé avec succés !'
            );

            return $this->redirectToRoute('contact.index');


        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
