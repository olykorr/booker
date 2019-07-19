<?php

/**
 * REST Controller - Users, gave access with requests:
 * GET, POST, PUT, DELETE
 * Accepts data, sends to the model
*/

class ControllerUsers extends RestServer
{
    private $model;
    private $response;

   /**
     * Create object - ModelEvents, Response, Viewer
     * run start
     * ControllerRooms construct.
    */

    public function __construct()
    {
        $this->model = new ModelUsers();
        $this->response = new Response();
        $this->view = new Viewer();
        $this->start();
    }

    /**
     * Get the requested users,
     * by method - GET
     * @param array $param 
     * @return array | string OR error
     */

    public function getUsers($param)
    {
        try
        {
                $result = $this->model->getUsers($param);
                $result=$this->view->encodedData($result);
                return $this->response->serverSuccess(200, $result);
        }
        catch (Exception $exception){ return $this->response->serverError(500, $exception->getMessage());}
    }

    /**
     * Add new users in DB with params,
     * by method - POST
     * @param array $param 
     * @return array | string OR error
     */

    public function postUsers($param)
    {
        try
        {
            $result = $this->model->addUser($param);
            return $this->response->serverSuccess(200, $result);
        }
        catch (Exception $exception){ return $this->response->serverError(500, $exception->getMessage());}
    }

    /**
     * Change (Edit or Update) events in DB,
     * by method - PUT
     * @param array $param
     * @return array | string OR error
     */

    public function putUsers($param)
    {
        try
        {
            if (isset($param['hash']) && isset($param['id_user']))
            {
                $result = $this->model->editUser($param);
                $result=$this->view->encodedData($result);
                return $this->response->serverSuccess(200, $result);
            }
            $result = $this->model->loginUser($param);
            $result=$this->view->encodedData($result);
            return $this->response->serverSuccess(200, $result);
        }
        catch (Exception $exception){ return $this->response->serverError(500, $exception->getMessage()); }
    }

    /**
     * Delete users in DB, 
     * by method - DELETE
     * @param array $param
     * @return string OR error
     */

    public function deleteUsers($param)
    {
        try
        {
            $result = $this->model->deleteUser($param);
            return $this->response->serverSuccess(200, $result);
        }
        catch (Exception $exception)
        {
            return $this->response->serverError(500, $exception->getMessage());
        }
    }
}
