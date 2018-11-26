<?php

declare(strict_types=1);

namespace AppTest\Service;

use App\Exception\ConfigException;
use App\Service\TokenManagerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class TokenManagerFactoryTest extends TestCase
{
    /** @var \Prophecy\Prophecy\ObjectProphecy<ContainerInterface> */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testThrowsExceptionWhenConfigCannotBeLocated(): void
    {
        self::expectException(ConfigException::class);
        self::expectExceptionMessage('[\'token_manager\'] config key is missing.');
        $this->container
            ->get('config')
            ->willReturn([
            ]);
        (new TokenManagerFactory())($this->container->reveal());
    }

    public function testSetConfigExpiry(): void
    {
        $this->container
            ->get('config')
            ->willReturn([
                'token_manager' => [
                    'private_key' => 'qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq',
                    //'allow_insecure_http' => $allow_insecure_http,
                    //'relaxed_hosts' => $relaxed_hosts,
                    'default_expiry' => 2250,
                ]
            ]);
        $tokenManager = (new TokenManagerFactory())->__invoke($this->container->reveal());
        self::assertEquals(2250, $tokenManager->getDefaultExpiry());
    }
}
