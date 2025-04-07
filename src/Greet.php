<?php

namespace Gomaa\Base;

class Greet
{
    public function greetTo(String $sName)
    {
        return 'Hi ' . $sName . '! How are you doing today?';
    }
}
