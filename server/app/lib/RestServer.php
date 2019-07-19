<?php
/**
 * Class RestServer get requests from client
 * 
 * Checks the existence of method, 
 * method is determined by the method of sending the request.
 * 
 * Gets gets client sent data,
 * send them to a certain class and method for
 * later processing.
 * 
 * Is the parent class for all controllers.
 * 
 */

class RestServer
{
    protected $reqMethod;
    protected $url;
    protected $class;
    protected $data;
    protected $encode = ENCODE_DEFAULT;

    /**
     * start() is entry point for App
     * start() send headers, choose request method,
     * set method and getData from it
    */

    protected function start()
    {
        $this->url = $_SERVER['REQUEST_URI'];
        $this->reqMethod = $_SERVER['REQUEST_METHOD'];
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: PUT, POST, GET, DELETE');
        header('Access-Control-Allow-Headers: Authorization, Content-Type');
        switch ($this->reqMethod)
        {
            case 'GET':
                $this->setMethod('get'.ucfirst($this->getClass()), $this->getData());
                break;
            case 'POST':
                $this->setMethod('post'.ucfirst($this->getClass()), $this->getData());
                break;
            case 'PUT':
                $this->setMethod('put'.ucfirst($this->getClass()), $this->getData());
                break;
            case 'DELETE':
                $this->setMethod('delete'.ucfirst($this->getClass()), $this->getData());
                break;
            case 'OPTIONS':
                exit();
                break;
        }
    }

    /**
     * Checks the existence of methods and, if successful, calls them
     * @param $Method
     * @param bool $data
     */

    protected function setMethod($Method, $data=false)
    {
        if(method_exists($this, $Method))
        {
            echo $this->$Method($data); //call choosen method
        }
        else
        {
            header("HTTP/1.0 405 Method Not Allowed");
            echo $this->class.'ERROR';
        }
    }

    /**
     * Get class from url
     * @return mixed $this->class
     */

    protected function getClass()
    {
        //get part of url after /api/
        $clearUrl = explode('/api/', $this->url);
        //fing class part
        $clearUrl = explode('/', $clearUrl[count($clearUrl)-1]);
        $this->class = $clearUrl[0];
        return $this->class;
    }

    /**
     * Get data from client request methods (GET, POST, PUT, DELETE)
     * @return array | bool | mixed $this->data
    */

    protected function getData()
    {
        if (($this->reqMethod == 'GET') || ($this->reqMethod == 'DELETE'))
        {
            return $this->oppWithData_POST_DELETE();
        }
        elseif ($this->reqMethod == 'POST')
        {
            $this->data = $_POST;
            return $this->data;
        }
        elseif ($this->reqMethod == 'PUT')
        {
            $this->data = json_decode(file_get_contents("php://input"), true);
            return $this->data;
        }
    }

    /**
     * Get data from URL
     * @return array $data
    */

    private function getDatafromURL()
    {
        $clearUrl = explode('/api/', $this->url);
        $clearUrl = explode('/', $clearUrl[count($clearUrl) - 1], 2);
        $data = $clearUrl[count($clearUrl) - 1];
        return $data;
    }

    /**
     * If wee have method POST or DELETE pars data
     * @return array $data
    */

    private function oppWithData_POST_DELETE()
    {
        $data = $this->getDatafromURL();
        preg_match('#(\.[a-z]+)#', $data, $match);
        if (!empty($match[0]))
        {
            $this->encode = $match[0]; //save for viewer
            $data = trim($data, $this->encode);
        }
        $data = explode('/', $data);
            
        if (count($data) % 2)
        {
            $data = array();
            $id = (int)$data[count($data) - 1];
            $data['id'] = $id;
            if ($data['id'] === 0) { $data = false; }
        }
        else
        {
            $arrEven = array();
            $arrOdd = array();
            foreach ($data as $key => $val) 
            {
                ($key % 2) ? $arrOdd[] = urldecode($val) : $arrEven[] = $val;
            }
            $data = array_combine($arrEven, $arrOdd);
        }
        $this->data = $data;
        return $this->data;
    }
    


}
