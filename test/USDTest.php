<?php
/**
 * Copyright (c) 2010 SecureHealthPay (http://www.securehealthpay.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * 
 * @category   Currency
 * @package    Currency
 * @copyright  Copyright (c) 2010 SecureHealthPay (http://www.securehealthpay.com)
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @author     Jason Ardell (on behalf of SecureHealthPay)
 */

/**
 * Tests for Currency_USD
 *
 * @link http://www.phpunit.de/pocket_guide/3.0/en/writing-tests-for-phpunit.html
 */

class Currency_USDTest extends PHPUnit_Framework_TestCase {
    /**
     * Test whether we can create a currency object from a string.
     *
     * @param string  $string     The string representation of the currency to be parsed.
     * @param integer $dollars    The number of dollars represented by the string.
     * @param integer $cents      The number of cents represented by the string.
     * @param boolean $isNegative Whether the currency object is negative.
     *
     * @dataProvider currencyAsStringDataProvider
     */
    public function testCanBeCreatedFromString($string, $dollars, $cents, $isNegative, $expectedException) {
        try {
            $currency = Currency_USD::fromString($string);
            $this->assertFalse($expectedException, "Exception expected, but none thrown");

            $this->assertEquals($dollars, $currency->getDollars());
            $this->assertEquals($cents, $currency->getCents());
            $this->assertEquals($isNegative, $currency->isNegative());
        } catch(Currency_USD_Exception $ex) {
            $this->assertTrue($expectedException, "Exception thrown, but none expected");
        }
    }

    /**
     * Currency as string data provider.
     *
     * @return array Returns array:
     *  - Representation
     *  - Dollars
     *  - Cents
     *  - IsNegative
     */
    public function currencyAsStringDataProvider() {
        return array(
            //    String            Dollar  Cen IsNegative expException
            array('0',              0,          0,  false, false),
            array('12',             12,         0,  false, false),
            array('.1',             0,          10, false, false),
            array('.01',            0,          1,  false, false),
            array('.12',            0,          12, false, false),
            array('0.12',           0,          12, false, false),
            array('1.2',            1,          20, false, false),
            array('1.23',           1,          23, false, false),
            array('12.34',          12,         34, false, false),
            array('123.45',         123,        45, false, false),
            array('-0',             0,          0,  true,  false),
            array('-0.0',           0,          0,  true,  false),
            array('-0.00',          0,          0,  true,  false),
            array('-1.23',          1,          23, true,  false),
            array('-1',             1,          0,  true,  false),
            array('-1.2',           1,          20, true,  false),
            array('-12.34',         12,         34, true,  false),
            array('-123.45',        123,        45, true,  false),
            array('$-123.45',       123,        45, true,  false),
            array('-$123.45',       123,        45, true,  false),
            array('1,234.56',       1234,       56, false, false),
            array('12,345.67',      12345,      67, false, false),
            array('123,456.78',     123456,     78, false, false),
            array('1,234,567.89',   1234567,    89, false, false),
            array('12,345,678.90',  12345678,   90, false, false),
            array('123,456,789.01', 123456789,  01, false, false),

            //exception cases: (TODO add more)
            array(null,             null,     null, null,  true),
            array(array(),          null,     null, null,  true),
            array(123.45,           null,     null, null,  true),
            array(123,              null,     null, null,  true),
            array('',               null,     null, null,  true),
        );
    }

    /**
     * @dataProvider currencyAsIntDataProvider
     */
    public function testCanBeCreatedFromInt($int, $dollars, $cents, $isNegative, $expectedException) {
        try {
            $currency = Currency_USD::fromInt($int);
            $this->assertFalse($expectedException, "Exception expected, but none thrown");

            $this->assertEquals($dollars, $currency->getDollars());
            $this->assertEquals($cents, $currency->getCents());
            $this->assertEquals($isNegative, $currency->isNegative());
        } catch(Currency_USD_Exception $ex) {
            $this->assertTrue($expectedException, "Exception thrown, but none expected");
        }
    }

    /**
     * Currency as int data provider
     * Returns array:
     *  - Representation
     *  - Dollars
     *  - Cents
     *  - IsNegative
     */
    public function currencyAsIntDataProvider() {
        return array(
            //    Int         Dollar  Cen IsNegative expException
            array(0,            0,      0,  false,  false),
            array(-0,           0,      0,  false,  false),
            array(1,            1,      0,  false,  false),
            array(10,           10,     0,  false,  false),
            array(100,          100,    0,  false,  false),
            array(-1,           1,      0,  true,   false),
            array(-10,          10,     0,  true,   false),
            array(-100,         100,    0,  true,   false),

            //exception cases:
            array(null,         null, null, null,   true),
            array(4.19,         null, null, null,   true),
            array('4.19',       null, null, null,   true),
            array('4',          null, null, null,   true),
            array('0',          null, null, null,   true),
            array(array(),      null, null, null,   true),
        );
    }

    /**
     * @dataProvider currencyAsDollarsAndCentsDataProvider
     */
    public function testCanBeCreatedFromDollarsAndCents($dollars, $cents, $isNegative) {
        $currency = Currency_USD::fromDollarsAndCents($dollars, $cents, $isNegative);
    }

    public function currencyAsDollarsAndCentsDataProvider() {
        return array(
            //    Dollars   Cents   IsNegative
            array(0,        0,      false),
            array(0,        0,      false),
            array(1,        0,      false),
            array(10,       0,      false),
            array(100,      0,      false),
            array(1,        0,      true),
            array(10,       0,      true),
            array(100,      0,      true),
        );
    }

    /**
     * @dataProvider invalidDollarsDataProvider
     */
    public function testExceptionIsThrownIfDollarsIsInvalid($dollars, $expectedException, $reasonForException = '') {
        try {
            Currency_USD::fromDollarsAndCents($dollars, 0);
            if ($expectedException) $this->fail("Expected exception because {$reasonForException}");
        } catch (Currency_USD_Exception $e) {
            if (!$expectedException) $this->fail("An unexpected exception was thrown");
        }
    }

    public function invalidDollarsDataProvider() {
        return array(
            //      dollars expectedException   reasonForException
            array(  0,      false,              null),
            array(  1,      false,              null),
            array(  10,     false,              null),
            array(  50,     false,              null),
            array(  99,     false,              null),
            array(  100,    false,              null),
            array(  101,    false,              null),
            array(  999999, false,              null),
            array(  -1,     true,               "dollars must be positive"),
            array(  0.5,    true,               "dollars must be an integer"),
            array(  null,   true,               "dollars cannot be null"),
        );
    }

    /**
     * @dataProvider invalidCentsDataProvider
     */
    public function testExceptionIsThrownIfCentsIsInvalid($cents, $expectedException, $reasonForException = '') {
        try {
            Currency_USD::fromDollarsAndCents(0, $cents);
            if ($expectedException) $this->fail("Expected exception because {$reasonForException}");
        } catch (Currency_USD_Exception $e) {
            if (!$expectedException) $this->fail("An unexpected exception was thrown");
        }
    }

    public function invalidCentsDataProvider() {
        return array(
            //      cents   expectedException   reasonForException
            array(  0,      false,              null),
            array(  1,      false,              null),
            array(  10,     false,              null),
            array(  50,     false,              null),
            array(  99,     false,              null),
            array(  -1,     true,               "cents must be positive"),
            array(  0.5,    true,               "cents must be an integer"),
            array(  100,    true,               "cents must be less than 100"),
            array(  null,   true,               "cents cannot be null"),
        );
    }

    /**
     * @dataProvider isNegativeDataProvider
     */
    public function testExceptionIsThrownIfIsNegativeIsNonBoolean($isNegative, $expectedException) {
        $currencyObj = Currency_USD::fromDollarsAndCents(1, 0);
        try {
            $currencyObj->setIsNegative($isNegative);
            if ($expectedException) $this->fail("Expected exception because isNegative must be true or false, got " . var_export($isNegative, true));
        } catch (Currency_USD_Exception $e) {
            if (!$expectedException) $this->fail("An unexpected exception was thrown");
        }
    }

    public function isNegativeDataProvider() {
        return array(
            //      value       expectedException
            array(  true,       false),
            array(  false,      false),
            array(  null,       true),
            array(  0,          true),
            array(  1,          true),
            array(  -1,         true),
        );
    }

    /**
     * @dataProvider currencyAsFloatDataProvider
     */
    public function testCanBeCreatedFromFloat($float, $dollars, $cents, $isNegative, $expectedException) {
        try {
            $currency = Currency_USD::fromFloat($float);
            $this->assertFalse($expectedException, "Exception expected, but none thrown");

            $this->assertEquals($dollars, $currency->getDollars());
            $this->assertEquals($cents, $currency->getCents());
            $this->assertEquals($isNegative, $currency->isNegative());
        } catch(Currency_USD_Exception $ex) {
            $this->assertTrue($expectedException, "Exception thrown, but none expected");
        }
    }

    /**
     * Currency as float data provider
     * Returns array:
     *  - Representation
     *  - Dollars
     *  - Cents
     *  - IsNegative
     */
    public function currencyAsFloatDataProvider() {
        return array(
            //    Float         Dollar  Cen IsNeg  expException
            array(0.0,          0,      0,  false, false),
            array(0.5,          0,      50, false, false),
            array(0.50,         0,      50, false, false),
            array(1.0,          1,      0,  false, false),
            array(10.0,         10,     0,  false, false),
            array(100.0,        100,    0,  false, false),
            array(123.45,       123,    45, false, false),
            array(123.4,        123,    40, false, false),
            array(-0.0,         0,      0,  false, false),
            array(-0.5,         0,      50, true,  false),
            array(-0.50,        0,      50, true,  false),
            array(-1.0,         1,      0,  true,  false),
            array(-10.0,        10,     0,  true,  false),
            array(-100.0,       100,    0,  true,  false),
            array(-123.45,      123,    45, true,  false),

            //exception cases:
            array('string',     null, null, null,  true),
            array(null,         null, null, null,  true),
            array('0',          null, null, null,  true),
            array('3.05',       null, null, null,  true),
            array(array(),      null, null, null,  true),
        );
    }

    /**
     * @dataProvider fromFloatValuesTestDataProvider
     */
    public function testCreatingFromInvalidFloatThrowsException($invalidFloat) {
        try {
            $currency = Currency_USD::fromFloat($invalidFloat);
            $this->fail("Expected failure because {$invalidFloat} is an invalid float");
        } catch (Currency_USD_Exception $e) {
            // We expected this exception
        }
    }

    public function fromFloatValuesTestDataProvider() {
        return array(
                    array(123.456),
                    array(123.123),
                    array(123.999),
                    array(""),
                    array(123.001),
                    array(123.50001),
                    array("123.50001"),
                    array(0.123456),
                    array(999.999),
                    array(999.019),
                    array(true),
                    array(false),
                    array("string"),
                    array("one"),
                    array("1"),
                    array("1.00"),
                    array(null),
                );
    }

    public function testToDecimalWorksWithWholeNumbers() {
        $currency = Currency_USD::fromInt(10);
        $this->assertEquals(10.00, $currency->toDecimal());
    }

    public function testToDecimalWorksWithDecimalNumbers() {
        $currency = Currency_USD::fromFloat(12.34);
        $this->assertEquals(12.34, $currency->toDecimal());
    }

    public function testToDecimalWorksWithNegativeNumbers() {
        $currency = Currency_USD::fromFloat(-12.34);
        $this->assertEquals(-12.34, $currency->toDecimal());
    }

    public function testToDecimalWorksWithNegativeZero() {
        $currency = Currency_USD::fromFloat(-0.00);
        $this->assertEquals(0.00, $currency->toDecimal());

        $currency = Currency_USD::fromInt(-0);
        $this->assertEquals(0.00, $currency->toDecimal());
    }

    /**
     * @dataProvider currencySumDataProvider
     */
    public function testAddingTwoCurrencyObjectsWorksProperly($floatValue1, $floatValue2, $sumAsFloat) {
        $currency1 = Currency_USD::fromFloat($floatValue1);
        $currency2 = Currency_USD::fromFloat($floatValue2);
        $sumObject = Currency_USD::fromFloat($sumAsFloat);
        $this->assertTrue($currency1->add($currency2)->equals($sumObject));
    }

    public function currencySumDataProvider() {
        return array(
            //      Value1  Value2  Sum
            array(  0.00,   0.00,   0.00),
            array(  1.00,   0.00,   1.00),
            array(  1.00,   1.00,   2.00),
            array(  1.00,   2.00,   3.00),
            array(  1.11,   0.00,   1.11),
            array(  1.11,   1.11,   2.22),
            array(  1.11,   2.22,   3.33),
            array(  -1.00,  0.00,   -1.00),
            array(  -1.00,  -1.00,  -2.00),
            array(  -1.00,  -2.00,  -3.00),
            array(  -1.11,  0.00,   -1.11),
            array(  -1.11,  -1.11,  -2.22),
            array(  -1.11,  -2.22,  -3.33),
            array(  -1.00,  0.00,   -1.00),
            array(  -1.00,  1.00,   0.00),
            array(  -1.00,  2.00,   1.00),
            array(  -1.11,  0.00,   -1.11),
            array(  -1.11,  1.11,   0.00),
            array(  -1.11,  2.22,   1.11),
            array(  1.10,   2.20,   3.30),
            array(  1.50,   1.51,   3.01),
        );
    }

    /**
     * @dataProvider currencySubtractionDataProvider
     */
    public function testSubtractingTwoCurrencyObjectsWorksProperly($floatValue1, $floatValue2, $differenceAsFloat) {
        $currency1 = Currency_USD::fromFloat($floatValue1);
        $currency2 = Currency_USD::fromFloat($floatValue2);
        $differenceObject = Currency_USD::fromFloat($differenceAsFloat);
        $this->assertTrue($currency1->subtract($currency2)->equals($differenceObject));
    }

    public function currencySubtractionDataProvider() {
        return array(
            //      Value1  Value2  Difference
            array(  0.00,   0.00,   0.00),
            array(  1.00,   1.00,   0.00),
            array(  0.00,   1.00,   -1.00),
            array(  1.00,   2.00,   -1.00),
            array(  2.00,   1.00,   1.00),
            array(  1.11,   0.00,   1.11),
            array(  0.00,   1.11,   -1.11),
            array(  1.11,   1.11,   0.00),
            array(  1.11,   2.22,   -1.11),
            array(  2.22,   1.11,   1.11),
            array(  -1.00,  0.00,   -1.00),
            array(  0.00,   -1.00,  1.00),
            array(  -1.00,  -1.00,  0.00),
            array(  -1.00,  -2.00,  1.00),
            array(  -2.00,  -1.00,  -1.00),
            array(  -1.11,  0.00,   -1.11),
            array(  0.00,   -1.11,  1.11),
            array(  -1.11,  -1.11,  0.00),
            array(  -1.11,  -2.22,  1.11),
            array(  -2.22,  -1.11,  -1.11),
            array(  -1.00,  0.00,   -1.00),
            array(  0.00,  -1.00,   1.00),
            array(  -1.00,  1.00,   -2.00),
            array(  1.00,   -1.00,  2.00),
            array(  -1.00,  2.00,   -3.00),
            array(  2.00,   -1.00,  3.00),
            array(  -1.11,  0.00,   -1.11),
            array(  0.00,   -1.11,  1.11),
            array(  -1.11,  1.11,   -2.22),
            array(  1.11,   -1.11,  2.22),
            array(  -1.11,  2.22,   -3.33),
            array(  2.22,   -1.11,  3.33),
            array(  1.10,   2.20,   -1.10),
            array(  2.20,   1.10,   1.10),
            array(  1.50,   1.51,   -0.01),
            array(  1.51,   1.50,   0.01),
            array(  1.00,   0.01,   0.99),
        );
    }

    /**
     * @dataProvider currencyMultiplicationDataProvider
     */
    public function testMultiplyingByScalarWorksProperly($floatValue, $scalar, $whatToDoWithPartialCents, $productAsFloat, $expectedException = false) {
        try {
            $currencyObj = Currency_USD::fromFloat($floatValue);
            $calculatedProductObj = $currencyObj->multiply($scalar, $whatToDoWithPartialCents);
            if ($expectedException) {
                $this->fail("Expected an exception here");
            }
        } catch (Currency_USD_Exception $e) {
            if (!$expectedException) {
                $this->fail("Did not expect an exception here, but got one");
            } else {
                return;
            }
        }
        $givenProductObj = Currency_USD::fromFloat($productAsFloat);
        $this->assertTrue($givenProductObj->equals($calculatedProductObj));
    }

    public function currencyMultiplicationDataProvider() {
        $throwException = Currency_USD::PARTIAL_CENTS_THROW_EXCEPTION;
        $roundDown      = Currency_USD::PARTIAL_CENTS_ROUND_DOWN;
        $roundUp        = Currency_USD::PARTIAL_CENTS_ROUND_UP;
        $roundNearest   = Currency_USD::PARTIAL_CENTS_ROUND_NEAREST;

        return array(
            //      Float   Scalar  OnError             Product     ExpectedExeception
            array(  0.00,   0,      $roundNearest,      0.00,       false),
            array(  0.00,   1,      $roundNearest,      0.00,       false),
            array(  1.00,   0,      $roundNearest,      0.00,       false),
            array(  -1.00,  0,      $roundNearest,      0.00,       false),
            array(  1.00,   -0,     $roundNearest,      0.00,       false),
            array(  1.00,   0.25,   $roundNearest,      0.25,       false),
            array(  4.00,   0.25,   $roundNearest,      1.00,       false),
            array(  1.00,   0.333,  $throwException,    null,       true),
            array(  1.00,   0.333,  $roundDown,         0.33,       false),
            array(  1.00,   0.333,  $roundUp,           0.34,       false),
            array(  1.00,   0.333,  $roundNearest,      0.33,       false),
            array(  1.00,   0.666,  $roundNearest,      0.67,       false),
            array(  -1.00,  0.333,  $throwException,    null,       true),
            array(  -1.00,  0.333,  $roundDown,         -0.34,      false),
            array(  -1.00,  0.333,  $roundUp,           -0.33,      false),
            array(  -1.00,  0.333,  $roundNearest,      -0.33,      false),
            array(  -1.00,  0.666,  $roundNearest,      -0.67,      false),
        );
    }

    /**
     * @dataProvider currencyDivisionDataProvider
     */
    public function testDividingByScalarWorksProperly($floatValue, $scalar, $whatToDoWithPartialCents, $quotientAsFloat, $expectedException = false) {
        try {
            $currencyObj = Currency_USD::fromFloat($floatValue);
            $calculatedQuotientObj = $currencyObj->divide($scalar, $whatToDoWithPartialCents);
            if ($expectedException) {
                $this->fail("Expected an exception here");
            }
        } catch (Currency_USD_Exception $e) {
            if (!$expectedException) {
                $this->fail("Did not expect an exception here, but got one");
            } else {
                return;
            }
        }

        $givenQuotientObj = Currency_USD::fromFloat($quotientAsFloat);
        $this->assertTrue($givenQuotientObj->equals($calculatedQuotientObj));
    }

    public function currencyDivisionDataProvider() {
        $throwException = Currency_USD::PARTIAL_CENTS_THROW_EXCEPTION;
        $roundDown      = Currency_USD::PARTIAL_CENTS_ROUND_DOWN;
        $roundUp        = Currency_USD::PARTIAL_CENTS_ROUND_UP;
        $roundNearest   = Currency_USD::PARTIAL_CENTS_ROUND_NEAREST;

        return array(
            //      Float   Scalar  OnError             Product     ExpectedExeception
            array(  0.00,   1,      $roundNearest,      0.00,       false),
            array(  0.00,   1,      $roundNearest,      0.00,       false),
            array(  1.00,   1,      $roundNearest,      1.00,       false),
            array(  -1.00,  1,      $roundNearest,      -1.00,      false),
            array(  1.00,   -1,     $roundNearest,      -1.00,      false),
            array(  1.00,   4,      $roundNearest,      0.25,       false),
            array(  4.00,   4,      $roundNearest,      1.00,       false),
            array(  1.00,   3,      $throwException,    null,       true),
            array(  1.00,   3,      $roundDown,         0.33,       false),
            array(  1.00,   3,      $roundUp,           0.34,       false),
            array(  1.00,   3,      $roundNearest,      0.33,       false),
            array(  2.00,   3,      $roundNearest,      0.67,       false),
            array(  -1.00,  3,      $throwException,    null,       true),
            array(  -1.00,  3,      $roundDown,         -0.34,      false),
            array(  -1.00,  3,      $roundUp,           -0.33,      false),
            array(  -1.00,  3,      $roundNearest,      -0.33,      false),
            array(  -2.00,  3,      $roundNearest,      -0.67,      false),
        );
    }

    public function testOverflowPositiveThrowsException() {
        try {
            $currencyObj = Currency_USD::fromDollarsAndCents(PHP_INT_MAX + 1, 0);
            $this->fail("Expected exception but got none");
        } catch (Currency_USD_Invalid_Value_Exception $e) {
            // Expected exception
        }
    }

    public function testOverflowNegativeThrowsException() {
        try {
            $currencyObj = Currency_USD::fromDollarsAndCents(-1 * PHP_INT_MAX - 1, 0);
            $this->fail("Expected exception but got none");
        } catch (Currency_USD_Invalid_Value_Exception $e) {
            // Expected exception
        }
    }

    public function testCanBeSerializedAndUnserializedPrecisely() {
        $beforeSerialization    = Currency_USD::fromDollarsAndCents(1, 23, true);
        $serialized             = serialize($beforeSerialization);
        $unserialized           = unserialize($serialized);

        // Make sure the objects are equal
        $this->assertTrue($unserialized instanceof Currency_USD);
        $this->assertTrue($beforeSerialization->equals($unserialized));
    }

    /**
     * @dataProvider toWordsDataProvider
     */
    public function testToWordsWorksCorrectly($floatValue, $inWords) {
        $currencyObj = Currency_USD::fromFloat($floatValue);
        $this->assertEquals($inWords, $currencyObj->toWords());
    }

    public function toWordsDataProvider() {
        return array(
            //      FloatVal    In Words
            array(  0,          'Zero dollars' ),
            array(  -0,         'Zero dollars' ),
            array(  0.00,       'Zero dollars' ),
            array(  -0.00,      'Zero dollars' ),
            array(  0.01,       'One cent' ),
            array(  0.10,       'Ten cents' ),
            array(  0.11,       'Eleven cents' ),
            array(  0.12,       'Twelve cents' ),
            array(  0.13,       'Thirteen cents' ),
            array(  0.14,       'Fourteen cents' ),
            array(  0.15,       'Fifteen cents' ),
            array(  0.16,       'Sixteen cents' ),
            array(  0.17,       'Seventeen cents' ),
            array(  0.18,       'Eighteen cents' ),
            array(  0.19,       'Nineteen cents' ),
            array(  0.20,       'Twenty cents' ),
            array(  0.21,       'Twenty-one cents' ),
            array(  0.30,       'Thirty cents' ),
            array(  0.40,       'Forty cents' ),
            array(  0.50,       'Fifty cents' ),
            array(  0.60,       'Sixty cents' ),
            array(  0.70,       'Seventy cents' ),
            array(  0.80,       'Eighty cents' ),
            array(  0.90,       'Ninety cents' ),
            array(  0.99,       'Ninety-nine cents' ),
            array(  1.10,       'One dollar and ten cents' ),
            array(  1.99,       'One dollar and ninety-nine cents' ),
            array(  10,         'Ten dollars' ),
            array(  11.99,      'Eleven dollars and ninety-nine cents' ),
            array(  99,         'Ninety-nine dollars' ),
            array(  99.99,      'Ninety-nine dollars and ninety-nine cents' ),
            array(  100,        'One hundred dollars' ),
            array(  100.99,     'One hundred dollars and ninety-nine cents' ),
            array(  111.99,     'One hundred eleven dollars and ninety-nine cents' ),
            array(  1111.99,    'One thousand one hundred eleven dollars and ninety-nine cents' ),
            array(  11111.99,   'Eleven thousand one hundred eleven dollars and ninety-nine cents' ),
            array(  111111.99,  'One hundred eleven thousand one hundred eleven dollars and ninety-nine cents' ),
            array(  1000000,    'One million dollars' ), // Lifts pinky like Dr. Evil
            array(  -0.01,      'Negative one cent' ),
            array(  -0.10,      'Negative ten cents' ),
            array(  -0.99,      'Negative ninety-nine cents' ),
            array(  -1.10,      'Negative one dollar and ten cents' ),
            array(  -1.99,      'Negative one dollar and ninety-nine cents' ),
        );
    }

    /**
     * @dataProvider formattedStringDataProvider
     */
    public function testFormattedStringFormatsCurrencyCorrectly($amountAsFloat, $amountAsString) {
        $currencyObj = Currency_USD::fromFloat($amountAsFloat);
        $this->assertEquals("{$amountAsString}", $currencyObj->formattedString(false));
        $this->assertEquals("\${$amountAsString}", $currencyObj->formattedString(true));
    }

    public function formattedStringDataProvider() {
        return array(
            array(123.45,       "123.45"),
            array(1.00,         "1.00"),
            array(1.50,         "1.50"),
            array(0.50,         "0.50"),
            array(0.00,         "0.00"),
            array(158.70,       "158.70"),
            array(158.07,       "158.07"),
            array(-158.70,      "-158.70"),
        );
    }
}
