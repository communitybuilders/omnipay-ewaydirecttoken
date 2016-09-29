<?php

namespace CommunityBuilders\Omnipay\EwayDirectToken\Message;

use Omnipay\Common\Message\RequestInterface;

class DirectResponse extends \Omnipay\Eway\Message\DirectResponse
{
    public function __construct(RequestInterface $request, $data)
    {
        parent::__construct($request, $data);

        if( !is_null($this->getCustomerReference()) ) {
            // The create card response does not include a message or status.
            // Create the properties with null values to avoid warnings/errors.
            $this->data->ewayTrxnStatus = "True";
            $this->data->ewayTrxnError = null;
        }
    }

    public function getCustomerReference()
    {
        if (empty($this->data->CreateCustomerResult)) {
            return null;
        }

        return (string)$this->data->CreateCustomerResult;
    }
}
