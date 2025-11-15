<?php

namespace Tests\AspireBuild\Tools\Sideways;

use AspireBuild\Tools\Sideways\Sideways;
use DirectoryIterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SidewaysTest extends TestCase
{

    private $dirs;
    protected $Sideways;

    protected function setUp(): void
    {
        parent::setUp();
        $this->Sideways = $this->initSideways();
    }

    /**
     * @return Sideways
     */
    protected function initSideways()
    {
        return new TestSideways();
    }

    /**
     * @param $test
     * @param $dir
     */
    #[DataProvider('data')]
    function test_data_dir($test, $dir)
    {
        $markdown = file_get_contents("$dir/$test.md");

        $expectedMarkup = file_get_contents("$dir/$test.html");

        $expectedMarkup = str_replace("\r\n", "\n", $expectedMarkup);
        $expectedMarkup = str_replace("\r", "\n", $expectedMarkup);

        $this->Sideways->setSafeMode(substr($test, 0, 3) === 'xss');
        $this->Sideways->setStrictMode(substr($test, 0, 6) === 'strict');

        $actualMarkup = $this->Sideways->text($markdown);

        $this->assertEquals($expectedMarkup, $actualMarkup);
    }

    function testRawHtml()
    {
        $markdown = "```php\nfoobar\n```";
        $expectedMarkup = '<pre><code class="language-php"><p>foobar</p></code></pre>';
        $expectedSafeMarkup = '<pre><code class="language-php">&lt;p&gt;foobar&lt;/p&gt;</code></pre>';

        $unsafeExtension = new UnsafeExtension;
        $actualMarkup = $unsafeExtension->text($markdown);

        $this->assertEquals($expectedMarkup, $actualMarkup);

        $unsafeExtension->setSafeMode(true);
        $actualSafeMarkup = $unsafeExtension->text($markdown);

        $this->assertEquals($expectedSafeMarkup, $actualSafeMarkup);
    }

    function testTrustDelegatedRawHtml()
    {
        $markdown = "```php\nfoobar\n```";
        $expectedMarkup = '<pre><code class="language-php"><p>foobar</p></code></pre>';
        $expectedSafeMarkup = $expectedMarkup;

        $unsafeExtension = new TrustDelegatedExtension;
        $actualMarkup = $unsafeExtension->text($markdown);

        $this->assertEquals($expectedMarkup, $actualMarkup);

        $unsafeExtension->setSafeMode(true);
        $actualSafeMarkup = $unsafeExtension->text($markdown);

        $this->assertEquals($expectedSafeMarkup, $actualSafeMarkup);
    }

    public static function data(): array
    {
        $data = [];

        $dir = __DIR__ . '/data/';
        $Folder = new DirectoryIterator($dir);

        foreach ($Folder as $File) {
            /** @var $File DirectoryIterator */

            if (!$File->isFile()) {
                continue;
            }

            $filename = $File->getFilename();

            $extension = pathinfo($filename, PATHINFO_EXTENSION);

            if ($extension !== 'md') {
                continue;
            }

            $basename = $File->getBasename('.md');

            if (file_exists($dir . $basename . '.html')) {
                $data [] = [$basename, $dir];
            }
        }

        return $data;
    }

    public function test_no_markup()
    {
        $markdownWithHtml = <<<MARKDOWN_WITH_MARKUP
            <div>_content_</div>

            sparse:

            <div>
            <div class="inner">
            _content_
            </div>
            </div>

            paragraph

            <style type="text/css">
                p {
                    color: red;
                }
            </style>

            comment

            <!-- html comment -->
            MARKDOWN_WITH_MARKUP;

        $expectedHtml = <<<EXPECTED_HTML
            <p>&lt;div&gt;<em>content</em>&lt;/div&gt;</p>
            <p>sparse:</p>
            <p>&lt;div&gt;
            &lt;div class="inner"&gt;
            <em>content</em>
            &lt;/div&gt;
            &lt;/div&gt;</p>
            <p>paragraph</p>
            <p>&lt;style type="text/css"&gt;
            p {
            color: red;
            }
            &lt;/style&gt;</p>
            <p>comment</p>
            <p>&lt;!-- html comment --&gt;</p>
            EXPECTED_HTML;

        $sidewaysWithNoMarkup = new TestSideways();
        $sidewaysWithNoMarkup->setMarkupEscaped(true);

        $this->assertEquals($expectedHtml, $sidewaysWithNoMarkup->text($markdownWithHtml));
    }

    public function testLateStaticBinding()
    {
        $sideways = Sideways::instance();
        $this->assertInstanceOf(Sideways::class, $sideways);

        // After instance is already called on Sideways
        // subsequent calls with the same arguments return the same instance
        $sameSideways = TestSideways::instance();
        $this->assertInstanceOf(Sideways::class, $sameSideways);
        $this->assertSame($sideways, $sameSideways);

        $testSideways = TestSideways::instance('test late static binding');
        $this->assertInstanceOf(TestSideways::class, $testSideways);

        $sameInstanceAgain = TestSideways::instance('test late static binding');
        $this->assertSame($testSideways, $sameInstanceAgain);
    }
}
