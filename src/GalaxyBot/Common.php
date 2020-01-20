<?php

namespace GalaxyBot;

class Common
{

    // Function to check string starting
    // with given substring
    public static function StartsWith($string, $startString)
    {
        $len = strlen($startString);
        return (substr($string, 0, $len) === $startString);
    }
}