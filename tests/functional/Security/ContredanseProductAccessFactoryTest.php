<?php

declare(strict_types=1);

namespace AppTest\Functional\Security;

use App\Exception\ConfigException;
use App\Security\ContredanseProductAccess;
use App\Security\ContredanseProductAccessFactory;
use App\Service\ContredanseDb;
use AppTest\Util\ContainerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ContredanseProductAccessFactoryTest extends TestCase
{
    /** @var \Prophecy\Prophecy\ObjectProphecy<ContainerInterface> */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container->get(ContredanseDb::class)
            ->willReturn(
                new ContredanseDb(
                    ContainerFactory::getConfig('contredanse')['db']
                )
            );
    }

    public function testShouldWorkWithRegisteredContainer(): void
    {
        $container     = ContainerFactory::getContainer();
        $productAccess = (new ContredanseProductAccessFactory())($container);
        self::assertInstanceOf(ContredanseProductAccess::class, $productAccess);
    }

    public function testShouldWorkWithCustomConfig(): void
    {
        $this->container->get('config')
            ->willReturn(
                [
                    'contredanse' => [
                        'products' => [
                            ContredanseProductAccess::PAXTON_PRODUCT => [1, 2]
                        ]
                    ]
                ]
            );

        $access = (new ContredanseProductAccessFactory())($this->container->reveal());

        self::assertInstanceOf(ContredanseProductAccess::class, $access);
    }

    public function testMustThrowConfigException(): void
    {
        self::expectException(ConfigException::class);

        $this->container->get('config')
            ->willReturn(
                [
                    'contredanse' => [
                        //'products' => [
                        //	ContredanseProductAccess::PAXTON_PRODUCT => [1, 2]
                        //]
                    ]
                ]
            );

        (new ContredanseProductAccessFactory())($this->container->reveal());
    }
}
