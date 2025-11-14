<?php
declare(strict_types=1);

namespace Tests\AspireBuild\Tools\WpPlugin;

use AspireBuild\Tools\WpPlugin\ReadmeParser;
use PHPUnit\Framework\TestCase;

class ReadmeParserTest extends TestCase
{

    public function test_parse_hello_cthulhu(): void
    {
        $hello_cthulhu = <<<'END'
            === Hello Cthulhu ===
            Contributors: chaz, chazworks
            Stable tag: 6.6.6
            Tested up to: 7.9
            Requires PHP: 8.8
            Tags: cthulhu, ftagn, rlyeh, eldritch
            Requires at least: 6.6
            Donate Link: https://www.gofundyourself.com/c/hello-cthulhu
            License: GPL 3.0 or later
            License URI: https://gnu.org
            This is not just a plugin, it symbolizes the mounting horror and insanity of an entire generation.  Ia! Ia! Cthulhu Ftagn!
            == Description ==

            This is not just a plugin, it symbolizes the mounting horror and insanity of an entire generation.  Ia! Ia! Cthulhu Ftagn!.

            When activated you will notice nothing, but gradually care about nothing, until your soul is an empty vessel
            into which the visions of his grand dread majesty will materialize and take form through your husk of a body
            as you do his bidding in the hopes that you shall be among those to watch the flames which consume the universe
            to base ash so that you may be be last to be burned in unholy eldritch fire.

            _Ph'nglui mglw'nafh Cthulhu R'lyeh wgah'nagl fhtagn_

            Oh, and it also shows lyrics from Louis Armstrong's famous song <title>Hello Dolly</title> on your admin dashboard.

            ## Upgrade Notice

            Your husk will be discarded when it is no longer of use.  You are not to concern yourself with it.

            ## FAQ

            Note that "Frequently Asked Questions" is apparently not recognized as a section despite the alias.

            ### Is this a FAQ?

            No.

            ## Other Notes

            other notes here... (parsed or not, no idea)

            ## Screenshots

            Screenshots go here but only things in list tags make it into the property

            * anything in a markdown list will do
            * it doesn't seem to have to be a link

            ## Changelog

            This looks like free-form markdown

            * But nonetheless it usually has bullet points.
            - So let's have more bullet points
            * like this one
            END;

        $parser = new ReadmeParser();
        $readme = $parser->parse($hello_cthulhu);

        $arr = (array)$readme;
        $sections = $arr['sections']; // tested separately

        $this->assertEquals([
            'name'              => 'Hello Cthulhu',
            'short_description' => 'This is not just a plugin, it symbolizes the mounting horror and insanity of an entire generation.  Ia! Ia! Cthulhu Ftagn!',
            'tags'              => ['cthulhu', 'ftagn', 'rlyeh', 'eldritch'],
            'requires'          => '6.6',
            'tested'            => '7.9',
            'requires_php'      => '8.8',
            'contributors'      => ['chaz', 'chazworks'],
            'stable_tag'        => '6.6.6',
            'donate_link'       => 'https://www.gofundyourself.com/c/hello-cthulhu',
            'license'           => 'GPL 3.0 or later',
            'license_uri'       => 'https://gnu.org',
            'sections'          => $sections,
            '_warnings'         => [],
        ], (array)$readme);

        $description = <<<'END'
            <p>This is not just a plugin, it symbolizes the mounting horror and insanity of an entire generation.  Ia! Ia! Cthulhu Ftagn!.</p>
            <p>When activated you will notice nothing, but gradually care about nothing, until your soul is an empty vessel
            into which the visions of his grand dread majesty will materialize and take form through your husk of a body
            as you do his bidding in the hopes that you shall be among those to watch the flames which consume the universe
            to base ash so that you may be be last to be burned in unholy eldritch fire.</p>
            <p><em>Ph'nglui mglw'nafh Cthulhu R'lyeh wgah'nagl fhtagn</em></p>
            <p>Oh, and it also shows lyrics from Louis Armstrong's famous song &lt;title&gt;Hello Dolly&lt;/title&gt; on your admin dashboard.
            other notes here... (parsed or not, no idea)</p>
            END;
        $this->assertEquals($description, $sections['description']);

          $faq = <<<'END'
            <p>Note that &quot;Frequently Asked Questions&quot; is apparently not recognized as a section despite the alias.</p>
            <h3>Is this a FAQ?</h3>
            <p>No.</p>
            END;
          $this->assertEquals($faq, $sections['faq']);

          $screenshots = <<<'END'
            <p>Screenshots go here but only things in list tags make it into the property</p>
            <ul>
            <li>anything in a markdown list will do</li>
            <li>it doesn't seem to have to be a link</li>
            </ul>
            END;
          $this->assertEquals($screenshots, $sections['screenshots']);

          $changelog = <<<'END'
            <p>This looks like free-form markdown</p>
            <ul>
            <li>But nonetheless it usually has bullet points.</li>
            <li>So let's have more bullet points</li>
            <li>like this one</li>
            </ul>
            END;
          $this->assertEquals($changelog, $sections['changelog']);


          $upgrade_notice = <<<'END'
            <p>Your husk will be discarded when it is no longer of use.  You are not to concern yourself with it.</p>
            END;
          $this->assertEquals($upgrade_notice, $sections['upgrade_notice']);
    }
}
