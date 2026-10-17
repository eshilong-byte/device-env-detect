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
     * 内部 UA 解析（简化版，覆盖主流场景）
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
        }

        // Mobile 判断
        $isMobile = (bool) preg_match(
            '/mobile|android|iphone|ipod|ipad|blackberry|windows phone|opera mini|iemobile/i',
            $ua
        );

        // 简单设备信息（可选增强）
        $vendor = '';
        $model = '';
        if (str_contains($ua, 'iphone')) {
            $vendor = 'Apple';
            $model = 'iPhone';
        } elseif (str_contains($ua, 'ipad')) {
            $vendor = 'Apple';
            $model = 'iPad';
        } elseif (preg_match('/;\s*([^;)]+)\s+build\//i', $ua, $m)) {
            $model = trim($m[1]);
            // 简单厂商猜测
            if (str_contains($model, 'SM-') || str_contains($model, 'Samsung')) {
                $vendor = 'Samsung';
            } elseif (str_contains($model, 'HUAWEI') || str_contains($model, 'Honor')) {
                $vendor = 'Huawei';
            } elseif (str_contains($model, 'MI ') || str_contains($model, 'Redmi') || str_contains($model, 'Xiaomi')) {
                $vendor = 'Xiaomi';
            }
        }

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
}
