<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\HomePageHandler;
use App\Handler\HomePageHandlerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Twig\TwigRenderer;

class HomePageHandlerFactoryTest extends TestCase
{
    /** @var \Prophecy\Prophecy\ObjectProphecy<ContainerInterface> */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactory(): void
    {
        //$this->container->has(TwigRenderer::class)->willReturn(true);
        $this->container
            ->get(TwigRenderer::class)
            ->willReturn($this->prophesize(TwigRenderer::class));

        $factory = new HomePageHandlerFactory();

        $homePage = $factory($this->container->reveal());

        self::assertInstanceOf(HomePageHandler::class, $homePage);
    }
}
