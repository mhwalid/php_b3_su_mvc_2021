<?php

namespace App\Auth;

/**
 * Interface UserInterface
 * @package DevCoder\Authentication
 */
interface UserInterface
{
    public function getUsername() :?string;

    public function getPassword() :?string;

}
