<?php

declare(strict_types=1);

namespace App\Service\Auth;

use App\Security\UserProviderInterface;
use App\Service\Auth\Exception\AuthException;
use App\Service\Auth\Exception\BadCredentialException;
use App\Service\Auth\Exception\MissingCredentialException;
use Zend\Expressive\Authentication\UserInterface;

class AuthenticationManager
{
    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    public function __construct(UserProviderInterface $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    /**
     * @throws Exception\AuthExceptionInterface
     */
    public function getAuthenticatedUser(string $email, string $password): UserInterface
    {
        $email    = trim($email);
        $password = trim($password);

        if ($email === '' || $password === '') {
            throw new MissingCredentialException('Missing or empty credentials');
        }

        try {
            $user = $this->userProvider->getUserByEmail($email);
            if ($user !== null) {
                $dbPassword = $user->getDetail('password');
                if ($dbPassword === $password) {
                    return $user;
                }
                throw new BadCredentialException('Login / password does not match');
            }
        } catch (\Throwable $e) {
            throw new AuthException($e->getMessage());
        }
    }
}
