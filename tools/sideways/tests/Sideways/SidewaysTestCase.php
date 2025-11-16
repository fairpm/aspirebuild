<?php
declare(strict_types=1);

namespace Tests\AspireBuild\Tools\Sideways;

use AspireBuild\Tools\Sideways\Sideways;
use PHPUnit\Framework\TestCase;

class SidewaysTestCase extends TestCase {

    protected Sideways $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = $this->newParser();
    }

    /** override this method to pass additional args to Sideways constructor */
    protected function newParser(): Sideways
    {
        return new Sideways();
    }

    protected function render(string $markdown): string
    {
        return $this->parser->renderToHtml($markdown);
    }
}
