<?php

namespace CommunityBuilders\Omnipay\EwayDirectToken\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Tests\TestCase;

class DirectTokenPurchaseRequestTest extends TestCase
{
    const TEST_CUSTOMER_REFERENCE = "913999895401";

    /** @var DirectTokenPurchaseRequest */
    protected $request;

    public function setUp(): void
    {
        $this->request = new DirectTokenPurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setCustomerReference(self::TEST_CUSTOMER_REFERENCE);
        $this->request->setTestMode(true);
    }

    public function testNullCustomerReferenceThrowsException()
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage("The customerReference parameter is required");

        $this->request->setCustomerReference(null);
        $this->request->getData();
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('TokenPurchaseSuccess.txt');

        /** @var DirectResponse $response */
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame(13960890, $response->getTransactionReference());
        $this->assertSame('100', $response->getData()->ewayReturnAmount);
        $this->assertNull($response->getCustomerReference());
        $this->assertSame('00, Transaction Approved (PHPUnit)', $response->getMessage());
    }

    public function testSendFailure()
    {
        $this->setMockHttpResponse('TokenPurchaseFailure.txt');

        /** @var DirectResponse $response */
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertNull($response->getCustomerReference());
        $this->assertSame("Amount has to be greater than 0. ", $response->getMessage());
    }
}
