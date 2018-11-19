<?php declare(strict_types=1);

namespace App\Security;

use Zend\Expressive\Authentication\UserInterface;

interface UserProviderInterface
{
    function getAllUsers();
    function getUserByEmail(string $email): ?UserInterface;
}
