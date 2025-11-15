<?php

namespace Tests\AspireBuild\Tools\Sideways\Extra;

use AspireBuild\Tools\Sideways\Extra\ParsedownExtra;

class TestParsedown extends ParsedownExtra
{
    public function getTextLevelElements()
    {
        return $this->textLevelElements;
    }
}
