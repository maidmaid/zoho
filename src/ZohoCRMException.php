<?php

namespace Maidmaid\Zoho;

use Exception;

class ZohoCRMException extends Exception
{
    protected $uri;

    public function __construct($message, $code, $uri = '', \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->uri = $uri;
    }

    /**
     * @return mixed
     */
    public function getUri()
    {
        return $this->uri;
    }
}
