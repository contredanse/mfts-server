<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Expressive\Twig\TwigRenderer;

class HomePageHandler implements RequestHandlerInterface
{
	/**
	 * @var TemplateRendererInterface
	 */
    private $template;

    public function __construct(TemplateRendererInterface $template)
    {
        $this->template = $template;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $data = [
            'content' => file_get_contents('./data/content/temp-home.html')
        ];

        return new HtmlResponse($this->template->render('app::static-content', $data));
    }
}
