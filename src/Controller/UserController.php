<?php

namespace App\Controller;

use App\Entity\User;
use App\Routing\Attribute\Route;
use DateTime;
use Doctrine\ORM\EntityManager;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class UserController extends AbstractController
{
    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    #[Route(path: "/users", name: "users_list")]
  public function list()
  {
    // Création liste users
    // Ne pas utiliser l'entity manager
    // Créer à l'aide d'une boucle un nombre X d'utilisateurs avec des données fakes
    // Transmettre ensuite ces utilisateurs à la vue
    $users = [];

    echo $this->twig->render('user/list.html.twig', ['users' => $users]);
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
}
