# device-env-detect

稳健的设备环境检测工具，用于区分：

- **电脑（Desktop）**
- **手机浏览器（Mobile）**
- **微信 H5（WeChat）**

提供 **JavaScript** 和 **PHP** 两个版本。

---

## JavaScript 版本

结合 `ua-parser-js` 的 UA 解析 + 微信特有对象（`WeixinJSBridge`、`__wxjs_is_wkwebview`），提升在新版微信中的判断准确率。

### 安装

```bash
npm install device-env-detect
```

或直接复制 `index.js` 使用。

### 使用

```js
import { detectEnv, detectEnvSync, isWeChat } from 'device-env-detect';

// 异步版本（推荐，支持 Client Hints）
const env = await detectEnv();
console.log(env.type); // 'desktop' | 'mobile' | 'wechat'

// 同步版本
const envSync = detectEnvSync();

// 仅判断是否微信
if (isWeChat()) {
  // 微信环境
}
```

### 返回结构

```js
{
  type: 'desktop' | 'mobile' | 'wechat',
  isWechat: boolean,
  isMiniProgram: boolean,
  isMobile: boolean,
  isDesktop: boolean,
  browser: { name, version },
  os: { name, version },
  device: { type, vendor, model },
  ua: string
}
```

---

## PHP 版本

纯 PHP 实现，兼容 **PHP 8.0 ~ 8.5**，无外部依赖。

### 安装

```bash
# 方式 1：Composer
composer require device-env-detect/php

# 方式 2：直接引入
require_once 'php/Detector.php';
```

### 使用

```php
use DeviceEnvDetect\Detector;

$detector = new Detector();
$result = $detector->detect();

echo $result['type']; // desktop / mobile / wechat
```

### 返回结构

```php
[
    'type'           => 'desktop' | 'mobile' | 'wechat',
    'is_wechat'      => bool,
    'is_miniprogram' => bool,
    'is_mobile'      => bool,
    'is_desktop'     => bool,
    'browser'        => ['name' => ..., 'version' => ...],
    'os'             => ['name' => ..., 'version' => ...],
    'device'         => ['type' => ..., 'vendor' => ..., 'model' => ...],
    'ua'             => string,
]
```

### 测试

```bash
php php/test.php
```

---

## 注意事项

- **JS 版**：微信判断优先使用特有对象，比纯 UA 更稳。
- **PHP 版**：只能依赖 User-Agent，已排除企业微信（`wxwork`）。
- 设备具体型号（`device.model`）在部分安卓机型上可能不完整，但已覆盖常见厂商（Samsung、Huawei、Xiaomi、OPPO、vivo 等）。
- 企业微信会被排除在普通微信之外。
