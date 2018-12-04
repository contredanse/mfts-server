<?php

declare(strict_types=1);

namespace App\Security;

use App\Security\Exception\MissingProductConfigException;
use App\Security\Exception\NoProductAccessException;
use App\Security\Exception\ProductAccessExpiredException;
use App\Security\Exception\ProductPaymentIssueException;
use App\Security\Exception\QueryErrorException;
use App\Security\Exception\UnsupportedProductException;

class ContredanseProductAccess
{
    public const PAXTON_PRODUCT = 'product:paxton';

    public const SUPPORTED_PRODUCTS = [
        self::PAXTON_PRODUCT
    ];

    /**
     * @var \PDO
     */
    private $adapter;

    /**
     * @var array<string,string[]>
     */
    private $productAccess;

    /**
     * @param array<string,string[]> $productAccess
     */
    public function __construct(\PDO $adapter, array $productAccess)
    {
        $this->adapter       = $adapter;
        $this->productAccess = $productAccess;
    }

    /**
     * @param string $productName see constants self::PAXTON_PRODUCT
     *
     * Those exceptions can be considered as system/config errors
     *
     * @throws MissingProductConfigException
     * @throws UnsupportedProductException
     * @throws QueryErrorException
     *
     * Those exceptions emplements ProductAccessExceptionInterface
     * and can be used to determine is the user have access to
     * the product
     * @throws NoProductAccessException
     * @throws ProductPaymentIssueException
     * @throws ProductPaymentIssueException
     */
    public function ensureAccess(string $productName, string $email): void
    {
        $orders = $this->getProductOrders($productName, $email);

        if (count($orders) === 0) {
            // Cool, he never bought anything
            throw new NoProductAccessException(sprintf(
                sprintf('No access, product "%s" is present in orders', $productName)
            ));
        }

        // Pick the most recent order

        $order = $orders[0];

        // Is there a payment issue ?

        // o.pay_status = 9; so all is cool ;)

        if ($order['pay_status'] !== 9) {
            throw new ProductPaymentIssueException(sprintf(
                sprintf(
                    'Look we have a payment issue, pay_status code in order detail %s is %s',
                    $order['detail_id'],
                    $order['pay_status']
                )
            ));
        }

        // Is there a validity issue ?

        $expires_at = $order['expires_at'];

        throw new ProductAccessExpiredException(sprintf(
            sprintf(
                'Product access have expired on %s (see order detail_id %s)',
                $expires_at,
                $order['detail_id']
            )
        ));
    }

    /**
     * Get user orders relative to a certain product.
     *
     * @param string $productName see constants self::PAXTON_PRODUCT
     * @param string $email       identity of the user to check for product access
     *
     * @return array<int, mixed[]>
     *
     * @throws MissingProductConfigException
     * @throws UnsupportedProductException
     * @throws QueryErrorException
     */
    public function getProductOrders(string $productName, string $email): array
    {
        if (!in_array($productName, self::SUPPORTED_PRODUCTS, true)) {
            throw new UnsupportedProductException(sprintf(
                'Product name %s is not supported',
                $productName
            ));
        }

        if (!array_key_exists($productName, $this->productAccess)) {
            throw new MissingProductConfigException(sprintf(
                'Missing configuration: product %s does not have associated ids',
                $productName
            ));
        }

        $productIds = $this->productAccess[$productName];

        $holderValues = [];
        foreach ($productIds as $idx => $productId) {
            $holderValues[":product_id_$idx"] = (int) $productId;
        }
        $inParams = implode(',', array_keys($holderValues));

        $sql = "		
			SELECT 
				s.suj_id AS subject_id,
				l.Login AS email,
				o.order_id,
				o.total_value,
				o.total_pay,
				o.pay_status,
				DATE_FORMAT(FROM_UNIXTIME(o.cre_dt),
						'%Y-%m-%d %H:%i:%s') AS order_created_at,
			    d.detail_id,  
				d.product_id,
				d.quantity,
				DATE_FORMAT(FROM_UNIXTIME(d.cre_dt),
						'%Y-%m-%d %H:%i:%s') AS line_created_at,
				DATE_FORMAT(FROM_UNIXTIME(d.expiry_dt),
						'%Y-%m-%d %H:%i:%s') AS expires_at
			FROM
				shop_order o
					INNER JOIN
				shop_order_detail d ON d.order_id = o.order_id
					INNER JOIN
				sujet s ON s.suj_id = o.suj_id
					INNER JOIN
				usr_login l ON l.suj_id = s.suj_id
			WHERE 
			          l.Login = :email
				  AND d.product_id in (${inParams})	
			ORDER BY d.expiry_dt desc
		";

        $stmt = $this->adapter->prepare($sql);
        $stmt->execute(array_merge([
            ':email' => $email,
        ], $holderValues));
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($rows === false) {
            throw new QueryErrorException('Cannot get users');
        }

        return $rows;
    }
}
