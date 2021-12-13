<?php
use App\Auth\Core\UserManager;
use App\Entity\User;



$userManager = new UserManager();
if ($userManager->hasUserToken()) {
    // connected

    $token = $userManager->getUserToken();
    $user = $token->getUser();
    var_dump($user);
// object(DevCoder\Authentication\User)[5]
//  private 'userName' => string 'username' (length=8)
//  private 'password' => string '$2y$10$OBobeLhdvdiftuedlv1a6e4.qF6sCG/usq5WEV4E3uB.UiS1egv/m' (length=60)
//  private 'enabled' => boolean true

}else {
    // not connected
}