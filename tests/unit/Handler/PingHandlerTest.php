<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\PingHandler;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class PingHandlerTest extends TestCase
{
    public function testResponse(): void
    {
        $pingHandler = new PingHandler();
        $response    = $pingHandler->handle(
            $this->prophesize(ServerRequestInterface::class)->reveal()
        );

        $json = json_decode((string) $response->getBody());

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertTrue(isset($json->ack));
    }
}
