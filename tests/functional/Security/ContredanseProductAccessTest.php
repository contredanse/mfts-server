<?php

declare(strict_types=1);

namespace AppTest\Functional\Security;

use App\Infra\Db\ContredanseDb;
use App\Security\ContredanseProductAccess;
use App\Security\ContredanseProductAccessFactory;
use AppTest\Util\ContainerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ContredanseProductAccessTest extends TestCase
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
    }

    public function testNoOrders(): void
    {
        $access = (new ContredanseProductAccessFactory())->__invoke($this->container->reveal());

        $orders = $access->getProductOrders(
            ContredanseProductAccess::PAXTON_PRODUCT,
            'NotAnEmail@notadomain.org'
        );

        self::assertCount(0, $orders);
    }
}
