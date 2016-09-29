<?php

namespace CommunityBuilders\Omnipay\EwayDirectToken\XML\SOAP;

class Response
{
    const FORMAT_OBJECT = "format_object";
    const FORMAT_ARRAY = "format_array";

    /* @var \SimpleXMLElement $request */
    protected $response;

    /* @var array $parsed_params */
    protected $parsed_params;

    public function __construct(\SimpleXMLElement $response = null)
    {
        $this->response = $response;
        $this->loadBody();
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getBody()
    {
        return $this->parsed_params;
    }

    protected function loadBody($format = self::FORMAT_OBJECT)
    {
        if (!isset($this->parsed_params)) {
            $body = $this->response->children('soap', true)->Body;

            if (isset($body->Fault)) {
                // Encountered a SOAP fault.
                $fault_children = $body->Fault->children();

                throw new \SoapFault($fault_children->faultcode->__toString(), $fault_children->faultstring->__toString());
            }

            // The first child of the body object will be our actual response object, which should
            // contain all the name => val pairs that we're looking for.
            $this->parsed_params = self::parseBodyParam($body->children()[ 0 ], $format);
        }

        return $this->parsed_params;
    }

    public function parseBodyParam(\SimpleXMLElement $param, $format)
    {
        $return_array = array();

        foreach ($param->children() as $param) {
            if ($param->children()->count() > 0) {
                // Recursively parse the parameters for arrays/objects.
                $result = self::parseBodyParam($param, $format);
            } else {
                $result = (string)$param;
            }

            $return_array[ $param->getName() ] = $result;
        }

        if ($format === self::FORMAT_OBJECT) {
            $return_array = (object)$return_array;
        }

        return $return_array;
    }
}
