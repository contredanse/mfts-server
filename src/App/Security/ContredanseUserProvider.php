<?php declare(strict_types=1);

namespace App\Security;

use Zend\Expressive\Authentication\UserInterface;

class ContredanseUserProvider implements UserProviderInterface
{
    /**
     * @var \PDO
     */
    private $adapter;

    function __construct(\PDO $adapter)
    {
        $this->adapter = $adapter;
    }

    function getUserByEmail(string $email): ?UserInterface
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
        if (!$rows || count($rows) !== 1) {
            return null;
        }

        return new ContredanseUser(
            $rows[0]['user_id'],
            explode(' ', $rows[0]['role'] ?? ''),
            $rows[0]
        );
    }

    /**
     * Return all users
     * @return array|false
     */
    public function getAllUsers()
    {
        $sql = $this->getBaseSql();
        $stmt = $this->adapter->prepare(
            $sql,
            [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]
        );
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
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
