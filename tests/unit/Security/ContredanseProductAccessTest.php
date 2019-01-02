<?php

declare(strict_types=1);

namespace AppTest\Security;

use App\Security\ContredanseProductAccess;
use App\Security\ContredanseUser;
use App\Security\Exception\NoProductAccessException;
use App\Security\Exception\ProductAccessExpiredException;
use App\Security\Exception\ProductPaymentIssueException;
use App\Security\Exception\UnsupportedExpiryFormatException;
use App\Security\Exception\UnsupportedProductException;
use Cake\Chronos\Chronos;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Zend\Expressive\Authentication\UserInterface;

class ContredanseProductAccessTest extends TestCase
{
    /** @var MockInterface|ContredanseProductAccess */
    private $accessMock;

    /**
     * @var string
     */
    private $productName;

	/**
	 * @var UserInterface
	 */
    private $testUser;

    protected function setUp(): void
    {
        $this->accessMock = Mockery::mock(ContredanseProductAccess::class);
        $this->accessMock->makePartial();

        $this->productName = ContredanseProductAccess::PAXTON_PRODUCT;
        $this->testUser = new class extends ContredanseUser {
        	public function __construct()
			{
				parent::__construct('10', [], ['email' => 'test@example.org']);
			}
		};
    }

    public function testEnsureAccessIsValidWithTomorrowExpiry(): void
    {
        $tomorrow = (new Chronos())->addDay(1);
        $this->accessMock->shouldReceive('getProductOrders')->andReturn([
            0 => [
                'pay_status' => ContredanseProductAccess::VALID_PAY_STATUS,
                'expires_at' => $tomorrow->toDateTimeString(),
                'detail_id'  => 123
            ]
        ]);
        $this->accessMock->ensureAccess($this->productName, $this->testUser);
        self::assertTrue(true);
    }

    public function testEnsureAccessIsValidWithFutureExpiry(): void
    {
        $future = (new Chronos())->addMonths(11);
        $this->accessMock->shouldReceive('getProductOrders')->andReturn([
            0 => [
                'pay_status' => ContredanseProductAccess::VALID_PAY_STATUS,
                'expires_at' => $future->toDateTimeString(),
                'detail_id'  => 123
            ]
        ]);
        $this->accessMock->ensureAccess($this->productName, $this->testUser);
        self::assertTrue(true);
    }

    public function testEnsureAccessFailsWithYesterdayExpiry(): void
    {
        self::expectException(ProductAccessExpiredException::class);
        $yesterday = (new Chronos())->subDay(1);

        $this->accessMock->shouldReceive('getProductOrders')->andReturn([
            0 => [
                'pay_status' => ContredanseProductAccess::VALID_PAY_STATUS,
                'expires_at' => $yesterday->toDateTimeString(),
                'detail_id'  => 123
            ]
        ]);
        $this->accessMock->ensureAccess($this->productName, $this->testUser);
    }

    public function testEnsureAccessPaymentIssue(): void
    {
        self::expectException(ProductPaymentIssueException::class);
        $this->accessMock->shouldReceive('getProductOrders')->andReturn([
            0 => ['pay_status' => null, 'expires_at' => '', 'detail_id' => 123]
        ]);
        $this->accessMock->ensureAccess($this->productName, $this->testUser);
    }

    public function testEnsureAccessInvalidExpiry(): void
    {
        self::expectException(UnsupportedExpiryFormatException::class);
        $this->accessMock->shouldReceive('getProductOrders')->andReturn([
            0 => [
                'pay_status' => ContredanseProductAccess::VALID_PAY_STATUS,
                'expires_at' => '3455',
                'detail_id'  => 123
            ]
        ]);
        $this->accessMock->ensureAccess($this->productName, $this->testUser);
    }

    public function testEnsureAccessNoOrderMadeException(): void
    {
        self::expectException(NoProductAccessException::class);
        self::expectExceptionMessageRegExp('/order/');
        $this->accessMock->shouldReceive('getProductOrders')->andReturn([
            // no orders !
        ]);
        $this->accessMock->ensureAccess($this->productName, $this->testUser);
    }

    public function testWrongProductThrowsException(): void
    {
        self::expectException(UnsupportedProductException::class);
        $this->accessMock->ensureAccess('invalidproduct', $this->testUser);
    }
}
