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
     * @ORM\Column(name="name", type="string", length=32)
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function jsonSerialize()
    {
        return [
            'id'   => $this->id,
            'name' => $this->name
        ];
    }
}
