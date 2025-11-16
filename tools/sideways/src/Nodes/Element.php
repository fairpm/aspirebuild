<?php
declare(strict_types=1);

namespace AspireBuild\Tools\Sideways\Nodes;

use Closure;

class Element extends Node
{
    public function __construct(
        public ?string $name = null, // the tag name

        public ?string $text = null,

        public ?string $rawHtml = null,

        /** @var array<string, mixed> */
        public array $attributes = [],

        public ?bool $autobreak = null, // null means inherit existing value

        /** @var list<Element> */
        public array $elements = [],

        public ?Element $element = null, // seems redundant with $elements

        /** @var array<array{function: Closure, argument: mixed, destination: string}> */
        public array $handler = [],

        /** @var array<string> */
        public array $nonNestables = [],

        public bool $allowRawHtmlInSafeMode = false,    // only ever used in footnotes
    ) {}
}
