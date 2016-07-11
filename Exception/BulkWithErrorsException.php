<?php

namespace ONGR\ElasticsearchBundle\Exception;

/**
 * Raised when _bulk response contains errors.
 */
class BulkWithErrorsException extends \Exception
{
    /**
     * @var array
     */
    protected $response;

    /**
     * {@inheritdoc}
     */
    public function __construct($message = '', $code = 0, \Exception $previous = null, $response = [])
    {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }

    /**
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }
}
