<?php

namespace App\Auth\Token;

use App\Auth\UserInterface;

/**
 * Interface UserTokenInterface
 * @package DevCoder\Authentication\Token
 */
interface UserTokenInterface
{
    const DEFAULT_PREFIX_KEY = 'user_security';

    public function getUser(): UserInterface;

    public function serialize(): string;
}
