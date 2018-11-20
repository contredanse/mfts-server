<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Twig\TwigRenderer;

class HomePageHandler implements RequestHandlerInterface
{
    private $template;

    public function __construct(TwigRenderer $template)
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
