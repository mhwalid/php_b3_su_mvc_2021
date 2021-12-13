<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{


    private User $User;
    public function setUp(): void
  {
    
    $this->User = new User();
    
  }

  public function TestGetID() {
   
      $result = $this->User->getId();
      $this->assertIsInt($result);
  }
  public function testGetName() {
    $this->User->setName("hap");
      $result = $this->User->getName();
      $this->assertIsString($result);
  }
  public function testSetName() {
    $name="hallouli";
      $result = $this->User->setName($name);
      $this->assertIsObject($result);
  }
  public function testGetFirstName() {
    $this->User->setFirstName("bob");
    $result = $this->User->getFirstName();
    $this->assertIsString($result);
}
public function testSetFirstName() {
    $result = $this->User->setFirstName('walid');
    $this->assertIsObject($result);
}
public function testGetUserName() {
  $this->User->setUserName("bobb");
    $result = $this->User->getUsername();
    $this->assertIsString($result);
}
public function testSetUserName() {
    $result = $this->User->setUsername("bobb");
    $this->assertIsObject($result);
}
public function testGetPassword() {
  $this->User->setPassword("****");
    $result = $this->User->getPassword();
    $this->assertIsString($result);
}

public function testSetPassword() {
    $result = $this->User->setPassword("****");
    $this->assertIsObject($result);
}

public function testGetEmail()
{
  $this->User->setEmail("walid.hallouli@gmail.com");
    $result = $this->User->getEmail();
    $this->assertIsString($result);
}

public function testSetEmail() {
  $result = $this->User->setEmail("****");
  $this->assertIsObject($result);
}
public function testGetBirthDate()
{  $date1 = new \DateTime('2019-01-01 00:00:00');

  
  $this->User->setBirthDate($date1);
    $result = $this->User->getBirthDate();
    $this->assertEquals($result,$date1);
}
public function testSetBirthDate() {
  $date1 = new \DateTime('2019-01-01');
  $result = $this->User->setBirthDate($date1);
  $this->assertIsObject($result);

}

}
