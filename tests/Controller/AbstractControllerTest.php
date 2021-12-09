<?php

use App\Controller\UserController;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

class AbstractControllerTest 
{
    protected Environment $twig;

    public function TestConstruct(){
       $result=  new UserController($this->twig);
       $this->assertIsObject($result);
    // $this->assertInstanceOf(Environment::class,$result);
    }
}