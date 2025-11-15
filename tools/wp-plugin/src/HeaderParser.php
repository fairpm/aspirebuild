<?php

declare(strict_types=1);

namespace AspireBuild\Tools\WpPlugin;

use AspireBuild\Util\Regex;

class HeaderParser
{
    /** @var array<string, string> */
    const array PLUGIN_HEADERS = [
        'Name'            => 'Plugin Name',
        'PluginURI'       => 'Plugin URI',
        'Description'     => 'Description',
        'Version'         => 'Version',
        'RequiresWP'      => 'Requires at least',
        'RequiresPHP'     => 'Requires PHP',
        'Author'          => 'Author',
        'AuthorURI'       => 'Author URI',
        'License'         => 'License',
        'LicenseURI'      => 'License URI',
        'TextDomain'      => 'Text Domain',
        'DomainPath'      => 'Domain Path',
        'Network'         => 'Network', // if present, only value accepted is true
        'UpdateURI'       => 'Update URI',
        'RequiresPlugins' => 'Requires Plugins',
        'TestedUpTo'      => 'Tested up to', // from Import::add_extra_plugin_headers
        // freaks and misfits
        // '_sitewide   => 'Site Wide Only',  // deprecated since 3.0, use Network instead
        // 'Title'      => 'Plugin Name',     // set by parser, not a header
        // 'AuthorName' => 'Author',          // set by parser, not a header
    ];

    /** @var array<string, string> */
    const array THEME_HEADERS = [
        // required fields
        'Name'        => 'Theme Name',
        'Author'      => 'Author',
        'Description' => 'Description',
        'Version'     => 'Version',
        'RequiresWP'  => 'Requires at least',
        'RequiresPHP' => 'Requires PHP',
        'TextDomain'  => 'Text Domain',
        // required fields documented on wp.org but not in WP_Theme::$file_headers.
        'TestedUpTo'  => 'Tested up to',
        'License'     => 'License',
        'LicenseURI'  => 'License URI',
        // optional fields
        'ThemeURI'    => 'Theme URI',
        'AuthorURI'   => 'Author URI',
        'Tags'        => 'Tags',
        'Template'    => 'Template', // required in a child theme (all other fields except name become optional)
        'DomainPath'  => 'Domain Path', // default: /languages
        // not documented on .org, presumably generated somewhere else
        'Status'      => 'Status',
        'UpdateURI'   => 'Update URI',
    ];

    /** @return array<string, string> */
    public function readPluginHeader(string $content): array
    {
        // https://developer.wordpress.org/plugins/plugin-basics/header-requirements/
        return $this->readHeaders($content, self::PLUGIN_HEADERS);
    }

    /** @return array<string, string> */
    public function readThemeHeader(string $content): array
    {
        // https://developer.wordpress.org/themes/basics/main-stylesheet-style-css/#explanations
        return $this->readHeaders($content, self::THEME_HEADERS);
    }

    /**
     * @param array<string, string> $headers
     * @return array<string, string>
     */
    public function readHeaders(string $content, array $headers): array
    {
        $parsed = [];
        foreach ($headers as $field => $key) {
            $pattern = '/^(?:[ \t]*<\?php)?[ \t\/*#@]*' . $key . ':(.*)$/mi';
            $matches = Regex::matches($pattern, $content);
            if (!$matches) {
                continue;
            }
            $parsed[$field] = mb_trim(Regex::replace('/\s*(?:\*\/|\?>).*/', '', $matches[1]));
        }
        return $parsed;
    }
}
