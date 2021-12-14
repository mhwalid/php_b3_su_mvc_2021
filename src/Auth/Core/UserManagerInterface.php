<?php

namespace App\Auth\Core;

use App\Auth\Token\UserTokenInterface;
use App\Auth\UserInterface;

interface UserManagerInterface
{
    public function logout(): void;

    public function cryptPassword(string $plainPassword): string;

    public function isPasswordValid(UserInterface $user, string $plainPassword): bool;
}
