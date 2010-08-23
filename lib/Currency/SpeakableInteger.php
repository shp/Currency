<?php

/**
 * A decorator for the standard php int
 * object that allows conversion to words.
 */
class Currency_SpeakableInteger {

    private $_int;

    public function __construct($intVal) {
        if (!is_int($intVal)) {
            throw new Currency_SpeakableInteger_Exception("Cannot create SpeakableInteger from non-int");
        }

        $this->_int = $intVal;
    }

    public function toWords() {
        if ($this->_int == 0) {
            return 'Zero';
        }

        $retVal = '';
        $retVal .= $this->_signToWords();
        $retVal .= $this->_numberToWords();
        return $retVal;
    }

    private function _signToWords() {
        if ($this->_int < 0) {
            return 'negative';
        }

        // What to say if it's non-negative ('' or positive?)
        return '';
    }

    /**
     * Adapted from http://bloople.net/num2text/cnumlib.txt
     * Copyright 2007-2008 Brenton Fletcher
     * http://bloople.net/num2text
     * "You can use this freely and modify it however you want."
     */
    private function _numberToWords() {
        // Split the number into groups of threes
        $num                = str_pad($this->_int, 36, "0", STR_PAD_LEFT);
        $inputGroupString   = rtrim(chunk_split($num, 3, " "), " ");
        $inputGroups        = explode(" ", $inputGroupString);

        $outputGroups       = array();
        foreach ($inputGroups as $group) {
            $digits = str_split($group, 1);
            $threeDigitString = $this->_convertThreeDigitsToWords($digits[0], $digits[1], $digits[2]);
            array_push($outputGroups, $threeDigitString);
        }

        // Now convert the array to a big long string and return
        $groupNames = self::_getAllGroupNames();
        $outputGroupsWithNames = array();
        foreach ($outputGroups as $group) {
            $groupName = array_pop($groupNames);
            if ($group != '') {
                array_push($outputGroupsWithNames, "{$group} {$groupName}");
            }
        }

        return ucfirst(trim(implode(' ', $outputGroupsWithNames)));
    }

    private static function _getAllGroupNames() {
        return array_reverse(array(
            'decillion',
            'nonillion',
            'octillion',
            'septillion',
            'sextillion',
            'quintrillion',
            'quadrillion',
            'trillion',
            'billion',
            'million',
            'thousand',
            '',
        ));
    }

    private function _convertThreeDigitsToWords($hundreds, $tens, $ones) {
        $asString = '';

        // Handle the zero case
        if($hundreds == '0' && $tens == '0' && $ones == '0') return '';

        // Start with hundreds
        if($hundreds != '0')
        {
           $asString .= "{$this->_convertDigitToWords($hundreds)} hundred";
           if($tens != '0' || $ones != '0') $asString .= ' ';
        }

        // Tens and ones come together if they're both set
        if($tens != '0') {
            $asString .= $this->_convertTwoDigitsToWords($tens, $ones);
        } elseif ($ones != '0') {
            $asString .= $this->_convertDigitToWords($ones);
        }

        return $asString;
    }

    private function _convertTwoDigitsToWords($tens, $ones) {
        if($ones == "0")
        {
           switch($tens)
           {
              case "1": return "ten";
              case "2": return "twenty";
              case "3": return "thirty";
              case "4": return "forty";
              case "5": return "fifty";
              case "6": return "sixty";
              case "7": return "seventy";
              case "8": return "eighty";
              case "9": return "ninety";
           }
        }
        else if($tens == "1")
        {
           switch($ones)
           {
              case "1": return "eleven";
              case "2": return "twelve";
              case "3": return "thirteen";
              case "4": return "fourteen";
              case "5": return "fifteen";
              case "6": return "sixteen";
              case "7": return "seventeen";
              case "8": return "eighteen";
              case "9": return "nineteen";
           }
        }
        else
        {
           $onesDigitAsString = $this->_convertDigitToWords($ones);
           switch($tens)
           {
              case "2": return "twenty-{$onesDigitAsString}";
              case "3": return "thirty-{$onesDigitAsString}";
              case "4": return "forty-{$onesDigitAsString}";
              case "5": return "fifty-{$onesDigitAsString}";
              case "6": return "sixty-{$onesDigitAsString}";
              case "7": return "seventy-{$onesDigitAsString}";
              case "8": return "eighty-{$onesDigitAsString}";
              case "9": return "ninety-{$onesDigitAsString}";
           }
        }
    }

    private function _convertDigitToWords($digit) {
        switch($digit)
        {
           case "0": return "zero";
           case "1": return "one";
           case "2": return "two";
           case "3": return "three";
           case "4": return "four";
           case "5": return "five";
           case "6": return "six";
           case "7": return "seven";
           case "8": return "eight";
           case "9": return "nine";
        }
    }

}

class Currency_SpeakableInteger_Exception extends Exception {}
