<?php

namespace CommunityBuilders\Omnipay\EwayDirectToken\Message;

class DirectRefundRequest extends \Omnipay\Eway\Message\DirectRefundRequest
{
    /**
     * Overridden to ensure transaction reference/transaction ID are in the correct
     * order and that the total amount is an absolute value.
     */
    public function getData()
    {
        $this->validate('refundPassword', 'transactionId');

        $xml = '<?xml version="1.0"?><ewaygateway></ewaygateway>';
        $sxml = new \SimpleXMLElement($xml);

        /* eWAY Customer Id */
        $sxml->addChild('ewayCustomerID', $this->getCustomerId());

        /* eWAY Transaction Details */
        $sxml->addChild('ewayTotalAmount', abs($this->getAmountInteger()));
        $sxml->addChild('ewayOriginalTrxnNumber', $this->getTransactionReference());

        /* Card Holder Details */
        $card = $this->getCard();
        $sxml->addChild('ewayCardExpiryMonth', $card->getExpiryDate('m'));
        $sxml->addChild('ewayCardExpiryYear', $card->getExpiryDate('y'));

        $sxml->addChild('ewayOption1', $this->getOption1());
        $sxml->addChild('ewayOption2', $this->getOption2());
        $sxml->addChild('ewayOption3', $this->getOption3());

        $sxml->addChild('ewayRefundPassword', $this->getRefundPassword());
        $sxml->addChild('ewayCustomerInvoiceRef', $this->getTransactionId());

        return $sxml;
    }

    public function sendData($data)
    {
        $http_response = $this->httpClient->request('POST', $this->getEndpoint(), [], $data->asXML());
        $response_body = $http_response->getBody()->getContents();

        return $this->response = new DirectResponse($this, simplexml_load_string($response_body));
    }
}
