<?php

namespace Tests\AspireBuild\Tools\Sideways;


use AspireBuild\Tools\Sideways\Parsedown;

class TestParsedown extends Parsedown
{
    public function getTextLevelElements()
    {
        return $this->textLevelElements;
    }
}
