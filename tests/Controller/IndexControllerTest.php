<?php
namespace App\Tests\controller;

use App\Controller\IndexController;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

class IndexControllerTest 
{
    protected EntityManager $em;
    public Environment $twig;
    public IndexController $IndeController;

  public function setUp(): void
  {
    $this->IndeController = new IndexController($this->twig);
  }

    // public function TestIndex(){
    //    $result=  $this->userController->index($this->em);
    //    $this->assertIsObject($result);
    // }
}