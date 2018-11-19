<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\HomePageHandlerFactory;
use App\Handler\HomePageHandler;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Expressive\Twig\TwigRenderer;

class HomePageHandlerFactoryTest extends TestCase
{
    /** @var \Prophecy\Prophecy\ObjectProphecy<ContainerInterface>  */
    protected $container;

    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }



    public function testFactory()
    {
        //$this->container->has(TwigRenderer::class)->willReturn(true);
        $this->container
            ->get(TwigRenderer::class)
            ->willReturn($this->prophesize(TwigRenderer::class));

        $factory = new HomePageHandlerFactory();

        $homePage = $factory($this->container->reveal());

        $this->assertInstanceOf(HomePageHandler::class, $homePage);
    }
}
