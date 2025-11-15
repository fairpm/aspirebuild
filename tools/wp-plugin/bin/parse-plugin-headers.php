#!/usr/bin/env php
<?php
declare(strict_types=1);

require_once $_ENV['ASPIREBUILD'] . '/vendor/autoload.php';

use AspireBuild\Tools\WpPlugin\HeaderParser;
use AspireBuild\Util\Json;

$parser = new HeaderParser();

$parsed = $parser->readPluginHeader(file_get_contents('php://stdin'));
echo Json::encode($parsed);

