<?php

namespace CommunityBuilders\Omnipay\EwayDirectToken\Message;

class DirectResponse extends \Omnipay\Eway\Message\DirectResponse
{
    public function getCustomerReference()
    {
        if (empty($this->data->CreateCustomerResult)) {
            return null;
        }

        return (string)$this->data->CreateCustomerResult;
    }
}
