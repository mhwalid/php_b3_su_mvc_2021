<?php

namespace App\Auth\Core;

use App\Auth\Token\UserToken;
use App\Auth\Token\UserTokenInterface;
use App\Auth\UserInterface;
use App\Entity\User;

/**
 * Class UserManager
 * @package DevCoder\Authentication\Core
 */
class UserManager implements UserManagerInterface
{

    use PasswordTrait;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    
    public function logout(): void
    {
        session_destroy();
        unset($_SESSION['user']);
    }

    public function login(User $user){
        $_SESSION["username"] = $user->getUsername();
    }
}

