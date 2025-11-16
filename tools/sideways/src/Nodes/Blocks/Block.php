<?php
declare(strict_types=1);

namespace AspireBuild\Tools\Sideways\Nodes\Blocks;

use AspireBuild\Tools\Sideways\Nodes\Node;

class Block extends Node
{
    public function __construct(
        // Universal Properties
        public string $type,  // blockList ('ul' | 'ol')  paragraph ('Paragraph')  linesElements ($blockType)
        public ?array $element = null, // everything
        public int $interrupted = 0, // many places

        public bool $continuable = false, // linesElements
        public bool $identified = false, // linesElements, blockTable,

        public bool $complete = false, // blockFencedCodeContinue
        public int $openerLength = 0, // blockFencedCode, blockFencedCodeContinue
        public string $char, // blockFencedCode, blockFencedCodeContinue,

        public string $label, // blockFootnote, blockFootnoteComplete
        public string $text,  // blockFootnoteContinue, blockFootnoteComplete

        public int $indent = 0, // blockList, blockListContinue
        public ?array $li = null, // blockList, blockListContinue
        public ?array $data = null, // blockList, blockListContinue
        public bool $loose = false, // blockListContinue, blockListComplete

        public bool $void = false, // _blockMarkup_Extra, blockMarkupComplete
        public string $name,  // blockMarkup, _blockMarkup_Extra, _blockMarkupContinue_Extra

        public bool $closed = false, // blockComment, blockCommentContinue, blockMarkup, _blockMarkup_Extra, _blockMarkupContinue_Extra

        public ?array $alignments = null, // blockTable, blockTableContinue

        public ?array $dd = null, // blockDefinitionListContinue, addDdElement
    ) {}
}

