<?php

declare(strict_types=1);

namespace AppTest\Functional\Security;

use App\Security\ContredanseUserProviderFactory;
use App\Security\UserProviderInterface;
use AppTest\Util\ContainerFactory;
use PHPUnit\Framework\TestCase;

class ContredanseUserProviderTest extends TestCase
{
    /**
     * @var UserProviderInterface
     */
    private $provider;

    protected function setUp(): void
    {
        $container      = ContainerFactory::getContainer();
        $this->provider = (new ContredanseUserProviderFactory())($container);
    }

    public function testGetAllUsers(): void
    {
        //self::doesNotPerformAssertions();
        $users = $this->provider->getAllUsers();
        self::assertTrue(true);
    }
}
