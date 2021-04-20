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
class IBANTest extends PHPUnit_Framework_TestCase
{

    public function testAccountHigherThanMaxInt()
    {
        $string = QRInvoice::accountToIban('2501301193/2010');

        $this->assertSame(
            'CZ3620100000002501301193',
            $string
        );
    }

}
