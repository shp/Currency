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

class Currency_USD {

    private $_dollars                   = 0;
    private $_cents                     = 0;
    private $_isNegative                = false;
    const PARTIAL_CENTS_THROW_EXCEPTION = 'throw exception';
    const PARTIAL_CENTS_ROUND_UP        = 'round up';
    const PARTIAL_CENTS_ROUND_DOWN      = 'round down';
    const PARTIAL_CENTS_ROUND_NEAREST   = 'round nearest';

    /**
     * Create a Currency_USD from a string.
     *
     * @param string $strVal Input string in format $-123.45.
     *
     * @throws Currency_USD_Exception If we are unable to parse the string into a currency.
     * @return Currency_USD Currency object
     */
    public static function fromString($strVal) {
        $regex   = "/^[\$]?(\-?)[\$]?([\d,]*)\.?([\d]{0,2})$/";
        $matches = array();
        $result  = preg_match($regex, $strVal, $matches);

        if ($result == 0) {
            throw new Currency_USD_Exception("Unable to parse string '{$strVal}' as currency");
        }
        $dollars    = (isset($matches[2]) ? self::_cleanDollarAmount($matches[2]) : 0);
        $cents      = (isset($matches[3]) ? self::_parseCentsFromString($matches[3]) : 0);
        $isNegative = (isset($matches[1]) && $matches[1] == '-');

        return self::fromDollarsAndCents($dollars, $cents, $isNegative);
    }

    /**
     * Create a Currency_USD object from an int.
     *
     * @param integer $intVal The integer you want to tunr into a Currency_USD object.
     *
     * @throws Currency_USD_Exception If input value is not an integer.
     * @return Currency_USD The value of intVal as a Currency_USD object.
     */
    public static function fromInt($intVal) {
        $positiveValue = abs($intVal);
        $isNegative    = ($intVal < 0);
        return self::fromDollarsAndCents($positiveValue, 0, $isNegative);
    }

    /**
     * Create a Currency_USD object from dollars, cents, and optionally an isNegative flag.
     *
     * @param integer $dollars    The number of dollars.
     * @param integer $cents      The number of cents.
     * @param integer $isNegative Whether the object should be negative. Default false (positive).
     *
     * @throws Currency_USD_Exception If dollars is non-integer.
     * @throws Currency_USD_Exception If cents is non-integer.
     * @throws Currency_USD_Exception If isNegative is non-boolean.
     * @return Currency_USD The Currency_USD object.
     */
    public static function fromDollarsAndCents($dollars, $cents, $isNegative = false) {
        $currencyObj = new Currency_USD();
        $currencyObj->setDollars($dollars);
        $currencyObj->setCents($cents);
        $currencyObj->setIsNegative($isNegative);
        return $currencyObj;
    }

    /**
     * Create a Currency_USD object from a floating point number.
     *
     * @param float $floatVal The value of the currency object as a float.
     *
     * @throws Currency_USD_Exception If the float value has partial cents (e.g. 123.456).
     * @return Currency_USD The Currency_USD object.
     */
    public static function fromFloat($floatVal) {
        // Instead of doing a bunch of bc_ math here,
        // we can simply convert to string and use
        // our fromString method here.  I have yet to
        // find a test case that doesn't work this
        // way.
        return self::fromString($floatVal . '');
    }

    /**
     * Create a currency object from the number of cents, for example, -123 cents becomes -$1.23.
     *
     * @param integer $numCents The number of cents we want to represent as a Currency_USD.
     *
     * @throws Currency_USD_Exception If numCents is non-integer.
     * @throws Currency_USD_Exception If numCents is greater than PHP_INT_MAX.
     * @return Currency_USD The Currency_USD object.
     */
    public static function fromNumCents($numCents) {
        $positiveNumCents = abs($numCents);

        if ($positiveNumCents > PHP_INT_MAX) {
            $message = "Overflow, {$numCents} is greater than PHP_INT_MAX: " . PHP_INT_MAX;
            throw new Currency_USD_Exception($message);
        }

        $dollars    = intVal(floor($positiveNumCents / 100));
        $cents      = $positiveNumCents % 100;
        $isNegative = ($numCents < 0);
        return self::fromDollarsAndCents($dollars, $cents, $isNegative);
    }

    /**
     * The value of this object as a decimal (float).
     *
     * @return float The value of this object as a float.
     */
    public function toDecimal() {
        $value = $this->getDollars() + $this->getCents() / 100;
        if ($this->isNegative()) {
            $value *= -1;
        }
        return $value;
    }

    /**
     * This object represented as an integer number of cents.
     *
     * @return integer The number of cents.
     */
    public function toNumCents() {
        $value = $this->getDollars() * 100 + $this->getCents();
        if ($this->isNegative()) {
            $value *= -1;
        }
        return $value;
    }

    /**
     * Format this object as a string, with or without a dollar sign.
     *
     * @param boolean $includeDollarSign True includes dollar sign, default no dollar sign.
     *
     * @throws Currency_USD_Exception If includeDollarSign is not boolean.
     * @return string A string representation of this object.
     */
    public function formattedString($includeDollarSign = false) {
        if ($includeDollarSign !== true && $includeDollarSign !== false) {
            throw new Currency_USD_Exception('Please specify true or false for $includeDollarSign');
        }

        $dollarSign = '';
        if ($includeDollarSign) {
            $dollarSign = '$';
        }
        $negativeSign = '';
        if ($this->isNegative()) {
            $negativeSign = '-';
        }
        return "{$dollarSign}{$negativeSign}{$this->getDollars()}.{$this->_getTwoDigitNumCents()}";
    }

    /**
     * The default representation of this object: a string with no dollar sign.
     *
     * @return string A string representation of this object.
     */
    public function __toString() {
        return $this->formattedString(false);
    }

    /**
     * Returns a representation like "Three dollars and sixteen cents" for $3.16.
     *
     * @return string The value of this object as spoken words.
     */
    public function toWords() {
        return ucfirst($this->_composeSpokenAmount());
    }

    /**
     * Add another Currency_USD object to this object.
     *
     * @param Currency_USD $currencyObj The currency object to add to this object.
     *
     * @return Currency_USD The sum of the two objects as a Currency_USD object.
     */
    public function add(Currency_USD $currencyObj) {
        $numCents1 = $this->toNumCents();
        $numCents2 = $currencyObj->toNumCents();

        $sum      = $numCents1 + $numCents2;
        $currency = self::fromNumCents($sum);
        return $currency;
    }

    /**
     * Subtract a Currency_USD object from this object.
     *
     * @param Currency_USD $currencyObj The Currency_USD object to subtract from this object.
     *
     * @return Currency_USD The difference of the two Currency_USD objects as a Currency_USD object.
     */
    public function subtract(Currency_USD $currencyObj) {
        $numCents1 = $this->toNumCents();
        $numCents2 = $currencyObj->toNumCents();

        $currency = self::fromNumCents($numCents1 - $numCents2);
        return $currency;
    }

    /**
     * Multiply this Currency_USD object by a scalar (int or float).
     *
     * @param mixed  $scalar                   The scalar (float or int) to multiply this object by.
     * @param string $whatToDoWithPartialCents What we should do with partial cents--either round them or throw an exception.
     *
     * @see    Class constant PARTIAL_CENTS_ROUND_NEAREST
     * @see    Class constant PARTIAL_CENTS_ROUND_DOWN
     * @see    Class constant PARTIAL_CENTS_ROUND_UP
     * @see    Class constant PARTIAL_CENTS_THROW_EXCEPTION
     * @throws Currency_USD_Exception If we are set to throw exceptions on partial cents, exception is thrown if multiplication results in partial cents.
     * @return Currency_USD The product as a Currency_USD object.
     */
    public function multiply($scalar, $whatToDoWithPartialCents = self::PARTIAL_CENTS_THROW_EXCEPTION) {
        $numCents = $this->toNumCents();
        $product  = $numCents * $scalar;

        if (is_float($product) && $whatToDoWithPartialCents == self::PARTIAL_CENTS_THROW_EXCEPTION) {
            throw new Currency_USD_Exception("Multiply resulted in partial cents");
        }

        if (is_float($product) && $whatToDoWithPartialCents == self::PARTIAL_CENTS_ROUND_UP) {
            $product = round($product + 0.5);
        }

        if (is_float($product) && $whatToDoWithPartialCents == self::PARTIAL_CENTS_ROUND_DOWN) {
            $product = round($product - 0.5);
        }

        if (is_float($product) && $whatToDoWithPartialCents == self::PARTIAL_CENTS_ROUND_NEAREST) {
            $product = round($product);
        }

        return self::fromNumCents($product);
    }

    /**
     * Divide this Currency_USD object by a scalar (int or float).
     *
     * @param mixed  $scalar                   The scalar (float or int) to divide this object by.
     * @param string $whatToDoWithPartialCents What we should do with partial cents--either round them or throw an exception.
     *
     * @see    Class constant PARTIAL_CENTS_ROUND_NEAREST
     * @see    Class constant PARTIAL_CENTS_ROUND_DOWN
     * @see    Class constant PARTIAL_CENTS_ROUND_UP
     * @see    Class constant PARTIAL_CENTS_THROW_EXCEPTION
     * @throws Currency_USD_Exception If we are set to throw exceptions on partial cents, exception is thrown if division results in partial cents.
     * @return Currency_USD The quotient as a Currency_USD object.
     */
    public function divide($scalar, $whatToDoWithPartialCents = self::PARTIAL_CENTS_THROW_EXCEPTION) {
        $numCents = $this->toNumCents();
        $quotient = $numCents / $scalar;

        if (is_float($quotient) && $whatToDoWithPartialCents == self::PARTIAL_CENTS_THROW_EXCEPTION) {
            throw new Currency_USD_Exception("Divide resulted in partial cents");
        }

        if (is_float($quotient) && $whatToDoWithPartialCents == self::PARTIAL_CENTS_ROUND_UP) {
            $quotient = round($quotient + 0.5);
        }

        if (is_float($quotient) && $whatToDoWithPartialCents == self::PARTIAL_CENTS_ROUND_DOWN) {
            $quotient = round($quotient - 0.5);
        }

        if (is_float($quotient) && $whatToDoWithPartialCents == self::PARTIAL_CENTS_ROUND_NEAREST) {
            $quotient = round($quotient);
        }

        return self::fromNumCents($quotient);
    }

    /**
     * Validate a dollar amount.
     *
     * @param integer $dollars The integer number of dollars.
     *
     * @throws Currency_USD_Exception If dollars is non-int, negative, or greater than PHP_INT_MAX.
     * @return void
     */
    public function validateDollars($dollars) {
        if (!is_int($dollars)) {
            throw new Currency_USD_Exception("Dollars must be an int, got: " . var_export($dollars, true));
        }
        if ($dollars < 0) {
            throw new Currency_USD_Exception("Dollars must be greater than 0, set negative values separately");
        }
        if (abs($dollars) > PHP_INT_MAX) {
            throw new Currency_USD_Exception("Overflow, {$dollars} is greater than PHP_INT_MAX: " . PHP_INT_MAX);
        }
    }

    /**
     * Validate a cents amount.
     *
     * @param integer $cents The integer number of cents.
     *
     * @throws Currency_USD_Exception If cents is non-int, negative, or greater than or equal to 100.
     * @return void
     */
    public function validateCents($cents) {
        if (!is_int($cents)) {
            throw new Currency_USD_Exception("Cents must be an int");
        }
        if ($cents >= 100) {
            throw new Currency_USD_Exception("Cents must be less than 100, use dollars and cents instead");
        }
        if ($cents < 0) {
            throw new Currency_USD_Exception("Cents must be greater than 0, set negative values separately");
        }
    }

    /**
     * Validate that the passed value for isNegative is boolean.
     *
     * @param boolean $isNegative The boolean value for isNegative.
     *
     * @throws Currency_USD_Exception If isNegative is non-boolean.
     * @return boolean True if the value is ok, otherwise exception is thrown.
     */
    public function validateIsNegative($isNegative) {
        if ($isNegative === true) {
            return true;
        }

        if ($isNegative === false) {
            return true;
        }

        throw new Currency_USD_Exception("Is negative must be either true or false");
    }

    /**
     * Accessor for number of dollars. Does not include cents.
     *
     * @return integer The number of dollars in this object.
     */
    public function getDollars() {
        return $this->_dollars;
    }

    /**
     * Modifier for number of dollars. Does not modify cents, Dollars only.
     *
     * @param integer $value The integer value of dollars; must be positive.
     *
     * @return void
     */
    public function setDollars($value) {
        $this->validateDollars($value);
        $this->_dollars = $value;
    }

    /**
     * Accessor for cents.
     *
     * @return integer Number of cents, not including dollars, so $1.50 would return int(50).
     */
    public function getCents() {
        return $this->_cents;
    }

    /**
     * Modifier for cents. Only modifies cents, does not affect dollars.
     *
     * @param integer $value Number of cents, must be integer between 0 and 99.
     *
     * @return void
     */
    public function setCents($value) {
        $this->validateCents($value);
        $this->_cents = $value;
    }

    /**
     * Accessor for isNegative, returns true if the object is negative.
     *
     * @return boolean True if the object is negative, otherwise false.
     */
    public function isNegative() {
        return $this->getIsNegative();
    }

    /**
     * Alias for isNegative().
     *
     * @return boolean True if the object is negative, otherwise false.
     */
    public function getIsNegative() {
        return $this->_isNegative;
    }

    /**
     * Modifier for isNegative. Sets whether the object is negative or not.
     *
     * @param boolean $value True if the object should be made negative, false if the object should be made positive.
     *
     * @return void
     */
    public function setIsNegative($value) {
        $this->validateIsNegative($value);
        $this->_isNegative = $value;
    }

    /**
     * Test whether or not two objects have the same value (number of cents, number of dollars, and negative sign).
     *
     * @param Currency_USD $currencyObj The object with which to test equality with this object.
     *
     * @return boolean True if the objects have the same currency value.
     */
    public function equals(Currency_USD $currencyObj) {
        return $this->toNumCents() == $currencyObj->toNumCents();
    }

    /**
     * Compare two Currency_USD objects. Return 0 if they are equal, 1 if the first is greater, -1 if the second is greater. Use this function with php's sort functions.
     *
     * @param Currency_USD $a The first Currency_USD object to compare.
     * @param Currency_USD $b The second Currency_USD object to compare.
     *
     * @throws Currency_USD_Exception If the comparison results in none of: equal, less than, or greater than.
     * @return integer 0 if the objects are equal, 1 if a > b, -1 if b > a
     */
    public static function compare(Currency_USD $a, Currency_USD $b) {
        // Return  1 if a is greater than b
        if ($a->isGreaterThan($b)) {
            return 1;
        }

        // Return -1 if a is less than b
        if ($a->isLessThan($b)) {
            return 1;
        }

        // Return  0 if a is equal to b
        if ($a->equals($b)) {
            return 0;
        }

        // Otherwise throw an exception
        throw new Currency_USD_Exception("Unexpected comparison value");
    }

    /**
     * Compare this object to another Currency_USD object. Return 0 if the objects are equal, 1 if this object is greater, -1 if this object is less.
     *
     * @param Currency_USD $compareTo Object to compare this object to.
     *
     * @return integer 0 if the objects are equal, 1 if this object is greater, -1 if this object is less.
     */
    public function compareTo(Currency_USD $compareTo) {
        return self::compare($this, $compareTo);
    }

    /**
     * Test whether this object is greater than another Currency_USD object.
     *
     * @param Currency_USD $currencyObj The object to which we want to compare.
     *
     * @return boolean True if this object is greater than the other object, otherwise false.
     */
    public function isGreaterThan(Currency_USD $currencyObj) {
        return $this->toNumCents() > $currencyObj->toNumCents();
    }

    /**
     * Test whether this object is less than another Currency_USD object.
     *
     * @param Currency_USD $currencyObj The object to which we want to compare.
     *
     * @return boolean True if this object is less than the other object, otherwise false.
     */
    public function isLessThan(Currency_USD $currencyObj) {
        return $this->toNumCents() < $currencyObj->toNumCents();
    }

    /**
     * Remove commas and turn the dollar amount into an int.
     *
     * @param string $amountAsString The amount to turn into an int.
     *
     * @return integer The amount as an integer.
     */
    private static function _cleanDollarAmount($amountAsString) {
        $dollarAmountWithoutCommas = str_replace(',', '', $amountAsString);
        $integerAmount             = intVal($dollarAmountWithoutCommas);
        return $integerAmount;
    }

    /**
     * Parse the number of cents from a string.
     *
     * @param string $centsStr The string with cents in it.
     *
     * @throws Currency_USD_Exception If we were unable to parse cents from the string.
     * @return integer The integer value of the cents part of the string.
     */
    private static function _parseCentsFromString($centsStr) {
        if ($centsStr === null) {
            return 0;
        }

        if ($centsStr == '') {
            return 0;
        }

        if (strlen($centsStr) == 2) {
            return intVal($centsStr);
        }

        // centsStr has 1 digit (e.g. '123.4', which should be translated to 40 cents
        if (strlen($centsStr) == 1) {
            return (intVal($centsStr) * 10);
        }

        throw new Currency_USD_Exception("Unable to parse cents: " . var_export($centsStr, true));
    }

    /**
     * Get a two-digit string representation of the number of cents.
     *
     * @return string The two-digit string representation.
     */
    private function _getTwoDigitNumCents() {
        if ($this->getCents() < 10) {
            return "0{$this->getCents()}";
        }
        return "{$this->getCents()}";
    }

    /**
     * Compose the spoken amounts of this object into a spoken-word string.
     *
     * @return string The value of this object as unformatted spoken words.
     */
    private function _composeSpokenAmount() {
        $speakableDollars  = new Currency_SpeakableInteger($this->getDollars());
        $spokenDollarsWord = ($this->getDollars() == 1 ? 'dollar' : 'dollars');
        $spokenDollars     = "{$speakableDollars->toWords()} {$spokenDollarsWord}";
        $speakableCents    = new Currency_SpeakableInteger($this->getCents());
        $spokenCentsWord   = ($this->getCents() == 1 ? 'cent' : 'cents');
        $spokenCents       = "{$speakableCents->toWords()} {$spokenCentsWord}";
        $spokenSign        = ($this->isNegative() ? 'Negative' : '');

        if ($this->getDollars() == 0 && $this->getCents() == 0) {
            return trim(strtolower("{$spokenSign} {$spokenDollars}"));
        }

        if ($this->getCents() == 0) {
            // Do not include cents if there are none
            return trim(strtolower("{$spokenSign} {$spokenDollars}"));
        }

        if ($this->getDollars() == 0) {
            // Do not include dollars if there are none
            return trim(strtolower("{$spokenSign} {$spokenCents}"));
        }

        return trim(strtolower("{$spokenSign} {$spokenDollars} and {$spokenCents}"));
    }

}

class Currency_USD_Exception extends Exception {
}
