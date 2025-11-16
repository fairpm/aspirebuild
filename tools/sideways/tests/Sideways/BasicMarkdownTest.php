<?php

namespace Sideways;

use Tests\AspireBuild\Tools\Sideways\SidewaysTestCase;

class BasicMarkdownTest extends SidewaysTestCase
{
    public function test_headers(): void
    {
        expect($this->render('# Header'))->toBe('<h1>Header</h1>');
        expect($this->render('## Header'))->toBe('<h2>Header</h2>');
        expect($this->render('### Header'))->toBe('<h3>Header</h3>');
        expect($this->render('#### Header'))->toBe('<h4>Header</h4>');
        expect($this->render('##### Header'))->toBe('<h5>Header</h5>');
        expect($this->render('###### Header'))->toBe('<h6>Header</h6>');

        expect($this->render('# Header #'))->toBe('<h1>Header</h1>');
        expect($this->render('# Header   ##'))->toBe('<h1>Header</h1>');
        expect($this->render('## Header    ###########'))->toBe('<h2>Header</h2>'); // eats all hashes on the right

        expect($this->render('####### Header'))->toBe('<p>####### Header</p>'); // 7 or more will not parse

        expect($this->render("Header\n======"))->toBe('<h1>Header</h1>');
        expect($this->render("Header\n------"))->toBe('<h2>Header</h2>');

        expect($this->render("Header\n======\n======"))->toBe("<h1>Header</h1>\n<p>======</p>");
        expect($this->render("Header\n------\n------"))->toBe("<h2>Header</h2>\n<hr />");

        // Any number of = or - on the line after will work
        expect($this->render("Header\n="))->toBe('<h1>Header</h1>');
        expect($this->render("Header\n-"))->toBe('<h2>Header</h2>');
        expect($this->render("Header\n====="))->toBe('<h1>Header</h1>');
        expect($this->render("Header\n-----"))->toBe('<h2>Header</h2>');
        expect($this->render("Header\n=========="))->toBe('<h1>Header</h1>');
        expect($this->render("Header\n----------"))->toBe('<h2>Header</h2>');

        expect($this->render("Header\n ======"))->toBe("<h1>Header</h1>");
        expect($this->render("Header\n   ======"))->toBe("<h1>Header</h1>"); // up to 3 leading whitespaces
    }

    public function test_emphasis(): void
    {
        expect($this->render('some *em text* here'))->toBe('<p>some <em>em text</em> here</p>');
        expect($this->render('some _em text_ here'))->toBe('<p>some <em>em text</em> here</p>');
    }

    public function test_links(): void
    {
        expect($this->render('[link text](http://example.com)'))->toBe('<p><a href="http://example.com">link text</a></p>');
    }

    public function test_lists(): void
    {
        expect($this->render('* item 1'))->toBe("<ul>\n<li>item 1</li>\n</ul>");
        expect($this->render('1. item 1'))->toBe("<ol>\n<li>item 1</li>\n</ol>");
    }

    public function test_fenced_code(): void
    {
        expect($this->render("```\nfoobar\n```"))->toBe('<pre><code>foobar</code></pre>');
        expect($this->render("```php\nfoobar\n```"))->toBe('<pre><code class="language-php">foobar</code></pre>');
        expect($this->render("```narf\nfoobar\n```"))->toBe('<pre><code class="language-narf">foobar</code></pre>');
    }
}
