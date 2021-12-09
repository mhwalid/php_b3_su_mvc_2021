<?php
namespace App\Routing;

use App\DependencyInjection\Container;
use PHPUnit\Framework\TestCase;
use App\Routing\Router;
use ReflectionClass;

class RouteTest extends TestCase
{
    private Router $route;
    public function setUp(): void
  {
    $container = new Container();
    
    $this->route = new Router($container);
  }

    public function testGetRoute(){
      $getroute=  $this->route->getRoute('/contact','GET');
        $this->assertNull($getroute);
    }
    public function TestNotGetRoute(){
        $getroute=  $this->route->getRoute('/boutique','GET');
          $this->assertNull($getroute);
      }

    public function TestExecute(){
      $execute=  $this->route->execute('/contact','GET');
        $this->assertIsArray($execute);
    }

    public function testAddRoute(){
        $routeAdded= $this->route->addRoute("home","/home",'GET',"indexController","home");
        $this->assertIsObject($routeAdded);
    }

    public function TestRegisterRoute() : void {
        $params=$this->route->registerRoute("userController");
        self::assertTrue(true);

    }
    public function TestRegisterRoutes() : void {
        $params=$this->route->registerRoutes();
        self::assertTrue(true);

    }

    public function testGetMethodParams(){
        $reflector = new ReflectionClass( 'App\Routing\Router');
		$method = $reflector->getMethod( 'getMethodParams' );
        $method->setAccessible(true);
        $result= $method->invokeArgs($this->route, array('App\Controller\indexController','index'));
        $this->assertIsArray(  $result ); // whatever your assertion is

    }

}