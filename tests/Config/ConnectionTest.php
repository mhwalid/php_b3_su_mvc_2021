<?php

use App\Config\Connection;
use App\Tests\Config;
use PHPUnit\Framework\TestCase;

class ConnnectionTest 
{

    public function Testinit(){

        $conx= new Connection();
        $result= $conx->init();
        $this->assertInstanceOf(EntityManager::class,$result);
    }

}