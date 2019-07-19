<?php

/**
 * Class ModelDB 
 * works with database tables MySQL.
 * Create PDO, Validator.
 * 
 * Is the parent class for all models.
 */

class ModelDB
{
    protected $pdo;
    protected $validator;

    /**
     * Create sql connection with PDO 
     * Create object validator
     * ModelDB constructor.
     * @throws Exception
     */

    public function __construct()
    {
        $this->validator = new Validator();
        $this->pdo = new PDO(DSN_MY, USER_NAME, PASS);
        if(!$this->pdo)
        {
            throw new Exception(ERR_DB);
        }
    }

    /**
     * Get insert, update, delete from DB
     * @param $sql
     * @return bool|int
     */

    protected function executeSQL($sql)
    {
        $count = $this->pdo->exec($sql);
        if ($count === false)
        {
            return false;
        }
        return $count;
    }
    
    /**
     * Get select from DB
     * @param $sql
     * @return array|string
     * @throws Exception
     */
    protected function executeSELECT($sql)
    {
        $sth = $this->pdo->prepare($sql);
        $result = $sth->execute();
        if (false === $result)
        {
            throw new Exception(ERR_QUERY);
        }
        $data = $sth->fetchAll(PDO::FETCH_ASSOC);
        if (empty($data))
        {
            return ERR_SEARCH;
        }
        return $data;
    }

    

    /**
     * Checks in data sent hash and id_user
     * for granting access
     * @param $param
     * @return bool
     */
    
    protected function checkUser($param)
    {
        if (isset($param['hash']) && isset($param['id_user']))
        {
            $hash = $this->pdo->quote($param['hash']);
            $id = $this->pdo->quote($param['id_user']);
            $sql = 'SELECT r_b.name as role FROM users_booker u_b LEFT JOIN roles_booker r_b ON u_b.id_role=r_b.id WHERE u_b.id='.$id.' AND u_b.hash='.$hash;
            $data = $this->executeSELECT($sql);
            return (is_array($data)) ? $data[0]['role'] : false;
        }
        else
        {
            return false;
        }
    }

    /**
     * Checks for booked events intersect in time 
     * @param $param
     * @return bool
    */

    protected function checkEvent($param)
    {
        $dateStart = new DateTime();
        $dateEnd = new DateTime();
        $dateStart->setTimestamp($param['dateTimeStart']/1000);
        $dateEnd->setTimestamp($param['dateTimeEnd']/1000);
        $day = $dateStart->format('Y-m-d');
        $day = $this->pdo->quote($day.'%');
        $rezparam = $this->putQuotes($param);
        
        $sql = 'SELECT time_start, time_end FROM events_booker WHERE time_start LIKE '.$day.' AND id_room ='.$rezparam['id_room'];
        
        if (!empty($param['event_id']))
        {
            $sql .= ' AND id !='.$rezparam['event_id'];
        }

        $data = $this->executeSELECT($sql);
        if (!is_array($data))
        {
            return true;
        }
        foreach ($data as $val)
        {
            if (!((new DateTime($val['time_start']) < $dateStart && new DateTime($val['time_end']) <= $dateStart) || ($dateEnd <= new DateTime($val['time_start']) && $dateEnd < new DateTime($val['time_end']))))
            {
                return false;
            }
        }
        return true;
    }

    public function putQuotes($data)
    {
        $rezData=array();
        foreach($data as $key => $value)
        {
            $rezData[$key]=$this->pdo->quote($data[$key]);
        }
        return $rezData;
    }
}
