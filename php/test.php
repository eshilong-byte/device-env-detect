<?php

/**
 * 简单测试（可在命令行运行）
 * php test.php
 */

require_once __DIR__ . '/Detector.php';

use DeviceEnvDetect\Detector;

$tests = [
    'Desktop Chrome' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'iPhone Safari'  => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1',
    'WeChat iOS'     => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 MicroMessenger/8.0.28',
    'WeChat Android' => 'Mozilla/5.0 (Linux; Android 12; SM-G991B) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/100.0.4896.127 Mobile Safari/537.36 MicroMessenger/8.0.22',
    'Enterprise WeChat' => 'Mozilla/5.0 (Linux; Android 10; MI 9) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/78.0.3904.62 Mobile Safari/537.36 wxwork/3.0.16',
];

foreach ($tests as $name => $ua) {
    $d = new Detector($ua);
    $r = $d->detect();
    echo sprintf(
        "%-20s => type: %-8s | wechat: %s | mobile: %s | browser: %s\n",
        $name,
        $r['type'],
        $r['is_wechat'] ? 'Y' : 'N',
        $r['is_mobile'] ? 'Y' : 'N',
        $r['browser']['name']
    );
}
