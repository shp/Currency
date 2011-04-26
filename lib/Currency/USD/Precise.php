<?php

class Currency_USD_Precise extends Currency_USD{

    private $_dollars                   = 0;
    private $_cents                     = 0;
    private $_partialCents              = 0;
    private $_isNegative                = false;

    /**
     * Create a Currency_USD_Precise from a string.
     *
     * @param string $strVal Input string in format $-123.4567.
     *
     * @throws Currency_USD_Precise_Exception If we are unable to parse the string into a currency.
     * @throws Currency_USD_Precise_Invalid_Value_Exception If the input value is invalid.
     * @return Currency_USD_Precise Currency object
     */
    public static function fromString($strVal) {
        if ($strVal === null) {
            throw new Currency_USD_Precise_Invalid_Value_Exception("\$strVal cannot be null.");
        }
        if (!is_string($strVal)) {
            throw new Currency_USD_Precise_Invalid_Value_Exception("\$strVal is not a string");
        }
        if ($strVal === "") {
            throw new Currency_USD_Precise_Invalid_Value_Exception("\$strVal cannot be empty-string.");
        }

        $regex   = "/^[\$]?(\-?)[\$]?([\d,]*)\.?([\d]{0,2})?([\d]{0,4})$/";
        $matches = array();
        $result  = preg_match($regex, $strVal, $matches);

        if ($result == 0) {
            throw new Currency_USD_Precise_Exception("Unable to parse string '{$strVal}' as currency");
        }
        $dollars        = (isset($matches[2]) ? self::_cleanDollarAmount($matches[2]) : 0);
        $cents          = (isset($matches[3]) ? self::_parseCentsFromString($matches[3]) : 0);
        $partialCents   = (isset($matches[4]) ? self::_parsePartialCentsFromString($matches[4]) : 0);
        $isNegative     = (isset($matches[1]) && $matches[1] == '-');

        return self::fromDollarsCentsAndPartialCents($dollars, $cents, $partialCents, $isNegative);
    }

    /**
     * Create a Currency_USD_Precise object from an int.
     *
     * @param integer $intVal The integer you want to turn into a Currency_USD_Precise object.
     *
     * @throws Currency_USD_Precise_Exception If input value is not an integer.
     * @throws Currency_USD_Precise_Invalid_Value_Exception If input value is null.
     * @return Currency_USD_Precise The value of intVal as a Currency_USD object.
     */
    public static function fromInt($intVal) {
        if ($intVal === null) {
            throw new Currency_USD_Precise_Invalid_Value_Exception("\$intVal cannot be null.");
        }
        if (!is_int($intVal)) {
            throw new Currency_USD_Precise_Exception("\$intVal is not an int");
        }
        $positiveValue = abs($intVal);
        $isNegative    = ($intVal < 0);
        return self::fromDollarsAndCents($positiveValue, 0, $isNegative);
    }

    /**
     * Create a Currency_USD_Precise object from dollars, cents, and optionally an isNegative flag.
     *
     * @param integer $dollars    The number of dollars.
     * @param integer $cents      The number of cents.
     * @param integer $isNegative Whether the object should be negative. Default false (positive).
     *
     * @throws Currency_USD_Precise_Exception If dollars is non-integer.
     * @throws Currency_USD_Precise_Exception If cents is non-integer.
     * @throws Currency_USD_Precise_Exception If isNegative is non-boolean.
     * @return Currency_USD_Precise The Currency_USD_Precise object.
     */
    public static function fromDollarsCentsAndPartialCents($dollars, $cents, $partialCents, $isNegative = false) {
        if ($dollars === null) {
            throw new Currency_USD_Invalid_Value_Exception("\$dollars cannot be null.");
        }
        if ($cents === null) {
            throw new Currency_USD_Invalid_Value_Exception("\$cents cannot be null.");
        }
        if ($partialCents === null) {
            throw new Currency_USD_Invalid_Value_Exception("\$cents cannot be null.");
        }
        $currencyObj = new Currency_USD_Precise();
        $currencyObj->setDollars($dollars);
        $currencyObj->setCents($cents);
        $currencyObj->setPartialCents($partialCents);
        $currencyObj->setIsNegative($isNegative);
        return $currencyObj;
    }

    /**
     * Create a Currency_USD_Precise object from a floating point number.
     *
     * @param float $floatVal The value of the currency object as a float.
     *
     * @throws Currency_USD_Precise_Exception If the float value has too much precision (e.g. 123.45678) or is not a float.
     * @return Currency_USD_Precise The Currency_USD_Precise object.
     */
    public static function fromFloat($floatVal) {
        if ($floatVal === null) {
            $message = "\$floatVal cannot be null.";
            throw new Currency_USD_Precise_Invalid_Value_Exception($message);
        }
        if (!is_float($floatVal) && !is_int($floatVal)) {
            $message = "Given value was not a float: " . var_export($floatVal, true);
            throw new Currency_USD_Precise_Invalid_Value_Exception($message);
        }
        if (abs(bcsub($floatVal, round(floatVal($floatVal), 4), 5)) >= .00001) {
            $message = "\$floatVal contained incorrect number of decimal values.";
            throw new Currency_USD_Precise_Invalid_Value_Exception($message);
        }
        return self::fromString($floatVal . '');
    }

    /**
     * Create a currency object from the number of cents, for example, -123.45 cents becomes -$1.2345.
     *
     * @param integer $numCents The number of cents we want to represent as a Currency_USD_Precise object.
     *
     * @throws Currency_USD_Precise_Exception If numCents is non-integer.
     * @throws Currency_USD_Precise_Exception If numCents is greater than PHP_INT_MAX.
     * @return Currency_USD_Precise The Currency_USD_Precise object.
     */
    public static function fromNumPartialCents($numPartialCents) {
        if ($numPartialCents === null) {
            throw new Currency_USD_Precise_Invalid_Value_Exception("\$numPartialCents cannot be null.");
        }
        $positiveNumCents = abs($numPartialCents);

        if ($positiveNumCents > PHP_INT_MAX) {
            $message = "Overflow, {$numCents} is greater than PHP_INT_MAX: " . PHP_INT_MAX;
            throw new Currency_USD_Precise_Exception($message);
        }

        $dollars        = intVal(floor($positiveNumCents / 10000));
        // $positiveNumCents % 10000 clears out anything that would qualify as a dollar amount.
        $cents          = intVal(floor(($positiveNumCents % 10000) / 100));
        $partialCents   = $positiveNumCents % 100;
        $isNegative     = ($numCents < 0);
        return self::fromDollarsCentsAndPartialCents($dollars, $cents, $partialCents, $isNegative);
    }

    /**
     * The value of this object as a decimal (float).
     *
     * @return float The value of this object as a float.
     */
    public function toDecimal() {
        $value = $this->getDollars() + ($this->getCents() / 100) + ($this->getPartialCents() / 10000);
        if ($this->isNegative()) {
            $value *= -1;
        }
        return $value;
    }

    /**
     * This object represented as an integer number of partial cents.
     *
     * @return integer The number of partial cents.
     */
    public function toNumPartialCents() {
        $value = $this->getDollars() * 10000 + $this->getCents() * 100 + $this->getPartialCents();
        if ($this->isNegative()) {
            $value *= -1;
        }
        return $value;
    }

    /**
     * Add another Currency_USD_Precise object to this object.
     *
     * @param Currency_USD_Precise $currencyObj The currency object to add to this object.
     *
     * @return Currency_USD_Precise The sum of the two objects as a Currency_USD object.
     */
    public function add(Currency_USD $currencyObj) {
        $numCents1 = $this->toNumPartialCents();
        $numCents2 = $currencyObj->toNumPartialCents();

        $sum      = $numCents1 + $numCents2;
        $currency = self::fromNumPartialCents($sum);
        return $currency;
    }

    /**
     * Subtract a Currency_USD_Precise object from this object.
     *
     * @param Currency_USD_Precise $currencyObj The Currency_USD object to subtract from this object.
     *
     * @return Currency_USD_Precise The difference of the two Currency_USD objects as a Currency_USD object.
     */
    public function subtract(Currency_USD_Precise $currencyObj) {
        $numPartialCents1 = $this->toNumPartialCents();
        $numPartialCents2 = $currencyObj->toNumPartialCents();

        $currency = self::fromNumPartialCents($numPartialCents1 - $numPartialCents2);
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
     * @throws Currency_USD_Precise_Exception If we are set to throw exceptions on partial cents, exception is thrown if multiplication results in partial cents.
     * @return Currency_USD_Precise The product as a Currency_USD_Precise object.
     */
    public function multiply($scalar, $whatToDoWithPartialCents = self::PARTIAL_CENTS_THROW_EXCEPTION) {
        $numCents = $this->toNumPartialCents();
        $product  = $numCents * $scalar;

        if (is_float($product) && $whatToDoWithPartialCents == self::PARTIAL_CENTS_THROW_EXCEPTION) {
            throw new Currency_USD_Precise_Exception("Multiply resulted in partial cents");
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

        return self::fromNumPartialCents($product);
    }

    /**
     * Divide this Currency_USD_Precise object by a scalar (int or float).
     *
     * @param mixed  $scalar                   The scalar (float or int) to divide this object by.
     * @param string $whatToDoWithPartialCents What we should do with partial cents--either round them or throw an exception.
     *
     * @see    Class constant PARTIAL_CENTS_ROUND_NEAREST
     * @see    Class constant PARTIAL_CENTS_ROUND_DOWN
     * @see    Class constant PARTIAL_CENTS_ROUND_UP
     * @see    Class constant PARTIAL_CENTS_THROW_EXCEPTION
     * @throws Currency_USD_Precise_Exception If we are set to throw exceptions on partial cents, exception is thrown if division results in partial cents.
     * @return Currency_USD_Precise The quotient as a Currency_USD_Precise object.
     */
    public function divide($scalar, $whatToDoWithPartialCents = self::PARTIAL_CENTS_THROW_EXCEPTION) {
        $numCents = $this->toNumCents();
        $quotient = $numCents / $scalar;

        if (is_float($quotient) && $whatToDoWithPartialCents == self::PARTIAL_CENTS_THROW_EXCEPTION) {
            throw new Currency_USD_Precise_Exception("Divide resulted in partial cents");
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
     * @throws Currency_USD_Precise_Exception If dollars is non-int, negative, or greater than PHP_INT_MAX.
     * @return void
     */
    public function validateDollars($dollars) {
        if (!is_int($dollars)) {
            throw new Currency_USD_Precise_Invalid_Value_Exception("Dollars must be an int, got: " . var_export($dollars, true));
        }
        if ($dollars < 0) {
            throw new Currency_USD_Precise_Invalid_Value_Exception("Dollars must be greater than 0, set negative values separately");
        }
        if (abs($dollars) > PHP_INT_MAX) {
            throw new Currency_USD_Precise_Invalid_Value_Exception("Overflow, {$dollars} is greater than PHP_INT_MAX: " . PHP_INT_MAX);
        }
    }

    /**
     * Validate a cents amount.
     *
     * @param integer $cents The integer number of cents.
     *
     * @throws Currency_USD_Precise_Exception If cents is non-int, negative, or greater than or equal to 100.
     * @return void
     */
    public function validateCents($cents) {
        if (!is_int($cents)) {
            throw new Currency_USD_Precise_Invalid_Value_Exception("Cents must be an int");
        }
        if ($cents >= 100) {
            throw new Currency_USD_Precise_Invalid_Value_Exception("Cents must be less than 100, use dollars and cents instead");
        }
        if ($cents < 0) {
            throw new Currency_USD_Precise_Invalid_Value_Exception("Cents must be greater than 0, set negative values separately");
        }
    }

    /**
     * Validate a partialCents amount.
     *
     * @param integer $cents The integer number of cents.
     *
     * @throws Currency_USD_Precise_Exception If partialCents is non-int, negative, or greater than or equal to 100.
     * @return void
     */
    public function validatePartialCents($partialCents) {
        if (!is_int($partialCents)) {
            throw new Currency_USD_Precise_Invalid_Value_Exception("Partial cents must be an int");
        }
        if ($partialCents >= 100) {
            throw new Currency_USD_Precise_Invalid_Value_Exception("Partial cents must be less than 100, use cents and partialCents instead");
        }
        if ($partialCents < 0) {
            throw new Currency_USD_Precise_Invalid_Value_Exception("Partial cents must be greater than 0, set negative values separately");
        }
    }

    /**
     * Validate that the passed value for isNegative is boolean.
     *
     * @param boolean $isNegative The boolean value for isNegative.
     *
     * @throws Currency_USD_Precise_Exception If isNegative is non-boolean.
     * @return boolean True if the value is ok, otherwise exception is thrown.
     */
    public function validateIsNegative($isNegative) {
        if ($isNegative === true) {
            return true;
        }

        if ($isNegative === false) {
            return true;
        }

        throw new Currency_USD_Precise_Exception("Is negative must be either true or false");
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
    public function getPartialCents() {
        return $this->_partialCents;
    }

    /**
     * Modifier for cents. Only modifies cents, does not affect dollars.
     *
     * @param integer $value Number of cents, must be integer between 0 and 9999.
     *
     * @return void
     */
    public function setPartialCents($value) {
        $this->validatePartialCents($value);
        $this->_partialCents = $value;
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
     * @param integer $value Number of cents, must be integer between 0 and 9999.
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
     * @param Currency_USD_Precise $currencyObj The object with which to test equality with this object.
     *
     * @return boolean True if the objects have the same currency value.
     */
    public function equals(Currency_USD_Precise $currencyObj) {
        return $this->toNumCents() == $currencyObj->toNumCents();
    }

    /**
     * Compare two Currency_USD objects. Return 0 if they are equal, 1 if the first is greater, -1 if the second is greater. Use this function with php's sort functions.
     *
     * @param Currency_USD_Precise $a The first Currency_USD_Precise object to compare.
     * @param Currency_USD_Precise $b The second Currency_USD_Precise object to compare.
     *
     * @throws Currency_USD_Precise_Exception If the comparison results in none of: equal, less than, or greater than.
     * @return integer 0 if the objects are equal, 1 if a > b, -1 if b > a
     */
    public static function compare(Currency_USD_Precise $a, Currency_USD_Precise $b) {
        // Return  1 if a is greater than b
        if ($a->isGreaterThan($b)) {
            return 1;
        }

        // Return -1 if a is less than b
        if ($a->isLessThan($b)) {
            return -1;
        }

        // Return  0 if a is equal to b
        if ($a->equals($b)) {
            return 0;
        }

        // Otherwise throw an exception
        throw new Currency_USD_Precise_Exception("Unexpected comparison value");
    }

    /**
     * Compare this object to another Currency_USD_Precise object. Return 0 if the objects are equal, 1 if this object is greater, -1 if this object is less.
     *
     * @param Currency_USD_Precise $compareTo Object to compare this object to.
     *
     * @return integer 0 if the objects are equal, 1 if this object is greater, -1 if this object is less.
     */
    public function compareTo(Currency_USD_Precise $compareTo) {
        return self::compare($this, $compareTo);
    }

    /**
     * Test whether this object is greater than another Currency_USD_Precise object.
     *
     * @param Currency_USD_Precise $currencyObj The object to which we want to compare.
     *
     * @return boolean True if this object is greater than the other object, otherwise false.
     */
    public function isGreaterThan(Currency_USD_Precise $currencyObj) {
        return $this->toNumCents() > $currencyObj->toNumCents();
    }

    /**
     * Test whether this object is less than another Currency_USD_Precise object.
     *
     * @param Currency_USD_Precise $currencyObj The object to which we want to compare.
     *
     * @return boolean True if this object is less than the other object, otherwise false.
     */
    public function isLessThan(Currency_USD_Precise $currencyObj) {
        return $this->toNumCents() < $currencyObj->toNumCents();
    }

    /**
     * Test whether this object is greater than or equal to another Currency_USD_Precise object.
     *
     * @param Currency_USD_Precise $currencyObj The object to which we want to compare.
     *
     * @return boolean True if this object is greater than or equal to the other object, otherwise false.
     */
    public function isGreaterThanOrEqualTo(Currency_USD_Precise $currencyObj) {
        return ($this->isGreaterThan($currencyObj) or $this->equals($currencyObj));
    }

    /**
     * Test whether this object is less than or equal to another Currency_USD_Precise object.
     *
     * @param Currency_USD_Precise $currencyObj The object to which we want to compare.
     *
     * @return boolean True if this object is less than or equal to the other object, otherwise false.
     */
    public function isLessThanOrEqualTo(Currency_USD_Precise $currencyObj) {
        return ($this->isLessThan($currencyObj) or $this->equals($currencyObj));
    }

    /**
     * Calculate a percentage that one currency object is of another
     *
     * @param Currency_USD_Precise $a The numerator Currency_USD_Precise object.
     * @param Currency_USD_Precise $b The denominator Currency_USD_Precise object.
     * @param integer $numDecimals The number of decimal places to round to. Valid values are 0-3 inclusive and default is 2.
     *
     * @throws Currency_USD_Precise_Exception If numDecimals is invalid or out of range.
     * @throws Currency_USD_Precise_Divide_By_Zero_Exception If $b equals 0
     * @return float the % that a is of b, rounded to numDecimals decimal places
     */
    public static function getPercent(Currency_USD_Precise $a, Currency_USD_Precise $b, $numDecimals = 2) {
        if ( (intVal($numDecimals) !== $numDecimals) || $numDecimals < 0 || $numDecimals > 3 ) {
            throw new Currency_USD_Precise_Exception("Invalid numDecimals");
        }

        if ($b->toNumCents() === 0) {
            throw new Currency_USD_Precise_Divide_By_Zero_Exception("Can't divide by 0");
        }

        return round(100 * $a->toNumPartialCents() / $b->toNumPartialCents(), $numDecimals);
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
     * Parse the number of partial cents from a string.
     *
     * @param string $partialCentsStr The string with partial cents in it.
     *
     * @throws Currency_USD_Precise_Exception If we were unable to parse partial cents from the string.
     * @return integer The integer value of the partial cents part of the string.
     */
    private static function _parsePartialCentsFromString($partialCentsStr) {
        if ($partialCentsStr === null) {
            return 0;
        }

        if ($partialCentsStr == '') {
            return 0;
        }

        if (strlen($partialCentsStr) == 2) {
            return intVal($centsStr);
        }

        // centsStr has 1 digit (e.g. '123.4', which should be translated to 40 cents
        if (strlen($partialCentsStr) == 1) {
            return (intVal($partialCentsStr) * 10);
        }

        throw new Currency_USD_Precise_Exception("Unable to parse cents: " . var_export($partialCentsStr, true));
    }

    /**
     * Parse the number of cents from a string.
     *
     * @param string $centsStr The string with cents in it.
     *
     * @throws Currency_USD_Precise_Exception If we were unable to parse cents from the string.
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

        throw new Currency_USD_Precise_Exception("Unable to parse cents: " . var_export($centsStr, true));
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
     * Get a two-digit string representation of the number of partial cents.
     *
     * @return string The two-digit string representation.
     */
    private function _getTwoDigitNumPartialCents() {
        if ($this->getPartialCents() < 10) {
            return "0{$this->getPartialCents()}";
        }
        return "{$this->getPartialCents()}";
    }



    // ***Deprecated Functions Inherited From Parent Class.*** //



    /**
     * Compose the spoken amounts of this object into a spoken-word string.
     *
     * @deprecated only valid in parent class
     */
    private function _composeSpokenAmount() {
        $message = "\nPrecise currency not valid for _composeSpokenAmount.  Call like object->roundPrecision()->_composeSpokenAmount()\n";
        throw new Currency_USD_Precise_Deprecated_Exception($message);
    }

    /**
     * The default representation of this object: a string with no dollar sign.
     *
     * @deprecated only valid in parent class
     */
    public function __toString() {
        $message = "\nPrecise currency not valid for __toString.  Call like object->roundPrecision()->__toString()\n";
        throw new Currency_USD_Precise_Deprecated_Exception($message);
    }

    /**
     * Returns a representation like "Three dollars and sixteen cents" for $3.16.
     *
     * @deprecated only valid in parent class
     */
    public function toWords() {
        $message = "\nPrecise currency not valid for toWords.  Call like object->roundPrecision()->toWords()\n";
        throw new Currency_USD_Precise_Deprecated_Exception();
    }

    /**
     * Format this object as a string, with or without a dollar sign.
     *
     * @param boolean $includeDollarSign True includes dollar sign, default no dollar sign.
     *
     * @deprecated only valid in parent class
     */
    public function formattedString($includeDollarSign = null, $includeCommaForThousands = null) {
        $message = "\nPrecise currency not valid for formattedString.  Call like object->roundPrecision()->formattedString()\n";
        throw new Currency_USD_Precise_Deprecated_Exception();
    }

    /**
     * Returns the currency object as a string with commas and a dollar sign.
     *
     * @deprecated only valid in parent class
     */
    public function getFormattedString() {
        $message = "\nPrecise currency not valid for getFormattedString.  Call like object->roundPrecision()->getFormattedString()\n";
        throw new Currency_USD_Precise_Deprecated_Exception();
    }

    /**
     * Adds commas to dollar amounts where necessary.
     *
     * @deprecated only valid in parent class
     */
    public function _getDollarsWithCommas() {
        $message = "\nPrecise currency not valid for _getDollarsWithCommas.  Call like object->roundPrecision()->_getDollarsWithCommas()\n";
        throw new Currency_USD_Precise_Deprecated_Exception();
    }

}

class Currency_USD_Precise_Exception extends Exception {
}
class Currency_USD_Precise_Divide_By_Zero_Exception extends Exception {
}
class Currency_USD_Precise_Invalid_Value_Exception extends Currency_USD_Precise_Exception {
}
class Currency_USD_Precise_Deprecated_Exception extends Currency_USD_Precise_Exception {
}
