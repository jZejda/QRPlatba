<?php

/*
 * This file is part of the library "QRPlatba".
 *
 * (c) Dennis Fridrich <fridrich.dennis@gmail.com>
 *
 * For the full copyright and license information,
 * please view LICENSE.
 */

namespace Defr\QRInvoice;

use chillerlan\QRCode\{QRCode, QROptions};

/**
 * Knihovna pro generování QR plateb v PHP.
 *
 * @see https://raw.githubusercontent.com/snoblucha/QRPlatba/master/QRPlatba.php
 */
class QRInvoice
{
    /**
     * Verze QR formátu QR Platby.
     */
    const VERSION = '1.0';

    /**
     * Verze QR formátu QR Faktury.
     */
    const QRF_VERSION = '1.0';

    /**
     * @var array
     */
    private static $currencies = [
        'AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN',
        'BAM', 'BBD', 'BDT', 'BGN', 'BHD', 'BIF', 'BMD', 'BND', 'BOB', 'BRL',
        'BSD', 'BTN', 'BWP', 'BYN', 'BZD', 'CAD', 'CDF', 'CHF', 'CLP', 'CNY',
        'COP', 'CRC', 'CUC', 'CUP', 'CVE', 'CZK', 'DJF', 'DKK', 'DOP', 'DZD',
        'EGP', 'ERN', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GGP', 'GHS',
        'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF',
        'IDR', 'ILS', 'IMP', 'INR', 'IQD', 'IRR', 'ISK', 'JEP', 'JMD', 'JOD',
        'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KPW', 'KRW', 'KWD', 'KYD', 'KZT',
        'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'LYD', 'MAD', 'MDL', 'MGA', 'MKD',
        'MMK', 'MNT', 'MOP', 'MRO', 'MUR', 'MVR', 'MWK', 'MXN', 'MYR', 'MZN',
        'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'OMR', 'PAB', 'PEN', 'PGK',
        'PHP', 'PKR', 'PLN', 'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'RWF', 'SAR',
        'SBD', 'SCR', 'SDG', 'SEK', 'SGD', 'SHP', 'SLL', 'SOS', 'SPL', 'SRD',
        'STD', 'SVC', 'SYP', 'SZL', 'THB', 'TJS', 'TMT', 'TND', 'TOP', 'TRY',
        'TTD', 'TVD', 'TWD', 'TZS', 'UAH', 'UGX', 'USD', 'UYU', 'UZS', 'VEF',
        'VND', 'VUV', 'WST', 'XAF', 'XCD', 'XDR', 'XOF', 'XPF', 'YER', 'ZAR',
        'ZMW', 'ZWD',
    ];

    /**
     * @var array
     */
    private $keys = [
        'ACC' => null,
        // Max. 46 - znaků IBAN, BIC Identifikace protistrany !povinny
        'ALT-ACC' => null,
        // Max. 93 - znaků Seznam alternativnich uctu. odddeleny carkou,
        'AM' => null,
        //Max. 10 znaků - Desetinné číslo Výše částky platby.
        'CC' => 'CZK',
        // Právě 3 znaky - Měna platby.
        'DT' => null,
        // Právě 8 znaků - Datum splatnosti YYYYMMDD.
        'MSG' => null,
        // Max. 60 znaků - Zpráva pro příjemce.
        'X-VS' => null,
        // Max. 10 znaků - Celé číslo - Variabilní symbol
        'X-SS' => null,
        // Max. 10 znaků - Celé číslo - Specifický symbol
        'X-KS' => null,
        // Max. 10 znaků - Celé číslo - Konstantní symbol
        'RF' => null,
        // Max. 16 znaků - Identifikátor platby pro příjemce.
        'RN' => null,
        // Max. 35 znaků - Jméno příjemce.
        'PT' => null,
        // Právě 3 znaky - Typ platby.
        'CRC32' => null,
        // Právě 8 znaků - Kontrolní součet - HEX.
        'NT' => null,
        // Právě 1 znak P|E - Identifikace kanálu pro zaslání notifikace výstavci platby.
        'NTA' => null,
        //Max. 320 znaků - Telefonní číslo v mezinárodním nebo lokálním vyjádření nebo E-mailová adresa
        'X-PER' => null,
        // Max. 2 znaky -  Celé číslo - Počet dní, po které se má provádět pokus o opětovné provedení neúspěšné platby
        'X-ID' => null,
        // Max. 20 znaků. -  Identifikátor platby na straně příkazce. Jedná se o interní ID, jehož použití a interpretace závisí na bance příkazce.
        'X-URL' => null,
        // Max. 140 znaků. -  URL, které je možno využít pro vlastní potřebu
    ];

    /**
     * @var array klice pro QR Fakturu
     */
    private $keys_QRF = [
        'ID' => null, // Max. 40 - znaků oznaceni dokladu   !povinny
        'DD' => null, // Max. 8 znaků - datum vystaveni     !povinny
        'AM' => null, //Max. 18 znaků - Desetinné číslo Výše částky k uhrade.  !povinny
        'TP' => null, // Právě 1 znaky - typ danoveho plneni
        'TD' => null, // Právě 1 znaků - typ dokladu
        'SA' => null, // Právě 1 znaků - zda fa obsahuje zuctovani zaloh
        'MSG' => null, // Max. 40 znaků - popis predmetu plneni
        'ON' => null, // Max. 20 znaků - oznaceni objednavky
        'VS' => null, // Max. 10 znaků - Celé číslo - variabilni symbol
        'VII' => null, // Max. 14 znaků - alfanum. znaky DIC vystavce
        'INI' => null, // Max. 14 znaků - alfanum. znaky ICO vystavce
        'VIR' => null, // Max. 14 znaků - alfanum. znaky DIC prijemce
        'INR' => null, // Max. 14 znaků - alfanum. znaky ICO prijemce
        'DUZP' => null, // Právě. 8 znaků - datum uskutecneni zdan. plneni
        'DPPD' => null, // Právě. 8 znaků - datum povinnosti priznat dan
        'DT' => null, // Právě. 8 znaků - datum splatnosti
        'TB0' => null, // Max. 18 znaků - Desetinné číslo Zaklad dane v zakladni sazbe DPH
        'T0' => null, // Max. 18 znaků - Desetinné číslo Dan v zakladni sazbe DPH
        'TB1' => null, // Max. 18 znaků - Desetinné číslo Zaklad dane v prvni snizene sazbe DPH 15%
        'T1' => null, // Max. 18 znaků - Desetinné číslo Dan v prvni snizene sazbe DPH
        'TB2' => null, // Max. 18 znaků - Desetinné číslo Zaklad dane v druhe snizene sazbe DPH 10%
        'T2' => null, // Max. 18 znaků - Desetinné číslo Dan v prvni druhe sazbe DPH
        'NTB' => null, // Max. 18 znaků - Desetinné číslo Osvobozena plneni
        'CC' => null, // Právě 3 znaky - Měna platby.
        'FX' => null, // Max. 18 znaků - Desetinné číslo Kurz cizi meny
        'FXA' => null, // Max. 5 znaků - Cele číslo Pocet jednotek cizi meny
        'ACC' => null, // Max. 46 - znaků IBAN, BIC Identifikace protistrany
        'CRC32' => null, // Právě 8 znaků - Kontrolní součet - HEX.
        'X-SW' => null, // Max. 30 - znaků oznaceni SW
        'X-URL' => null, // Max. 70 - znaků ziskani faktury z online uloziste
    ];

    /**
     * zda kod obsahuje i QRPlatbu
     */
    private $isQRFaktura = true;

    /**
     * velikost kazdeho ctverce v QR kodu v px pro png
     */
    private $QRSquareSize = 4;

    private $QRSVGviewBox = 350;


    /**
     * Kontruktor nové platby.
     *
     * @param null $account
     * @param null $amount
     * @param null $variable
     * @param null $currency
     * @throws \InvalidArgumentException
     */
    public function __construct($account = null, $amount = null, $variable = null, $currency = null)
    {
        if ($account) {
            $this->setAccount($account);
        }
        if ($amount) {
            $this->setAmount($amount);
        }
        if ($variable) {
            $this->setVariableSymbol($variable);
        }
        if ($currency) {
            $this->setCurrency($currency);
        }
    }

    /**
     * Statický konstruktor nové platby.
     *
     * @param null $account
     * @param null $amount
     * @param null $variable
     *
     * @return QRInvoice
     * @throws \InvalidArgumentException
     */
    public static function create($account = null, $amount = null, $variable = null)
    {
        return new self($account, $amount, $variable);
    }

    /**
     * Nastavení čísla účtu ve formátu 12-3456789012/0100.
     *
     * @param $account
     *
     * @return $this
     */
    public function setAccount($account)
    {

        $this->keys['ACC'] = self::accountToIban($account);

        return $this;
    }

    /**
     * Set IBAN number.
     *
     * @param $iban
     *
     * @return $this
     */
    public function setAccountIBAN($iban)
    {

        $this->keys['ACC'] = $iban;

        return $this;
    }

    /**
     * Nastavení částky.
     *
     * @param $amount
     *
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->keys['AM'] = sprintf('%.2f', $amount);

        return $this;
    }

    /**
     * Nastavení variabilního symbolu.
     *
     * @param $vs
     *
     * @return $this
     */
    public function setVariableSymbol($vs)
    {
        $this->keys['X-VS'] = $vs;

        return $this;
    }

    /**
     * Nastavení konstatního symbolu.
     *
     * @param $cs
     *
     * @return $this
     */
    public function setConstantSymbol($cs)
    {
        $this->keys['X-KS'] = $cs;

        return $this;
    }

    /**
     * Nastavení specifického symbolu.
     *
     * @param $ss
     *
     * @throws QRInvoiceException
     *
     * @return $this
     */
    public function setSpecificSymbol($ss)
    {
        if (mb_strlen($ss) > 10) {
            throw new QRInvoiceException('Specific symbol is higher than 10 chars');
        }
        $this->keys['X-SS'] = $ss;

        return $this;
    }

    /**
     * Nastavení zprávy pro příjemce. Z řetězce bude odstraněna diaktirika.
     *
     * @param $msg
     *
     * @return $this
     */
    public function setMessage($msg)
    {
        $this->keys['MSG'] = mb_substr($this->stripDiacritics($msg), 0, 60);

        if($this->isQRFaktura){
            $this->setQRFakturaMessage($msg);
        }

        return $this;
    }

    /**
     * Nastavení jména příjemce. Z řetězce bude odstraněna diaktirika.
     *
     * @param $name
     *
     * @return $this
     */
    public function setRecipientName($name)
    {
        $this->keys['RN'] = mb_substr($this->stripDiacritics($name, false), 0, 35);

        return $this;
    }

    /**
     * Nastavení data úhrady.
     *
     * @param \DateTime $date
     *
     * @return $this
     */
    public function setDueDate(\DateTime $date)
    {
        $this->keys['DT'] = $date->format('Ymd');

        return $this;
    }


    /**
     * @param $cc
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setCurrency($cc)
    {
        if (!in_array($cc, self::$currencies, true)) {
            throw new \InvalidArgumentException(sprintf('Currency %s is not supported.', $cc));
        }

        $this->keys['CC'] = $cc;

        return $this;
    }

    // ---------------------- QRInvoice RQPay -----------------------------

    /**
     * Nastaveni generovani QRFaktury do QRPlatby
     *
     * @param bool $isQRFaktura
     * @return $this
     */
    public function setGenerateQRInvoice(bool $isQRFaktura)
    {
        $this->isQRFaktura = $isQRFaktura;

        return $this;
    }

    /**
     * Nastaveni data vystaveni faktury pro QRFakturu
     *
     * @param \DateTime $date
     * @return $this
     */
    public function setInvoiceDate (\DateTime  $date)
    {
        $this->keys_QRF['DD'] = $date->format('Ymd');

        return $this;
    }

    /**
     * Nataveni ID faktury pro QRFaktruu
     *
     * @param $id
     * @return $this
     * @throws QRInvoiceException
     */
    public function setIDQRInvoice($id)
    {
        if (mb_strlen($id) > 40) {
            throw new QRInvoiceException('Invoice ID is higher then 40 chars');
        }
        else {
            $this->keys_QRF['ID'] = $id;
        }

        return $this;
    }

    /**
     * Nastavení zprávy pro příjemce pro QRFakturu. Z řetězce bude odstraněna diaktirika. Ma rozdilnou delku retezce
     *
     * @param $msg
     *
     * @return $this
     */
    public function setQRFakturaMessage($msg)
    {
        $this->keys_QRF['MSG'] = mb_substr($this->stripDiacritics($msg), 0, 40);

        return $this;
    }

    // ---------------------- QRInvoice -----------------------------

    /**
     * Metoda vrátí QR Platbu + QR Fakturu jako textový řetězec.
     *
     * @return string
     */
    public function __toString()
    {
        $chunks = ['SPD', self::VERSION];
        foreach ($this->keys as $key => $value) {
            if (null === $value) {
                continue;
            }
            $chunks[] = $key.':'.$value;
        }

        $QRP_string = implode('*', $chunks);

        if(!$this->isQRFaktura) {
            return $QRP_string;
        }
        else {

            $chunks_QRF = array('SID', self::QRF_VERSION);
            foreach ($this->keys_QRF as $key => $value) {
                //vezmeme pouze klice QRFaktury
                if ($value === null) {
                    continue;
                }
                $chunks_QRF[] = $key.":".$value;
            }
            $QRF_string = implode('%2A', $chunks_QRF);

            return $QRP_string . '*X-INV:'. $QRF_string;
        }

    }


    /**
     * Instance třídy QrCode pro libovolné úpravy (barevnost, atd.).
     *
     * @param string $format
     * @param bool $rawFormat
     *
     * @return QrCode
     */
    public function getQRCode(string $format = 'svg', int $size = null,  bool $isBase64Encoded = false)
    {

        $qrOptinsData = array (
            'version'           => QRCode::VERSION_AUTO,
            'eccLevel'          => QRCode::ECC_L,
        );

        switch ($format) {
            case "png":
                $qrOptinsData['outputType'] = QRCode::OUTPUT_IMAGE_PNG;
                $qrOptinsData['imageBase64'] = $isBase64Encoded;
                $qrOptinsData['scale'] = $this->QRSquareSize;

                break;
            default:
                $qrOptinsData['outputType'] = QRCode::OUTPUT_MARKUP_SVG;
                $qrOptinsData['svgDefs'] = ((!is_null($size)) ? '<style>rect{shape-rendering:crispEdges} svg{width: '.$size.'px !important;height: '.$size.'px !important;}</style>' : '');
        }

        $qrOptions = new QROptions($qrOptinsData);

        $qrcode = new QRCode($qrOptions);
        $data = $qrcode->render((string) $this);

        return $data;
    }

    /**
     * Převedení čísla účtu na formát IBAN.
     *
     * @param $accountNumber
     *
     * @return string
     */
    public static function accountToIban($accountNumber)
    {
        $accountNumber = explode('/', $accountNumber);
        $bank = $accountNumber[1];
        $pre = 0;
        $acc = 0;
        if (false === mb_strpos($accountNumber[0], '-')) {
            $acc = $accountNumber[0];
        } else {
            list($pre, $acc) = explode('-', $accountNumber[0]);
        }

        $accountPart = sprintf('%06d%010s', $pre, $acc);
        $iban = 'CZ00'.$bank.$accountPart;

        $alfa = 'A B C D E F G H I J K L M N O P Q R S T U V W X Y Z';
        $alfa = explode(' ', $alfa);
        $alfa_replace = [];
        for ($i = 1; $i < 27; ++$i) {
            $alfa_replace[] = $i + 9;
        }
        $controlegetal = str_replace(
            $alfa,
            $alfa_replace,
            mb_substr($iban, 4, mb_strlen($iban) - 4).mb_substr($iban, 0, 2).'00'
        );
        $controlegetal = 98 - (int) bcmod($controlegetal, 97);
        $iban = sprintf('CZ%02d%04d%06d%010s', $controlegetal, $bank, $pre, $acc);

        return $iban;
    }

    /**
     * Odstranění diaktitiky.
     *
     * @param $string
     * @param bool $is_uppercase
     *
     * @return mixed
     */
    private function stripDiacritics($string, $is_uppercase = true)
    {

        setlocale(LC_CTYPE, 'cs_CZ');
        $clean_string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        $clean_string = str_replace(array('\'', '"', '^'), '', $clean_string);

        if($is_uppercase === true){
            $clean_string = strtoupper($clean_string);
        }

        return $clean_string;
    }
}
