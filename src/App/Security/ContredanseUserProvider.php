<?php

declare(strict_types=1);

namespace App\Security;

use App\Security\Exception\QueryErrorException;
use App\Security\Exception\UserNotFoundException;
use Zend\Expressive\Authentication\UserInterface;

class ContredanseUserProvider implements UserProviderInterface
{
    /**
     * @var \PDO
     */
    private $adapter;

    public function __construct(\PDO $adapter)
    {
        $this->adapter = $adapter;
    }

    public function getUserByEmail(string $email): ?UserInterface
    {
        $sql = sprintf(
            "%s\n%s",
            $this->getBaseSql(),
            'where `l`.`Login` = :email'
        );
        $stmt = $this->adapter->prepare(
            $sql,
            [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]
        );
        $stmt->execute([':email' => $email]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($rows === false || count($rows) !== 1) {
            return null;
        }

        return new ContredanseUser(
            $rows[0]['user_id'],
            explode(' ', $rows[0]['role'] ?? ''),
            $rows[0]
        );
    }

    /**
     * Return all users.
     *
     * @throws QueryErrorException
     */
    public function getAllUsers(): array
    {
        $sql  = $this->getBaseSql();
        $stmt = $this->adapter->prepare(
            $sql,
            [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]
        );
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($rows === false) {
            throw new QueryErrorException('Cannot get users');
        }

        return $rows;
    }

    /**
     * Return a specific user.
     *
     * @throws QueryErrorException
     * @throws UserNotFoundException
     */
    public function findUser(string $user_id): array
    {
        $sql = sprintf(
            "%s\n%s",
            $this->getBaseSql(),
            'where `l`.`user_id` = :user_id'
        );
        $stmt = $this->adapter->prepare(
            $sql,
            [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]
        );
        $stmt->execute([':user_id' => $user_id]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($rows === false) {
            throw new QueryErrorException('Cannot find user, query error');
        }
        if (count($rows) !== 1) {
            throw new UserNotFoundException(sprintf(
                'User \'%d\' not found',
                $user_id
            ));
        }

        return $rows[0];
    }

    public function ensureAuthIsWorking(): void
    {
        // Check query working
        $sql = sprintf(
            "%s\n%s",
                $this->getBaseSql(),
                'limit 1'
            );

        try {
            $stmt = $this->adapter->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            throw new QueryErrorException(
                sprintf('Cannot query database, query error: %s', $e->getMessage())
            );
        }

        if ($rows === false || count($rows) !== 1) {
            throw new \RuntimeException('User database empty');
        }

        // Take first user email

        $email  = $rows[0]['email'];
        $userId = $rows[0]['user_id'];

        $user = $this->getUserByEmail($email);

        if ($user === null) {
            throw new \RuntimeException(
                'Cannot locate user'
            );
        }

        if ($user->getDetail('user_id') !== $userId) {
            throw new \RuntimeException(
                sprintf('Users does no match: %s', '')
            );
        }
    }

    private function getBaseSql(): string
    {
        $sql = '
                  select 
                      `s`.`suj_id` as `subject_id`,
                      `l`.`User_id` as `user_id`, 
                      `l`.`Login` as `email`, 
                      `l`.`Pwd` as `password`, 
                      `s`.`suj_type` as `subject_type`,
                      `s`.`suj_title` as `title`,
                      `s`.`suj_name` as `name`,
                      `s`.`suj_firstname` as `firstname`,                      
                      `l`.`role`,
                      `s`.`membre_cd`,
                      `s`.`membre_cf`
                  from `usr_login` as `l` inner join `sujet` as `s` 
                  on `s`.`suj_id` = `l`.`suj_id`  
               ';

        return $sql;
    }
}
