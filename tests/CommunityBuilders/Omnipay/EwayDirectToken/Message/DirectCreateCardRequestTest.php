<?php

namespace CommunityBuilders\Omnipay\EwayDirectToken\Message;

use Omnipay\Common\Exception\InvalidRequestException as InvalidRequestExceptionAlias;
use Omnipay\Tests\TestCase;

class DirectCreateCardRequestTest extends TestCase
{
    /** @var DirectCreateCardRequest */
    protected $request;

    public function setUp(): void
    {
        $this->request = new DirectCreateCardRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setCard($this->getValidCard());
        $this->request->setTestMode(true);
    }

    /**
     */
    public function testNullCardThrowsException()
    {
        $this->expectException(InvalidRequestExceptionAlias::class);
        $this->expectExceptionMessage('The card parameter is required');

        $this->request->setCard(null);
        $this->request->getData();
    }

    public function testCountryIsAlwaysLowercase()
    {
        $this->request->getCard()->setCountry("AU");

        $data = $this->request->getData();
        self::assertSame("au", $data[ 'arguments' ][ 'Country' ]);
    }

    public function testExpiryYearIsTwoDigits()
    {
        $expiry_year = $this->request->getCard()->getExpiryYear();

        $data = $this->request->getData();

        self::assertSame(2, strlen($data[ 'arguments' ][ 'CCExpiryYear' ]));
        self::assertSame(substr($expiry_year, -2), $data[ 'arguments' ][ 'CCExpiryYear' ]);
    }

    public function testCommentWithoutCustomerReference()
    {
        $this->request->setCustomerReference(null);
        $data = $this->request->getData();

        self::assertNull($data[ 'arguments' ][ 'Comments' ]);
    }

    public function testCommentWithCustomerReference()
    {
        $this->request->setCustomerReference("1234 bla bla bla");
        $data = $this->request->getData();

        self::assertStringContainsString("1234 bla bla bla", $data[ 'arguments' ][ 'Comments' ]);
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('CreateCardSuccess.txt');

        /** @var DirectResponse $response */
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertSame('913999895401', $response->getCustomerReference());
        $this->assertEmpty($response->getMessage());
    }

    public function testSendFailure()
    {
        $this->setMockHttpResponse('CreateCardFailure.txt');

        /** @var DirectResponse $response */
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertNull($response->getCustomerReference());
        $this->assertSame("The 'CCExpiryYear' element is invalid - The value '2016' is invalid according to its datatype 'CreditCardExpiry' - The Pattern constraint failed.", $response->getMessage());
    }
}
