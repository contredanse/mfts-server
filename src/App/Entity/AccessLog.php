<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

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
     *
     * @var string
     */
    private $log_type;

    /**
     * @ORM\Column(name="email", type="string", length=32, nullable=true)
     *
     * @var string
     */
    private $email;

    /**
     * @ORM\Column(name="language", type="string", length=5, nullable=true)
     *
     * @var string
     */
    private $language;

    /**
     * @ORM\Column(name="ip_address", type="string", length=32, nullable=true)
     *
     * @var string
     */
    private $ip_address;

    /**
     * @ORM\Column(name="browser", type="string", length=32, nullable=true)
     *
     * @var string
     */
    private $browser;

    /**
     * @ORM\Column(name="browser_version", type="string", length=10, nullable=true)
     *
     * @var string
     */
    private $browser_version;

    /**
     * @ORM\Column(name="os", type="string", length=10, nullable=true)
     *
     * @var string
     */
    private $os;

    /**
     * @ORM\Column(name="device_type", type="string", length=20, nullable=true)
     *
     * @var string
     */
    private $device_type;

    /**
     * @ORM\Column(name="user_agent", type="string", length=50, nullable=true)
     *
     * @var string
     */
    private $user_agent;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"comment" = "Record creation timestamp"})
     *
     * @var \DateTime
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
        $this->log_type        = mb_substr($logType, 0, 16);
        $this->email           = mb_substr($email ?? '', 0, 32);
        $this->language        = mb_substr($language ?? '', 0, 5);
        $this->ip_address      = mb_substr($ipAddress ?? '', 0, 32);
        $this->user_agent      = mb_substr($userAgent ?? '', 0, 50);
        $this->browser         = mb_substr($browser ?? '', 0, 32);
        $this->browser_version = mb_substr($browserVersion ?? '', 0, 10);
        $this->device_type     = mb_substr($deviceType ?? '', 0, 10);
        $this->os              = mb_substr($os ?? '', 0, 32);

        $this->created_at = $createdAt ?? new DateTime();
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
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
