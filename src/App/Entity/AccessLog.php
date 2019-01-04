<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\Column(name="type", type="string", length=32)
     */
    private $type;

    /**
     * @ORM\Column(name="ip_address", type="string", length=32)
     */
    private $ip_address;

    /**
     * @ORM\Column(name="email", type="string", length=32)
     */
    private $email;

    public function __construct(string $type, string $email, ?string $ip_address)
    {
        $this->type       = $type;
        $this->email      = $email;
        $this->ip_address = $ip_address;
    }

    public function jsonSerialize()
    {
        return [
            'id'         => $this->id,
            'type'       => $this->type,
            'email'      => $this->email,
            'ip_address' => $this->ip_address,
        ];
    }
}
