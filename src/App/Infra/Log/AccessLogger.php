<?php

declare(strict_types=1);

namespace App\Infra\Log;

use App\Entity\AccessLog;
use DeviceDetector\DeviceDetector;
use Doctrine\ORM\EntityManager;
use Webmozart\Assert\Assert;

class AccessLogger
{
    public const TYPE_LOGIN_SUCCESS               = 'success';
    public const TYPE_LOGIN_FAILURE               = 'fail';
    public const TYPE_LOGIN_FAILURE_EXPIRY        = 'fail.expiry';
    public const TYPE_LOGIN_FAILURE_PAYMENT_ISSUE = 'fail.payment';
    public const TYPE_LOGIN_FAILURE_CREDENTIALS   = 'fail.credentials';
    public const TYPE_LOGIN_FAILURE_NO_ACCESS     = 'fail.no_access';

    public const SUPPORTED_TYPES = [
        self::TYPE_LOGIN_SUCCESS,
        self::TYPE_LOGIN_FAILURE,
        self::TYPE_LOGIN_FAILURE_CREDENTIALS,
        self::TYPE_LOGIN_FAILURE_EXPIRY,
        self::TYPE_LOGIN_FAILURE_PAYMENT_ISSUE,
        self::TYPE_LOGIN_FAILURE_NO_ACCESS,
    ];

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Device detector caches.
     *
     * @var array<string, DeviceDetector>
     */
    private $ddCache;

    /**
     * Whether user agent detection is enabled.
     *
     * @var bool
     */
    private $uaDetectionEnabled;

    /**
     * @param bool $uaDetectionEnabled Whether user agent detection is enabled
     */
    public function __construct(EntityManager $entityManager, $uaDetectionEnabled = true)
    {
        $this->em                 = $entityManager;
        $this->uaDetectionEnabled = $uaDetectionEnabled;
        $this->ddCache            = [];
    }

    /**
     * @throws \Throwable
     */
    public function log(string $type, string $email, ?string $language, ?string $ip, ?string $userAgent): void
    {
        Assert::oneOf($type, self::SUPPORTED_TYPES);

        $browserInfo = $this->getBrowserInfo($this->uaDetectionEnabled ? $userAgent : null);

        ['os' => $os, 'browser' => $browser, 'version' => $browserVersion, 'type' => $deviceType] = $browserInfo;

        $accessLog = new AccessLog($type, $email, $language, $ip, $userAgent, $browser, $browserVersion, $os, $deviceType);

        try {
            $this->em->persist($accessLog);
            $this->em->flush();
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getBrowserInfo(?string $userAgent): array
    {
        if ($userAgent === null) {
            return [
                'os'              => null,
                'browser'         => null,
                'version' 		      => null,
                'type'			         => null,
            ];
        }

        $dd = $this->getDeviceDetector($userAgent);
        $dd->skipBotDetection();
        $dd->parse();

        return [
            'os'      	 => $dd->getOs('short_name'),
            'browser'   => $dd->getClient('name'),
            'version'   => $dd->getClient('version'),
            'type'      => $dd->getDeviceName(),
        ];
    }

    /**
     * Basic in-memory caching, should be fine for AccessLogger in context.
     */
    private function getDeviceDetector(string $userAgent): DeviceDetector
    {
        $agentKey = md5($userAgent);
        if (!isset($this->ddCache[$agentKey])) {
            $this->ddCache[$agentKey] = new DeviceDetector($userAgent);
        }

        return $this->ddCache[$agentKey];
    }
}
