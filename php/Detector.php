<?php

/**
 * Device Environment Detector (PHP version)
 * Compatible with PHP 8.0 ~ 8.5
 *
 * 区分：电脑 / 手机浏览器 / 微信 H5
 * 纯 PHP 实现，无外部依赖
 */

declare(strict_types=1);

namespace DeviceEnvDetect;

class Detector
{
    private string $ua;
    private array $parsed = [];

    /** @var array<string, string> 常见厂商关键词映射 */
    private const VENDOR_MAP = [
        'samsung' => 'Samsung',
        'sm-'     => 'Samsung',
        'gt-'     => 'Samsung',
        'huawei'  => 'Huawei',
        'honor'   => 'Honor',
        'xiaomi'  => 'Xiaomi',
        'redmi'   => 'Xiaomi',
        'mi '     => 'Xiaomi',
        'oppo'    => 'OPPO',
        'vivo'    => 'vivo',
        'oneplus' => 'OnePlus',
        'realme'  => 'realme',
        'meizu'   => 'Meizu',
        'lenovo'  => 'Lenovo',
        'motorola'=> 'Motorola',
        'nokia'   => 'Nokia',
        'sony'    => 'Sony',
        'lg'      => 'LG',
        'asus'    => 'ASUS',
        'google'  => 'Google',
        'pixel'   => 'Google',
        'apple'   => 'Apple',
        'iphone'  => 'Apple',
        'ipad'    => 'Apple',
    ];

    public function __construct(?string $userAgent = null)
    {
        $this->ua = strtolower($userAgent ?? ($_SERVER['HTTP_USER_AGENT'] ?? ''));
        $this->parse();
    }

    /**
     * 增强版微信判断
     * PHP 端只能依赖 UA（无法检测 JS 对象），但做了更严谨的匹配
     */
    public function isWeChat(): bool
    {
        // 排除企业微信
        if (str_contains($this->ua, 'wxwork')) {
            return false;
        }

        return str_contains($this->ua, 'micromessenger');
    }

    public function isMiniProgram(): bool
    {
        return str_contains($this->ua, 'miniprogram');
    }

    public function isMobile(): bool
    {
        return $this->parsed['is_mobile'];
    }

    public function isDesktop(): bool
    {
        return !$this->isMobile() && !$this->isWeChat();
    }

    /**
     * 主检测方法
     */
    public function detect(): array
    {
        $isWechat = $this->isWeChat();
        $isMobile = $this->isMobile();

        $type = 'desktop';
        if ($isWechat) {
            $type = 'wechat';
        } elseif ($isMobile) {
            $type = 'mobile';
        }

        return [
            'type'           => $type,
            'is_wechat'      => $isWechat,
            'is_miniprogram' => $this->isMiniProgram(),
            'is_mobile'      => $isMobile,
            'is_desktop'     => $this->isDesktop(),
            'browser'        => [
                'name'    => $this->parsed['browser'],
                'version' => $this->parsed['browser_version'],
            ],
            'os'             => [
                'name'    => $this->parsed['os'],
                'version' => $this->parsed['os_version'],
            ],
            'device'         => [
                'type'   => $isMobile ? 'mobile' : 'desktop',
                'vendor' => $this->parsed['vendor'],
                'model'  => $this->parsed['model'],
            ],
            'ua'             => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ];
    }

    /**
     * 内部 UA 解析（覆盖主流场景 + 更完善的厂商/机型识别）
     */
    private function parse(): void
    {
        $ua = $this->ua;

        // OS
        $os = 'Unknown';
        $osVersion = '';
        if (preg_match('/windows nt ([\d.]+)/i', $ua, $m)) {
            $os = 'Windows';
            $osVersion = $m[1];
        } elseif (preg_match('/android ([\d.]+)/i', $ua, $m)) {
            $os = 'Android';
            $osVersion = $m[1];
        } elseif (preg_match('/iphone os ([\d_]+)/i', $ua, $m) || preg_match('/cpu os ([\d_]+)/i', $ua, $m)) {
            $os = 'iOS';
            $osVersion = str_replace('_', '.', $m[1]);
        } elseif (str_contains($ua, 'mac os x')) {
            $os = 'macOS';
        } elseif (str_contains($ua, 'linux')) {
            $os = 'Linux';
        }

        // Browser
        $browser = 'Unknown';
        $browserVersion = '';
        if (str_contains($ua, 'micromessenger')) {
            $browser = 'WeChat';
            if (preg_match('/micromessenger\/([\d.]+)/i', $ua, $m)) {
                $browserVersion = $m[1];
            }
        } elseif (str_contains($ua, 'edg/')) {
            $browser = 'Edge';
            if (preg_match('/edg\/([\d.]+)/i', $ua, $m)) {
                $browserVersion = $m[1];
            }
        } elseif (str_contains($ua, 'chrome') && !str_contains($ua, 'edg')) {
            $browser = 'Chrome';
            if (preg_match('/chrome\/([\d.]+)/i', $ua, $m)) {
                $browserVersion = $m[1];
            }
        } elseif (str_contains($ua, 'safari') && !str_contains($ua, 'chrome')) {
            $browser = 'Safari';
            if (preg_match('/version\/([\d.]+)/i', $ua, $m)) {
                $browserVersion = $m[1];
            }
        } elseif (str_contains($ua, 'firefox')) {
            $browser = 'Firefox';
            if (preg_match('/firefox\/([\d.]+)/i', $ua, $m)) {
                $browserVersion = $m[1];
            }
        }

        // Mobile 判断
        $isMobile = (bool) preg_match(
            '/mobile|android|iphone|ipod|ipad|blackberry|windows phone|opera mini|iemobile/i',
            $ua
        );

        // 设备厂商 & 型号
        [$vendor, $model] = $this->extractDevice($ua);

        $this->parsed = [
            'os'              => $os,
            'os_version'      => $osVersion,
            'browser'         => $browser,
            'browser_version' => $browserVersion,
            'is_mobile'       => $isMobile,
            'vendor'          => $vendor,
            'model'           => $model,
        ];
    }

    /**
     * 提取设备厂商和型号
     *
     * @return array{0: string, 1: string}
     */
    private function extractDevice(string $ua): array
    {
        $vendor = '';
        $model = '';

        // Apple 设备
        if (str_contains($ua, 'iphone')) {
            return ['Apple', 'iPhone'];
        }
        if (str_contains($ua, 'ipad')) {
            return ['Apple', 'iPad'];
        }

        // Android Build 字段：; MODEL Build/
        if (preg_match('/;\s*([^;)]+)\s+build\//i', $ua, $m)) {
            $model = trim($m[1]);
            $vendor = $this->guessVendor($model);
            return [$vendor, $model];
        }

        // 其他常见模式
        if (preg_match('/android[^;]*;\s*([^;)]+)/i', $ua, $m)) {
            $candidate = trim($m[1]);
            if ($candidate && !str_contains($candidate, 'build')) {
                $model = $candidate;
                $vendor = $this->guessVendor($model);
                return [$vendor, $model];
            }
        }

        return [$vendor, $model];
    }

    /**
     * 根据型号字符串猜测厂商
     */
    private function guessVendor(string $model): string
    {
        $lower = strtolower($model);
        foreach (self::VENDOR_MAP as $keyword => $name) {
            if (str_contains($lower, $keyword)) {
                return $name;
            }
        }
        return '';
    }
}
