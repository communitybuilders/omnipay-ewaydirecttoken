<?php

namespace CommunityBuilders\Omnipay\EwayDirectToken\Message;

use Omnipay\Eway\Message\AbstractRequest;

/**
 * eWAY Direct Abstract SOAP Request.
 * Simply uses the SoapClient class to create and send a SOAP request.
 */
abstract class DirectAbstractRequest extends \Omnipay\Eway\Message\DirectAbstractRequest
{
    public function sendData($data)
    {
        $soap_client = new \SoapClient($this->getEndpoint(), array("trace" => 1));

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
        $arguments = array(
            $data[ 'soap_function' ] => $data[ 'arguments' ]
        );

        $result = $soap_client->__soapCall($data[ 'soap_function' ], $arguments, null, $headers);

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
                "namespace" => "https://www.eway.com.au/gateway/managedpayment",
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
}
