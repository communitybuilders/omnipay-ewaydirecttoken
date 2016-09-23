<?php

namespace CommunityBuilders\Omnipay\EwayDirectToken\Message;

/**
 * eWAY Direct Request for creating credit card token.
 */
class DirectCreateCardRequest extends DirectAbstractRequest
{
    protected $liveEndpoint = 'https://www.eway.com.au/gateway/ManagedPaymentService/managedcreditcardpayment.asmx?WSDL';
    protected $testEndpoint = 'https://www.eway.com.au/gateway/ManagedPaymentService/test/managedcreditcardpayment.asmx?WSDL';

    public function getData()
    {
        $this->validate("card");

        $card = $this->getCard();

        $comment = !is_null($this->getCustomerReference()) ? "Customer #{$this->getCustomerReference()}" : null;

        $arguments = array(
            "Title"         => $card->getTitle(),
            "FirstName"     => $card->getFirstName(),
            "LastName"      => $card->getLastName(),
            "Address"       => $card->getAddress1() . " " . $card->getAddress2(),
            "Suburb"        => $card->getCity(),
            "State"         => $card->getState(),
            "Company"       => $card->getCompany(),
            "PostCode"      => $card->getPostcode(),
            "Country"       => strtolower($card->getCountry()), // Country MUST be lowercase. E.g. "au" for Australia.
            "Email"         => $card->getEmail(),
            "Fax"           => $card->getFax(),
            "Phone"         => $card->getPhone(),
            "Mobile"        => "",
            "CustomerRef"   => $this->getCustomerReference(),
            "JobDesc"       => "",
            "Comments"      => $comment,
            "URL"           => "",
            "CCNumber"      => $card->getNumber(),
            "CCNameOnCard"  => $card->getName(),
            "CCExpiryMonth" => $card->getExpiryMonth(),
            "CCExpiryYear"  => substr($card->getExpiryYear(), -2), // Expiry year MUST be a 2 digit value. (e.g. 18 for 2018)
            "CVN"           => $card->getCvv()
        );

        $return_array = $this->createRequestArray("CreateCustomer", $arguments);

        return $return_array;
    }
}
