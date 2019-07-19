<?php

/**
 * REST Controller - Events, gave access with requests:
 * GET, POST, PUT, DELETE
 * Accepts data, sends to the model
 */

class ControllerEvents extends RestServer
{
    private $model;
    private $response;

    /**
     * Create object - ModelEvents, Response, Viewer
     * run start
     * ControllerEvents construct.
     */

    public function __construct()
    {
        $this->model = new ModelEvents();
        $this->response = new Response();
        $this->view = new Viewer();
        $this->start();
    }

    /**
     * Get events, 
     * by method - GET
     * @param array $param 
     * @return array | string OR error
     */

    public function getEvents($param)
    {
        try
        {
            $result = $this->model->getEvents($param);
            $result = $this->view->encodedData($result);
            return $this->response->serverSuccess(200, $result);
        }
        catch (Exception $exception)
        {
            return $this->response->serverError(500, $exception->getMessage());
        }
    }

    /**
     * Add new events to DB, 
     * by method - POST
     * @param array $param array vith event-param
     * @return array | string OR error
     */

    public function postEvents($param)
    {
        try
        {
            $result = $this->model->addEvents($param);
            $result=$this->view->encodedData($result);
            return $this->response->serverSuccess(200, $result);
        }
        catch (Exception $exception){return $this->response->serverError(500, $exception->getMessage());}
    }

    /**
     * Changr (Edit or Update) events in DB
     * by method - PUT
     * @param array $param
     * @return array | string OR error
     */

    public function putEvents($param)
    {
        try
        {
            $result = $this->model->editEvent($param);
            $result=$this->view->encodedData($result);
            return $this->response->serverSuccess(200, $result);
        }
        catch (Exception $exception){ return $this->response->serverError(500, $exception->getMessage()); }
    }

    /**
     * Delete events in DB,
     * by method - DELETE
     * @param array $param 
     * @return string OR error
     */

    public function deleteEvents($param)
    {
        try
        {
            $result = $this->model->deleteEvent($param);
            return $this->response->serverSuccess(200, $result);
        }
        catch (Exception $exception){ return $this->response->serverError(500, $exception->getMessage()); }
    }
}
