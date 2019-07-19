<?php

/**
 * Class ModelEvents
 * works with controller Events.
 * Receives data from the controller,
 * works DB and return final data
 * to ControllerEvents
 */

class ModelEvents extends ModelDB
{
    public $time_start;
    public $time_end;
    
    /**
     * Get Events from DB
     * Access - admin or user
     * @param $param
     * @return array|string
     */

    public function getEvents($param)
    {
        if ($this->checkUser($param) == 'admin' || $this->checkUser($param) == 'user')
        {
            unset($param['hash'], $param['id_user']);
            if (isset($param['flag']) && $param['flag'] === 'like')
            {
                $data = $this->getEventLike($param);
                return $data;
            }
            
            else if (isset($param['flag']) && $param['flag'] === 'parent')
            {
                $data = $this->getEventParent($param);
                return $data;
            }
        }
        else
        {
            return ERR_ACCESS;
        }
    }

    /**
     * Get Events from DB if get flag like
     * Access - admin or user
     * @param $param
     * @return array|string
     */

    private function getEventLike($param)
    {
        $sql = 'SELECT e_b.id, e_b.id_user, u_b.username as user_name, e_b.id_room, r_b.name as room_name, e_b.description, e_b.time_start, e_b.time_end, e_b.id_parent, e_b.create_time'
        .' FROM events_booker e_b LEFT JOIN users_booker u_b ON e_b.id_user=u_b.id LEFT JOIN rooms_booker r_b ON e_b.id_room=r_b.id';
        
        unset($param['flag']);

        $sql .= " WHERE ";
        if (count($param) == 0)
        {
            return false;
        }
        foreach ($param as $key => $val)
        {
            list($year, $month) = explode('-',$val, 2);
            if ($month<10)
            {
                $sql .= $key.' LIKE '.$this->pdo->quote($year.'-0'.$month.'-0%').
                  ' OR '.$key.' LIKE '.$this->pdo->quote($year.'-0'.$month.'-1%').
                  ' OR '.$key.' LIKE '.$this->pdo->quote($year.'-0'.$month.'-2%').
                  ' OR '.$key.' LIKE '.$this->pdo->quote($year.'-0'.$month.'-3%').' AND ';
            }
            else
            {
              $sql .= $key.' LIKE '.$this->pdo->quote($year.'-'.$month.'-%').' AND ';
            }
        }
        $sql = substr($sql, 0, -5);
        $sql .= ' ORDER BY e_b.time_start';
        return $this->executeSELECT($sql);
    }

    /**
     * Get paren event by future time
     * DB if get flag parennt
     * Access - admin or user
     * @param $param
     * @return array|string
     */

    private function getEventParent($param)
    {
        $sql = 'SELECT e_b.id, e_b.id_user, u_b.username as user_name, e_b.id_room, r_b.name as room_name, e_b.description, e_b.time_start, e_b.time_end, e_b.id_parent, e_b.create_time'
                .' FROM events_booker e_b LEFT JOIN users_booker u_b ON e_b.id_user=u_b.id LEFT JOIN rooms_booker r_b ON e_b.id_room=r_b.id';
            
        unset($param['flag']);
        $rezparam=$this->putQuotes($param);
        $sql .= ' WHERE (e_b.id='.$rezparam['id'].' OR e_b.id_parent='.$rezparam['id'].') AND e_b.time_start > NOW()'
                .' AND e_b.id_user='.$rezparam['event_id_user'];
        return $this->executeSELECT($sql);
    }

    /**
     * Adding an event - single or recursive
     * @param $param 
     * @return array|bool|int|string
     */

    public function addEvents($param)
    {
        if ($this->checkUser($param) == 'admin' || $this->checkUser($param) == 'user')
        {
            $validate = $this->validator->isValidEventAdd($param);
            if ($validate === true)
            {
                $dateStart = new DateTime();
                $dateStart->setTimestamp($param['dateTimeStart']/1000);
                $dateEnd = new DateTime();
                $dateEnd->setTimestamp($param['dateTimeEnd']/1000);

                if($this->checkEvent($param) === true)
                {
                    $result = $this->addSingleEvent($param, $dateStart, $dateEnd);
                    if (isset($param['recurringMethod']))
                    {
                        $param['id_parent'] = $this->pdo->lastInsertId();
                        $param['duration'] = (int)$param['duration'];
                        $result = $this->addRecurringEvent($param, $dateStart, $dateEnd);
                    }
                    return $result;
                }
                return ERR_ADDEVENT;
            }
            return $validate;
        }
        else
        {
            return ERR_ACCESS;
        }
    }

     /**
     * Adding one event
     * @param $param 
     * @return int|string
     */
    private function addSingleEvent($param, $dateStart, $dateEnd)
    {
        $rezparam=$this->putQuotes($param);
        
        $timeStart = $this->pdo->quote($dateStart->format(TIME_FORMAT));
        $timeEnd = $this->pdo->quote($dateEnd->format(TIME_FORMAT));
       
        $sql = 'INSERT INTO events_booker (id_user, id_room, description, time_start, time_end)'
              .' VALUES ('.$rezparam['booked_for'].', '.$rezparam['id_room'].', '.$rezparam['description'].', '.$timeStart.', '.$timeEnd.')';
        $result = $this->executeSQL($sql);
        return $result;
    }

    /**
     * Adding event - recursive
     * @param $param
     * @param $dateStart
     * @param $dateEnd
     * @return array|bool
     */
    private function addRecurringEvent($param, $dateStart, $dateEnd)
    {
        $arrErrors = array();
        for ($i=0; $i< $param['duration']; $i++)
        {
            $rezparam=$this->putQuotes($param);
            
            $dateStart->modify($this->getEventPeriod($param['recurringMethod']));
            $dateEnd->modify($this->getEventPeriod($param['recurringMethod']));
            $timeStart = $this->pdo->quote($dateStart->format(TIME_FORMAT));
            $timeEnd = $this->pdo->quote($dateEnd->format(TIME_FORMAT));
            $param['dateTimeStart'] = $dateStart->getTimestamp()*1000;
            $param['dateTimeEnd'] = $dateEnd->getTimestamp()*1000;
            
            if ($this->validator->isNotWeekend($param['dateTimeStart']))
            {
                if($this->checkEvent($param) === true)
                {
                    $sql = 'INSERT INTO events_booker (id_user, id_room, description, time_start, time_end, id_parent)'
                          .' VALUES ('.$rezparam['booked_for'].', '.$rezparam['id_room'].', '.$rezparam['description'].', '.$timeStart.', '.$timeEnd.', '.$param['id_parent'].')';
                    $this->executeSQL($sql);
                }
                else
                {
                    $arrErrors[]='This time at choosen day has been booked by another user: '.$dateS.' - '.$dateE;
                }
            }
            else
            {
                $arrErrors[]= $dateS.' is a weekend. '. INVAL_WEEKEND;
            }
        }
        if (count($arrErrors) == 0)
        {
            return true;
        }
        return $arrErrors;
    }

    /**
     * Get event's period - weekly, bi-weekly, monthly
     * @param $recurring
     * @return string
     */

    private function getEventPeriod($recurring)
    {
        $period = '';
        switch ($recurring)
        {
        case 'weekly':
            $period = '+1 week';
            break;
        case 'bi-weekly':
            $period = '+2 week';
            break;
        case 'monthly':
            $period = '+1 month';
            break;
        }
        return $period;
    }

    /**
     * Edit events - normal and recursive
     * @param $param
     * @return array|bool|int|string
     */
    public function editEvent($param)
    {
        if ($this->checkUser($param) == 'admin' || $this->checkUser($param) == 'user')
        {
            if(array_key_exists('checked',$param))
            {
                if ($param['checked'])
                {
                    $result = $this->editRecurringEvents($param['checked'], $param['timestamp']);
                    return $result;
                }
            }
            else
            {
                $result = $this->editSingleEvents($param);
                return $result;
            }
        }
        else
        {
            return ERR_ACCESS;
        }
    }

/**
     * Edit single event
     * @param $param
     * @return int|string
     */
    private function editSingleEvents($param)
    {
        $validate = $this->validator->isValidEventAdd($param);
            if ($validate === true)
            {
                if ($this->checkEvent($param) === true)
                {
                    return $this->UpdateEvetn($param);
                }
                return ERR_ADDEVENT;
            }
        return $validate;
    }

    /**
     * Edit events recursive
     * @param $param
     * @param $timestamp
     * @return array|bool|string
     */
    private function editRecurringEvents($param, $timestamp)
    {
        $arrErrors = array();
        $timePoint = new DateTime();
        $timePoint->setTimestamp($timestamp/1000);
        $timeP = $this->pdo->quote($timePoint->format(TIME_FORMAT));
        if (!is_array($param))
        {
            return ERR_DATA;
        }
        for ($i=0; $i<count($param); $i++)
        {
            $validate = $this->validator->isValidEventAdd($param[$i]);
            if ($validate == true)
            {
                if ($this->checkEvent($param[$i]) === true)
                {
                    $this->UpdateEvetn($param[$i]);
                }
                else
                {
                   $arrErrors[]= 'Date and time is already RESERVED: '. $this->time_start.' - '. $this->time_end;
                }
            }
            else
            {
                $arrErrors[] = $i. '. Error validation: ' . $validate;
            }
        }

        if (count($arrErrors) == 0)
        {
            return true;
        }
        else
        {
            return $arrErrors;
        }
    }

    /**
     * Update events recursive
     * @param array $data
     * @return int
     */

    private function UpdateEvetn($data)
    {
        $rezdata=$this->putQuotes($data);
        
        $dateStart = new DateTime();
        $dateEnd = new DateTime();
        $dateStart->setTimestamp($data['dateTimeStart']/1000);
        $dateEnd->setTimestamp($data['dateTimeEnd']/1000);
        $this->time_start = $this->pdo->quote($dateStart->format(TIME_FORMAT));
        $this->time_end = $this->pdo->quote($dateEnd->format(TIME_FORMAT));
        $sql = 'UPDATE events_booker SET id_user='.$rezdata['booked_for'].', time_start='.$this->time_start.', time_end='.$this->time_end.', description='.$rezdata['description'].', create_time=CURRENT_TIMESTAMP'
              .' WHERE id='.$rezdata['event_id'];
        $result = $this->executeSQL($sql);
        return $result;
    }

    /**
     * Delete Event's - single and recursive
     * @param $param
     * @return bool|int|string
     */

    public function deleteEvent($param)
    {
        if ($this->checkUser($param) == 'admin' || $this->checkUser($param) == 'user')
        {
            if(array_key_exists('checked',$param))
            {
                if($param['checked'])
                {
                    $result = $this->deleteRecurringEvents($param);
                    return $result;
                }
            }
            else
            {
                $result = $this->deleteSingleEvents($param);
                return $result;
            }
        }
        else
        {
            return ERR_ACCESS;
        }
    }

   /**
     * Delete single event
     * @param $param
     * @return int
     */

    private function deleteSingleEvents($param)
    {
        $id = $this->pdo->quote($param['id']);
        $sql = 'DELETE FROM events_booker WHERE id='.$id;
        $result = $this->executeSQL($sql);
        return $result;
    }


    /**
     * Delete recursive event's 
     * @param $param
     * @return bool|int
     */
    private function deleteRecurringEvents($param)
    {
        $rezparam=$this->putQuotes($param);
        if ($param['id_parent'] == 'null')
        {
            $sql = 'DELETE FROM events_booker WHERE (id='.$rezparam['id'].' OR id_parent='.$rezparam['id'].') AND id_user='.$rezparam['event_id_user'];
            $result = $this->executeSQL($sql);
            return $result;
        }
        else
        {
            $sql = 'DELETE FROM events_booker WHERE (id='.$rezparam['id'].' OR id_parent='.$rezparam['id_parent'].') AND time_start >='.$rezparam['time_start']
                  .' AND id_user='.$rezparam['event_id_user'];
            $result = $this->executeSQL($sql);
            return $result;
        }
    }


}
