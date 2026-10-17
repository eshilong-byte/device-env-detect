/**
 * 设备环境检测工具
 * 区分：电脑 / 手机浏览器 / 微信 H5
 * 结合 UA 解析 + 微信特有对象，提升准确率
 */

import { UAParser } from 'ua-parser-js';

/**
 * 增强版微信环境检测
 * 优先 UA，再检查微信特有全局对象，兼容新旧版本
 */
export function isWeChat() {
  if (typeof window === 'undefined') return false;

  const ua = navigator.userAgent.toLowerCase();

  // 排除企业微信
  if (/wxwork/.test(ua)) return false;

  // 传统 UA 判断
  if (/micromessenger/.test(ua)) return true;

  // 微信特有对象（更可靠）
  if (typeof window.WeixinJSBridge !== 'undefined') return true;
  if (typeof window.__wxjs_is_wkwebview !== 'undefined') return true;

  // 小程序 web-view
  if (window.__wxjs_environment === 'miniprogram') return true;

  return false;
}

/**
 * 是否微信小程序环境
 */
export function isMiniProgram() {
  if (typeof window === 'undefined') return false;
  return window.__wxjs_environment === 'miniprogram';
}

/**
 * 主检测函数
 * @returns {Promise<object>} 环境信息
 */
export async function detectEnv() {
  const parser = new UAParser();
  let result = parser.getResult();

  // 尝试使用 Client Hints 获取更准确信息（现代浏览器）
  try {
    if (typeof parser.withClientHints === 'function') {
      result = await parser.withClientHints();
    }
  } catch (e) {
    // 忽略 Client Hints 失败，回退到普通 UA
  }

  const isWechat = isWeChat();
  const isMini = isMiniProgram();

  const deviceType = result.device.type; // mobile | tablet | undefined
  const isMobile = deviceType === 'mobile' || deviceType === 'tablet';
  const isDesktop = !isMobile && !isWechat;

  let type = 'desktop';
  if (isWechat) {
    type = 'wechat';
  } else if (isMobile) {
    type = 'mobile';
  }

  return {
    type,                    // 'desktop' | 'mobile' | 'wechat'
    isWechat,
    isMiniProgram: isMini,
    isMobile,
    isDesktop,
    browser: {
      name: result.browser.name || 'Unknown',
      version: result.browser.version || '',
    },
    os: {
      name: result.os.name || 'Unknown',
      version: result.os.version || '',
    },
    device: {
      type: deviceType || 'desktop',
      vendor: result.device.vendor || '',
      model: result.device.model || '',
    },
    ua: navigator.userAgent,
  };
}

/**
 * 同步版本（不使用 Client Hints，更快）
 */
export function detectEnvSync() {
  const parser = new UAParser();
  const result = parser.getResult();

  const isWechat = isWeChat();
  const isMini = isMiniProgram();
  const deviceType = result.device.type;
  const isMobile = deviceType === 'mobile' || deviceType === 'tablet';
  const isDesktop = !isMobile && !isWechat;

  let type = 'desktop';
  if (isWechat) type = 'wechat';
  else if (isMobile) type = 'mobile';

  return {
    type,
    isWechat,
    isMiniProgram: isMini,
    isMobile,
    isDesktop,
    browser: {
      name: result.browser.name || 'Unknown',
      version: result.browser.version || '',
    },
    os: {
      name: result.os.name || 'Unknown',
      version: result.os.version || '',
    },
    device: {
      type: deviceType || 'desktop',
      vendor: result.device.vendor || '',
      model: result.device.model || '',
    },
    ua: typeof navigator !== 'undefined' ? navigator.userAgent : '',
  };
}

// 默认导出同步版本，方便直接使用
export default detectEnvSync;
