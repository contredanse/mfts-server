<?php

declare(strict_types=1);

namespace App\Security;

use Zend\Expressive\Authentication\UserInterface;

class ContredanseUser implements UserInterface
{
    private $identity;
    private $details;
    private $roles;

    /**
     * @param string[] $roles
     * @param string[] $details
     */
    public function __construct(string $identity, array $roles = [], array $details = [])
    {
        $this->identity = $identity;
        $this->details  = $details;
        $this->roles    = $roles;
    }

    public function getIdentity(): string
    {
        return $this->identity;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getDetail(string $name, $default = null)
    {
        return $this->details[$name] ?? $default;
    }

    /**
     * Get all additional user details, if any.
     */
    public function getDetails(): array
    {
        return $this->details;
    }
}
