<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Entity
 * @ORM\Table(
 *   name="access_log",
 *   indexes={
 *     @ORM\Index(name="created_at_idx", columns={"created_at"}),
 *     @ORM\Index(name="email_idx", columns={"email"}),
 *   },
 *   options={
 *     "comment" = "Access/auth log",
 *      "charset"="utf8mb4",
 *      "collate"="utf8mb4_unicode_ci"
 *   }
 * )
 */
class AccessLog implements \JsonSerializable
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
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="log_type", type="string", length=16)
     */
    private $log_type;

    /**
     * @ORM\Column(name="email", type="string", length=32, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(name="language", type="string", length=5, nullable=true)
     */
    private $language;

    /**
     * @ORM\Column(name="ip_address", type="string", length=33, nullable=true)
     */
    private $ip_address;

    /**
     * @ORM\Column(name="browser", type="string", length=32, nullable=true)
     */
    private $browser;

    /**
     * @ORM\Column(name="browser_version", type="string", length=10, nullable=true)
     */
    private $browser_version;

    /**
     * @ORM\Column(name="os", type="string", length=10, nullable=true)
     */
    private $os;

    /**
     * @ORM\Column(name="device_type", type="string", length=20, nullable=true)
     */
    private $device_type;

    /**
     * @ORM\Column(name="user_agent", type="string", length=50, nullable=true)
     */
    private $user_agent;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"comment" = "Record creation timestamp"})
     */
    private $created_at;

    public function __construct(
        string $logType,
        ?string $email = null,
        ?string $language = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?string $browser = null,
        ?string $browserVersion = null,
        ?string $os = null,
        ?string $deviceType = null,
        ?DateTime $createdAt = null
    ) {
        Assert::oneOf($logType, self::SUPPORTED_TYPES);
        $this->log_type        = mb_substr($logType, 0, 15);
        $this->email           = mb_substr($email ?? '', 0, 31);
        $this->language        = mb_substr($language ?? '', 0, 5);
        $this->ip_address      = mb_substr($ipAddress ?? '', 0, 32);
        $this->user_agent      = mb_substr($userAgent ?? '', 0, 49);
        $this->browser         = mb_substr($browser ?? '', 0, 31);
        $this->browser_version = mb_substr($browserVersion ?? '', 0, 9);
        $this->device_type     = mb_substr($deviceType ?? '', 0, 9);
        $this->os              = mb_substr($os ?? '', 0, 31);

        $this->created_at = $createdAt ?? new DateTime();
    }

    public function jsonSerialize()
    {
        return [
            'id'              => $this->id,
            'log_type'        => $this->log_type,
            'email'           => $this->email,
            'language'	 	     => $this->language,
            'ip_address'      => $this->ip_address,
            'user_agent'	     => $this->user_agent,
            'browser'	        => $this->browser,
            'browser_version' => $this->browser_version,
            'os'	 	           => $this->os,
            'device_type'     => $this->device_type,
            'created_at'      => $this->created_at,
        ];
    }
}
