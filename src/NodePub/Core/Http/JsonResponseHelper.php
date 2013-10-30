<?php

namespace NodePub\Core\Http;

use Symfony\Component\HttpFoundation\Response;

/**
 * A helper for preparing JSON responses with pre-formatted success and errors
 */
class JsonResponseHelper
{
    const CONTENT_TYPE = 'Content-Type';
    const APPLICATION_JSON = 'application/json';

    const ERR_TYPE_400 = 'Request Error';
    const ERR_MSG_400 = 'The request data was not properly formed.';
    
    const ERR_TYPE_404 = 'Not Found';
    const ERR_MSG_404 = 'No resources were found with the given parameters.';
    
    const ERR_TYPE_500 = 'Server Error';
    const ERR_MSG_500 = 'File could not be saved.';

    /**
     * Prepares a Response object in JSON with proper headers
     */
    public function prepareJsonResponse($body, $statusCode)
    {
        $response = new Response(json_encode($body), $statusCode);
        $response->headers->set(self::CONTENT_TYPE, self::APPLICATION_JSON);
        
        return $response;
    }
    
    public function prepareSuccessResponse($type, $message)
    {
        return $this->prepareJsonResponse($this->prepareSuccess($type, $message), 200);
    }
    
    public function prepareErrorResponse($type, $message, $errorCode)
    {
        return $this->prepareJsonResponse($this->prepareError($type, $message), $errorCode);
    }

    public function prepareSuccess($type, $message)
    {
        $success = array(
            'success' => array(
                'successType' => $type,
                'successMessage' => $message
            )
        );
        
        return (object) $success;
    }
    
    public function prepareError($type, $message)
    {
        $error = array(
            'error' => array(
                'errorType' => $type,
                'errorMessage' => $message
            )
        );

        return (object) $error;
    }
    
    public function error400Response($message = null)
    {
        $message = isset($message) ? $message : self::ERR_MSG_400;
        return $this->prepareErrorResponse(self::ERR_TYPE_400, $message, 400);
    }
    
    public function error404Response($message = null)
    {
        $message = isset($message) ? $message : self::ERR_MSG_404;
        return $this->prepareErrorResponse(self::ERR_TYPE_404, $message, 404);
    }
    
    public function error500Response($message = null)
    {
        $message = isset($message) ? $message : self::ERR_MSG_500;
        return $this->prepareErrorResponse(self::ERR_TYPE_500, $message, 500);
    }
}