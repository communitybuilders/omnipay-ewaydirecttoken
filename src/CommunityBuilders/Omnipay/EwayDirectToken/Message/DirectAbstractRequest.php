<?php

namespace CommunityBuilders\Omnipay\EwayDirectToken\Message;

use CommunityBuilders\Omnipay\EwayDirectToken\XML\SOAP\Response as SoapResponse;
use Guzzle\Http\Exception\ServerErrorResponseException;
use Omnipay\Eway\Message\AbstractRequest;

/**
 * eWAY Direct Abstract SOAP Request.
 * Simply uses the SoapClient class to create and send a SOAP request.
 */
abstract class DirectAbstractRequest extends \Omnipay\Eway\Message\DirectAbstractRequest
{
    protected $namespace_url = "https://www.eway.com.au/gateway/managedpayment";

    protected $liveEndpoint;
    protected $testEndpoint;

    public function sendData($data)
    {
        $headers = array();
        // Headers in SOAP are grouped. Loop through any header "groups" we may have.
        foreach ($data[ 'headers' ] as $header_group => $header_group_details) {
            // Get the namespace of these headers.
            $header_namespace = isset($header_group_details[ 'namespace' ]) ? $header_group_details[ 'namespace' ] : null;

            // Now, create a SoapHeader object with the actual headers (body) in this group of headers.
            $headers[] = new \SoapHeader($header_namespace, $header_group, $header_group_details[ 'body' ]);
        }

        // Convert any NULL arguments to empty strings.
        // NULL is accepted by the sandbox eWay gateway, but not production!
        foreach ($data[ 'arguments' ] as &$argument) {
            if (is_null($argument)) {
                $argument = "";
            }
        }
        unset($argument);

        // The arguments need to be grouped by function name, or we will
        // produce an empty element for the SOAP function (e.g. <ns1:CreateCustomer />, instead of <ns1:CreateCustomer>{ARGS}</ns1:CreateCustomer>)

        $http_headers = [
            'Content-Type' => 'text/xml; charset=utf-8',
            'SOAPAction'   => "{$this->namespace_url}/{$data['soap_function']}"
        ];

        $soap_body = $this->getSOAPBodyFromData($data[ 'soap_function' ], $data[ 'arguments' ], $headers);

        $response = $this->httpClient->request('POST', $this->getEndpoint(), $http_headers, $soap_body);

        $xml_response = new \SimpleXMLElement($response->getBody()->getContents());

        try {
            // Attempt to parse the SOAP response.
            $soap_response = new SoapResponse($xml_response);
            $result = $soap_response->getBody();
        } catch (\SoapFault $e) {
            // SoapFault encountered - set the error message
            // on our result object and mark as unsuccessful.
            $result = new \stdClass();
            $result->ewayTrxnStatus = "False";
            $result->ewayTrxnError = $e->getMessage();
        }

        if (isset($result->ewayResponse)) {
            // If we got an "ewayResponse" object in our response, point our result at it.
            $result = $result->ewayResponse;
        }

        $this->response = new DirectResponse($this, $result);

        return $this->response;
    }

    public function getCustomerReference()
    {
        return $this->getParameter("customerReference");
    }

    public function setCustomerReference($customer_reference)
    {
        return $this->setParameter("customerReference", $customer_reference);
    }

    public function getUsername()
    {
        return $this->getParameter('username');
    }

    public function setUsername($username)
    {
        return $this->setParameter('username', $username);
    }

    public function getPassword()
    {
        return $this->getParameter('password');
    }

    /**
     * Sets the password which will be used as a default header.
     * This function gets called automatically when we call createRequest on the AbstractGateway.
     *
     * @param $password
     *
     * @return AbstractRequest Provides a fluent interface
     */
    public function setPassword($password)
    {
        return $this->setParameter('password', $password);
    }

    /**
     * Gets an array of default required
     *
     * @return array
     */
    public function getDefaultEwayHeaders()
    {
        return array(
            "eWAYHeader" => array(
                "namespace" => $this->namespace_url,
                "body"      => array(
                    "eWAYCustomerID" => $this->getCustomerId(),
                    "Username"       => $this->getUsername(),
                    "Password"       => $this->getPassword()
                )
            )
        );
    }

    /**
     * @param string $soap_function The name of the soap function to call.
     * @param array $args An associative array of parameters and their values for the SOAP WebService call.
     * @param array|NULL $headers Any headers to add to the SOAP request. (See
     *     DirectAbstractSoapRequest::getDefaultEwayHeaders for example array.)
     *
     * @return array An array ready to be passed to the sendData method.
     */
    public function createRequestArray($soap_function, array $args, array $headers = null)
    {
        return array(
            'soap_function' => $soap_function,
            'headers'       => isset($headers) ? $headers : $this->getDefaultEwayHeaders(),
            'arguments'     => $args
        );
    }

    protected function getSOAPBodyFromData($function, $args, array $headers = array())
    {
        $soap_xml = new \DOMDocument('1.0', 'UTF-8');
        $namespace_prefix = "ns1";

        $soap_envelope = $soap_xml->appendChild($soap_xml->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'SOAP-ENV:Envelope'));
        $soap_envelope->setAttribute('xmlns:ns1', $this->namespace_url);

        if (count($headers) > 0) {
            // Add SOAP headers if we have any.
            $soap_headers_envelope = $soap_envelope->appendChild($soap_xml->createElement('SOAP-ENV:Header'));

            /** @var \SoapHeader[] $headers */
            foreach ($headers as $header) {
                $soap_header = $soap_headers_envelope->appendChild($soap_xml->createElement("{$namespace_prefix}:{$header->name}"));

                foreach ($header->data as $header_name => $header_value) {
                    $soap_header->appendChild($soap_xml->createElement("{$namespace_prefix}:{$header_name}", $header_value));
                }
            }
        }

        // Add soap body
        $soap_body_envelope = $soap_envelope->appendChild($soap_xml->createElement("SOAP-ENV:Body"));
        $soap_body_envelope = $soap_body_envelope->appendChild($soap_xml->createElement("{$namespace_prefix}:{$function}"));

        foreach ($args as $name => $val) {
            $soap_body_envelope->appendChild($soap_xml->createElement("{$namespace_prefix}:{$name}", $val));
        }

        $xml = $soap_xml->saveXML();

        return $xml;
    }
}
