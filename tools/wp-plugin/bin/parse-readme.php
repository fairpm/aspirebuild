#!/usr/bin/env php
<?php
declare(strict_types=1);

require_once $_ENV['ASPIREBUILD'] . '/vendor/autoload.php';

use AspireBuild\Tools\WpPlugin\ReadmeParser;
use AspireBuild\Util\Json;

$parser = new ReadmeParser();

$parsed = $parser->parse(file_get_contents('php://stdin'));
echo Json::encode($parsed);

