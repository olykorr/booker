<?php

/**
 * REST Controller - Rooms, gave access with requests:
 * GET, POST, PUT, DELETE
 * Accepts data, sends to the model
 */

class ControllerRooms extends RestServer
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
        $this->model = new ModelRooms();
        $this->response = new Response();
        $this->view = new Viewer();
        $this->start();
    }

    /**
     * Get the rooms by parametres, 
     * by method - GET
     * @param array $param
     * @return array | string OR error
     */
    public function getRooms($param)
    {
        try
        {
            $result = $this->model->getRooms($param);
            $result=$this->view->encodedData($result);
            return $this->response->serverSuccess(200, $result);
        }
        catch (Exception $exception){ return $this->response->serverError(500, $exception->getMessage());}
    }
}
