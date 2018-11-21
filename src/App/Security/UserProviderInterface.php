<?php

declare(strict_types=1);

namespace App\Security;

use Zend\Expressive\Authentication\UserInterface;

interface UserProviderInterface
{
    public function getAllUsers();

    public function getUserByEmail(string $email): ?UserInterface;

    public function findUser(string $user_id);
}
