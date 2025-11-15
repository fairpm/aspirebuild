<?php
declare(strict_types=1);

namespace Tests\AspireBuild\Tools\WpPlugin;

use AspireBuild\Tools\WpPlugin\ReadmeParser;
use PHPUnit\Framework\TestCase;

class ReadmeParserTest extends TestCase
{
    public function test_parse_yolo_seo(): void
    {
        // Tests the range of our parser while remaining relatively compliant with the established readme format.
        $yolo_seo_readme = <<<'END'
            === YOLO SEO – Move Fast & Break Your Site Ranking ===
            Contributors: nobody you know, just you
            Tags: SEO, YOLO & YAGNI!, meta something, schema
            Tested up to: 9.6
            Requires at least: 2.7
            Requires PHP: 6.6
            Stable tag: .9
            License: GPLv8 or earlier
            License URI: https://www.example.org

            YOLO SEO is the least powerful WordPress SEO plugin 〰 no kidding!

            == Description ==

            ### YOLO SEO - The Worst WordPress Plugin & Toolkit ###

            It would probably be a good idea to put more description here

            Our users consistently rate [YOLOSEO](https://yoloseo.example.org/?utm_source=unit_tests&utm_medium=link 'Something from Nothing SEO') as the most useless WordPress plugin ever to have been created.

            > <strong>YOLO-SEO Pro</strong><br />
            > If you [pay us more for some reason](https://yoloseo.example.io/?utm_source=unit_tests&utm_medium=link&utm_campaign=liteplugin 'Something from Nothing SEO Pro') then you get a bonus of nothing!

            [youtube https://youtu.be/dQw4w9WgXcQ?si=cCtl1K4wsYVfkpGv]

            ### What Makes YOLO-SEO Better than Other SEO Plugins ###

            Nothing whatsoever.  We can't even get the name of the plugin consistent.

            * **Inscrutable Setup Wizard**
            Our setup wizard consists of editing a php file with no documentation whatsoever.  No hints here either, bub!

            ### Advanced Features ###

            * **Total Control**
            Since YOLO-SEO doesn't actually function, you have to fix everything yourself, so you get to
            learn how the system functions from the ground up!

            == Changelog ==

            **New in Version 496.381.7.16 **

            * 🐐: Added support for .goat images.

            **New in Version 496.381.7.15 **

            * changed the architecture of the entire plugin
            (5,376 files changed with 8,255 additions and 97,022 deletions).

            **New in Version 496.381.7.14 **

            * fix typo

            == Frequently Asked Questions ==

            Please [go here first](https://dev-null-as-a-service.com) for help.

            = Who should use YOLO:SEO? =

            Not anyone sane.  But you're still reading, aren't you?

            = Will YOLO_SEO slow down my website? =

            It'll probably bring it to a crawl.

            = Why are these subheadings in h1 and the main headings in h2? =

            You know why.

            == Screenshots ==

            1. Numbered list items ([foo](http://bar.baz))
            2. Turn into screenshot links

            == Random Section That Doesn't Belong Anywhere ==

            > Here's a quoted line
            > And another right below it.

                                                               Here's a way-indented line

            And another regular line.


            == Upgrade Notice ==

            = 3.1.2 =

            This update did stuff and I don't remember what.  YOLO.
            END;

        $parser = new ReadmeParser();
        $readme = $parser->parse($yolo_seo_readme);

        $arr = $readme->jsonSerialize();
        $sections = $arr['sections'];

        $this->assertEquals([
            'name'              => 'YOLO SEO – Move Fast &amp; Break Your Site Ranking',
            'short_description' => 'YOLO SEO is the least powerful WordPress SEO plugin 〰 no kidding!',
            'tested'            => '9.6',
            'requires_php'      => '6.6',
            'requires_wp'       => '2.7',
            'contributors'      => ['nobody you know', 'just you'],
            'stable_tag'        => '0.9',
            'sections'          => $sections,
            'tags'              => ['SEO', 'YOLO & YAGNI!', 'meta something', 'schema'],
            'donate_link'       => '',
            'license'           => 'GPLv8 or earlier',
            'license_uri'       => 'https://www.example.org',
            '_warnings'         => [],
        ], $arr);

        $expected_description = <<<'END'
            <h3>YOLO SEO - The Worst WordPress Plugin &amp; Toolkit</h3>
            <p>It would probably be a good idea to put more description here</p>
            <p>Our users consistently rate <a href="https://yoloseo.example.org/?utm_source=unit_tests&amp;utm_medium=link" title="Something from Nothing SEO">YOLOSEO</a> as the most useless WordPress plugin ever to have been created.</p>
            <blockquote>
            <p>&lt;strong&gt;YOLO-SEO Pro&lt;/strong&gt;&lt;br /&gt;
            If you <a href="https://yoloseo.example.io/?utm_source=unit_tests&amp;utm_medium=link&amp;utm_campaign=liteplugin" title="Something from Nothing SEO Pro">pay us more for some reason</a> then you get a bonus of nothing!</p>
            </blockquote>
            <p>[youtube <a href="https://youtu.be/dQw4w9WgXcQ?si=cCtl1K4wsYVfkpGv">https://youtu.be/dQw4w9WgXcQ?si=cCtl1K4wsYVfkpGv</a>]</p>
            <h3>What Makes YOLO-SEO Better than Other SEO Plugins</h3>
            <p>Nothing whatsoever.  We can't even get the name of the plugin consistent.</p>
            <ul>
            <li><strong>Inscrutable Setup Wizard</strong>
            Our setup wizard consists of editing a php file with no documentation whatsoever.  No hints here either, bub!</li>
            </ul>
            <h3>Advanced Features</h3>
            <ul>
            <li><strong>Total Control</strong>
            Since YOLO-SEO doesn't actually function, you have to fix everything yourself, so you get to
            learn how the system functions from the ground up!
            &lt;h3&gt;Frequently Asked Questions&lt;/h3&gt;
            Please <a href="https://yoloseo.example.dev/docs">sod right off</a> if you think we'll help with anything.</li>
            </ul>
            <p>= Who should use YOLO:SEO? =</p>
            <p>Not anyone sane.  But you're still reading, aren't you?</p>
            <p>= Will YOLO_SEO slow down my website? =</p>
            <p>It'll probably bring it to a crawl.&lt;h3&gt;Random Section That Doesn't Belong Anywhere&lt;/h3&gt;</p>
            <blockquote>
            <p>Here's a quoted line
            And another right below it.</p>
            </blockquote>
            <pre><code>                                               Here's a way-indented line</code></pre>
            <p>And another regular line.</p>
            END;

        $expected_faq = <<<'END'
            first faq is where is the faq?
            END;

        $wrong_expected_screenshots = <<<'END'
            <ol>
            <li>Numbered list items (<a href="http://bar.baz">foo</a>)</li>
            <li>Turn into screenshot links</li>
            </ol>
            END;

        $expected_screenshots = $wrong_expected_screenshots;

        $expected_changelog = <<<'END'
            <p><strong>New in Version 496.381.7.16 </strong></p>
            <ul>
            <li>🐐: Added support for .goat images.</li>
            </ul>
            <p><strong>New in Version 496.381.7.15 </strong></p>
            <ul>
            <li>changed the architecture of the entire plugin
            (5,376 files changed with 8,255 additions and 97,022 deletions).</li>
            </ul>
            <p><strong>New in Version 496.381.7.14 </strong></p>
            <ul>
            <li>fix typo</li>
            </ul>
            END;

        $expected_upgrade_notice = <<<'END'
            <p>= 3.1.2 =</p>
            <p>This update did stuff and I don't remember what.  YOLO.</p>
            END;

        $this->assertEquals([
            'description' => $expected_description,
            // 'faq'         => $expected_faq,
            'screenshots' => $expected_screenshots,
            'changelog'   => $expected_changelog,
            'upgrade_notice' => $expected_upgrade_notice,
        ], $sections);
    }


    public function test_parse_hello_dolly(): void
    {
        $hello_dolly = <<<'END'
            === Hello Dolly ===
            Contributors: matt, wordpressdotorg
            Stable tag: 1.7.2
            Tested up to: 6.9
            Requires at least: 4.6

            This is not just a plugin, it symbolizes the hope and enthusiasm of an entire generation summed up in two words sung most famously by Louis Armstrong.

            == Description ==

            This is not just a plugin, it symbolizes the hope and enthusiasm of an entire generation summed up in two words sung most famously by Louis Armstrong: Hello, Dolly. When activated you will randomly see a lyric from <cite>Hello, Dolly</cite> in the upper right of your admin screen on every page.

            Thanks to Sanjib Ahmad for the artwork.
            END;

        $parser = new ReadmeParser();
        $readme = $parser->parse($hello_dolly);

        $arr = $readme->jsonSerialize();
        $sections = $arr['sections'];

        $this->assertEquals([
            'name'              => 'Hello Dolly',
            'short_description' => 'This is not just a plugin, it symbolizes the hope and enthusiasm of an entire generation summed up in two words sung most famously by Louis Armstrong.',
            'tested'            => '6.9',
            'requires_php'      => '',
            'requires_wp'       => '4.6',
            'contributors'      => ['matt', 'wordpressdotorg'],
            'stable_tag'        => '1.7.2',
            'sections'          => $sections,
            'tags'              => [],
            'donate_link'       => '',
            'license'           => '',
            'license_uri'       => '',
            '_warnings'         => [],
        ], $arr);

        $this->markTestIncomplete('tags need to be stripped from the description');

        $expected_description = "<p>This is not just a plugin, it symbolizes the hope and enthusiasm of an entire generation summed up in two words sung most famously by Louis Armstrong: Hello, Dolly. When activated you will randomly see a lyric from Hello, Dolly in the upper right of your admin screen on every page.</p>\n<p>Thanks to Sanjib Ahmad for the artwork.</p>\n";

        $this->assertEquals(['description' => $expected_description], $sections);
    }

    public function test_parse_aioseo(): void
    {
        $aioseo_readme = <<<'END'
            === All in One SEO – Powerful SEO Plugin to Boost SEO Rankings & Increase Traffic ===
            Contributors: aioseo, smub, benjaminprojas
            Tags: SEO, Google Search Console, XML Sitemap, meta description, schema
            Tested up to: 6.8
            Requires at least: 5.7
            Requires PHP: 7.2
            Stable tag: 4.9.0
            License: GPLv3 or later
            License URI: https://www.gnu.org/licenses/gpl-3.0.txt

            AIOSEO is the most powerful WordPress SEO plugin. Improve SEO rankings and traffic with comprehensive SEO tools and smart AI SEO optimizations!

            == Description ==

            ### AIOSEO - The Best WordPress SEO Plugin & Toolkit ###

            All in One SEO is the original WordPress SEO plugin started in 2007. Today, over 3 million website owners and SEO experts use AIOSEO for higher SEO rankings.

            Our users consistently rate [AIOSEO](https://aioseo.com/?utm_source=wprepo&utm_medium=link&utm_campaign=liteplugin 'All in One SEO for WordPress') as the most comprehensive WordPress SEO plugin and marketing toolkit. It's the fastest way to optimize WordPress SEO settings, add schema markup, create XML sitemap, add local SEO, track SEO keyword rankings, automate internal linking, perform SEO audits, add Author SEO (EEAT), monitor SEO revisions, connect Google search console, and basically everything a SEO Pro would use to rank higher in search engines.

            We have AI SEO features that help you optimize your posts for SEO by automatically generating SEO titles, meta descriptions, FAQs, key points, social media posts, and more.

            > <strong>AIOSEO Pro</strong><br />
            > This is the lite version of the All in One WordPress SEO Pro plugin that comes with all the powerful SEO features you will ever need to rank higher in search engines including **smart SEO schema markup, advanced SEO modules, powerful SEO sitemap suite, local SEO module, SEO keyword ranking tracking, automatic internal linking, WooCommerce SEO**, and tons more. [Click here to purchase the best premium WordPress SEO plugin now!](https://aioseo.com/?utm_source=wprepo&utm_medium=link&utm_campaign=liteplugin 'All in One SEO for WordPress')

            Here's why smart business owners, SEO experts, marketers, and developers love AIOSEO, and you will too!

            [youtube https://youtu.be/UbOYEEIvXvY]

            ### What Makes AIOSEO Better than Other WordPress SEO Plugins ###

            AIOSEO is leading the innovation in WordPress SEO space, and our SEO features will give you a competitive advantage.

            * **Easy SEO Setup Wizard**
            Our SEO setup wizard optimizes your website's SEO settings based on your unique industry needs in less than 5 minutes.

            * **Smart Schema Markup (aka Rich Snippets)**
            Get better click through rate (CTR) and Google rich featured snippets using advanced SEO schema markups like FAQ schema, product schema, recipe schema markup, and dozens more using our custom [Schema Generator](https://aioseo.com/features/rich-snippets-schema/?utm_source=wprepo&utm_medium=link&utm_campaign=liteplugin 'Schema Generator').

            * **AI Content**
            Create anything you need, such as blog articles and tables, with our AI Assistant block. Generate stunning visuals instantly with the built-in AI Image Generator. Save time by automatically generating SEO titles, meta descriptions, FAQs, key points, social media posts, and more with our [AI Content Generator](https://aioseo.com/features/ai-content/?utm_source=wprepo&utm_medium=link&utm_campaign=liteplugin 'AI Content').

            * **Unlimited SEO Keywords**
            Optimize for unlimited SEO keywords using our SEO content analyzer. Our TruSEO score gives you detailed content & readability analysis, so you can get higher SEO rankings.

            * **Google Keyword Rank Tracking**
            Easily track how your website is ranking for different keywords in Google from your [WordPress dashboard](https://aioseo.com/features/search-statistics/?utm_source=wprepo&utm_medium=link&utm_campaign=liteplugin 'Google Keyword Rank Tracker').

            * **Automatic Link Assistant**
            Automate internal links between your pages using our smart [internal linking algorithm](https://aioseo.com/features/internal-link-assistant/?utm_source=wprepo&utm_medium=link&utm_campaign=liteplugin 'Link Assistant') that will help improve on-page SEO.

            * **Local Business SEO**
            Improve your local SEO presence with local business schema, support for multiple local store locations, business opening hours, Google Maps integration, contact info (business email, business phone, business address, etc), and more with our [Local SEO module](https://aioseo.com/features/local-seo/?utm_source=wprepo&utm_medium=link&utm_campaign=liteplugin 'Local SEO').

            * **Site Audit**
            Get a detailed report of SEO issues for all posts and terms on your site, discover why these issues are important and how you can fix them.

            * **SEO Revisions**
            Keep a [historical record of SEO changes](https://aioseo.com/seo-revisions/?utm_source=wprepo&utm_medium=link&utm_campaign=liteplugin 'SEO Revisions'), monitor the impact of changes, and restore previous versions in one click.

            * **Content Decay Tracking**
            Never lose traffic to competitors. Quickly detect which content is losing traffic / SEO rankings, so you can optimize it to regain your rankings with our [Search Statistics module](https://aioseo.com/features/search-statistics/?utm_source=wprepo&utm_medium=link&utm_campaign=liteplugin 'Search Statistics').

            * **Smart XML Sitemap**
            Advanced XML sitemaps to boost your SEO rankings (with easy setup inside Google Search Console). Also includes Video SEO XML sitemap, News SEO XML sitemap, RSS sitemap, and HTML sitemap.

            * **Smart SEO Redirects**
            The most powerful [SEO Redirection manager](https://aioseo.com/features/redirection-manager/?utm_source=wprepo&utm_medium=link&utm_campaign=liteplugin 'Redirection Manager') for setting up advanced SEO redirects including 301 redirects, 302, 307, 410, 404 redirection, REGEX redirects, and more.

            * **404 Error Monitor**
            Automatic 404 error monitor helps you track and redirect 404 errors, so you don't lose SEO rankings.

            * **Author SEO**
            Add [custom author profile pages, author bio box, and relevant author schema](https://aioseo.com/features/author-seo-google-e-e-a-t/?utm_source=wprepo&utm_medium=link&utm_campaign=liteplugin 'Author SEO (E-E-A-T)') to boost Google EEAT score to help with Google's Helpful Content Update (HCU).

            * **LLMs.txt Generator**
            Generate an llms.txt file to help AI engines discover your site's content more easily so your content can rank in AI search results.

            * **SEO Audit Checklist**
            Improve your SEO ranking with our comprehensive SEO audit checklist.

            * **Knowledge Graph Support**
            Improve your website's search appearance with SEO Knowledge panel.

            * **Table of Contents**
            Automatically generate a table of content, customize headings, anchors, and you can also hide or reorder the headings.

            ### Advanced SEO Plugin Features ###

            * **User Access Control**
            Control who can manage your SEO settings with our advanced SEO access control.

            * **WordPress REST API**
            Manage your SEO metadata with WordPress REST API. Great for headless WordPress installations.

            * **Advanced Robots Meta SEO Settings**
            Granular controls for no index, no follow, no archive, no snippet, max snippet, max video, etc.

            * **RSS Content for SEO**
            Stop content scraping from hurting your SEO rankings.

            * **Full Site Redirects**
            Merging websites or switching domains? Full site redirect makes it easy to switch domains without losing SEO rankings.

            * **Smart Meta Title & Description**
            Automatic SEO generation, dynamic SEO smart tags, include Emoji, add shortcodes, and more features to stand out in search results.

            * **Smart Breadcrumbs**
            Add Breadcrumb navigation to improve user experience and boost your SEO rankings. Comes with full SEO JSON+LD support.

            * **Automatic Image SEO**
            Helps your images rank higher by autogenerating image title, clean SEO image filenames, and more.

            * **Advanced SEO Canonical URLs**
            Prevent duplicate content in SEO with automatic canonical URLs.

            * **SEO Cleanup / Manual SEO Penalty Removal**
            Domains Report feature in Link Assistant automatically removes all links for specific domains with just one click. Huge time saver when doing SEO cleanups.

            * **Link Opportunities Report**
            See better internal link opportunities with our smart algorithm. Easily add internal links with just a few clicks.

            * **Robots.txt Editor**
            Manage and customize SEO robots.txt files in WordPress.

            * **Crawl Quota Management**
            Crawl Cleanup feature manages your search engine crawl quota and index your important content faster.

            * **Title and Nofollow for SEO**
            Easily add title and nofollow to external links.

            * **Headline Analyzer**
            Analyze your page / posts headlines to improve CTR and SEO rankings.

            * **Competitor Site SEO Analysis**
            Use competitor SEO analysis to outrank them with better SEO optimization.

            * **SEO Code Snippets**
            Integration with [WPCode plugin](https://wordpress.org/plugins/insert-headers-and-footers/) for SEO code snippets to further customize every aspect of your SEO.


            ### WordPress SEO Integrations ###

            * **Google Search Console Integration**
            Connect with Google webmaster tools and Google Search Console to see SEO insights (like content rankings, keyword rankings, page speed insights, post index status, etc) directly in your WordPress dashboard.

            * **WooCommerce SEO**
            Improves your WooCommerce SEO rankings. Easily optimize WooCommerce product pages, product categories, and more for best eCommerce SEO results.

            * **Knowledge Panel SEO**
            Improve website SEO appearance by adding social media profile links for Facebook, Twitter, Wikipedia, Instagram, LinkedIn, Yelp, YouTube, and more.

            * **Webmaster Tool Integrations**
            Connect with all webmaster tools including Google Search Console, Bing SEO, Yandex SEO, Baidu SEO, Google Analytics, Pinterest site verification, and more.

            * **Social Media Integration**
            Facebook SEO, Twitter SEO, and Pinterest SEO with better website previews.

            * **Google AMP SEO Integration**
            Improve your mobile SEO rankings with Google AMP SEO.

            * **Semrush SEO integration**
            See additional SEO keywords with Semrush SEO integration.

            * **Microsoft Clarity Integration**
            See visitor interactions with heatmaps and session recordings.

            * **IndexNow Integration**
            Instantly notify Bing and Yandex for faster SEO indexing.

            * **Elementor SEO**
            Better Elementor SEO for landing pages.

            * **Divi SEO**
            Better Divi SEO for landing pages.

            * **Avada SEO**
            Better Avada SEO for landing pages.

            * **WP Bakery SEO**
            Better WP Bakery SEO for landing pages.

            * **SeedProd SEO**
            Optimize SeedProd landing pages for SEO.

            * **SiteOrigin SEO**
            Better SiteOrigin SEO for landing pages.

            * **Open Graph Support**
            Improve SEO rankings with open graph meta data.

            ### WordPress SEO Plugin Importer ###

            Not happy with your current SEO plugin? We make SEO migration easy with our point-and-click automated SEO data transfer tool. We currently support SEO migration from following SEO tools:

            * Yoast SEO Importer
            * Yoast SEO Premium Importer
            * RankMath SEO Importer
            * SEOPress

            We also support importing SEO redirects from the following plugins:

            * Redirection Plugin
            * Simple 301 Redirects Importer
            * Safe Redirect Manager
            * 301 Redirects Importer

            Aside from that, our SEO migration tool also helps you with:

            * Import / Export AIOSEO settings from one site to another
            * Create SEO Settings Backup
            * CSV Sitemap Import to Import additional pages to your XML Sitemaps


            **Now you can see why AIOSEO is often rated the best SEO plugin in WordPress.**

            Give AIOSEO a try.

            Want to unlock more SEO features? [Upgrade to AIOSEO Pro](https://aioseo.com/?utm_source=wprepo&utm_medium=link&utm_campaign=liteplugin 'All in One SEO for WordPress').

            ### Credits ###

            This plugin is created by [Benjamin Rojas](https://benjaminrojas.net/ 'Benjamin Rojas') and [Syed Balkhi](https://syedbalkhi.com/ 'Syed Balkhi').

            ### Branding Guideline ###

            AIOSEO&reg; is a registered trademark of Semper Plugins LLC. When writing about the WordPress SEO plugin by AIOSEO, please use the following format.

            * AIOSEO (correct)
            * All in One SEO (correct)
            * AIO SEO (incorrect)
            * All in 1 SEO (incorrect)
            * AISEO (incorrect)

            == Changelog ==

            **New in Version 4.9.0**

            * New: Table of Contents Block 2.0 - Our revamped Table of Contents block now supports multiple blocks on the same page, with a standalone or synced mode. We also added accordion support so you can collapse or expand it.
            * New: Recipe Block - Highlight your best recipes with a new block that also comes with schema markup to get your recipes featured in search results.
            * New: Products Block - Showcase your products with granular controls and Product schema markup to drive more organic traffic to your page.
            * Fixed: Rare PHP error when action scheduler arguments are not a JSON object.
            * Fixed: DB lock issue when checking table schema in MariaDB.

            **New in Version 4.8.9**

            * Updated: Improved user permission checks to display Site Audit action buttons.
            * Updated: Added filter to disable AI Image Generator buttons in the block editor.
            * Fixed: Conflict with Avada theme where post content was disappearing in backend editor.
            * Fixed: Elementor Side Cart automatically opening even when the cart is empty on single product pages.
            * Fixed: Category title missing from meta descriptions on new sites.
            * Fixed: Redundant schema queries when database schema cache fails to update.
            * Fixed: JS error when accessing string offsets in SEO revisions data processing.
            * Fixed: Keyword Rank Tracker button broke in the Post editor if the Spectra or Starter Templates plugin was activated.
            * Fixed: Homepage meta description character counter breaks when the site is set as an Organization.
            * Fixed: New feature popups rendering outside the viewport on smaller screens.

            **New in Version 4.8.8**

            * New: AI Assistant Block - Generate any type of content right inside the post editor: blog articles, summaries, comparison tables, and more. Whatever you need, the AI Assistant block makes it happen.
            * New: AI Image Generator - Instantly create eye-catching visuals for your posts and use them anywhere—from featured images to inline content. You can even edit existing images to give them a unique twist.
            * New: LLMs.txt Improvements – The new llms-full.txt file makes it easy for AI engines to index your site without overloading your server. We’ve also added post-to-Markdown conversion and new settings to control exactly what content gets included.
            * Updated: All existing AI Content features have been made compatible with all our supported page builders. You can now auto-generate SEO titles, meta descriptions, FAQs, keypoints and social posts directly inside Elementor, Divi, SeedProd, Avada, WPBakery, SiteOrigin and Thrive Architect!
            * Updated: The llms.txt file is now generated as a static file, removing the need for rewrite rules (e.g. on WP Engine).
            * Updated: Moved llms.txt under Sitemaps menu.
            * Fixed: Site Audit sometimes not showing results when all content types are included.
            * Fixed: PHP error when Site Audit cannot scan post due to uninstantiated social class.
            * Fixed: PHP error when dashboard widget failed to fetch RSS news feed.

            **New in Version 4.8.7.2**

            * Updated: Added additional hardening to REST API routes.

            **New in Version 4.8.7.1**

            * Fixed: WooCommerce products being automatically added to the cart.

            **New in Version 4.8.7**

            * Updated: Hardened API routes to prevent unauthorized access.
            * Updated: Added support for tracking breadcrumb setting changes in SEO Revisions.
            * Updated: Added support for WooCommerce GTIN field to Product schema.
            * Updated: Added support for .avif images to Image Sitemap and Image SEO addon.
            * Updated: Review limit for Product schema can now be modified via a new filter hook.
            * Updated: Use site URL instead of home URL for llms.txt to handle WP installs in subdirectories.
            * Updated: Remove all user meta when AIOSEO is uninstalled.
            * Updated: Improved performance of Cornerstone Content filtering.
            * Fixed: Product shipping details schema clearing cart content in rare case where logged-in user adds a product to the cart and then edits a product in the admin panel.
            * Fixed: ProfilePage schema sometimes invalid due to incorrect author URL.
            * Fixed: Hide AIOSEO custom link fields inside the Edit Link modal in WPBakery visual builder to prevent plugin conflict.
            * Fixed: AIOSEO Settings not successfully saving before post is published in WPBakery visual builder.
            * Fixed: The SEO title and description were not persisting if the post content is too large.
            * Fixed: Theme conflict with Neve where the EDD Checkout block does not show if Run Shortcodes is enabled.
            * Fixed: Breadcrumb block not updating when changing the post title.
            * Fixed: If WooCommerce brand is selected as primary category, it is now correctly used in the URL.
            * Fixed: Headline Analyzer layout breaks when entering very long headlines.

            **New in Version 4.8.6.1**

            * Updated: Prevent potential plugin conflicts due to the loading of dependencies multiple times.
            * Updated: Local SEO render options for Opening Hours are now hidden when feature is disabled.
            * Fixed: Loading spinner for SEO Revisions in sidebar menu not aligned.

            **New in Version 4.8.6**

            * New: Site Audit - Get a detailed report of SEO issues for all posts and terms on your site, discover why these issues are important and how you can fix them. 🔨
            * Fixed: Multisite subsite requests to Search Statistics server sometimes fail due to missing license.
            * Fixed: Rare PHP error when breadcrumbs cannot be determined for non-standard pages.

            **New in Version 4.8.5**

            * Updated: Author SEO (E-E-A-T) addon and Writing Assistant data are now fully removed when uninstalling the plugin.
            * Updated: Product Schema now favours WooCommerce brand field over its own custom field.
            * Updated: PHP version notices are now only shown to Administrators.
            * Fixed: Breadcrumbs taxonomy preview now correctly refreshes when user toggles between using the default settings
            * Fixed: WooCommerce Brand smart tag not correctly converted to expected value in previews.
            * Fixed: AIOSEO Details column now correctly reloads after quick-editing a post.
            * Fixed: Image URLs are no longer forcefully converted to lowercase in the Image Sitemap.
            * Fixed: Focus Keyword now saves correctly again for Divi built posts.
            * Fixed: Image SEO no longer breaks Divi built posts in rare cases.
            * Fixed: Multisite subsites no longer incorrectly inherit the "Block AI Crawlers" and "Block Crawling of Internal Site Search URLs" setting values from the main site in the network.
            * Fixed: Notifications no longer contain encoded HTML entities
            * Fixed: Default options for new redirects not localized.
            * Fixed: PHP error in rare cases when breadcrumb link is a WP_Error.
            * Fixed: PHP error due to plugin conflict with MasterStudy LMS.

            **See our [changelog on aioseo.com](https://aioseo.com/changelog/?utm_source=wprepo&utm_medium=link&utm_campaign=aioseo) for previous releases.**

            == Frequently Asked Questions ==

            Please visit our [complete AIOSEO documentation](https://aioseo.com/docs/?utm_source=wprepo&utm_medium=link&utm_campaign=liteplugin) before requesting support for SEO from the AIOSEO team.

            = Who should use AIOSEO? =

            SEO is essential for all websites. AIOSEO is perfect for business owners, bloggers, marketers, designers, developers, photographers, and basically everyone else. If you want to rank higher in search, then you need to use AIOSEO WordPress SEO plugin.

            = Which themes does AIOSEO support? =

            AIOSEO works with all WordPress themes. Simply enable AIOSEO to make your WordPress theme SEO friendly.

            = Will AIOSEO slow down my website? =

            Nope, AIOSEO will NOT slow down your website. We understand that speed is important for SEO, that's why our code is properly optimized for maximum performance. Remember, faster websites rank higher in search. Use AIOSEO for fast SEO improvements.

            = Can I use AIOSEO on client sites? =

            Yes, you can use AIOSEO on client websites.

            = Are AIOSEO sitemaps better than default WordPress sitemaps? =

            Yes, AIOSEO smart sitemaps are a lot more optimized than the default WordPress sitemaps. Once you enable AIOSEO, our XML sitemaps will override the default WordPress sitemaps, so you can improve your SEO rankings.

            We also offer advanced SEO sitemaps such as News Sitemap, Video Sitemap, and RSS Sitemap.

            Our SEO sitemaps come with granular control such as links per sitemap, enable / disable post types or taxonomies, include / exclude specific links from sitemap, add additional non-WordPress pages to sitemaps, customize sitemap priority & frequency for each section of your site, and more.

            This is why experts rate AIOSEO as the best WordPress SEO plugin.

            = Does AIOSEO help with SEO Verification? =

            Yes. AIOSEO can help you with website SEO verification with various webmaster tools such as Google Search Console, Bing Webmaster Tools, Yandex, Baidu, Pinterest, and just about every other site verification you need.

            = Why is AIOSEO better than other SEO plugins? =

            There are many WordPress SEO plugins out there. Unlike others, AIOSEO WordPress SEO plugin is always reliable. Our SEO features are results focused (no bloat), and we offer exceptional customer support.

            AIOSEO is the original WordPress SEO plugin, and it's trusted by over 3 million website owners.

            = Do I really need an XML Sitemap? =

            **Yes! XML Sitemaps help Google and other search engines to find all the pages of your website.**

            An XML sitemap is a list of all the content on your website. The sitemap helps search engine bots to easily see all the content on your site in one place, The XML sitemap file is hidden from your human visitors, however search engines like Google can see it.

            Without an XML sitemap, some of your web pages may never be included in Google search results, and won't get any traffic.

            XML Sitemaps also help you tell Google which pages you DON'T want included in search results. This can help your SEO to prevent keyword cannibalization and duplicate content issues.

            As part of your SEO strategy, **an XML sitemap can help you to improve your domain authority and unlock more traffic from Google, Bing and other search engines**.

            AIOSEO can easily help you get your sitemaps listed inside Google Search Console so your content can start to get indexed today!

            = Does AIOSEO integrate directly with Google Search Console? =

            Absolutely! Our integration with Google Search Console allows you to monitor and maintain your website's presence on Google. With our direct integration, you can easily view important information about your website, such as the number of clicks, impressions, and the average position for each keyword that your website's content appears for in Google search results. You can also track your contents page speed using Google's Page Speed Insights directly inside your WordPress dashboard.

            Additionally, AIOSEO can also provide you with data on the most frequently used keywords, the most popular pages on your website, and any crawl errors or security issues that may arise. By integrating with Google Search Console, AIOSEO provides website owners with valuable insights that can help to improve SEO and overall online visibility. With this integration, you can track your site's progress over time and make data-driven decisions that will help you achieve your SEO goals.

            == Screenshots ==

            1. SEO Content Analyzer (Gutenberg)
            2. SEO Content Analyzer (Classic Editor)
            3. SEO Setup Wizard
            4. SEO Site Analysis
            5. Webmaster Tools Connect
            6. Social Media Integrations
            7. Local SEO
            8. Sitemaps
            9. Search Appearance Settings
            10. Robots.txt Editor
            11. RSS Content Control
            12. Headline Analyzer
            13. Redirect Manager
            14. Link Assistant

            == Upgrade Notice ==

            = 4.9.0 =

            This update adds major improvements and bug fixes.
            END;

        $parser = new ReadmeParser();
        $parsed = $parser->parse($aioseo_readme);

        $arr = (array)$parsed;
        $sections = $arr['sections'];

        $this->assertEquals([
            'name'              => 'All in One SEO – Powerful SEO Plugin to Boost SEO Rankings &amp; Increase Traffic',
            'short_description' => 'AIOSEO is the most powerful WordPress SEO plugin. Improve SEO rankings and traffic with comprehensive SEO tools and smart AI SEO optimizations!',
            'contributors'      => ['aioseo', 'smub', 'benjaminprojas'],
            'tags'              => ['SEO', 'Google Search Console', 'XML Sitemap', 'meta description', 'schema'],
            'tested'            => '6.8',
            'requires_wp'       => '5.7',
            'donate_link'       => '',
            '_warnings'         => [],
            'requires_php'      => '7.2',
            'stable_tag'        => '4.9.0',
            'license'           => 'GPLv3 or later',
            'license_uri'       => 'https://www.gnu.org/licenses/gpl-3.0.txt',
            'sections'          => $sections,
        ], $arr);

        $expected_changelog = <<<END
            <p><strong>New in Version 4.8.9</strong></p>\n<ul>\n<li>Updated: Improved user permission checks to display Site Audit action buttons.</li>\n<li>Updated: Added filter to disable AI Image Generator buttons in the block editor.</li>\n<li>Fixed: Conflict with Avada theme where post content was disappearing in backend editor.</li>\n<li>Fixed: Elementor Side Cart automatically opening even when the cart is empty on single product pages.</li>\n<li>Fixed: Category title missing from meta descriptions on new sites.</li>\n<li>Fixed: Redundant schema queries when database schema cache fails to update.</li>\n<li>Fixed: JS error when accessing string offsets in SEO revisions data processing.</li>\n<li>Fixed: Keyword Rank Tracker button broke in the Post editor if the Spectra or Starter Templates plugin was activated.</li>\n<li>Fixed: Homepage meta description character counter breaks when the site is set as an Organization.</li>\n<li>Fixed: New feature popups rendering outside the viewport on smaller screens.</li>\n</ul>\n<p><strong>New in Version 4.8.8</strong></p>\n<ul>\n<li>New: AI Assistant Block &#8211; Generate any type of content right inside the post editor: blog articles, summaries, comparison tables, and more. Whatever you need, the AI Assistant block makes it happen.</li>\n<li>New: AI Image Generator &#8211; Instantly create eye-catching visuals for your posts and use them anywhere—from featured images to inline content. You can even edit existing images to give them a unique twist.</li>\n<li>New: LLMs.txt Improvements – The new llms-full.txt file makes it easy for AI engines to index your site without overloading your server. We’ve also added post-to-Markdown conversion and new settings to control exactly what content gets included.</li>\n<li>Updated: All existing AI Content features have been made compatible with all our supported page builders. You can now auto-generate SEO titles, meta descriptions, FAQs, keypoints and social posts directly inside Elementor, Divi, SeedProd, Avada, WPBakery, SiteOrigin and Thrive Architect!</li>\n<li>Updated: The llms.txt file is now generated as a static file, removing the need for rewrite rules (e.g. on WP Engine).</li>\n<li>Updated: Moved llms.txt under Sitemaps menu.</li>\n<li>Fixed: Site Audit sometimes not showing results when all content types are included.</li>\n<li>Fixed: PHP error when Site Audit cannot scan post due to uninstantiated social class.</li>\n<li>Fixed: PHP error when dashboard widget failed to fetch RSS news feed.</li>\n</ul>\n<p><strong>New in Version 4.8.7.2</strong></p>\n<ul>\n<li>Updated: Added additional hardening to REST API routes.</li>\n</ul>\n<p><strong>New in Version 4.8.7.1</strong></p>\n<ul>\n<li>Fixed: WooCommerce products being automatically added to the cart.</li>\n</ul>\n<p><strong>New in Version 4.8.7</strong></p>\n<ul>\n<li>Updated: Hardened API routes to prevent unauthorized access.</li>\n<li>Updated: Added support for tracking breadcrumb setting changes in SEO Revisions.</li>\n<li>Updated: Added support for WooCommerce GTIN field to Product schema.</li>\n<li>Updated: Added support for .avif images to Image Sitemap and Image SEO addon.</li>\n<li>Updated: Review limit for Product schema can now be modified via a new filter hook.</li>\n<li>Updated: Use site URL instead of home URL for llms.txt to handle WP installs in subdirectories.</li>\n<li>Updated: Remove all user meta when AIOSEO is uninstalled.</li>\n<li>Updated: Improved performance of Cornerstone Content filtering.</li>\n<li>Fixed: Product shipping details schema clearing cart content in rare case where logged-in user adds a product to the cart and then edits a product in the admin panel.</li>\n<li>Fixed: ProfilePage schema sometimes invalid due to incorrect author URL.</li>\n<li>Fixed: Hide AIOSEO custom link fields inside the Edit Link modal in WPBakery visual builder to prevent plugin conflict.</li>\n<li>Fixed: AIOSEO Settings not successfully saving before post is published in WPBakery visual builder.</li>\n<li>Fixed: The SEO title and description were not persisting if the post content is too large.</li>\n<li>Fixed: Theme conflict with Neve where the EDD Checkout block does not show if Run Shortcodes is enabled.</li>\n<li>Fixed: Breadcrumb block not updating when changing the post title.</li>\n<li>Fixed: If WooCommerce brand is selected as primary category, it is now correctly used in the URL.</li>\n<li>Fixed: Headline Analyzer layout breaks when entering very long headlines.</li>\n</ul>\n<p><strong>New in Version 4.8.6.1</strong></p>\n<ul>\n<li>Updated: Prevent potential plugin conflicts due to the loading of dependencies multiple times.</li>\n<li>Updated: Local SEO render options for Opening Hours are now hidden when feature is disabled.</li>\n<li>Fixed: Loading spinner for SEO Revisions in sidebar menu not aligned.</li>\n</ul>\n<p><strong>New in Version 4.8.6</strong></p>\n<ul>\n<li>New: Site Audit &#8211; Get a detailed report of SEO issues for all posts and terms on your site, discover why these issues are important and how you can fix them. 🔨</li>\n<li>Fixed: Multisite subsite requests to Search Statistics server sometimes fail due to missing license.</li>\n<li>Fixed: Rare PHP error when breadcrumbs cannot be determined for non-standard pages.</li>\n</ul>\n<p><strong>New in Version 4.8.5</strong></p>\n<ul>\n<li>Updated: Author SEO (E-E-A-T) addon and Writing Assistant data are now fully removed when uninstalling the plugin.</li>\n<li>Updated: Product Schema now favours WooCommerce brand field over its own custom field.</li>\n<li>Updated: PHP version notices are now only shown to Administrators.</li>\n<li>Fixed: Breadcrumbs taxonomy preview now correctly refreshes when user toggles between using the default settings</li>\n<li>Fixed: WooCommerce Brand smart tag not correctly converted to expected value in previews.</li>\n<li>Fixed: AIOSEO Details column now correctly reloads after quick-editing a post.</li>\n<li>Fixed: Image URLs are no longer forcefully converted to lowercase in the Image Sitemap.</li>\n<li>Fixed: Focus Keyword now saves correctly again for Divi built posts.</li>\n<li>Fixed: Image SEO no longer breaks Divi built posts in rare cases.</li>\n<li>Fixed: Multisite subsites no longer incorrectly inherit the &#8220;Block AI Crawlers&#8221; and &#8220;Block Crawling of Internal Site Search URLs&#8221; setting values from the main site in the network. </li>\n<li>Fixed: Notifications no longer contain encoded HTML entities</li>\n<li>Fixed: Default options for new redirects not localized.</li>\n<li>Fixed: PHP error in rare cases when breadcrumb link is a WP_Error.</li>\n<li>Fixed: PHP error due to plugin conflict with MasterStudy LMS.</li>\n</ul>\n<p><strong>See our <a href="https://aioseo.com/changelog/?utm_source=wprepo&amp;utm_medium=link&amp;utm_campaign=aioseo" rel="nofollow ugc">changelog on aioseo.com</a> for previous releases.</strong></p>\n
            END;

        $expected_description = <<<END
            <h3>AIOSEO &#8211; The Best WordPress SEO Plugin &amp; Toolkit</h3>\n<p>All in One SEO is the original WordPress SEO plugin started in 2007. Today, over 3 million website owners and SEO experts use AIOSEO for higher SEO rankings.</p>\n<p>Our users consistently rate <a href="https://aioseo.com/?utm_source=wprepo&amp;utm_medium=link&amp;utm_campaign=liteplugin" title="All in One SEO for WordPress" rel="nofollow ugc">AIOSEO</a> as the most comprehensive WordPress SEO plugin and marketing toolkit. It&#8217;s the fastest way to optimize WordPress SEO settings, add schema markup, create XML sitemap, add local SEO, track SEO keyword rankings, automate internal linking, perform SEO audits, add Author SEO (EEAT), monitor SEO revisions, connect Google search console, and basically everything a SEO Pro would use to rank higher in search engines.</p>\n<p>We have AI SEO features that help you optimize your posts for SEO by automatically generating SEO titles, meta descriptions, FAQs, key points, social media posts, and more.</p>\n<blockquote>\n<p><strong>AIOSEO Pro</strong><br />\n  This is the lite version of the All in One WordPress SEO Pro plugin that comes with all the powerful SEO features you will ever need to rank higher in search engines including <strong>smart SEO schema markup, advanced SEO modules, powerful SEO sitemap suite, local SEO module, SEO keyword ranking tracking, automatic internal linking, WooCommerce SEO</strong>, and tons more. <a href="https://aioseo.com/?utm_source=wprepo&amp;utm_medium=link&amp;utm_campaign=liteplugin" title="All in One SEO for WordPress" rel="nofollow ugc">Click here to purchase the best premium WordPress SEO plugin now!</a></p>\n</blockquote>\n<p>Here&#8217;s why smart business owners, SEO experts, marketers, and developers love AIOSEO, and you will too!</p>\n<span class="embed-youtube" style="text-align:center; display: block;"><iframe loading="lazy" class="youtube-player" width="750" height="422" src="https://www.youtube.com/embed/UbOYEEIvXvY?version=3&#038;rel=1&#038;showsearch=0&#038;showinfo=1&#038;iv_load_policy=1&#038;fs=1&#038;hl=en-US&#038;autohide=2&#038;wmode=transparent" allowfullscreen="true" style="border:0;" sandbox="allow-scripts allow-same-origin allow-popups allow-presentation allow-popups-to-escape-sandbox"></iframe></span>\n<h3>What Makes AIOSEO Better than Other WordPress SEO Plugins</h3>\n<p>AIOSEO is leading the innovation in WordPress SEO space, and our SEO features will give you a competitive advantage.</p>\n<ul>\n<li>\n<p><strong>Easy SEO Setup Wizard</strong><br />\nOur SEO setup wizard optimizes your website&#8217;s SEO settings based on your unique industry needs in less than 5 minutes.</p>\n</li>\n<li>\n<p><strong>Smart Schema Markup (aka Rich Snippets)</strong><br />\nGet better click through rate (CTR) and Google rich featured snippets using advanced SEO schema markups like FAQ schema, product schema, recipe schema markup, and dozens more using our custom <a href="https://aioseo.com/features/rich-snippets-schema/?utm_source=wprepo&amp;utm_medium=link&amp;utm_campaign=liteplugin" title="Schema Generator" rel="nofollow ugc">Schema Generator</a>.</p>\n</li>\n<li>\n<p><strong>AI Content</strong><br />\nCreate anything you need, such as blog articles and tables, with our AI Assistant block. Generate stunning visuals instantly with the built-in AI Image Generator. Save time by automatically generating SEO titles, meta descriptions, FAQs, key points, social media posts, and more with our <a href="https://aioseo.com/features/ai-content/?utm_source=wprepo&amp;utm_medium=link&amp;utm_campaign=liteplugin" title="AI Content" rel="nofollow ugc">AI Content Generator</a>.</p>\n</li>\n<li>\n<p><strong>Unlimited SEO Keywords</strong><br />\nOptimize for unlimited SEO keywords using our SEO content analyzer. Our TruSEO score gives you detailed content &amp; readability analysis, so you can get higher SEO rankings.</p>\n</li>\n<li>\n<p><strong>Google Keyword Rank Tracking</strong><br />\nEasily track how your website is ranking for different keywords in Google from your <a href="https://aioseo.com/features/search-statistics/?utm_source=wprepo&amp;utm_medium=link&amp;utm_campaign=liteplugin" title="Google Keyword Rank Tracker" rel="nofollow ugc">WordPress dashboard</a>.</p>\n</li>\n<li>\n<p><strong>Automatic Link Assistant</strong><br />\nAutomate internal links between your pages using our smart <a href="https://aioseo.com/features/internal-link-assistant/?utm_source=wprepo&amp;utm_medium=link&amp;utm_campaign=liteplugin" title="Link Assistant" rel="nofollow ugc">internal linking algorithm</a> that will help improve on-page SEO.</p>\n</li>\n<li>\n<p><strong>Local Business SEO</strong><br />\nImprove your local SEO presence with local business schema, support for multiple local store locations, business opening hours, Google Maps integration, contact info (business email, business phone, business address, etc), and more with our <a href="https://aioseo.com/features/local-seo/?utm_source=wprepo&amp;utm_medium=link&amp;utm_campaign=liteplugin" title="Local SEO" rel="nofollow ugc">Local SEO module</a>.</p>\n</li>\n<li>\n<p><strong>Site Audit</strong><br />\nGet a detailed report of SEO issues for all posts and terms on your site, discover why these issues are important and how you can fix them.</p>\n</li>\n<li>\n<p><strong>SEO Revisions</strong><br />\nKeep a <a href="https://aioseo.com/seo-revisions/?utm_source=wprepo&amp;utm_medium=link&amp;utm_campaign=liteplugin" title="SEO Revisions" rel="nofollow ugc">historical record of SEO changes</a>, monitor the impact of changes, and restore previous versions in one click.</p>\n</li>\n<li>\n<p><strong>Content Decay Tracking</strong><br />\nNever lose traffic to competitors. Quickly detect which content is losing traffic / SEO rankings, so you can optimize it to regain your rankings with our <a href="https://aioseo.com/features/search-statistics/?utm_source=wprepo&amp;utm_medium=link&amp;utm_campaign=liteplugin" title="Search Statistics" rel="nofollow ugc">Search Statistics module</a>.</p>\n</li>\n<li>\n<p><strong>Smart XML Sitemap</strong><br />\nAdvanced XML sitemaps to boost your SEO rankings (with easy setup inside Google Search Console). Also includes Video SEO XML sitemap, News SEO XML sitemap, RSS sitemap, and HTML sitemap.</p>\n</li>\n<li>\n<p><strong>Smart SEO Redirects</strong><br />\nThe most powerful <a href="https://aioseo.com/features/redirection-manager/?utm_source=wprepo&amp;utm_medium=link&amp;utm_campaign=liteplugin" title="Redirection Manager" rel="nofollow ugc">SEO Redirection manager</a> for setting up advanced SEO redirects including 301 redirects, 302, 307, 410, 404 redirection, REGEX redirects, and more.</p>\n</li>\n<li>\n<p><strong>404 Error Monitor</strong><br />\nAutomatic 404 error monitor helps you track and redirect 404 errors, so you don&#8217;t lose SEO rankings.</p>\n</li>\n<li>\n<p><strong>Author SEO</strong><br />\nAdd <a href="https://aioseo.com/features/author-seo-google-e-e-a-t/?utm_source=wprepo&amp;utm_medium=link&amp;utm_campaign=liteplugin" title="Author SEO (E-E-A-T)" rel="nofollow ugc">custom author profile pages, author bio box, and relevant author schema</a> to boost Google EEAT score to help with Google&#8217;s Helpful Content Update (HCU).</p>\n</li>\n<li>\n<p><strong>LLMs.txt Generator</strong><br />\nGenerate an llms.txt file to help AI engines discover your site&#8217;s content more easily so your content can rank in AI search results.</p>\n</li>\n<li>\n<p><strong>SEO Audit Checklist</strong><br />\nImprove your SEO ranking with our comprehensive SEO audit checklist.</p>\n</li>\n<li>\n<p><strong>Knowledge Graph Support</strong><br />\nImprove your website&#8217;s search appearance with SEO Knowledge panel.</p>\n</li>\n<li>\n<p><strong>Table of Contents</strong><br />\nAutomatically generate a table of content, customize headings, anchors, and you can also hide or reorder the headings.</p>\n</li>\n</ul>\n<h3>Advanced SEO Plugin Features</h3>\n<ul>\n<li>\n<p><strong>User Access Control</strong><br />\nControl who can manage your SEO settings with our advanced SEO access control.</p>\n</li>\n<li>\n<p><strong>WordPress REST API</strong><br />\nManage your SEO metadata with WordPress REST API. Great for headless WordPress installations.</p>\n</li>\n<li>\n<p><strong>Advanced Robots Meta SEO Settings</strong><br />\nGranular controls for no index, no follow, no archive, no snippet, max snippet, max video, etc.</p>\n</li>\n<li>\n<p><strong>RSS Content for SEO</strong><br />\nStop content scraping from hurting your SEO rankings.</p>\n</li>\n<li>\n<p><strong>Full Site Redirects</strong><br />\nMerging websites or switching domains? Full site redirect makes it easy to switch domains without losing SEO rankings.</p>\n</li>\n<li>\n<p><strong>Smart Meta Title &amp; Description</strong><br />\nAutomatic SEO generation, dynamic SEO smart tags, include Emoji, add shortcodes, and more features to stand out in search results.</p>\n</li>\n<li>\n<p><strong>Smart Breadcrumbs</strong><br />\nAdd Breadcrumb navigation to improve user experience and boost your SEO rankings. Comes with full SEO JSON+LD support.</p>\n</li>\n<li>\n<p><strong>Automatic Image SEO</strong><br />\nHelps your images rank higher by autogenerating image title, clean SEO image filenames, and more.</p>\n</li>\n<li>\n<p><strong>Advanced SEO Canonical URLs</strong><br />\nPrevent duplicate content in SEO with automatic canonical URLs.</p>\n</li>\n<li>\n<p><strong>SEO Cleanup / Manual SEO Penalty Removal</strong><br />\nDomains Report feature in Link Assistant automatically removes all links for specific domains with just one click. Huge time saver when doing SEO cleanups.</p>\n</li>\n<li>\n<p><strong>Link Opportunities Report</strong><br />\nSee better internal link opportunities with our smart algorithm. Easily add internal links with just a few clicks.</p>\n</li>\n<li>\n<p><strong>Robots.txt Editor</strong><br />\nManage and customize SEO robots.txt files in WordPress.</p>\n</li>\n<li>\n<p><strong>Crawl Quota Management</strong><br />\nCrawl Cleanup feature manages your search engine crawl quota and index your important content faster.</p>\n</li>\n<li>\n<p><strong>Title and Nofollow for SEO</strong><br />\nEasily add title and nofollow to external links.</p>\n</li>\n<li>\n<p><strong>Headline Analyzer</strong><br />\nAnalyze your page / posts headlines to improve CTR and SEO rankings.</p>\n</li>\n<li>\n<p><strong>Competitor Site SEO Analysis</strong><br />\nUse competitor SEO analysis to outrank them with better SEO optimization.</p>\n</li>\n<li>\n<p><strong>SEO Code Snippets</strong><br />\nIntegration with <a href="https://wordpress.org/plugins/insert-headers-and-footers/" rel="ugc">WPCode plugin</a> for SEO code snippets to further customize every aspect of your SEO.</p>\n</li>\n</ul>\n<h3>WordPress SEO Integrations</h3>\n<ul>\n<li>\n<p><strong>Google Search Console Integration</strong><br />\nConnect with Google webmaster tools and Google Search Console to see SEO insights (like content rankings, keyword rankings, page speed insights, post index status, etc) directly in your WordPress dashboard.</p>\n</li>\n<li>\n<p><strong>WooCommerce SEO</strong><br />\nImproves your WooCommerce SEO rankings. Easily optimize WooCommerce product pages, product categories, and more for best eCommerce SEO results.</p>\n</li>\n<li>\n<p><strong>Knowledge Panel SEO</strong><br />\nImprove website SEO appearance by adding social media profile links for Facebook, Twitter, Wikipedia, Instagram, LinkedIn, Yelp, YouTube, and more.</p>\n</li>\n<li>\n<p><strong>Webmaster Tool Integrations</strong><br />\nConnect with all webmaster tools including Google Search Console, Bing SEO, Yandex SEO, Baidu SEO, Google Analytics, Pinterest site verification, and more.</p>\n</li>\n<li>\n<p><strong>Social Media Integration</strong><br />\nFacebook SEO, Twitter SEO, and Pinterest SEO with better website previews.</p>\n</li>\n<li>\n<p><strong>Google AMP SEO Integration</strong><br />\nImprove your mobile SEO rankings with Google AMP SEO.</p>\n</li>\n<li>\n<p><strong>Semrush SEO integration</strong><br />\nSee additional SEO keywords with Semrush SEO integration.</p>\n</li>\n<li>\n<p><strong>Microsoft Clarity Integration</strong><br />\nSee visitor interactions with heatmaps and session recordings.</p>\n</li>\n<li>\n<p><strong>IndexNow Integration</strong><br />\nInstantly notify Bing and Yandex for faster SEO indexing.</p>\n</li>\n<li>\n<p><strong>Elementor SEO</strong><br />\nBetter Elementor SEO for landing pages.</p>\n</li>\n<li>\n<p><strong>Divi SEO</strong><br />\nBetter Divi SEO for landing pages.</p>\n</li>\n<li>\n<p><strong>Avada SEO</strong><br />\nBetter Avada SEO for landing pages.</p>\n</li>\n<li>\n<p><strong>WP Bakery SEO</strong><br />\nBetter WP Bakery SEO for landing pages.</p>\n</li>\n<li>\n<p><strong>SeedProd SEO</strong><br />\nOptimize SeedProd landing pages for SEO.</p>\n</li>\n<li>\n<p><strong>SiteOrigin SEO</strong><br />\nBetter SiteOrigin SEO for landing pages.</p>\n</li>\n<li>\n<p><strong>Open Graph Support</strong><br />\nImprove SEO rankings with open graph meta data.</p>\n</li>\n</ul>\n<h3>WordPress SEO Plugin Importer</h3>\n<p>Not happy with your current SEO plugin? We make SEO migration easy with our point-and-click automated SEO data transfer tool. We currently support SEO migration from following SEO tools:</p>\n<ul>\n<li>Yoast SEO Importer</li>\n<li>Yoast SEO Premium Importer</li>\n<li>RankMath SEO Importer</li>\n<li>SEOPress</li>\n</ul>\n<p>We also support importing SEO redirects from the following plugins:</p>\n<ul>\n<li>Redirection Plugin</li>\n<li>Simple 301 Redirects Importer</li>\n<li>Safe Redirect Manager</li>\n<li>301 Redirects Importer</li>\n</ul>\n<p>Aside from that, our SEO migration tool also helps you with:</p>\n<ul>\n<li>Import / Export AIOSEO settings from one site to another</li>\n<li>Create SEO Settings Backup</li>\n<li>CSV Sitemap Import to Import additional pages to your XML Sitemaps</li>\n</ul>\n<p><strong>Now you can see why AIOSEO is often rated the best SEO plugin in WordPress.</strong></p>\n<p>Give AIOSEO a try.</p>\n<p>Want to unlock more SEO features? <a href="https://aioseo.com/?utm_source=wprepo&amp;utm_medium=link&amp;utm_campaign=liteplugin" title="All in One SEO for WordPress" rel="nofollow ugc">Upgrade to AIOSEO Pro</a>.</p>\n<h3>Credits</h3>\n<p>This plugin is created by <a href="https://benjaminrojas.net/" title="Benjamin Rojas" rel="nofollow ugc">Benjamin Rojas</a> and <a href="https://syedbalkhi.com/" title="Syed Balkhi" rel="nofollow ugc">Syed Balkhi</a>.</p>\n<h3>Branding Guideline</h3>\n<p>AIOSEO&reg; is a registered trademark of Semper Plugins LLC. When writing about the WordPress SEO plugin by AIOSEO, please use the following format.</p>\n<ul>\n<li>AIOSEO (correct)</li>\n<li>All in One SEO (correct)</li>\n<li>AIO SEO (incorrect)</li>\n<li>All in 1 SEO (incorrect)</li>\n<li>AISEO (incorrect)</li>\n</ul>\n
            END;

        $expected_faq = <<<END
            <p>Please visit our <a href="https://aioseo.com/docs/?utm_source=wprepo&amp;utm_medium=link&amp;utm_campaign=liteplugin" rel="nofollow ugc">complete AIOSEO documentation</a> before requesting support for SEO from the AIOSEO team.</p>

            <dt id='who%20should%20use%20aioseo%3F'>
            Who should use AIOSEO?
            </h4>
            <p>
            <p>SEO is essential for all websites. AIOSEO is perfect for business owners, bloggers, marketers, designers, developers, photographers, and basically everyone else. If you want to rank higher in search, then you need to use AIOSEO WordPress SEO plugin.</p>
            </p>
            <dt id='which%20themes%20does%20aioseo%20support%3F'>\nWhich themes does AIOSEO support?\n</h4>\n<p>\n<p>AIOSEO works with all WordPress themes. Simply enable AIOSEO to make your WordPress theme SEO friendly.</p>\n</p>\n<dt id='will%20aioseo%20slow%20down%20my%20website%3F'>\nWill AIOSEO slow down my website?\n</h4>\n<p>\n<p>Nope, AIOSEO will NOT slow down your website. We understand that speed is important for SEO, that&#8217;s why our code is properly optimized for maximum performance. Remember, faster websites rank higher in search. Use AIOSEO for fast SEO improvements.</p>\n</p>\n<dt id='can%20i%20use%20aioseo%20on%20client%20sites%3F'>\nCan I use AIOSEO on client sites?\n</h4>\n<p>\n<p>Yes, you can use AIOSEO on client websites.</p>\n</p>\n<dt id='are%20aioseo%20sitemaps%20better%20than%20default%20wordpress%20sitemaps%3F'>\nAre AIOSEO sitemaps better than default WordPress sitemaps?\n</h4>\n<p>\n<p>Yes, AIOSEO smart sitemaps are a lot more optimized than the default WordPress sitemaps. Once you enable AIOSEO, our XML sitemaps will override the default WordPress sitemaps, so you can improve your SEO rankings.</p>\n<p>We also offer advanced SEO sitemaps such as News Sitemap, Video Sitemap, and RSS Sitemap.</p>\n<p>Our SEO sitemaps come with granular control such as links per sitemap, enable / disable post types or taxonomies, include / exclude specific links from sitemap, add additional non-WordPress pages to sitemaps, customize sitemap priority &amp; frequency for each section of your site, and more.</p>\n<p>This is why experts rate AIOSEO as the best WordPress SEO plugin.</p>\n</p>\n<dt id='does%20aioseo%20help%20with%20seo%20verification%3F'>\nDoes AIOSEO help with SEO Verification?\n</h4>\n<p>\n<p>Yes. AIOSEO can help you with website SEO verification with various webmaster tools such as Google Search Console, Bing Webmaster Tools, Yandex, Baidu, Pinterest, and just about every other site verification you need.</p>\n</p>\n<dt id='why%20is%20aioseo%20better%20than%20other%20seo%20plugins%3F'>\nWhy is AIOSEO better than other SEO plugins?\n</h4>\n<p>\n<p>There are many WordPress SEO plugins out there. Unlike others, AIOSEO WordPress SEO plugin is always reliable. Our SEO features are results focused (no bloat), and we offer exceptional customer support.</p>\n<p>AIOSEO is the original WordPress SEO plugin, and it&#8217;s trusted by over 3 million website owners.</p>\n</p>\n<dt id='do%20i%20really%20need%20an%20xml%20sitemap%3F'>\nDo I really need an XML Sitemap?\n</h4>\n<p>\n<p><strong>Yes! XML Sitemaps help Google and other search engines to find all the pages of your website.</strong></p>\n<p>An XML sitemap is a list of all the content on your website. The sitemap helps search engine bots to easily see all the content on your site in one place, The XML sitemap file is hidden from your human visitors, however search engines like Google can see it.</p>\n<p>Without an XML sitemap, some of your web pages may never be included in Google search results, and won&#8217;t get any traffic.</p>\n<p>XML Sitemaps also help you tell Google which pages you DON&#8217;T want included in search results. This can help your SEO to prevent keyword cannibalization and duplicate content issues.</p>\n<p>As part of your SEO strategy, <strong>an XML sitemap can help you to improve your domain authority and unlock more traffic from Google, Bing and other search engines</strong>.</p>\n<p>AIOSEO can easily help you get your sitemaps listed inside Google Search Console so your content can start to get indexed today!</p>\n</p>\n<dt id='does%20aioseo%20integrate%20directly%20with%20google%20search%20console%3F'>\nDoes AIOSEO integrate directly with Google Search Console?\n</h4>\n<p>\n<p>Absolutely! Our integration with Google Search Console allows you to monitor and maintain your website&#8217;s presence on Google. With our direct integration, you can easily view important information about your website, such as the number of clicks, impressions, and the average position for each keyword that your website&#8217;s content appears for in Google search results. You can also track your contents page speed using Google&#8217;s Page Speed Insights directly inside your WordPress dashboard.</p>\n<p>Additionally, AIOSEO can also provide you with data on the most frequently used keywords, the most popular pages on your website, and any crawl errors or security issues that may arise. By integrating with Google Search Console, AIOSEO provides website owners with valuable insights that can help to improve SEO and overall online visibility. With this integration, you can track your site&#8217;s progress over time and make data-driven decisions that will help you achieve your SEO goals.</p>\n</p>\n\n
            END;

        $expected_reviews = <<<END
            <div class="review">\n\t<div class="review-head">\n\t\t<div class="reviewer-info">\n\t\t\t<div class="review-title-section">\n\t\t\t\t<h4 class="review-title">very helpful</h4>\n\t\t\t\t<div class="star-rating">\n\t\t\t\t<div class="wporg-ratings" aria-label="5 out of 5 stars" data-title-template="%s out of 5 stars" data-rating="5" style="color:#ffb900;"><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span></div>\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<p class="reviewer">\n\t\t\t\tBy <a href="https://profiles.wordpress.org/chukswhyte/"><img alt='' src='https://secure.gravatar.com/avatar/7c057e85535bc33c74636f1fdb8d8ed4e52426b92ab027e65fcfc11ccfe18f1d?s=16&#038;d=monsterid&#038;r=g' srcset='https://secure.gravatar.com/avatar/7c057e85535bc33c74636f1fdb8d8ed4e52426b92ab027e65fcfc11ccfe18f1d?s=32&#038;d=monsterid&#038;r=g 2x' class='avatar avatar-16 photo' height='16' width='16' loading='lazy' decoding='async'/></a><a href="https://profiles.wordpress.org/chukswhyte/" class="reviewer-name">chukswhyte</a> on <span class="review-date">October 29, 2025</span>\t\t\t</p>\n\t\t</div>\n\t</div>\n\t<div class="review-body"><!-- wp:paragraph -->\n<p>Very Helpful</p>\n<!-- /wp:paragraph --></div>\n</div>\n<div class="review">\n\t<div class="review-head">\n\t\t<div class="reviewer-info">\n\t\t\t<div class="review-title-section">\n\t\t\t\t<h4 class="review-title">Ottimo</h4>\n\t\t\t\t<div class="star-rating">\n\t\t\t\t<div class="wporg-ratings" aria-label="5 out of 5 stars" data-title-template="%s out of 5 stars" data-rating="5" style="color:#ffb900;"><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span></div>\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<p class="reviewer">\n\t\t\t\tBy <a href="https://profiles.wordpress.org/marialuisaportaluppi/"><img alt='' src='https://secure.gravatar.com/avatar/0ef738724ed2ce07f9aca0cce0a3ef0a21d02bd1bc55cd5912a133fd545a9347?s=16&#038;d=monsterid&#038;r=g' srcset='https://secure.gravatar.com/avatar/0ef738724ed2ce07f9aca0cce0a3ef0a21d02bd1bc55cd5912a133fd545a9347?s=32&#038;d=monsterid&#038;r=g 2x' class='avatar avatar-16 photo' height='16' width='16' loading='lazy' decoding='async'/></a><a href="https://profiles.wordpress.org/marialuisaportaluppi/" class="reviewer-name">Marialuisa Portaluppi <small>(marialuisaportaluppi)</small></a> on <span class="review-date">October 28, 2025</span>\t\t\t</p>\n\t\t</div>\n\t</div>\n\t<div class="review-body"><!-- wp:paragraph -->\n<p>Buongiorno, io utilizzo molto All in One SEO, mi trovo molto bene. Mi Piace.</p>\n<!-- /wp:paragraph --></div>\n</div>\n<div class="review">\n\t<div class="review-head">\n\t\t<div class="reviewer-info">\n\t\t\t<div class="review-title-section">\n\t\t\t\t<h4 class="review-title">SEO help at it&#039;s finest!</h4>\n\t\t\t\t<div class="star-rating">\n\t\t\t\t<div class="wporg-ratings" aria-label="5 out of 5 stars" data-title-template="%s out of 5 stars" data-rating="5" style="color:#ffb900;"><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span></div>\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<p class="reviewer">\n\t\t\t\tBy <a href="https://profiles.wordpress.org/garry187gmcdesign/"><img alt='' src='https://secure.gravatar.com/avatar/bc20c343c4149df08ff4d67c6727f786743584b3a1b81ab73ade1a132b11f718?s=16&#038;d=monsterid&#038;r=g' srcset='https://secure.gravatar.com/avatar/bc20c343c4149df08ff4d67c6727f786743584b3a1b81ab73ade1a132b11f718?s=32&#038;d=monsterid&#038;r=g 2x' class='avatar avatar-16 photo' height='16' width='16' loading='lazy' decoding='async'/></a><a href="https://profiles.wordpress.org/garry187gmcdesign/" class="reviewer-name">garry187gmcdesign</a> on <span class="review-date">October 27, 2025</span>\t\t\t</p>\n\t\t</div>\n\t</div>\n\t<div class="review-body"><!-- wp:paragraph -->\n<p>Very helpful SEO application. Well worth the subscription</p>\n<!-- /wp:paragraph --></div>\n</div>\n<div class="review">\n\t<div class="review-head">\n\t\t<div class="reviewer-info">\n\t\t\t<div class="review-title-section">\n\t\t\t\t<h4 class="review-title">Muy buen plugin para SEO</h4>\n\t\t\t\t<div class="star-rating">\n\t\t\t\t<div class="wporg-ratings" aria-label="5 out of 5 stars" data-title-template="%s out of 5 stars" data-rating="5" style="color:#ffb900;"><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span></div>\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<p class="reviewer">\n\t\t\t\tBy <a href="https://profiles.wordpress.org/saure/"><img alt='' src='https://secure.gravatar.com/avatar/02c0036bd47247bb4c84af953cd3d63148dbb3e358a4317bab29942f81cbf2e5?s=16&#038;d=monsterid&#038;r=g' srcset='https://secure.gravatar.com/avatar/02c0036bd47247bb4c84af953cd3d63148dbb3e358a4317bab29942f81cbf2e5?s=32&#038;d=monsterid&#038;r=g 2x' class='avatar avatar-16 photo' height='16' width='16' loading='lazy' decoding='async'/></a><a href="https://profiles.wordpress.org/saure/" class="reviewer-name">Saure</a> on <span class="review-date">October 26, 2025</span>\t\t\t</p>\n\t\t</div>\n\t</div>\n\t<div class="review-body"><!-- wp:paragraph -->\n<p>Muy intuitivo</p>\n<!-- /wp:paragraph --></div>\n</div>\n<div class="review">\n\t<div class="review-head">\n\t\t<div class="reviewer-info">\n\t\t\t<div class="review-title-section">\n\t\t\t\t<h4 class="review-title">Great experience. Pure Genius</h4>\n\t\t\t\t<div class="star-rating">\n\t\t\t\t<div class="wporg-ratings" aria-label="5 out of 5 stars" data-title-template="%s out of 5 stars" data-rating="5" style="color:#ffb900;"><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span></div>\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<p class="reviewer">\n\t\t\t\tBy <a href="https://profiles.wordpress.org/steve9000/"><img alt='' src='https://secure.gravatar.com/avatar/9c7386837075699e3a213614a40eb5623512746cb6ea26cf6340743aaaef9f29?s=16&#038;d=monsterid&#038;r=g' srcset='https://secure.gravatar.com/avatar/9c7386837075699e3a213614a40eb5623512746cb6ea26cf6340743aaaef9f29?s=32&#038;d=monsterid&#038;r=g 2x' class='avatar avatar-16 photo' height='16' width='16' loading='lazy' decoding='async'/></a><a href="https://profiles.wordpress.org/steve9000/" class="reviewer-name">steve9000</a> on <span class="review-date">October 25, 2025</span>\t\t\t</p>\n\t\t</div>\n\t</div>\n\t<div class="review-body"><!-- wp:paragraph -->\n<p>Great experience. Pure Genius</p>\n<!-- /wp:paragraph --></div>\n</div>\n<div class="review">\n\t<div class="review-head">\n\t\t<div class="reviewer-info">\n\t\t\t<div class="review-title-section">\n\t\t\t\t<h4 class="review-title">OK</h4>\n\t\t\t\t<div class="star-rating">\n\t\t\t\t<div class="wporg-ratings" aria-label="5 out of 5 stars" data-title-template="%s out of 5 stars" data-rating="5" style="color:#ffb900;"><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span></div>\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<p class="reviewer">\n\t\t\t\tBy <a href="https://profiles.wordpress.org/qbo64/"><img alt='' src='https://secure.gravatar.com/avatar/6c2ccd03ef34a7cdba9f7ff3cdf89edd361961f7b5c894cc1343a7a48383bd73?s=16&#038;d=monsterid&#038;r=g' srcset='https://secure.gravatar.com/avatar/6c2ccd03ef34a7cdba9f7ff3cdf89edd361961f7b5c894cc1343a7a48383bd73?s=32&#038;d=monsterid&#038;r=g 2x' class='avatar avatar-16 photo' height='16' width='16' loading='lazy' decoding='async'/></a><a href="https://profiles.wordpress.org/qbo64/" class="reviewer-name">qbo64</a> on <span class="review-date">October 22, 2025</span>\t\t\t</p>\n\t\t</div>\n\t</div>\n\t<div class="review-body"><!-- wp:paragraph -->\n<p>It works well :)</p>\n<!-- /wp:paragraph --></div>\n</div>\n<div class="review">\n\t<div class="review-head">\n\t\t<div class="reviewer-info">\n\t\t\t<div class="review-title-section">\n\t\t\t\t<h4 class="review-title">Excellent Support and Fast Problem Resolution</h4>\n\t\t\t\t<div class="star-rating">\n\t\t\t\t<div class="wporg-ratings" aria-label="5 out of 5 stars" data-title-template="%s out of 5 stars" data-rating="5" style="color:#ffb900;"><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span></div>\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<p class="reviewer">\n\t\t\t\tBy <a href="https://profiles.wordpress.org/goldshopzlatara/"><img alt='' src='https://secure.gravatar.com/avatar/b59e88efa6953c829a5c0c99fab4f01fb8375d42728ee76fba1f0df8ca4f0774?s=16&#038;d=monsterid&#038;r=g' srcset='https://secure.gravatar.com/avatar/b59e88efa6953c829a5c0c99fab4f01fb8375d42728ee76fba1f0df8ca4f0774?s=32&#038;d=monsterid&#038;r=g 2x' class='avatar avatar-16 photo' height='16' width='16' loading='lazy' decoding='async'/></a><a href="https://profiles.wordpress.org/goldshopzlatara/" class="reviewer-name">goldshopzlatara</a> on <span class="review-date">October 22, 2025</span>\t\t\t</p>\n\t\t</div>\n\t</div>\n\t<div class="review-body"><!-- wp:paragraph -->\n<p>I had a great experience with the AIOSEO team! Their support was fast, professional, and very friendly. They quickly understood my issue and provided a clear solution that worked perfectly. It’s rare to find such responsive and helpful customer service.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>Highly recommend AIOSEO — not only for its powerful SEO features, but also for the amazing support behind it!</p>\n<!-- /wp:paragraph --></div>\n</div>\n<div class="review">\n\t<div class="review-head">\n\t\t<div class="reviewer-info">\n\t\t\t<div class="review-title-section">\n\t\t\t\t<h4 class="review-title">Already Happy With Service &amp; Features</h4>\n\t\t\t\t<div class="star-rating">\n\t\t\t\t<div class="wporg-ratings" aria-label="5 out of 5 stars" data-title-template="%s out of 5 stars" data-rating="5" style="color:#ffb900;"><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span></div>\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<p class="reviewer">\n\t\t\t\tBy <a href="https://profiles.wordpress.org/douglaswebdesigns/"><img alt='' src='https://secure.gravatar.com/avatar/ed88a48c134c4a3d718de70eafe83533168d55b9b264b043145c16d5eb0acf4e?s=16&#038;d=monsterid&#038;r=g' srcset='https://secure.gravatar.com/avatar/ed88a48c134c4a3d718de70eafe83533168d55b9b264b043145c16d5eb0acf4e?s=32&#038;d=monsterid&#038;r=g 2x' class='avatar avatar-16 photo' height='16' width='16' loading='lazy' decoding='async'/></a><a href="https://profiles.wordpress.org/douglaswebdesigns/" class="reviewer-name">douglaswebdesigns</a> on <span class="review-date">October 21, 2025</span>\t\t\t</p>\n\t\t</div>\n\t</div>\n\t<div class="review-body"><!-- wp:paragraph -->\n<p>Thank you Steve for the great work investigating my site and recommending updates that I had missed along with analyzing the site for Default Pages and junk that didn't need to be there. I would highly recommend AIOSEO for service, right out of the gate. If you're in the market for an SEO plugin - check AIOSEO. Looks like a game changer!</p>\n<!-- /wp:paragraph --></div>\n</div>\n<div class="review">\n\t<div class="review-head">\n\t\t<div class="reviewer-info">\n\t\t\t<div class="review-title-section">\n\t\t\t\t<h4 class="review-title">good</h4>\n\t\t\t\t<div class="star-rating">\n\t\t\t\t<div class="wporg-ratings" aria-label="5 out of 5 stars" data-title-template="%s out of 5 stars" data-rating="5" style="color:#ffb900;"><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span></div>\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<p class="reviewer">\n\t\t\t\tBy <a href="https://profiles.wordpress.org/iberepaiva/"><img alt='' src='https://secure.gravatar.com/avatar/40626bf055101e29d3d63065f0fd15cd226bb80c22980733058d4979ff5e7854?s=16&#038;d=monsterid&#038;r=g' srcset='https://secure.gravatar.com/avatar/40626bf055101e29d3d63065f0fd15cd226bb80c22980733058d4979ff5e7854?s=32&#038;d=monsterid&#038;r=g 2x' class='avatar avatar-16 photo' height='16' width='16' loading='lazy' decoding='async'/></a><a href="https://profiles.wordpress.org/iberepaiva/" class="reviewer-name">iberepaiva</a> on <span class="review-date">October 21, 2025</span>\t\t\t</p>\n\t\t</div>\n\t</div>\n\t<div class="review-body"><!-- wp:paragraph -->\n<p>Its good</p>\n<!-- /wp:paragraph --></div>\n</div>\n<div class="review">\n\t<div class="review-head">\n\t\t<div class="reviewer-info">\n\t\t\t<div class="review-title-section">\n\t\t\t\t<h4 class="review-title">The best SEO</h4>\n\t\t\t\t<div class="star-rating">\n\t\t\t\t<div class="wporg-ratings" aria-label="5 out of 5 stars" data-title-template="%s out of 5 stars" data-rating="5" style="color:#ffb900;"><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span><span class="star dashicons dashicons-star-filled"></span></div>\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<p class="reviewer">\n\t\t\t\tBy <a href="https://profiles.wordpress.org/almotzki/"><img alt='' src='https://secure.gravatar.com/avatar/9577159633b6309c765516dcff4aad593533c0fffed675e3e0de084bcb88679b?s=16&#038;d=monsterid&#038;r=g' srcset='https://secure.gravatar.com/avatar/9577159633b6309c765516dcff4aad593533c0fffed675e3e0de084bcb88679b?s=32&#038;d=monsterid&#038;r=g 2x' class='avatar avatar-16 photo' height='16' width='16' loading='lazy' decoding='async'/></a><a href="https://profiles.wordpress.org/almotzki/" class="reviewer-name">almotzki</a> on <span class="review-date">October 19, 2025</span>\t\t\t</p>\n\t\t</div>\n\t</div>\n\t<div class="review-body"><!-- wp:paragraph -->\n<p>Great Plug-In! Does wat it should! That's nearby perfection</p>\n<!-- /wp:paragraph --></div>\n</div>\n
            END;

        $expected_screenshots = <<<END
            <ol><li><a href="https://ps.w.org/all-in-one-seo-pack/assets/screenshot-1.gif?rev=2443290"><img src="https://ps.w.org/all-in-one-seo-pack/assets/screenshot-1.gif?rev=2443290" alt="SEO Content Analyzer (Gutenberg)"></a><p>SEO Content Analyzer (Gutenberg)</p></li><li><a href="https://ps.w.org/all-in-one-seo-pack/assets/screenshot-2.gif?rev=2443290"><img src="https://ps.w.org/all-in-one-seo-pack/assets/screenshot-2.gif?rev=2443290" alt="SEO Content Analyzer (Classic Editor)"></a><p>SEO Content Analyzer (Classic Editor)</p></li><li><a href="https://ps.w.org/all-in-one-seo-pack/assets/screenshot-3.gif?rev=2443290"><img src="https://ps.w.org/all-in-one-seo-pack/assets/screenshot-3.gif?rev=2443290" alt="SEO Setup Wizard"></a><p>SEO Setup Wizard</p></li><li><a href="https://ps.w.org/all-in-one-seo-pack/assets/screenshot-4.gif?rev=2443290"><img src="https://ps.w.org/all-in-one-seo-pack/assets/screenshot-4.gif?rev=2443290" alt="SEO Site Analysis"></a><p>SEO Site Analysis</p></li><li><a href="https://ps.w.org/all-in-one-seo-pack/assets/screenshot-5.gif?rev=2443290"><img src="https://ps.w.org/all-in-one-seo-pack/assets/screenshot-5.gif?rev=2443290" alt="Webmaster Tools Connect"></a><p>Webmaster Tools Connect</p></li><li><a href="https://ps.w.org/all-in-one-seo-pack/assets/screenshot-6.gif?rev=2443290"><img src="https://ps.w.org/all-in-one-seo-pack/assets/screenshot-6.gif?rev=2443290" alt="Social Media Integrations"></a><p>Social Media Integrations</p></li><li><a href="https://ps.w.org/all-in-one-seo-pack/assets/screenshot-7.gif?rev=2443290"><img src="https://ps.w.org/all-in-one-seo-pack/assets/screenshot-7.gif?rev=2443290" alt="Local SEO"></a><p>Local SEO</p></li><li><a href="https://ps.w.org/all-in-one-seo-pack/assets/screenshot-8.gif?rev=2443290"><img src="https://ps.w.org/all-in-one-seo-pack/assets/screenshot-8.gif?rev=2443290" alt="Sitemaps"></a><p>Sitemaps</p></li><li><a href="https://ps.w.org/all-in-one-seo-pack/assets/screenshot-9.gif?rev=2443290"><img src="https://ps.w.org/all-in-one-seo-pack/assets/screenshot-9.gif?rev=2443290" alt="Search Appearance Settings"></a><p>Search Appearance Settings</p></li><li><a href="https://ps.w.org/all-in-one-seo-pack/assets/screenshot-10.gif?rev=2443290"><img src="https://ps.w.org/all-in-one-seo-pack/assets/screenshot-10.gif?rev=2443290" alt="Robots.txt Editor"></a><p>Robots.txt Editor</p></li><li><a href="https://ps.w.org/all-in-one-seo-pack/assets/screenshot-11.gif?rev=2443290"><img src="https://ps.w.org/all-in-one-seo-pack/assets/screenshot-11.gif?rev=2443290" alt="RSS Content Control"></a><p>RSS Content Control</p></li><li><a href="https://ps.w.org/all-in-one-seo-pack/assets/screenshot-12.gif?rev=2674999"><img src="https://ps.w.org/all-in-one-seo-pack/assets/screenshot-12.gif?rev=2674999" alt="Headline Analyzer"></a><p>Headline Analyzer</p></li><li><a href="https://ps.w.org/all-in-one-seo-pack/assets/screenshot-13.gif?rev=2674999"><img src="https://ps.w.org/all-in-one-seo-pack/assets/screenshot-13.gif?rev=2674999" alt="Redirect Manager"></a><p>Redirect Manager</p></li><li><a href="https://ps.w.org/all-in-one-seo-pack/assets/screenshot-14.gif?rev=2674999"><img src="https://ps.w.org/all-in-one-seo-pack/assets/screenshot-14.gif?rev=2674999" alt="Link Assistant"></a><p>Link Assistant</p></li></ol>
            END;

        $this->markTestIncomplete('Markdown renderer results are not close to what we need');

        $this->assertEquals([
            'changelog'   => $expected_changelog,
            'description' => $expected_description,
            'faq'         => $expected_faq,
            'reviews'     => $expected_reviews,
            'screenshots' => $expected_screenshots,
        ], $sections);
    }

    public function test_parse_hello_cthulhu(): void
    {
        // This should eventually test every corner case, probably including zalgo text with invalid UTF-8 sequences.

        $hello_cthulhu = <<<'END'
            === Hello C'thulhu ===
            Contributors: chaz, chazworks
            Stable tag: 6.6.6
            Tested up to: 7.9
            Requires PHP: 8.8
            Tags: cthulhu, f'tagn, rlyeh, eldritch, old ones
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
            'name'              => 'Hello C&#039;thulhu',
            'short_description' => 'This is not just a plugin, it symbolizes the mounting horror and insanity of an entire generation.  Ia! Ia! Cthulhu Ftagn!',
            'tags'              => ['cthulhu', "f'tagn", 'rlyeh', 'eldritch', 'old ones'],
            'tested'            => '7.9',
            'requires_php'      => '8.8',
            'requires_wp'       => '6.6',
            'contributors'      => ['chaz', 'chazworks'],
            'stable_tag'        => '6.6.6',
            'donate_link'       => 'https://www.gofundyourself.com/c/hello-cthulhu',
            'license'           => 'GPL 3.0 or later',
            'license_uri'       => 'https://gnu.org',
            'sections'          => $sections,
            '_warnings'         => [],
        ], (array)$readme);

        $expected_description = <<<'END'
            <p>This is not just a plugin, it symbolizes the mounting horror and insanity of an entire generation.  Ia! Ia! Cthulhu Ftagn!.</p>
            <p>When activated you will notice nothing, but gradually care about nothing, until your soul is an empty vessel
            into which the visions of his grand dread majesty will materialize and take form through your husk of a body
            as you do his bidding in the hopes that you shall be among those to watch the flames which consume the universe
            to base ash so that you may be be last to be burned in unholy eldritch fire.</p>
            <p><em>Ph'nglui mglw'nafh Cthulhu R'lyeh wgah'nagl fhtagn</em></p>
            <p>Oh, and it also shows lyrics from Louis Armstrong's famous song &lt;title&gt;Hello Dolly&lt;/title&gt; on your admin dashboard.
            other notes here... (parsed or not, no idea)</p>
            END;

        $expected_faq = <<<'END'
            <p>Note that &quot;Frequently Asked Questions&quot; is apparently not recognized as a section despite the alias.</p>
            <h3>Is this a FAQ?</h3>
            <p>No.</p>
            END;

        $expected_screenshots = <<<'END'
            <p>Screenshots go here but only things in list tags make it into the property</p>
            <ul>
            <li>anything in a markdown list will do</li>
            <li>it doesn't seem to have to be a link</li>
            </ul>
            END;

        $expected_changelog = <<<'END'
            <p>This looks like free-form markdown</p>
            <ul>
            <li>But nonetheless it usually has bullet points.</li>
            <li>So let's have more bullet points</li>
            <li>like this one</li>
            </ul>
            END;

        $expected_upgrade_notice = <<<'END'
            <p>Your husk will be discarded when it is no longer of use.  You are not to concern yourself with it.</p>
            END;

        $this->assertEquals([
            'description'    => $expected_description,
            'faq'            => $expected_faq,
            'screenshots'    => $expected_screenshots,
            'changelog'      => $expected_changelog,
            'upgrade_notice' => $expected_upgrade_notice,
        ], $sections);
    }

}
