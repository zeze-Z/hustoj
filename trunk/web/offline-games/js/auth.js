/**
 * 离线游戏授权验证模块
 * 功能：RSA-2048签名验证、license管理
 */

var Auth = (function() {
    // RSA公钥（PEM格式，用于验证license签名）
    // 私钥由管理员持有，用于生成license
    var PUBLIC_KEY_PEM = `-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAoRrAiHFHGUgWeF7IcmUB
65imIHrAKOfqUPJYWvSPOGBN66wt0azQeGgxXOyu6cihZ23/sB/IKe+OIDg7hDaI
BPpMpal3grABqSbQYhatNjv+VT2Gw1nlZ09k7SsIURy4ZB2KNQMXboSUTjZEZx49
H/97QGLbtnt3TzXhdIQ8A5IaV3yu3Lsx4BgQfQXpW4LlUR/igqGv3DyUCfe1wvBV
VcR55numKmU9OO3tKVV8N9J+ciKUHoxUyvQPeJoikhp83c4iqrL2yT+aEOZDmGJY
q0wG1JQVL1MhLhUkr7+nx7YJ1zP6FqTkIZXidd2IFknCKGsaFFCBBnZ6LZSzbKkf
pQIDAQAB
-----END PUBLIC KEY-----`;

    /**
     * 将PEM格式公钥转换为CryptoKey对象
     */
    function importPublicKey() {
        var pemContents = PUBLIC_KEY_PEM
            .replace('-----BEGIN PUBLIC KEY-----', '')
            .replace('-----END PUBLIC KEY-----', '')
            .replace(/\s/g, '');

        var binaryString = atob(pemContents);
        var bytes = new Uint8Array(binaryString.length);
        for (var i = 0; i < binaryString.length; i++) {
            bytes[i] = binaryString.charCodeAt(i);
        }

        return crypto.subtle.importKey(
            'spki',
            bytes.buffer,
            {
                name: 'RSA-PSS',
                hash: 'SHA-256'
            },
            false,
            ['verify']
        );
    }

    /**
     * 将Base64字符串转换为Uint8Array
     */
    function base64ToUint8Array(base64) {
        var binaryString = atob(base64);
        var bytes = new Uint8Array(binaryString.length);
        for (var i = 0; i < binaryString.length; i++) {
            bytes[i] = binaryString.charCodeAt(i);
        }
        return bytes;
    }

    /**
     * 使用RSA-PSS验证签名
     * 优先使用 Web Crypto API（需要安全上下文），
     * 不可用时回退到纯 JS 实现（兼容 file:// 协议）
     */
    async function verifySignature(data, signatureBase64) {
        // 检查 Web Crypto API 是否可用（file:// 协议下不可用）
        if (typeof crypto !== 'undefined' && crypto.subtle) {
            try {
                var publicKey = await importPublicKey();
                var dataBytes = new TextEncoder().encode(data);
                var signatureBytes = base64ToUint8Array(signatureBase64);

                var result = await crypto.subtle.verify(
                    'RSA-PSS',
                    publicKey,
                    {
                        name: 'RSA-PSS',
                        saltLength: 32
                    },
                    signatureBytes,
                    dataBytes
                );

                return result;
            } catch (e) {
                console.error('Web Crypto API 签名验证失败:', e);
                // 继续尝试纯 JS 回退
            }
        }

        // 纯 JS 回退方案（兼容 file:// 协议）
        if (typeof RSAVerify !== 'undefined') {
            try {
                return RSAVerify.verify(data, signatureBase64, PUBLIC_KEY_PEM);
            } catch (e) {
                console.error('纯JS签名验证失败:', e);
                return false;
            }
        }

        console.error('无可用的签名验证方案');
        return false;
    }

    // localStorage 键名：activate.html 激活成功后写入，checkAuth 优先读取
    // （Chrome/Edge 在 file:// 下禁用 fetch/XHR，localStorage 是主路径）
    var LICENSE_STORAGE_KEY = 'og_license';

    /**
     * 文件兜底的候选路径（覆盖根目录页 index.html/activate.html 与 games/ 子页两种深度），去重
     */
    function licenseFileCandidates() {
        var paths = ['license.dat', '../license.dat'];
        var seen = {};
        var result = [];
        for (var i = 0; i < paths.length; i++) {
            if (!seen[paths[i]]) {
                seen[paths[i]] = true;
                result.push(paths[i]);
            }
        }
        return result;
    }

    /**
     * 从 localStorage 读取授权数据（主路径）
     * @returns {Object|null}
     */
    function loadLicenseFromStorage() {
        try {
            var raw = localStorage.getItem(LICENSE_STORAGE_KEY);
            if (!raw) return null;
            var license = JSON.parse(raw);
            return license || null;
        } catch (e) {
            console.warn('localStorage 授权读取失败:', e);
            return null;
        }
    }

    /**
     * 将授权数据写入 localStorage（activate.html 激活成功、或文件兜底验证通过后调用）
     * @returns {boolean} 是否写入成功（隐私模式等场景可能失败）
     */
    function persistLicense(license) {
        try {
            localStorage.setItem(LICENSE_STORAGE_KEY, JSON.stringify(license));
            return true;
        } catch (e) {
            console.warn('localStorage 写入失败（隐私模式或被禁用），请改用手动保存 license.dat:', e);
            return false;
        }
    }

    /**
     * 按候选路径依次尝试读取 license.dat 文件（兜底，Firefox 等允许 file:// 读取的场景）
     * 每条路径先 fetch，失败再 XHR；全部失败时 reject
     */
    function loadLicenseFromFile() {
        var paths = licenseFileCandidates();
        return paths.reduce(function(prev, path) {
            return prev.catch(function() {
                return loadLicensePathWithFetch(path).catch(function() {
                    return loadLicenseWithXHR(path);
                });
            });
        }, Promise.reject(new Error('no license file candidate')));
    }

    /**
     * 使用 fetch 读取指定路径的 license.dat
     */
    function loadLicensePathWithFetch(path) {
        return fetch(path).then(function(response) {
            if (!response.ok) {
                throw new Error('License file not found: ' + path);
            }
            return response.json();
        });
    }

    /**
     * 使用 XMLHttpRequest 加载 license.dat（兼容 file:// 协议）
     */
    function loadLicenseWithXHR(path) {
        return new Promise(function(resolve, reject) {
            try {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', path, true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200 || xhr.status === 0) {
                            try {
                                var license = JSON.parse(xhr.responseText);
                                resolve(license);
                            } catch (e) {
                                reject(new Error('Invalid JSON'));
                            }
                        } else {
                            reject(new Error('File not found'));
                        }
                    }
                };
                xhr.send();
            } catch (e) {
                reject(e);
            }
        });
    }

    /**
     * 验证license文件
     * @param {Object} license - license数据对象
     * @returns {Promise<Object>} 验证结果
     */
    async function validateLicense(license) {
        // 1. 检查必要字段
        if (!license || !license.school || !license.room || !license.expire || !license.signature) {
            return { valid: false, message: '授权文件格式错误' };
        }

        // 2. 验证有效期
        var expireDate = new Date(license.expire);
        var now = new Date();
        if (isNaN(expireDate.getTime())) {
            return { valid: false, message: '授权有效期格式错误' };
        }
        if (expireDate < now) {
            return { valid: false, message: '授权已过期，请联系管理员续费' };
        }

        // 3. 验证RSA签名
        var signData = license.school + '|' + license.room + '|' + license.expire;
        var signatureValid = await verifySignature(signData, license.signature);

        if (!signatureValid) {
            return { valid: false, message: '授权文件签名无效，授权码可能被篡改' };
        }

        return { valid: true, message: '授权验证通过' };
    }

    /**
     * 检查授权状态
     * 读取顺序：① localStorage（已激活机器的主路径，Chrome/Edge file:// 下 fetch/XHR 全被禁）
     *          ② window.OG_LICENSE_DATA（license.js 随包授权，页面经 <script src> 注入，学生机双击 index.html 零操作进入）
     *          ③ license.dat 文件兜底（Firefox 等允许 file:// 读取的场景，多级相对路径）
     * @returns {Promise} 验证结果
     */
    async function checkAuth() {
        var message = '未找到授权，请先激活';

        // 1. 主路径：localStorage
        var stored = loadLicenseFromStorage();
        if (stored) {
            var storedResult = await validateLicense(stored);
            if (storedResult.valid) {
                return { valid: true, message: storedResult.message, license: stored };
            }
            // localStorage 命中但无效（过期/签名错），继续尝试后续通道，并把无效原因带给调用方
            message = storedResult.message;
        }

        // 2. 随包授权：license.js 经 <script src> 注入的 window.OG_LICENSE_DATA
        //    （<script> 加载本地文件不受 file:// 限制，是学生机零操作进入的关键通道）
        //    验证通过后刻意不写 localStorage：包内文件每次加载重新验签，续期换包后新
        //    license.js 直接生效，不会被本机旧 localStorage 遮蔽（localStorage 过期 → 落到这里 → 放行）
        if (typeof window.OG_LICENSE_DATA !== 'undefined' && window.OG_LICENSE_DATA) {
            var packedResult = await validateLicense(window.OG_LICENSE_DATA);
            if (packedResult.valid) {
                return { valid: true, message: packedResult.message, license: window.OG_LICENSE_DATA };
            }
            // license.js 无效（被篡改/过期），携带原因继续走 license.dat 兜底
            message = packedResult.message;
        }

        // 3. 兜底：license.dat 文件（多级相对路径，适配根目录页与 games/ 子页）
        try {
            var license = await loadLicenseFromFile();
            var fileResult = await validateLicense(license);
            if (fileResult.valid) {
                // 文件验证通过后同步写入 localStorage，后续页面加载直接命中主路径
                persistLicense(license);
                return { valid: true, message: fileResult.message, license: license };
            }
            message = fileResult.message;
        } catch (e) {
            // 所有路径读取失败（如 Chrome file:// 禁止 fetch/XHR），走未授权提示
        }

        return { valid: false, message: message, license: null };
    }

    /**
     * 显示授权信息（公示）
     */
    function displayAuthInfo(license) {
        if (!license) return;

        var infoHtml = '© ' + license.school + ' - ' + license.room +
                       ' | 授权至 ' + license.expire;

        // 创建或更新公示元素
        var $footer = $('#auth-footer');
        if ($footer.length === 0) {
            $footer = $('<div id="auth-footer"></div>').css({
                'position': 'fixed',
                'bottom': '0',
                'left': '0',
                'right': '0',
                'background': 'rgba(0,0,0,0.8)',
                'color': 'white',
                'text-align': 'center',
                'padding': '8px',
                'font-size': '12px',
                'z-index': '9999'
            });
            $('body').append($footer);
        }
        $footer.text(infoHtml);
    }

    // 公开API
    return {
        validateLicense: validateLicense,
        checkAuth: checkAuth,
        displayAuthInfo: displayAuthInfo,
        persistLicense: persistLicense
    };
})();

// 页面加载时自动检查授权
$(document).ready(function() {
    // 仅在index.html页面检查授权
    if (window.location.pathname.endsWith('index.html') ||
        window.location.pathname.endsWith('/')) {
        Auth.checkAuth().then(function(result) {
            if (!result.valid) {
                // 未授权，跳转到激活页面（activate.html 位于包内 games/ 目录）
                window.location.href = 'games/activate.html';
            } else {
                // 显示授权信息（公示）
                if (result.license) {
                    Auth.displayAuthInfo(result.license);
                }
            }
        });
    }
});
