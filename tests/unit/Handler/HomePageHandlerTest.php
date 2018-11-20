<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\HomePageHandler;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Twig\TwigRenderer;

class HomePageHandlerTest extends TestCase
{
    /** @var \Prophecy\Prophecy\ObjectProphecy & TwigRenderer */
    protected $template;

    protected function setUp(): void
    {
        $this->template = $this->prophesize(TwigRenderer::class);
    }

    public function testReturnsHtmlResponseWhenTemplateRendererProvided(): void
    {
        $this->template
            ->render('app::static-content', Argument::type('array'))
            ->willReturn('');

        $homePage = new HomePageHandler($this->template->reveal());

        $response = $homePage->handle(
            $this->prophesize(ServerRequestInterface::class)->reveal()
        );

        self::assertInstanceOf(HtmlResponse::class, $response);
    }
}
