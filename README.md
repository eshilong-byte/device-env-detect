# device-env-detect

稳健的设备环境检测工具，用于区分：

- **电脑（Desktop）**
- **手机浏览器（Mobile）**
- **微信 H5（WeChat）**

结合 `ua-parser-js` 的 UA 解析 + 微信特有对象（`WeixinJSBridge`、`__wxjs_is_wkwebview`），提升在新版微信中的判断准确率。

## 安装

```bash
npm install device-env-detect
```

或直接复制 `index.js` 使用。

## 使用

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

## 返回结构

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

## 注意事项

- 微信判断优先使用特有对象，比纯 UA 更稳。
- 设备具体型号（`device.model`）在部分安卓机型上可能不完整。
- 企业微信会被排除在普通微信之外。
