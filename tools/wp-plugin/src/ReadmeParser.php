<?php

namespace AspireBuild\Tools\WpPlugin;

use AspireBuild\Util\Regex;
use stdClass;

// Items still to do:
//
//   * Markdown is not parsed in sections

// Known incompatibilities (will not be fixed):
//
//   * "Contributors": names are passed through raw, and not expanded to full names
//   * "Tested Up To": not checked against latest stable version -- any version number is accepted

class ReadmeParser
{

    public array $warnings = [];

    public const array expected_sections = [
        'description',
        'installation',
        'faq',
        'screenshots',
        'changelog',
        'upgrade_notice',
        'other_notes',
    ];

    public const array alias_sections = [
        'frequently_asked_questions' => 'faq',
        'change_log'                 => 'changelog',
        'screenshot'                 => 'screenshots',
    ];

    public const array valid_headers = [
        'tested'            => 'tested',
        'tested up to'      => 'tested',
        'requires'          => 'requires',
        'requires at least' => 'requires',
        'requires php'      => 'requires_php',
        'tags'              => 'tags',
        'contributors'      => 'contributors',
        'donate link'       => 'donate_link',
        'stable tag'        => 'stable_tag',
        'license'           => 'license',
        'license uri'       => 'license_uri',
    ];

    public const array ignore_tags = ['plugin', 'wordpress'];

    public const array maximum_field_lengths = [
        'short_description' => 150,
        'section'           => 2500,
        'section-changelog' => 5000,
        'section-faq'       => 5000,
    ];

    public function parse(string $str): stdClass
    {
        $str = $this->ensure_utf8($str);
        $contents = array_map(fn($line) => rtrim($line, "\r\n"), preg_split('!\R!u', $str));

        $fields = [
            'name' => '',
            'short_description' => '',
            'tags' => [],
            'requires' => '',
            'tested' => '',
            'requires_php' => '',
            'contributors' => [],
            'stable_tag' => '',
            'donate_link' => '',
            'license' => '',
            'license_uri' => '',
            'upgrade_notice' => [],
            'screenshots' => [],
            'faq' => [],
            'sections' => [],
        ];

        [$fields['name'], $contents] = $this->parse_plugin_name($contents);
        [$raw_headers, $contents] = $this->parse_headers($contents);
        [$fields['short_description'], $contents] = $this->parse_short_description($contents);
        [$fields['sections'],] = $this->parse_sections($contents);

        $fields = $this->extract_fields_from_headers($raw_headers, $fields);
        $fields = $this->fixup_sections($fields);

        //// Gather warnings

        // Should generalize to testing any validated data as being blank/missing when the source is not

        // Not setting tested_header_ignored because we don't know the current WP version, and it fails for 6.9 -> 7.0 anyway

        return (object)[
            ...$fields,
            '_warnings' => $this->warnings,
        ];
    }

    protected function ensure_utf8(string $str): string
    {
        if (str_starts_with($str[0], "\xFF\xFE")) {
            // UTF-16 BOM detected, convert to UTF8.  This is our only attempt at encoding detection.
            $str = \Safe\mb_convert_encoding($str, 'UTF-8', 'UTF-16');
        }

        if (str_starts_with($str, "\xEF\xBB\xBF")) {
            // UTF-8 BOM detected, strip it.
            $str = substr($str, 3);
        }

        // Sets it globally.  That's the mbstring api for you, but it's fine in this case.
        mb_substitute_character(0xFFFD); // � - Replacement Character

        $str = mb_scrub($str, 'UTF-8');

        return normalizer_normalize($str);
    }

    protected function parse_first_nonblank_line(array $contents): array
    {
        while (($line = array_shift($contents)) !== null) {
            if (trim($line) !== '') {
                break;
            }
        }
        return [$line ?? '', $contents];
    }

    protected function parse_plugin_name(array $contents): array
    {
        [$line, $contents] = $this->parse_first_nonblank_line($contents);

        $name = $this->sanitize_text(trim($line, "#= \t\0\x0B"));

        if ($this->parse_possible_header($line, true /* only valid headers */)) {
            array_unshift($contents, $line);

            $this->warnings['invalid_plugin_name_header'] = true;
            $name = false;
        }

        // Strip Github style header\n==== underlines.
        if (!empty($contents) && '' === trim($contents[0], '=-')) {
            array_shift($contents);
        }

        // Handle readme's which do `=== Plugin Name ===\nMy SuperAwesomePlugin Name\n...`
        if ('plugin name' === strtolower($name)) {
            $this->warnings['invalid_plugin_name_header'] = true;

            $name = false;
            [$line, $contents] = $this->parse_first_nonblank_line($contents);

            // Ensure that the line read doesn't look like a description.
            if (strlen($line) < 50 && !$this->parse_possible_header($line, true /* only valid headers */)) {
                $name = $this->sanitize_text(trim($line, "#= \t\0\x0B"));
            } else {
                // Put it back on the stack to be processed.
                array_unshift($contents, $line);
            }
        }
        return [$name, $contents];
    }

    protected function parse_headers(array $contents): array
    {
        $headers = [];

        [$line, $contents] = $this->parse_first_nonblank_line($contents);
        $last_line_was_blank = false;
        do {
            $value = null;
            $header = $this->parse_possible_header($line);

            // If it doesn't look like a header value, maybe break to the next section.
            if (!$header) {
                if (empty($line)) {
                    // Some plugins have line-breaks within the headers...
                    $last_line_was_blank = true;
                    continue;
                }

                // We've hit a line that is not blank, but also doesn't look like a header, assume the Short Description and end Header parsing.
                break;
            }

            [$key, $value] = $header;

            if (isset(self::valid_headers[$key])) {
                $headers[self::valid_headers[$key]] = $value;
            } elseif ($last_line_was_blank) {
                // If we skipped over a blank line, and then ended up with an unexpected header, assume we parsed too far and ended up in the Short Description.
                // This final line will be added back into the stack after the loop for further parsing.
                break;
            }

            $last_line_was_blank = false;
        } while (($line = array_shift($contents)) !== null);
        array_unshift($contents, $line);
        return [$headers, $contents];
    }

    protected function read_tags_header(string $input): array
    {
        $tags = explode(',', $input);
        $tags = array_map(trim(...), $tags);
        $tags = array_filter($tags);

        if (array_intersect($tags, self::ignore_tags)) {
            $this->warnings['ignored_tags'] = array_intersect($tags, self::ignore_tags);
            $tags = array_diff($tags, self::ignore_tags);
        }

        if (count($tags) > 5) {
            $this->warnings['too_many_tags'] = array_slice($tags, 5);
            $tags = array_slice($tags, 0, 5);
        }

        return $tags;
    }

    protected function read_contributors_header(string $input): array
    {
        return $this->sanitize_contributors(array_filter(array_map(trim(...), explode(',', $input))));
    }

    protected function extract_fields_from_headers(array $headers, array $fields): array
    {
        $fields['tags'] = $this->read_tags_header($headers['tags'] ?? '');
        $fields['requires'] = $this->normalize_version($headers['requires'] ?? '');
        $fields['tested'] = $this->normalize_version($headers['tested'] ?? '');
        $fields['requires_php'] = $this->normalize_version($headers['requires_php'] ?? '');
        $fields['contributors'] = $this->read_contributors_header($headers['contributors'] ?? '');
        $fields['stable_tag'] = $this->normalize_stable_tag($headers['stable_tag'] ?? '');
        $fields['donate_link'] = $headers['donate_link'] ?? '';
        $fields['license'] = $headers['license'] ?? ''; // will want to normalize this later
        $fields['license_uri'] = $headers['license_uri'] ?? '';

        if (!empty($fields['license'])
            && empty($headers['license_uri'])
            && ($url = Regex::extract('!https?://\S+!i', $headers['license']))) {
            // Handle the many cases of "License: GPLv2 - http://..."
            $fields['license_uri'] = trim($url, " -*\t\n\r(");
            $fields['license'] = trim(str_replace($url, '', $headers['license']), " -*\t\n\r(");
        }

        // Validate the license specified.
        if (!$fields['license']) {
            $this->warnings['license_missing'] = true;
        } else {
            $license_error = $this->validate_license($fields['license']);
            if (true !== $license_error) {
                $this->warnings[$license_error] = $fields['license'];
            }
        }

        if ($fields['requires_php'] === '' && !empty($headers['requires_php'])) {
            $this->warnings['requires_php_header_ignored'] = true;
        }

        return $fields;
    }

    protected function fixup_sections(array $fields): array
    {
        $sections = $fields['sections'];

        if (empty($sections['description'])) {
            $sections['description'] = $fields['short_description'];
        }

        // Suffix the Other Notes section to the description.
        if (!empty($sections['other_notes'])) {
            $sections['description'] .= "\n" . $sections['other_notes'];
            unset($sections['other_notes']);
        }

        if (isset($sections['upgrade_notice'])) {
            $upgrade_notice = $this->parse_section($sections['upgrade_notice']);
            $upgrade_notice = array_map($this->sanitize_text(...), $upgrade_notice);
            unset($sections['upgrade_notice']);
            $fields['upgrade_notice'] = $upgrade_notice;
        }

        foreach ($sections as $section => $content) {
            $max_length = "section-$section";
            if (!isset(self::maximum_field_lengths[$max_length])) {
                $max_length = 'section';
            }

            $sections[$section] = $this->trim_length($content, $max_length, 'words');

            if ($content !== $sections[$section]) {
                $this->warnings["trimmed_section_$section"] = true;
            }
        }

        // Display FAQs as a definition list.
        if (isset($sections['faq'])) {
            $faq = $this->parse_section($sections['faq']);
            $sections['faq'] = '';
        }

        // Markdownify!
        $sections = array_map($this->parse_markdown(...), $sections);
        $upgrade_notice = array_map($this->parse_markdown(...), $upgrade_notice);
        $faq = array_map($this->parse_markdown(...), $faq);

        $short_description = $fields['short_description'];

        // Use the first line of the description for the short description if not provided.
        if (!$short_description && !empty($sections['description'])) {
            $short_description = array_filter(explode("\n", $sections['description']))[0];
            $this->warnings['no_short_description_present'] = true;
        }

        // Sanitize and trim the short_description to match requirements.
        $short_description = $this->sanitize_text($short_description);
        $short_description = $this->parse_markdown($short_description);
        // $short_description = wp_strip_all_tags($short_description); // TODO
        $trimmed = $this->trim_length($short_description, 'short_description');
        if ($short_description !== $trimmed) {
            if (empty($this->warnings['no_short_description_present'])) {
                $this->warnings['trimmed_short_description'] = true;
            }
            $short_description = $trimmed;
        }
        $fields['short_description'] = $short_description;

        if (isset($sections['screenshots'])) {
            preg_match_all('#<li>(.*?)</li>#is', $sections['screenshots'], $screenshots, PREG_SET_ORDER);
            if ($screenshots) {
                $i = 1; // Screenshots start from 1.
                foreach ($screenshots as $ss) {
                    $screenshots[$i++] = $this->filter_text($ss[1]);
                }
            }
            unset($sections['screenshots']);
        }

        if (!empty($faq)) {
            // If the FAQ contained data we couldn't parse, we'll treat it as freeform and display it before any questions which are found.
            if (isset($faq[''])) {
                $sections['faq'] .= $faq[''];
                unset($faq['']);
            }

            if ($faq) {
                $sections['faq'] .= "\n<dl>\n";
                foreach ($faq as $question => $answer) {
                    $question_slug = rawurlencode(strtolower(trim($question)));
                    $sections['faq'] .= "<dt id='$question_slug'><h3>$question</h3></dt>\n<dd>$answer</dd>\n";
                }
                $sections['faq'] .= "\n</dl>\n";
            }
        }

        $sections = array_map($this->filter_text(...), $sections);

        $fields['sections'] = $sections;
        return $fields;

    }

    protected function parse_short_description(array $contents): array
    {
        $short_description = '';

        while (($line = array_shift($contents)) !== null) {
            $trimmed = trim($line);
            if (empty($trimmed)) {
                continue;
            }
            if (('=' === $trimmed[0] && isset($trimmed[1]) && '=' === $trimmed[1])
                || ('#' === $trimmed[0]
                    && isset($trimmed[1])
                    && '#' === $trimmed[1])
            ) {
                // Stop after any Markdown heading.
                array_unshift($contents, $line);
                break;
            }

            $short_description .= $line . ' ';
        }
        $short_description = trim($short_description);

        return [$short_description, $contents, []];
    }

    protected function parse_sections(array $contents): array
    {
        $sections = array_fill_keys(self::expected_sections, '');
        $current = '';
        $section_name = '';
        while (($line = array_shift($contents)) !== null) {
            $trimmed = trim($line);
            if (empty($trimmed)) {
                $current .= "\n";
                continue;
            }

            // Stop only after a ## Markdown header, not a ###.
            if (('=' === $trimmed[0] && isset($trimmed[1]) && '=' === $trimmed[1])
                || ('#' === $trimmed[0] && isset($trimmed[1]) && '#' === $trimmed[1] && isset($trimmed[2])
                    && '#'
                    !== $trimmed[2])
            ) {
                if (!empty($section_name)) {
                    $sections[$section_name] .= trim($current);
                }

                $current = '';
                $section_title = trim($line, "#= \t");
                $section_name = self::alias_sections[$section_name]
                    ??
                    strtolower(str_replace(' ', '_', $section_title));

                // If we encounter an unknown section header, include the provided Title, we'll filter it to other_notes later.
                if (!in_array($section_name, self::expected_sections, true)) {
                    $current .= '<h3>' . $section_title . '</h3>';
                    $section_name = 'other_notes';
                }
                continue;
            }

            $current .= $line . "\n";
        }

        if (!empty($section_name)) {
            $sections[$section_name] .= trim($current);
        }

        // Filter out any empty sections.
        $sections = array_filter($sections);

        return [$sections, $contents];
    }

    protected function trim_length(string $desc, int|string $length = 150, string $type = 'char'): string
    {
        if (is_string($length)) {
            $length = self::maximum_field_lengths[$length] ?? $length;
        }

        if ('words' === $type) {
            // Split by whitespace, capturing it so we can put it back together.
            $pieces = @preg_split('/(\s+)/u', $desc, -1, PREG_SPLIT_DELIM_CAPTURE);

            // In the event of an error (Likely invalid UTF8 data), perform the same split, this time in a non-UTF8 safe manner, as a fallback.
            if ($pieces === false) {
                $pieces = preg_split('/(\s+)/', $desc, -1, PREG_SPLIT_DELIM_CAPTURE);
            }

            $word_count_with_spaces = $length * 2;

            if (count($pieces) < $word_count_with_spaces) {
                return $desc;
            }

            $pieces = array_slice($pieces, 0, $word_count_with_spaces);

            return implode('', $pieces) . ' &hellip;';
        }

        // Apply the length restriction without counting html entities.
        $str_length = mb_strlen(html_entity_decode($desc) ?: $desc);

        if ($str_length > $length) {
            $desc = mb_substr($desc, 0, $length);

            // If not a full sentence...
            if ('.' !== mb_substr($desc, -1)) {
                // ..and one ends within 20% of the end, trim it to that.
                if (($pos = mb_strrpos($desc, '.')) > (0.8 * $length)) {
                    $desc = mb_substr($desc, 0, $pos + 1);
                } else {
                    // ..else mark it as being trimmed.
                    $desc .= ' &hellip;';
                }
            }
        }

        return trim($desc);
    }

    protected function parse_possible_header(string $line, bool $only_valid = false): false|array
    {
        if (!str_contains($line, ':') || str_starts_with($line, '#') || str_starts_with($line, '=')) {
            return false;
        }

        [$key, $value] = explode(':', $line, 2);
        $key = strtolower(trim($key, " \t*-\r\n"));
        $value = trim($value, " \t*-\r\n");

        if ($only_valid && !isset(self::valid_headers[$key])) {
            return false;
        }

        return [$key, $value];
    }

    protected function filter_text(string $text): string
    {
        // TODO
        // $text = trim($text);
        //
        // $allowed = [
        //     'a'          => ['href' => true, 'title' => true, 'rel' => true],
        //     'blockquote' => ['cite' => true],
        //     'br'         => [],
        //     'p'          => [],
        //     'code'       => [],
        //     'pre'        => [],
        //     'em'         => [],
        //     'strong'     => [],
        //     'ul'         => [],
        //     'ol'         => [],
        //     'dl'         => [],
        //     'dt'         => ['id' => true],
        //     'dd'         => [],
        //     'li'         => [],
        //     'h3'         => [],
        //     'h4'         => [],
        // ];
        //
        // $text = force_balance_tags($text);
        // TODO: make_clickable() will act inside shortcodes.
        // $text = make_clickable( $text );
        // $text = wp_kses($text, $allowed);
        //
        // wpautop() will eventually replace all \n's with <br>s, and that isn't what we want (The text may be line-wrapped in the readme, we don't want that, we want paragraph-wrapped text)
        // TODO: This incorrectly also applies within `<code>` tags which we don't want either.
        // $text = preg_replace( "/(?<![> ])\n/", ' ', $text );
        return trim($text);
    }

    protected function sanitize_text(string $text): string
    {
        // not fancy
        $text = strip_tags($text);
        // $text = esc_html($text); // TODO
        return trim($text);
    }

    protected function sanitize_contributors(array $users): array
    {
        // foreach ($users as $i => $name) {
        //     // Trim any leading `@` off the name, in the event that someone uses `@joe-bloggs`.
        //     $name = ltrim($name, '@');
        //
        //     // Contributors should be listed by their WordPress.org Login name (Example: 'Joe Bloggs')
        //     $user = get_user_by('login', $name);
        //
        //     // Or failing that, by their user_nicename field (Example: 'joe-bloggs')
        //     if (!$user) {
        //         $user = get_user_by('slug', $name);
        //     }
        //
        //     // In the event that something invalid is used, we'll ignore it (Example: 'Joe Bloggs (Australian Translation)')
        //     if (!$user) {
        //         $this->warnings['contributor_ignored'] ??= [];
        //         $this->warnings['contributor_ignored'][] = $name;
        //         unset($users[$i]);
        //         continue;
        //     }
        //
        //     // Overwrite whatever the author has specified with the sanitized nicename.
        //     $users[$i] = $user->user_nicename;
        // }
        return $users; // TODO
    }

    protected function normalize_stable_tag(string $stable_tag): string
    {
        $stable_tag = trim($stable_tag);
        $stable_tag = trim($stable_tag, '"\''); // "trunk"
        $stable_tag = Regex::replace('!^/?tags/!i', '', $stable_tag); // "tags/1.2.3"
        $stable_tag = Regex::replace('![^a-z0-9_.-]!i', '', $stable_tag);

        // If the stable_tag begins with a ., we treat it as 0.blah.
        if (str_starts_with($stable_tag, '.')) {
            $stable_tag = "0$stable_tag";
        }

        return $stable_tag;
    }

    protected function normalize_version(string $version): string
    {
        return Regex::extract('(\d+(\.\d+){1,2})', $version);
    }

    protected function parse_section(array|string $lines): array
    {
        $key = $value = '';
        $return = [];

        if (!is_array($lines)) {
            $lines = explode("\n", $lines);
        }
        $trimmed_lines = array_map('trim', $lines);

        /*
         * The heading style being matched in the block. Can be 'heading' or 'bold'.
         * Standard Markdown headings (## .. and == ... ==) are used, but if none are present.
         * full line bolding will be used as a heading style.
         */
        $heading_style = 'bold'; // 'heading' or 'bold'
        foreach ($trimmed_lines as $trimmed) {
            if ($trimmed && ($trimmed[0] === '#' || $trimmed[0] === '=')) {
                $heading_style = 'heading';
                break;
            }
        }

        $line_count = count($lines);
        for ($i = 0; $i < $line_count; $i++) {
            $line = &$lines[$i];
            $trimmed = &$trimmed_lines[$i];
            if (!$trimmed) {
                $value .= "\n";
                continue;
            }

            $is_heading = false;
            if ('heading' === $heading_style && ($trimmed[0] === '#' || $trimmed[0] === '=')) {
                $is_heading = true;
            } elseif ('bold' === $heading_style && (str_starts_with($trimmed, '**') && str_ends_with($trimmed, '**'))) {
                $is_heading = true;
            }

            if ($is_heading) {
                if ($value) {
                    $return[$key] = trim($value);
                }

                $value = '';
                // Trim off the first character of the line, as we know that's the heading style we're expecting to remove.
                $key = trim($line, $trimmed[0] . " \t");
                continue;
            }

            $value .= $line . "\n";
        }

        if ($key || $value) {
            $return[$key] = trim($value);
        }

        return $return;
    }

    protected function parse_markdown(string $text): string
    {
        return $text; // TODO
        // static $markdown = null;
        //
        // // Return early if the Markdown processor isn't available.
        // if (!class_exists('\WordPressdotorg\Plugin_Directory\Markdown')) {
        //     return $text;
        // }
        //
        // if (is_null($markdown)) {
        //     $markdown = new Markdown();
        // }
        //
        // return $markdown->transform($text);
    }

    protected function validate_license(string $license): bool|string
    {
        /*
         * This is a shortlist of keywords that are expected to be found in a valid license field.
         * See https://www.gnu.org/licenses/license-list.en.html for possible compatible licenses.
         */
        $probably_compatible = [
            'GPL',
            'General Public License',
            // 'GNU 2', 'GNU Public', 'GNU Version 2' explicitely not included, as it's not a specific license.
            'MIT',
            'ISC',
            'Expat',
            'Apache 2',
            'Apache License 2',
            'X11',
            'Modified BSD',
            'New BSD',
            '3 Clause BSD',
            'BSD 3',
            'FreeBSD',
            'Simplified BSD',
            '2 Clause BSD',
            'BSD 2',
            'MPL',
            'Mozilla Public License',
            strrev('LPFTW'),
            strrev('kcuf eht tahw od'), // To avoid some code scanners..
            'Public Domain',
            'CC0',
            'Unlicense',
            'CC BY', // Note: BY-NC & BY-ND are a no-no. See below.
            'zlib',
        ];

        /*
         * This is a shortlist of keywords that are likely related to a non-GPL  compatible license.
         * See https://www.gnu.org/licenses/license-list.en.html for possible explanations.
         */
        $probably_incompatible = [
            '4 Clause BSD',
            'BSD 4 Clause',
            'Apache 1',
            'CC BY-NC',
            'CC-NC',
            'NonCommercial',
            'CC BY-ND',
            'NoDerivative',
            'EUPL',
            'OSL',
            'Personal use',
            'without permission',
            'without prior auth',
            'you may not',
            'Proprietery',
            'proprietary',
        ];

        $sanitize_license = static function (string $license): string {
            $license = strtolower($license);

            // Localised or verbose licences.
            $license = str_replace('licence', 'license', $license);
            $license = str_replace('clauses', 'clause', $license); // BSD
            $license = str_replace('creative commons', 'cc', $license);

            // If it looks like a full GPL statement, trim it back, for this function.
            if (str_contains($license, 'gnu general public license version 2, june 1991 copyright (c) 1989')) {
                $license = 'gplv2';
            }

            // Replace 'Version 9' & v9 with '9' for simplicity.
            $license = preg_replace('/(version |v)([0-9])/i', '$2', $license);

            // Remove unexpected characters
            $license = preg_replace('/(\s*[^a-z0-9. ]+\s*)/i', '', $license);

            // Remove all spaces
            return preg_replace('/\s+/', '', $license);
        };

        $probably_compatible = array_map($sanitize_license, $probably_compatible);
        $probably_incompatible = array_map($sanitize_license, $probably_incompatible);
        $license = $sanitize_license($license);

        // First check to see if it's most probably an incompatible license.
        if (array_any($probably_incompatible, fn($match) => str_contains($license, $match))) {
            return 'invalid_license';
        }

        // Check to see if it's likely compatible.
        if (array_any($probably_compatible, fn($match) => str_contains($license, $match))) {
            return true;
        }

        // If we've made it this far, it's neither likely incompatible, or likely compatible, so unknown.
        return 'unknown_license';
    }
}
