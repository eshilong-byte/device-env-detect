<?php

/**
 * 使用示例
 */

require_once __DIR__ . '/Detector.php';

use DeviceEnvDetect\Detector;

// 自动读取当前请求的 UA
$detector = new Detector();
$result = $detector->detect();

echo '<pre>';
print_r($result);
echo '</pre>';

// 手动传入 UA 测试
$testUa = 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 MicroMessenger/8.0.28';
$test = new Detector($testUa);
print_r($test->detect());
