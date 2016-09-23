<?php

namespace CommunityBuilders\Omnipay\EwayDirectToken\Message;

/**
 * eWAY Direct Purchase Request with credit card token.
 */
class DirectTokenPurchaseRequest extends DirectAbstractRequest
{
    protected $liveEndpoint = 'https://www.eway.com.au/gateway/ManagedPaymentService/managedcreditcardpayment.asmx?WSDL';
    protected $testEndpoint = 'https://www.eway.com.au/gateway/ManagedPaymentService/test/managedcreditcardpayment.asmx?WSDL';

    public function getData()
    {
        $this->validate('customerReference');

        $arguments = array(
            "managedCustomerID"  => $this->getCustomerReference(), // This is the credit card token
            "amount"             => $this->getAmountInteger(),
            "invoiceReference"   => $this->getTransactionReference(),
            "invoiceDescription" => $this->getDescription()
        );

        if ($this->isCvvRequired()) {
            $arguments[ 'cvn' ] = $this->getCvv();
            $return_array = $this->createRequestArray("ProcessPaymentWithCVN", $arguments);
        } else {
            $return_array = $this->createRequestArray("ProcessPayment", $arguments);
        }

        return $return_array;
    }

    public function setCvvRequired($cvv_required)
    {
        return $this->setParameter('cvv_required', $cvv_required);
    }

    public function isCvvRequired()
    {
        return $this->getParameter('cvv_required');
    }

    public function setCvv($cvv)
    {
        return $this->setParameter('cvv', $cvv);
    }

    public function getCvv()
    {
        return $this->getParameter('cvv');
    }
}
