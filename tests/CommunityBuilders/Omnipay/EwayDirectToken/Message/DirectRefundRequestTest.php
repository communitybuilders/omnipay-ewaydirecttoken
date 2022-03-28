<?php

namespace CommunityBuilders\Omnipay\EwayDirectToken\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Tests\TestCase;

class DirectRefundRequestTest extends TestCase
{
    const TEST_TRANSACTION_REFERENCE = "13961018";

    /** @var DirectRefundRequest */
    protected $request;

    public function setUp(): void
    {
        $this->request = new DirectRefundRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setRefundPassword("1234");
        $this->request->setTransactionReference(self::TEST_TRANSACTION_REFERENCE);
        $this->request->setCard($this->getValidCard());
        $this->request->setTransactionId(1);
        $this->request->setTestMode(true);
    }

    public function testNullRefundPasswordThrowsException()
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The refundPassword parameter is required');

        $this->request->setRefundPassword(null);
        $this->request->getData();
    }

    public function testNullTransactionIdThrowsException()
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('The transactionId parameter is required');

        $this->request->setTransactionId(null);
        $this->request->getData();
    }

    public function testGetDataThrowsExceptionForNegativeAmount()
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage("A negative amount is not allowed.");

        $this->request->setAmount(-100.00);
        $this->request->getData();
    }

    public function testGetDataConvertsAmountToInt()
    {
        $this->request->setAmount(100.00);
        $data = $this->request->getData();

        self::assertSame(10000, (int)$data->ewayTotalAmount);
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('RefundSuccess.txt');

        /** @var DirectResponse $response */
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame(13960990, $response->getTransactionReference());
        $this->assertSame('100', $response->getAmount());
        $this->assertSame('00,Transaction Approved (PHPUnit)', $response->getMessage());
    }

    public function testSendFailure()
    {
        $this->setMockHttpResponse('RefundFailure.txt');

        /** @var DirectResponse $response */
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertSame("Error: Original transaction does not exist. Your refund could not be processed.", $response->getMessage());
    }
}
