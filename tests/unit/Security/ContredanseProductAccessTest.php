<?php

declare(strict_types=1);

namespace AppTest\Security;

use App\Security\ContredanseProductAccess;
use App\Security\Exception\NoProductAccessException;
use App\Security\Exception\ProductPaymentIssueException;
use App\Security\Exception\UnsupportedProductException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class ContredanseProductAccessTest extends TestCase
{
    /** @var MockInterface|ContredanseProductAccess */
    private $accessMock;

    /**
     * @var string
     */
    private $productName;

    protected function setUp(): void
    {
        $this->accessMock = Mockery::mock(ContredanseProductAccess::class);
        $this->accessMock->makePartial();

        $this->productName = ContredanseProductAccess::PAXTON_PRODUCT;
    }

    public function testEnsureAccessPaymentIssue(): void
    {
        self::expectException(ProductPaymentIssueException::class);
        $this->accessMock->shouldReceive('getProductOrders')->andReturn([
            0 => ['product_id' => 219, 'pay_status' => null, 'expires_at' => '2015-12-31', 'detail_id' => 123]
        ]);
        $this->accessMock->ensureAccess($this->productName, 'email');
    }

    public function testEnsureAccessNoOrderMadeException(): void
    {
        self::expectException(NoProductAccessException::class);
        self::expectExceptionMessageRegExp('/order/');
        $this->accessMock->shouldReceive('getProductOrders')->andReturn([
            // no orders !
        ]);
        $this->accessMock->ensureAccess($this->productName, 'email');
    }

    public function testWrongProductThrowsException(): void
    {
        self::expectException(UnsupportedProductException::class);
        $this->accessMock->ensureAccess('invalidproduct', '');
    }
}
