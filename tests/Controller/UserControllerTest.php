<?php
namespace App\Tests\controller;

use App\Controller\UserController;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

class UserControllerTest {

    private UserController $UserController;
    public Environment $twig;

  public function setUp(): void
  {
    $this->UserController = new UserController($this->twig);
  }

  // public function testList(){
  //     $this->UserController->list();
  //     self::assertTrue(true);
  // }


}