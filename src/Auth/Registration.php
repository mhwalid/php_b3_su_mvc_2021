<?php

use App\Auth\Core\UserManager;
use App\Entity\User;

// register
$userManager = new UserManager();

$password = $userManager->cryptPassword($_POST['password']);
$user = (new User())
    ->setUserName($_POST['username'])
    ->setPassword($password);

$userManager->createUserToken($user);

// check Token in Session
var_dump($userManager->getUserToken());
// object(App\Entity\Token\UserToken)[4]
//  private 'user' => 
//    object(DevCoder\Authentication\User)[5]
//      private 'userName' => string 'username' (length=8)
//      private 'password' => string '$2y$10$iWdcmebmikUFlgKMqW7/rOmUp1DjFAuWKqdUHBhL08FZ7LL6bwRey' (length=60)
//      private 'enabled' => boolean true