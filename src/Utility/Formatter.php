<?php

namespace ConstantNull\Backstubber\Utility;

use Illuminate\Support\Arr;

class Formatter
{
    const ARR_MODE_AUTO = 0;

    const ARR_MODE_SINGLELINE = 1;

    const ARR_MODE_MULTILINE = 2;

    protected static $arrayMode = self::ARR_MODE_AUTO;

    /**
     * @var int psr-2 standard tabulation size
     */
    protected static $tabSize = 4;

    /**
     * Determines if array should be formatted as a multiline one or not
     *
     * @param array $array
     * @return bool
     */
    protected static function isArrayMultiline($array)
    {
        if (empty($array)) return false;

        switch (self::getArrayMode()) {
            case self::ARR_MODE_SINGLELINE:
                $isMultiline = false;
                break;
            case self::ARR_MODE_MULTILINE:
                $isMultiline = true;
                break;
            default: // for ARR_MODE_AUTO
                $isMultiline = Arr::isAssoc($array) ? true : false;
                return $isMultiline;
        }
        return $isMultiline;
    }

    /**
     * Returns empty string with specified length
     *
     * @param integer $size
     * @return string
     */
    protected static function indent($size)
    {
        return str_pad('', $size);
    }

    /**
     * Prepare array elements for formatting.
     *
     * @param array $array
     * @return array
     */
    protected static function prepareArrayLines(array $array)
    {
        $isAssoc = Arr::isAssoc($array);

        array_walk($array, function (&$value, $index, $withIndexes = false) {
            if (is_array($value)) {
                $value = self::formatArray($value);
            } else {
                $value = self::formatScalar($value);
            }

            if ($withIndexes) {
                $value = self::formatScalar($index) . ' => ' . $value;
            }
        }, $isAssoc);

        return $array;
    }

    /**
     * Add indents to multiline text
     *
     * @param string $text
     * @param integer $indent indent in spaces
     * @param bool $skipFirstLine = false not apply indent to the first line of text
     * @return array
     */
    public static function indentLines($text, $indent, $skipFirstLine = false)
    {
        $lines = explode(PHP_EOL, $text);

        // (つ◕.◕)つ━☆ﾟ.*･｡ﾟ
        !$skipFirstLine || $preparedLines[] = array_shift($lines);

        foreach ($lines as $line) {
            $preparedLines[] = self::indent($indent) . rtrim($line);
        }

        return implode(PHP_EOL, $preparedLines);
    }

    /**
     * Set array formatting mode
     *
     * @param int $arrayMode Formatter::ARR_MODE_*
     */
    public static function setArrayMode($arrayMode)
    {
        self::$arrayMode = $arrayMode;
    }

    /**
     * Get array formatting mode
     *
     * @return int
     */
    public static function getArrayMode()
    {
        return self::$arrayMode;
    }

    /**
     * Return text representation of array
     *
     * @param array $array input array
     * @param bool $braces add array braces to output
     * @return string
     */
    public static function formatArray(array $array, $braces = true)
    {
        $isMultiline = self::isArrayMultiline($array);

        $array = self::prepareArrayLines($array);

        $eol = $isMultiline ? PHP_EOL : '';

        $output = implode($array, ', ' . $eol);

        if ($isMultiline) {
            // apply base indent to array elements
            $output = self::indentLines($output, self::$tabSize);
        }

        if ($braces) {
            $output = implode(['[', $output, ']'], $eol);
        } else {
            $output = $eol . $output . $eol;
        }

        return $output;
    }

    /**
     * Prepares scalar value
     * (adds quotes to the ends of the string, replaces booleans with literal true|false)
     *
     * @param mixed $value scalar type value
     * @return string
     */
    public static function formatScalar($value)
    {
        if (is_string($value)) {
            $value = "'$value'";
        }

        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }

        return "$value";
    }

    /**
     * Generates stub ready variable line
     *
     * @param string $name variable name
     * @param mixed $value value of the variable
     * @return string
     */
    public static function formatVariable($name, $value)
    {
        $value = self::formatScalar($value);

        $declaration = "\$$name = $value;";

        return $declaration;
    }

    /**
     * Generates stub ready property
     *
     * @param string $keywords property keywords such as protected | public static
     * @param string $name property name
     * @param mixed $value value of the property
     * @return string
     */
    public static function formatProperty($keywords, $name, $value)
    {
        $declaration = "$keywords \$$name = $value;";

        return $declaration;
    }
}
