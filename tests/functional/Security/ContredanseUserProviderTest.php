<?php

declare(strict_types=1);

namespace AppTest\Functional\Security;

use App\Security\ContredanseUserProvider;
use App\Security\ContredanseUserProviderFactory;
use AppTest\Util\ContainerFactory;
use PHPUnit\Framework\TestCase;

class ContredanseUserProviderTest extends TestCase
{
    /**
     * @var ContredanseUserProvider
     */
    private $provider;

    protected function setUp(): void
    {
        $container = ContainerFactory::getContainer();
        $this->provider = (new ContredanseUserProviderFactory())($container);
    }

    public function testGetAllUsers(): void {
        $users = $this->provider->getAllUsers();
        self::assertInternalType('array', $users);
    }
}
