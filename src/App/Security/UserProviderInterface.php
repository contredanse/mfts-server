<?php

declare(strict_types=1);

namespace App\Security;

use App\Security\Exception\QueryErrorException;
use App\Security\Exception\UserNotFoundException;
use Zend\Expressive\Authentication\UserInterface;

interface UserProviderInterface
{
	/**
	 * @throws QueryErrorException
	 */
    public function getAllUsers(): array;

    public function getUserByEmail(string $email): ?UserInterface;

	/**
	 * @throws QueryErrorException
	 * @throws UserNotFoundException
	 */
    public function findUser(string $user_id): array;
}
