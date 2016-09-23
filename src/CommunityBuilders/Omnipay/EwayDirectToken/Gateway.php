<?php

namespace CommunityBuilders\Omnipay\EwayDirectToken;

use Omnipay\Eway\DirectGateway;

/**
 * Extends the eWAY Legacy Direct XML Payments Gateway
 * Allows creating tokens for credit cards and using the tokens to make payments.
 */
class Gateway extends DirectGateway
{
    public function purchase(array $parameters = array())
    {
        return $this->createRequest('\CommunityBuilders\Omnipay\EwayDirectToken\Message\DirectTokenPurchaseRequest', $parameters);
    }

    public function createCard(array $parameters = array())
    {
        return $this->createRequest('\CommunityBuilders\Omnipay\EwayDirectToken\Message\DirectCreateCardRequest', $parameters);
    }

    public function refund(array $parameters = array())
    {
        return $this->createRequest('\CommunityBuilders\Omnipay\EwayDirectToken\Message\DirectRefundRequest', $parameters);
    }

    public function getUsername()
    {
        $this->getParameter('username');
    }

    public function setUsername($username)
    {
        $this->setParameter('username', $username);
    }

    public function getPassword()
    {
        $this->getParameter('password');
    }

    public function setPassword($password)
    {
        $this->setParameter('password', $password);
    }

    function completeAuthorize(array $options = array())
    {
        // TODO: Implement completeAuthorize() method.
    }

    function completePurchase(array $options = array())
    {
        // TODO: Implement completePurchase() method.
    }

    function updateCard(array $options = array())
    {
        // TODO: Implement updateCard() method.
    }

    function deleteCard(array $options = array())
    {
        // TODO: Implement deleteCard() method.
    }
}
