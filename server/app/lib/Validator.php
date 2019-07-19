<?php

/**
 * Class Validator - Validates data resived from
 * client to the REST
 */
class Validator
{
    /**
     * Validator check the date $start of the day off or weekday
     * @param $start
     * @return bool
     */
    public function isNotWeekend($start){$start = date("w", $start/1000); return ($start == WEEKEND1 || $start == WEEKEND2) ? false : true; }

    /**
     * Validator check start time to be les than the end time of an event
     * @param $start
     * @param $end
     * @return bool
     */
    private function isTimeStartLessTimeEnd($start, $end){ return ($start < $end && $start != $end) ? true : false;}

    /**
     * Validator check the number of characters in the field
     * @param $userName
     * @return bool
     */
    private function isValidUserName($userName){ return (strlen($userName) > 3 && strlen($userName) < 35) ? true :false;}

    /**
     * Validator check characters in the field
     * @param $login
     * @return bool
     */
    private function isValidLogin($login){return (preg_match("/^[a-zA-Z0-9]{3,30}+$/",$login)) ? true : false;}

    /**
     * Validator check email format by filter
     * @param $email
     * @return bool
     */
    private function isValidEmail($email){return (filter_var($email, FILTER_VALIDATE_EMAIL)) ? true : false;}

    /**
     * Validator check characters in the field
     * @param $pass
     * @return bool
     */
    private function isValidPass($pass){return (preg_match("/^[a-zA-Z0-9]{4,20}+$/",$pass)) ? true : false;}

     /**
     * Validator check the number of characters in the field
     * @param $string
     * @return bool
     */
    private function isValidLength($string) {return (strlen($string) > 6) ? true : false;}

    /**
     * Validator check start time and end time to be within work time of rooms 
     * @param $start
     * @param $end
     * @return bool
     */
    private function isValidTimeStEn($start, $end)
    {
        $start = date("G", $start/1000);
        $end = date("G", $end/1000);
        if ($start >= TIME_START && $start < TIME_END)
        {
            if ($end >= TIME_START && $end <= TIME_END) {return true;}
        }
        return false;
    }

    /**
     * Validation of registration form 
     * @param $param
     * @return bool|string
     */
    public function isValidateRegistration($param)
    {
        if (!empty($param['username']) && !empty($param['login']) && !empty($param['email']) && !empty($param['pass']) && !empty($param['id_role']))
        {
            if ($this->isValidUserName($param['username']))
            {
                if ($this->isValidLogin($param['login']))
                {
                    if ($this->isValidEmail($param['email']))
                    {
                        if ($this->isValidPass($param['pass']))
                        {
                            return true;
                        }
                        return INCORRECT_PASS;
                    }
                    return INCORRECT_EMAIL;
                }
                return INCORRECT_LOGIN;
            }
            return INCORRECT_USERNAME;
        }
        return ERR_FIELDS;
    }

    /**
     * Validator check fields editing user data
     * @param $param
     * @return bool|string
     */
    public function isValidateEdit($param)
    {
        if (!empty($param['username']) && !empty($param['email']) && !empty($param['role']))
        {
            if ($this->isValidUserName($param['username']))
            {
                    if ($this->isValidEmail($param['email']))
                    {
                        if (isset($param['pass']))
                        {
                            if ($this->isValidPass($param['pass']))
                            {
                                return true;
                            }
                            return INCORRECT_PASS;
                        }
                        return true;
                    }
                    return INCORRECT_EMAIL;
            }
            return INCORRECT_USERNAME;
        }
        return ERR_FIELDS;
    }

    /**
     * Validator check added events
     * @param $param
     * @return bool|string
     */
    public function isValidEventAdd($param)
    {
        if (!empty($param['booked_for']) && !empty($param['dateTimeStart']) && !empty($param['dateTimeEnd']) && !empty($param['description']))
        {
            if ($this->isValidLength($param['description']))
            {
                if ($this->isTimeStartLessTimeEnd($param['dateTimeStart'], $param['dateTimeEnd']))
                {
                    if ($this->isNotWeekend($param['dateTimeStart']))
                    {
                        if ($this->isValidTimeStEn($param['dateTimeStart'], $param['dateTimeEnd']))
                        {
                            if (!isset($param['recurringMethod'])) 
                            { 
                                return true; 
                            }
                            else
                            {
                                if ($this->isValidRecurring($param)) { return true; }
                                else { return INCORRECT_RECURR;}
                            }
                        }
                        return INCORRECT_TIME_S_E;
                    }
                    return INCORRECT_WEEKEND;
                }
                return INCORRECT_TIME_FOR_EVENT;
            }
            return INCORRECT_TEXT;
        }
        return ERR_FIELDS;
    }

    /**
     * Validator check of the recursive method and number of
     * @param $param
     * @return bool
     */
    private function isValidRecurring($param)
    {
        if ($param['recurringMethod'] == 'weekly' || $param['recurringMethod'] == 'bi-weekly' || $param['recurringMethod'] == 'monthly')
        {
            if (!empty($param['duration']))
            {
                $duration = (int)$param['duration'];
                switch ($param['recurringMethod'])
                {
                    case 'weekly':
                        if ($duration >= 1 && $duration < 5){ return true;}
                        break;
                    case 'bi-weekly':
                        if ($duration >= 1 && $duration < 3){ return true;}
                        break;
                    case 'monthly':
                        if ($duration == 1){ return true;}
                        break;
                }
                return false;
            }
            return false;
        }
        return false;
    }
    
}
