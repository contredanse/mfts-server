<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="access_log")
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
     * @ORM\Column(name="log_type", type="string", length=32)
     */
    private $log_type;

	/**
	 * @Gedmo\Timestampable(on="create")
	 * @ORM\Column(type="datetime", nullable=true, options={"comment" = "Record creation timestamp"})
	 */
	private $created_at;


    /**
     * @ORM\Column(name="ip_address", type="string", length=32, nullable=true)
     */
    private $ip_address;

    /**
     * @ORM\Column(name="email", type="string", length=32)
     */
    private $email;

    public function __construct(string $type, \DateTime $created_at, string $email, ?string $ip_address)
    {
        $this->log_type   = $type;
        $this->email      = $email;
        $this->ip_address = $ip_address;
		$this->created_at = $created_at;
    }

    public function jsonSerialize()
    {
        return [
            'id'         => $this->id,
            'log_type'   => $this->log_type,
            'email'      => $this->email,
            'ip_address' => $this->ip_address,
			'created_at' => $this->created_at,
        ];
    }
}
