<?php
namespace App\Routing;

use App\Controller\IndexController;
use App\DependencyInjection\Container;
use App\Routing\Attribute\Route;
use PHPUnit\Framework\TestCase;
use App\Routing\Router;
use ReflectionClass;

class RouteTest extends TestCase
{
    private Router $router;
    private Route $route;
    public function setUp(): void
  {
    $container = new Container();
    
    $this->router = new Router($container);
    
    $this->route= new Route("/contact","GET","contact");
    $this->routePram= new Route("/users","GET","home");

  }

    public function testGetRoute(){
      $this->router->addRoute("contact","/contact","GET","IndexController","contact");
      $getroute=  $this->router->getRoute('/contact','GET');
        $this->assertIsArray($getroute);
    }

    public function testNotGetRoute(){
        $getroute=  $this->router->getRoute('/boutique','GET');
          $this->assertNull($getroute);
      }

    public function testExecute()
    {
    $this->router->addRoute("contact","/contact","GET","IndexController","contact");
    $this->router->getRoute("/contact","GET");

      $execute= $this->router->execute("/contact","GET");
      // $this->assertIsArray($execute);
    }

    public function testAddRoute(){
        $routeAdded= $this->router->addRoute("home","/home",'GET',"indexController","home");
        $this->assertIsObject($routeAdded);
    }

    public function testRegisterRoute() : void {
        $params=$this->router->registerRoute("userController");
        self::assertTrue(true);

    }
    public function testRegisterRoutes() : void {
        $params=$this->router->registerRoutes();
        self::assertTrue(true);

    }

    public function testGetMethodParams(){

      
    $reflector = new ReflectionClass( 'App\Routing\Router');
		$method = $reflector->getMethod( 'getMethodParams' );
        $method->setAccessible(true);
        $result= $method->invokeArgs($this->router, array('App\Controller\UserController','list'));
        $this->assertIsArray(  $result ); // whatever your assertion is

    }
    public function testRouteNotFoundException(){

    $exp= new RouteNotFoundException();
    $this->assertIsString( $exp );
    }

}