<?php

namespace App\Controller;

use App\Config\TwigEnvironment;
use App\Entity\User;
use App\Routing\Attribute\Route;
use DateTime;
use Doctrine\ORM\EntityManager;
use Service\MailService;

class IndexController extends AbstractController
{
    #[Route(path: "/", name: "accueil", httpMethod: "GET")]
    public function accueil()
    {
        echo $this->twig->render('accueil/accueil.html.twig');
    }

  #[Route(path: "/createUser")]
  public function index(EntityManager $em)
  {
    $user = new User();

    $user->setName("Bob")
      ->setFirstName("John")
      ->setUsername("Bobby")
      ->setPassword("randompass")
      ->setEmail("bob@bob.com")
      ->setBirthDate(new DateTime('1981-02-16'));

    // On demande au gestionnaire d'entités de persister l'objet
    // Attention, à ce moment-là l'objet n'est pas encore enregistré en BDD
    $em->persist($user);
    $em->flush();
  }

  #[Route(path: "/contact", name: "contact", httpMethod: "GET")]
  public function contact()
  {
    echo $this->twig->render('index/contact.html.twig');
  }

    #[Route(path: "/mail/attach", name: "mailAttach", httpMethod: "GET")]
    public function mailAttach(MailService $mail) {
        $mail->sendMailWithAttach(
            'basile.regnault@gmail.com',
            'Basile',
            'basile.regnault@gmail.com',
            'basile',
            'basile1',
            'php-logo.png',
            '',
            ['shirken21gaming@gmail.com' , 'basile.regnault@gmail.com'],
            true,
            'customMail.html.twig'
        );
        echo $this->twig->render('mail/successSendMail.html.twig' , [
            'titre' => 'Envoie Mail PJ',
            'to' => 'basile.regnault@gmail.com',
            'fromName' => 'Basile',
            'subject' => 'basile'
        ]);
    }

    #[Route(path: "/mail", name: "mail", httpMethod: "GET")]
    public function mail(MailService $mail) {
        $mail->sendMail(
            'basile.regnault@gmail.com',
            'Basile',
            'basile.regnault@gmail.com',
            'basile',
            'basile1',
            '',
            ['shirken21gaming@gmail.com' , 'basile.regnault@gmail.com'],
            true,
            'customMail.html.twig'
        );
        echo $this->twig->render('mail/successSendMail.html.twig' , [
            'titre' => 'Envoie Mail',
            'to' => 'basile.regnault@gmail.com',
            'fromName' => 'Basile',
            'subject' => 'basile'
        ]);
    }
}
