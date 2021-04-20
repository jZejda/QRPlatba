<?php

/*
 * This file is part of the library "QRInvoice".
 *
 * (c) Dennis Fridrich <fridrich.dennis@gmail.com>
 *
 * For the full copyright and license information,
 * please view LICENSE.
 */

use Defr\QRInvoice;

/**
 * Class QRInvoiceTest.
 */
class QRInvoiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFakeCurrencyString()
    {
        QRInvoice::create('12-3456789012/0100', '1234.56', '2016001234')
            ->setMessage('Düakrítičs')
            ->setCurrency('FAKE');
    }

    public function testCzkString()
    {
        $string = QRInvoice::create('12-3456789012/0100', '1234.56', '2016001234')
            ->setMessage('Düakrítičs');

        $this->assertSame(
            'SPD*1.0*ACC:CZ0301000000123456789012*AM:1234.56*CC:CZK*MSG:Duakritics*X-VS:2016001234',
            $string->__toString()
        );

        $string = QRInvoice::create('12-3456789012/0100', '1234.56', '2016001234')
            ->setMessage('Düakrítičs')
            ->setCurrency('CZK');

        $this->assertSame(
            'SPD*1.0*ACC:CZ0301000000123456789012*AM:1234.56*CC:CZK*MSG:Duakritics*X-VS:2016001234',
            $string->__toString()
        );
    }

    public function testEurString()
    {
        $string = QRInvoice::create('12-3456789012/0100', '1234.56', '2016001234')
            ->setMessage('Düakrítičs')
            ->setCurrency('EUR');

        $this->assertSame(
            'SPD*1.0*ACC:CZ0301000000123456789012*AM:1234.56*CC:EUR*MSG:Duakritics*X-VS:2016001234',
            $string->__toString()
        );
    }

    public function testRecipientName()
    {
        $string = QRInvoice::create('12-3456789012/0100', '1234.56', '2016001234')
            ->setRecipientName('Düakrítičs');

        $this->assertSame(
            'SPD*1.0*ACC:CZ0301000000123456789012*AM:1234.56*CC:CZK*X-VS:2016001234*RN:Duakritics',
            $string->__toString()
        );
    }
}
