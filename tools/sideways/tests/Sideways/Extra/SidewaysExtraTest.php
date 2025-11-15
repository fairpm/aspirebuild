<?php

namespace Tests\AspireBuild\Tools\Sideways\Extra;

use PHPUnit\Framework\TestCase;

class SidewaysExtraTest extends TestCase
{
    public function test_in_a_sane_way(): void
    {
        $this->markTestIncomplete('Still disentangling ParsedownExtra tests from inheritance');
    }
}

// class ParsedownExtraTest extends ParsedownTest
// {
//     public static function data(): array
//     {
//         // what fresh hell is this?
//         // foreach ($this->initDirs() as $i => $dir)
//         // {
//         //     $newData = $this->dataFromDirectory($dir);
//         //
//         //     if ($i < 1)
//         //     {
//         //         # Parsedown-Extra has different treatment of HTML
//         //         $newData = array_filter($newData, function ($s) { return strpos($s[0], 'markup') === false; });
//         //         $newData = array_filter($newData, function ($s) { return strpos($s[0], 'html') === false; });
//         //     }
//         //
//         //     $data = array_merge($data, $newData);
//         // }
//
//         $data = [];
//
//         $Folder = new DirectoryIterator(__DIR__ . '/data');
//
//         foreach ($Folder as $File) {
//             /** @var $File DirectoryIterator */
//
//             if (!$File->isFile()) {
//                 continue;
//             }
//
//             $filename = $File->getFilename();
//
//             $extension = pathinfo($filename, PATHINFO_EXTENSION);
//
//             if ($extension !== 'md') {
//                 continue;
//             }
//
//             $basename = $File->getBasename('.md');
//
//             $html = __DIR__ . "/data/$basename.html";
//
//             if (file_exists($html)) {
//                 $data [] = [$basename, __DIR__ . '/data'];
//             }
//         }
//
//         return $data;
//     }
//
// }
