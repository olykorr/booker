<?php

/**
 * Class ModelRooms
 * works with controller Events.
 * Receives data from the controller,
 * works DB and return final data
 * to ControllerRooms
 */

class ModelRooms extends ModelDB
{

    /**
     * Get Rooms
     * @param $param
     * @return array|string
     */

    public function getRooms($param)
    {
        if ($this->checkUser($param) == 'admin' || $this->checkUser($param) == 'user' )
        {
            unset($param['hash'], $param['id_user']);
            $sql = 'SELECT id, name FROM rooms_booker';
            if ($param != false) 
            {
                if (is_array($param)) 
                {
                    $sql .= " WHERE ";
                    foreach ($param as $key => $val) 
                    {
                        $sql .= $key . '=' . $this->pdo->quote($val) . ' AND ';
                    }
                    $sql = substr($sql, 0, -5);
                }
            } 
            $data = $this->executeSELECT($sql);
            return $data;
        }
        else
        {
            return ERR_ACCESS;
        }
    }
}
