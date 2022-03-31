<?php

/**
 * Yasmin
 * Copyright 2017-2018 Charlotte Dunois, All Rights Reserved
 *
 * Website: https://charuru.moe
 * License: https://github.com/CharlotteDunois/Yasmin/blob/master/LICENSE
 */

/**
 * Represents a Snowflake. This is identical to the Yasmin version, just with an edited epoch.
 * @property float       $timestamp  The timestamp of when this snowflake got generated. In seconds with microseconds.
 * @property int         $workerID   The ID of the worker which generated this snowflake.
 * @property int         $processID  The ID of the process which generated this snowflake.
 * @property int         $increment  The increment index of the snowflake.
 * @property string      $binary     The binary representation of this snowflake.
 * @property string|int  $value      The snowflake value.
 * @property \DateTime   $date       A DateTime instance of the timestamp.
 */
class Snowflake
{
    /**
     * Time since UNIX epoch to Huntress epoch.
     * @var int
     */
    const EPOCH = 1546300800;

    protected static $incrementIndex = 0;
    protected static $incrementTime  = 0;
    protected $value;
    protected $timestamp;
    protected $workerID;
    protected $processID;
    protected $increment;
    protected $binary;

    /**
     * Constructor.
     * @param string|int  $snowflake
     * @throws \InvalidArgumentException
     */
    function __construct($snowflake)
    {

        $snowflake   = (int) $snowflake;
        $this->value = $snowflake;

        $this->binary = \str_pad(\decbin($snowflake), 64, 0, \STR_PAD_LEFT);

        $time = (string) ($snowflake >> 2);

        $this->timestamp = (int) $time + self::EPOCH;
        $this->increment = ($snowflake & 0x3);


        if ($this->timestamp < self::EPOCH || $this->increment < 0 || $this->increment >= 4) {
            throw new \InvalidArgumentException('Invalid snow in snowflake');
        }
    }

    /**
     * @return mixed
     * @throws \Exception
     * @internal
     */
    function __get($name)
    {
        switch ($name) {
            case 'timestamp':
            case 'workerID':
            case 'processID':
            case 'increment':
            case 'binary':
                return $this->$name;
                break;
        }

        throw new \Exception('Undefined property: ' . (self::class) . '::$' . $name);
    }

    /**
     * Deconstruct a snowflake.
     * @param string|int  $snowflake
     * @return Snowflake
     */
    static function deconstruct($snowflake)
    {
        return (new self($snowflake));
    }

    /**
     * Generates a new snowflake.
     * @return string
     */
    static function generate()
    {
        $time = time();

        if ($time === self::$incrementTime) {
            self::$incrementIndex++;

            if (self::$incrementIndex >= 4) {
                sleep(1);

                $time = time();

                self::$incrementIndex = 0;
            }
        } else {
            self::$incrementIndex = 0;
            self::$incrementTime  = $time;
        }

        $time = (string) $time - self::EPOCH;

        $binary = \str_pad(\decbin(((int) $time)), 62, 0, \STR_PAD_LEFT) . \str_pad(\decbin(self::$incrementIndex), 2, 0, \STR_PAD_LEFT);
        return ((string) \bindec($binary));
    }

    public static function format(int $snow): string
    {
        return base_convert($snow, 10, 36);
    }

    public static function parse(string $snow): int
    {
        return base_convert($snow, 36, 10);
    }
}
