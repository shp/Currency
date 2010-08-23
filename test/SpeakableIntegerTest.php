<?php

/**
 * Tests for Currency_USD
 *
 * @link http://www.phpunit.de/pocket_guide/3.0/en/writing-tests-for-phpunit.html
 */

class Currency_SpeakableIntegerTest extends PHPUnit_Framework_TestCase {

    /**
     * Make sure we get the correct response for each
     * spoken integer.
     *
     * @dataProvider toWordsDataProvider
     */
    public function testToWordsWorksCorrectly($intValue, $asWords) {
        $speakableInteger = new Currency_SpeakableInteger($intValue);
        $this->assertEquals($asWords, $speakableInteger->toWords());
    }

    public function toWordsDataProvider() {
        return array(
            //    IntValue          AsWords
            array(0,                'Zero'),
            array(1,                'One'),
            array(2,                'Two'),
            array(3,                'Three'),
            array(4,                'Four'),
            array(5,                'Five'),
            array(6,                'Six'),
            array(7,                'Seven'),
            array(8,                'Eight'),
            array(9,                'Nine'),
            array(10,               'Ten'),
            array(11,               'Eleven'),
            array(12,               'Twelve'),
            array(13,               'Thirteen'),
            array(14,               'Fourteen'),
            array(15,               'Fifteen'),
            array(16,               'Sixteen'),
            array(17,               'Seventeen'),
            array(18,               'Eighteen'),
            array(19,               'Nineteen'),
            array(20,               'Twenty'),
            array(21,               'Twenty-one'),
            array(22,               'Twenty-two'),
            array(23,               'Twenty-three'),
            array(24,               'Twenty-four'),
            array(25,               'Twenty-five'),
            array(26,               'Twenty-six'),
            array(27,               'Twenty-seven'),
            array(28,               'Twenty-eight'),
            array(29,               'Twenty-nine'),
            array(30,               'Thirty'),
            array(40,               'Forty'),
            array(50,               'Fifty'),
            array(60,               'Sixty'),
            array(70,               'Seventy'),
            array(80,               'Eighty'),
            array(90,               'Ninety'),
            array(100,              'One hundred'),
            array(101,              'One hundred one'),
            array(111,              'One hundred eleven'),
            array(121,              'One hundred twenty-one'),
            array(200,              'Two hundred'),
            array(300,              'Three hundred'),
            array(400,              'Four hundred'),
            array(500,              'Five hundred'),
            array(600,              'Six hundred'),
            array(700,              'Seven hundred'),
            array(800,              'Eight hundred'),
            array(900,              'Nine hundred'),
            array(1000,             'One thousand'),
            array(1001,             'One thousand one'),
            array(1011,             'One thousand eleven'),
            array(1021,             'One thousand twenty-one'),
            array(1101,             'One thousand one hundred one'),
            array(1111,             'One thousand one hundred eleven'),
            array(1121,             'One thousand one hundred twenty-one'),
        );
    }

}
