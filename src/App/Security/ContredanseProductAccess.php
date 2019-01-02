<?php

declare(strict_types=1);

namespace App\Security;

use App\Security\Exception\MissingProductConfigException;
use App\Security\Exception\NoProductAccessException;
use App\Security\Exception\ProductAccessExpiredException;
use App\Security\Exception\ProductPaymentIssueException;
use App\Security\Exception\QueryErrorException;
use App\Security\Exception\UnsupportedExpiryFormatException;
use App\Security\Exception\UnsupportedProductException;
use Cake\Chronos\Chronos;
use Zend\Expressive\Authentication\UserInterface;

class ContredanseProductAccess
{
    /**
     * VALID PAY_STATUS CODE AT CONTREDANSE.
     */
    public const VALID_PAY_STATUS = 9;

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
     * Ensure that a product (paxton) is available to the user.
     *
     * @param string $productName see constants self::PAXTON_PRODUCT
     *
     * Those exceptions can be considered as system/config errors
     *
     * @throws QueryErrorException
     * @throws MissingProductConfigException
     * @throws UnsupportedProductException
     * @throws UnsupportedExpiryFormatException
     *
     * Those exceptions implements ProductAccessExceptionInterface
     * and can be used to determine the exact cause of failure
     * @throws NoProductAccessException
     * @throws ProductPaymentIssueException
     * @throws ProductAccessExpiredException
     */
    public function ensureAccess(string $productName, UserInterface $user): void
    {

		if (in_array('admin', (array) $user->getRoles(), true)) {
			return;
		}

		$email = $user->getDetail('email');

        $orders = $this->getProductOrders($productName, $email);

        if (count($orders) === 0) {
            // Cool, he never bought anything
            throw new NoProductAccessException(sprintf(
                sprintf('No access, product "%s" have not yet been ordered', $productName)
            ));
        }

        // Pick the most recent order

        $order = $orders[0];

        // Is there a payment issue ?

        if ((int) $order['pay_status'] !== self::VALID_PAY_STATUS) {
            throw new ProductPaymentIssueException(sprintf(
                sprintf(
                    'Look we have a payment issue, pay_status code in order detail %s is %s',
                    $order['detail_id'],
                    $order['pay_status']
                )
            ));
        }

        // Check expiration if any given
        if (trim($order['expires_at'] ?? '') !== '') {
            try {
                $expiresAt = Chronos::createFromFormat('Y-m-d H:i:s', $order['expires_at']);
            } catch (\Throwable $e) {
                throw new UnsupportedExpiryFormatException(
                    sprintf(
                        'Unexpected product expiry data (%s) for order detail %s. (%s)',
                        $order['expires_at'],
                        $order['detail_id'],
                        $e->getMessage()
                    )
                );
            }

            if ($expiresAt->isPast()) {
                throw new ProductAccessExpiredException(sprintf(
                    sprintf(
                        'Product access have expired on %s (see order detail_id %s)',
                        $expiresAt->format('Y-m-d'),
                        $order['detail_id']
                    )
                ));
            }
        }
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
				d.support_id,
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
