<?php

namespace Sideways;

use Tests\AspireBuild\Tools\Sideways\SidewaysTestCase;

class MalformedMarkdownTest extends SidewaysTestCase
{
    public function test_broken_underline_headers_are_text(): void
    {
        expect($this->render("Header\n=== ==="))->toBe("<p>Header\n=== ===</p>"); // broken lines will not parse
        expect($this->render("Header\n-=-=-="))->toBe("<p>Header\n-=-=-=</p>");   // mixed lines will not parse
    }

    public function test_h1_indent_underline_is_text(): void
    {
        // all leading space is consumed
        expect($this->render("Header\n    ======"))->toBe("<p>Header\n======</p>");
        expect($this->render("Header\n        ======"))->toBe("<p>Header\n======</p>");
    }

    public function test_h2_indent_underline_is_hr(): void
    {
        // all leading space is consumed
        expect($this->render("Header\n    ------"))->toBe("<p>Header</p>\n<hr />");
        expect($this->render("Header\n        ------"))->toBe("<p>Header</p>\n<hr />");
    }
}
