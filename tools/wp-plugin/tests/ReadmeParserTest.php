<?php
declare(strict_types=1);

namespace Tests\AspireBuild\Tools\WpPlugin;

use AspireBuild\Tools\WpPlugin\ReadmeParser;
use PHPUnit\Framework\TestCase;

class ReadmeParserTest extends TestCase
{

    public function testParse(): void
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

            <li>anything in list tags will do</li>
            <li>it doesn't seem to have to be a link</li>

            ## Changelog

            This looks like free-form markdown

            * But nonetheless it usually has bullet points.
            * So let's have more bullet points
            * like this one
            END;

        $parser = new ReadmeParser();
        $readme = $parser->parse($hello_cthulhu);

        $this->assertEquals('Hello Cthulhu', $readme->name);
        $this->assertEquals(['cthulhu', 'ftagn', 'rlyeh', 'eldritch'], $readme->tags);
        $this->assertEquals('6.6', $readme->requires);
        $this->assertEquals('7.9', $readme->tested);
        $this->assertEquals('8.8', $readme->requires_php);

        $this->assertEquals(['chaz', 'chazworks'], $readme->contributors);
        $this->assertEquals('6.6.6', $readme->stable_tag);
        $this->assertEquals('https://www.gofundyourself.com/c/hello-cthulhu', $readme->donate_link);
        $this->assertEquals(
            'This is not just a plugin, it symbolizes the mounting horror and insanity of an entire generation.  Ia! Ia! Cthulhu Ftagn!',
            $readme->short_description,
        );
        $this->assertEquals('GPL 3.0 or later', $readme->license);
        $this->assertEquals('https://gnu.org', $readme->license_uri);
        $this->assertArrayHasKey('description', $readme->sections);
        $this->assertArrayHasKey('faq', $readme->sections);
        $this->assertArrayHasKey('changelog', $readme->sections);
        $this->assertEquals(
            'Your husk will be discarded when it is no longer of use.  You are not to concern yourself with it.',
            $readme->upgrade_notice[''],
        );
        $this->assertEquals('anything in list tags will do', $readme->screenshots['1']);
        $this->assertEquals('it doesn\'t seem to have to be a link', $readme->screenshots['2']);
        $this->assertEquals('No.', $readme->faq['Is this a FAQ?']);
    }
}
