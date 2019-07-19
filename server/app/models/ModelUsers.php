<?php
/**
 * Class ModelUsers
 * works with controller Users.
 * Receives data from the controller,
 * works DB and return final data
 * to ControllerUsers
 */

class ModelUsers extends ModelDB
{
    /**
     * Get User(s) by hash and id from DB
     * @param $param
     * @return array|string
     */
    public function getUsers($param)
    {
        if ($this->checkUser($param) == 'admin' || $this->checkUser($param) == 'user')
        {
            unset($param['hash'], $param['id_user']);
            $sql = 'SELECT u_b.id, r_b.name as role, u_b.id_role, u_b.login, u_b.email, u_b.hash, u_b.username'
                .' FROM users_booker u_b'
                .' LEFT JOIN roles_booker r_b'
                .' ON u_b.id_role=r_b.id';
            if (!empty($param))
            {
                if (is_array($param))
                {
                    $sql .= " WHERE ";
                    foreach ($param as $key => $val)
                    {
                        $sql .= 'u_b.'.$key.'='.$this->pdo->quote($val).' AND ';
                    }
                    $sql = substr($sql, 0, -5);
                }
                $sql .= ' ORDER BY u_b.id';
            }
            else
            {
               $sql .= ' ORDER BY u_b.id';
            }
            $data = $this->executeSELECT($sql);
            return $data;
        }
        else
        {
            return ERR_ACCESS;
        }
    }

    /**
     * Add User (Registered)
     * @param $param
     * @return bool|int|string
     */
    public function addUser($param)
    {
        if ($this->checkUser($param) == 'admin')
        {
            $validate = $this->validator->isValidateRegistration($param);
            if ($validate === true)
            {
                $rezparam=$this->putQuotes($param);
                $pass = md5(md5(trim($param['pass'])));
                $pass = $this->pdo->quote($pass);
               
                $sql = 'INSERT INTO users_booker (id_role, login, pass, username, email) 
                VALUES ('.$rezparam['id_role'].', '.$rezparam['login'].', '.$pass.', '.$rezparam['username'].', '.$rezparam['email'].')';
                $result = $this->executeSQL($sql);
                if ($result === false)
                {
                    return ERR_LOGIN;
                }
                return $result;
            }
            return $validate;
        }
        return ERR_ACCESS;
    }

    /**
     * Edit User information
     * @param $param
     * @return bool|int|string
     */
    public function editUser($param)
    {
        if ($this->checkUser($param) == 'admin')
        {
            $validate = $this->validator->isValidateEdit($param);
            if ($validate === true)
            {
                $rezparam=$this->putQuotes($param);
                
                $sql = 'UPDATE users_booker SET username='.$rezparam['username'].', id_role='.$rezparam['role'].', email='.$rezparam['email'];
                if(isset($param['pass']))
                {
                    $pass = md5(md5(trim($param['pass'])));
                    $pass = $this->pdo->quote($pass);
                    $sql .=', pass='.$pass;
                }
                $sql .=' WHERE id='.$rezparam['id'];
                $data = $this->executeSQL($sql);
                return $data;
            }
        }
        return ERR_ACCESS;
    }

    /**
     * Checked pass and login user if true generate hash and sent data
     * @param $param
     * @return array|string
     */
    public function loginUser($param)
    {
        if (!empty($param['login']) && !empty($param['pass']))
        {
            $pass = md5(md5(trim($param['pass'])));
            $rezparam=$this->putQuotes($param);
            
            $id = '';
            $role = '';
            $sql = 'SELECT u_b.id, r_b.name as role, u_b.username, u_b.pass'
                .' FROM users_booker u_b LEFT JOIN roles_booker r_b ON u_b.id_role=r_b.id'
                .' WHERE login='.$rezparam['login'];
            $data = $this->executeSELECT($sql);
            if (is_array($data))
            {
                foreach ($data as $val)
                {
                    if ($pass !== $val['pass'])
                    {
                        return ERR_AUTH;
                    }
                    else
                    {
                        $id = $this->pdo->quote($val['id']);
                        $userName = $val['username'];
                        $role = $val['role'];
                    }
                }
            }
            else
            {
                return ERR_SEARCH;
            }
            $hash = $this->pdo->quote(md5($this->generateHash(10)));
            $sql = 'UPDATE users_booker SET hash='.$hash.' WHERE id='.$id;
            $this->executeSQL($sql);
            $id = trim($id, "'");
            $hash = trim($hash, "'");
            $login = trim($rezparam['login'], "'");
            $arrRes = ['id'=>$id, 'login'=>$login, 'hash'=>$hash, 'username'=>$userName, 'role'=>$role];
            return $arrRes;
        }
        else
        {
            return ERR_FIELDS;
        }
    }

    /**
     * Delete User from DB or Admin and his future events
     * You can not remove the admin if it is left in the table last
     * @param $param
     * @return bool|int|string
     */
    public function deleteUser($param)
    {
        if ($this->checkUser($param) == 'admin')
        {
            //check with what role the user being deleted
            if ($this->getRole($param['id']) == 'user')
            {
                $rezparam=$this->putQuotes($param);
                //Delete future events
                $sql = 'DELETE FROM events_booker WHERE id_user='.$rezparam['id'].' AND time_start > NOW()';
                $this->executeSQL($sql);
                //Delete User
                $sql = 'DELETE FROM users_booker WHERE id='.$rezparam['id'];
                $result = $this->executeSQL($sql);
                return $result;
            }
            else
            {
                //Check if present at least one admin
                $sql = 'SELECT count(id_role) as sum FROM users_booker WHERE id_role=2';
                $data = $this->executeSELECT($sql);
                if ($data[0]['sum'] > 1)
                {
                    //only one - delete!
                    $rezparam=$this->putQuotes($param);
                    $sql = 'DELETE FROM users_booker WHERE id='.$rezparam['id'];
                    $result = $this->executeSQL($sql);
                    return $result;
                }
                //only one - no delete
                return ERR_A_DEL;
            }

        }
        return ERR_ACCESS;
    }

    /**
     * Get user role  by Id
     * @param $id
     * @return bool
     */

    private function getRole($id)
    {
        $id = $this->pdo->quote($id);
        $sql = 'SELECT r_b.name as role FROM users_booker u_b LEFT JOIN roles_booker r_b ON u_b.id_role=r_b.id WHERE u_b.id='.$id;
        $data = $this->executeSELECT($sql);
        if (is_array($data))
        {
            return $data[0]['role'];
        }
        return false;
    }

    /**
     * Generate random hash for user
     * @param int $length
     * @return string
     */

    private function generateHash($length=6)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
        $code = "";
        $clen = strlen($chars) - 1;
        while (strlen($code) < $length)
        {
            $code .= $chars[mt_rand(0,$clen)];
        }
        return $code;
    }
}
