<?php

namespace App\Controller;

use App\Entity\User;
use App\Routing\Attribute\Route;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class RegisterController extends AbstractController
{
    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[Route(path: "/register", httpMethod: "GET", name: "register")]
    public function register(){
        echo $this->twig->render('Auth/Register.html.twig');
    }

    /**
     * @throws OptimisticLockException
     * @throws SyntaxError
     * @throws ORMException
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[Route(path: "/register/create", httpMethod: "POST", name: "create")]
    public function create(EntityManager $em){
        $password = $this->_cryptPassword($_POST['password']);
        $user = (new User())
            ->setUserName($_POST['UserName'])
            ->setFirstName($_POST['FirstName'])
            ->setName($_POST['Name'])
            ->setEmail($_POST['email'])
            ->setBirthDate(new DateTime())
            ->setPassword($password);
        $em->persist($user);
        $em->flush();

        echo $this->twig->render('accueil/accueil.html.twig',(array)$user);
    }

    private function _cryptPassword(string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => 10]);
    }
}