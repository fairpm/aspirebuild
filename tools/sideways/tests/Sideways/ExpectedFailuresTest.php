<?php

namespace Sideways;

use Tests\AspireBuild\Tools\Sideways\SidewaysTestCase;

// Ideally this class will have no tests.  Til then, verify we still haven't implemented any of them
class ExpectedFailuresTest extends SidewaysTestCase
{
    public function test_xfail_ghfm_headers(): void
    {
        expect($this->render('= Header ='))->notToBe('<h1>Header</h1>');
        expect($this->render('== Header =='))->notToBe('<h2>Header</h2>');
    }
}
