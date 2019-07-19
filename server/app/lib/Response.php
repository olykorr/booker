<?php

/**
 * Called from Controller
 * Get data from controller
 * and sends with a
 * nesessary header
 */
class Response
{
  
    /**
   * Success type
   * @return array
   */

  private function serverOKType()
  {
      return array(
          200 => "HTTP/1.0 200 Ok",
          201 => "HTTP/1.0 201 Created",
          202 => "HTTP/1.0 202 Accepted",
          203 => "HTTP/1.1 203 Non-Authoritative Information",
          204 => "HTTP/1.0 204 No Content",
          205 => "HTTP/1.0 205 Reset Content"
      );
  }

    /**
     * Client error
     * @return array
    */

    private function clientErrorType()
    {
        return array(
            400 => "HTTP/1.0 400 Bad Request", //server does not understand the request from the client
            401 => "HTTP/1.0 401 Unauthorized",
            402 => "HTTP/1.0 402",
            403 => "HTTP/1.0 403 Forbidden", //insufficient rights for the current user 
            404 => "HTTP/1.0 404 Not Found",
            405 => "HTTP/1.1 405 Method Not Allowed",
            406 => "HTTP/1.0 406 Not Acceptable",
            412 => "HTTP/1.0 412 Precondition Failed",
            415 => "HTTP/1.1 415 Unsupported Media Type", //The format not supported or JSON request failed or request not JSON
            422 => "HTTP/1.0 422 Unprocessable Entity"
        );
    }

    /**
     * Server error
     * @return array
    */

    private function serverErrorType()
    {
        return array(
            500 => "HTTP/1.0 500 Internal Server Error",
            501 => "HTTP/1.0 501 Not Implemented",
            502 => "HTTP/1.0 502 Bad Gateway",
            503 => "HTTP/1.0 503 Service Unavailable",
            504 => "HTTP/1.0 504 Gateway Timeout",
            505 => "HTTP/1.0 505 Version Not Supported"
        );
    }

    /**
     * Server success response
     * @param $type
     * @param null | string $msg
     * @return null | string, set header
     */
    
    public function serverSuccess($type, $msg=null)
    {
        $responseHeader = $this->serverOKType();
        header($responseHeader[$type]);
        return $msg;
    }

    /**
     * Server errors response
     * @param $errorType
     * @param null | string $msg 
     * @return null | string, set header
     */

    public function serverError($errorType, $msg=null)
    {
        $responseHeader = $this->serverErrorType();
        header($responseHeader[$errorType]);
        return $msg;
    }

    /**
     * Client errors response
     * @param $errorType
     * @param string $msg 
     * @return string, set header
     */

    public function clientError($errorType, $msg)
    {
        $responseHeader = $this->clientErrorType();
        header($responseHeader[$errorType]);
        return $msg;
    }
}
