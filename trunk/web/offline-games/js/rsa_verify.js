/**
 * 纯JS RSA-PSS签名验证（兼容 file:// 协议）
 * 当 Web Crypto API (crypto.subtle) 不可用时使用此回退方案
 */
var RSAVerify = (function() {

    // ========== SHA-256 ==========
    var SHA256_K = [
        0x428a2f98,0x71374491,0xb5c0fbcf,0xe9b5dba5,0x3956c25b,0x59f111f1,0x923f82a4,0xab1c5ed5,
        0xd807aa98,0x12835b01,0x243185be,0x550c7dc3,0x72be5d74,0x80deb1fe,0x9bdc06a7,0xc19bf174,
        0xe49b69c1,0xefbe4786,0x0fc19dc6,0x240ca1cc,0x2de92c6f,0x4a7484aa,0x5cb0a9dc,0x76f988da,
        0x983e5152,0xa831c66d,0xb00327c8,0xbf597fc7,0xc6e00bf3,0xd5a79147,0x06ca6351,0x14292967,
        0x27b70a85,0x2e1b2138,0x4d2c6dfc,0x53380d13,0x650a7354,0x766a0abb,0x81c2c92e,0x92722c85,
        0xa2bfe8a1,0xa81a664b,0xc24b8b70,0xc76c51a3,0xd192e819,0xd6990624,0xf40e3585,0x106aa070,
        0x19a4c116,0x1e376c08,0x2748774c,0x34b0bcb5,0x391c0cb3,0x4ed8aa4a,0x5b9cca4f,0x682e6ff3,
        0x748f82ee,0x78a5636f,0x84c87814,0x8cc70208,0x90befffa,0xa4506ceb,0xbef9a3f7,0xc67178f2
    ];

    function sha256(msg) {
        var ml = msg.length;
        var bitLen = ml * 8;
        // Padding
        msg = msg.concat([0x80]);
        while (msg.length % 64 !== 56) msg.push(0);
        for (var i = 56; i >= 0; i -= 8) msg.push((bitLen / Math.pow(2, i)) & 0xff);

        var H = [0x6a09e667,0xbb67ae85,0x3c6ef372,0xa54ff53a,0x510e527f,0x9b05688c,0x1f83d9ab,0x5be0cd19];

        for (var chunk = 0; chunk < msg.length; chunk += 64) {
            var W = new Array(64);
            for (var i = 0; i < 16; i++)
                W[i] = (msg[chunk+i*4] << 24) | (msg[chunk+i*4+1] << 16) | (msg[chunk+i*4+2] << 8) | msg[chunk+i*4+3];
            for (var i = 16; i < 64; i++) {
                var s0 = ror(W[i-15], 7) ^ ror(W[i-15], 18) ^ (W[i-15] >>> 3);
                var s1 = ror(W[i-2], 17) ^ ror(W[i-2], 19) ^ (W[i-2] >>> 10);
                W[i] = (W[i-16] + s0 + W[i-7] + s1) | 0;
            }
            var a=H[0],b=H[1],c=H[2],d=H[3],e=H[4],f=H[5],g=H[6],h=H[7];
            for (var i = 0; i < 64; i++) {
                var S1 = ror(e, 6) ^ ror(e, 11) ^ ror(e, 25);
                var ch = (e & f) ^ (~e & g);
                var t1 = (h + S1 + ch + SHA256_K[i] + W[i]) | 0;
                var S0 = ror(a, 2) ^ ror(a, 13) ^ ror(a, 22);
                var maj = (a & b) ^ (a & c) ^ (b & c);
                var t2 = (S0 + maj) | 0;
                h=g; g=f; f=e; e=(d+t1)|0; d=c; c=b; b=a; a=(t1+t2)|0;
            }
            H[0]=(H[0]+a)|0; H[1]=(H[1]+b)|0; H[2]=(H[2]+c)|0; H[3]=(H[3]+d)|0;
            H[4]=(H[4]+e)|0; H[5]=(H[5]+f)|0; H[6]=(H[6]+g)|0; H[7]=(H[7]+h)|0;
        }
        var out = [];
        for (var i = 0; i < 8; i++) {
            out.push((H[i]>>>24)&0xff, (H[i]>>>16)&0xff, (H[i]>>>8)&0xff, H[i]&0xff);
        }
        return out;
    }

    function ror(v, n) { return ((v >>> n) | (v << (32 - n))) | 0; }

    // ========== MGF1 ==========
    function mgf1(seed, maskLen) {
        var hashLen = 32;
        var count = Math.ceil(maskLen / hashLen);
        var mask = [];
        for (var i = 0; i < count; i++) {
            var c = seed.concat([(i>>>24)&0xff, (i>>>16)&0xff, (i>>>8)&0xff, i&0xff]);
            mask = mask.concat(sha256(c));
        }
        return mask.slice(0, maskLen);
    }

    // ========== DER/SPKI Parsing ==========
    function derLength(arr, offset) {
        var len = arr[offset]; offset++;
        if (len < 0x80) return { value: len, offset: offset };
        var numBytes = len & 0x7f;
        var value = 0;
        for (var i = 0; i < numBytes; i++) value = (value << 8) | arr[offset++];
        return { value: value, offset: offset };
    }

    function parsePublicKey(pem) {
        var b64 = pem.replace(/-----BEGIN PUBLIC KEY-----/g, '')
                      .replace(/-----END PUBLIC KEY-----/g, '')
                      .replace(/\s/g, '');
        var der = atob(b64).split('').map(function(c) { return c.charCodeAt(0); });

        // SPKI: SEQUENCE { AlgorithmIdentifier, BIT STRING { RSAPublicKey } }
        var offset = 0;
        offset++; // outer SEQUENCE tag
        var r = derLength(der, offset); offset = r.offset;

        // Skip AlgorithmIdentifier SEQUENCE
        offset++; // AlgorithmIdentifier SEQUENCE tag
        r = derLength(der, offset); offset = r.offset;
        offset += r.value; // skip entire AlgorithmIdentifier content

        // BIT STRING
        offset++; // BIT STRING tag
        r = derLength(der, offset); offset = r.offset;
        offset++; // skip unused bits byte

        // RSAPublicKey: SEQUENCE { INTEGER n, INTEGER e }
        offset++; // SEQUENCE tag
        r = derLength(der, offset); offset = r.offset;

        // n
        offset++; // INTEGER tag
        r = derLength(der, offset); offset = r.offset;
        var nBytes = der.slice(offset, offset + r.value); offset += r.value;

        // e
        offset++; // INTEGER tag
        r = derLength(der, offset); offset = r.offset;
        var eBytes = der.slice(offset, offset + r.value); offset += r.value;

        // Convert to BigInt (skip leading zero byte if present)
        if (nBytes[0] === 0) nBytes = nBytes.slice(1);
        if (eBytes[0] === 0) eBytes = eBytes.slice(1);
        var nHex = nBytes.map(function(b) { return b.toString(16).padStart(2, '0'); }).join('');
        var eHex = eBytes.map(function(b) { return b.toString(16).padStart(2, '0'); }).join('');
        return { n: BigInt('0x' + nHex), e: BigInt('0x' + eHex) };
    }

    // ========== BigInt Modular Exponentiation ==========
    function modPow(base, exp, mod) {
        var result = 1n;
        base = ((base % mod) + mod) % mod;
        while (exp > 0n) {
            if (exp & 1n) result = (result * base) % mod;
            exp >>= 1n;
            base = (base * base) % mod;
        }
        return result;
    }

    // ========== PSS Verify ==========
    function verifyOneSaltLen(DB, H, hLen, pLen, hash, sLen) {
        var padEnd = pLen - sLen - 1;
        if (padEnd < 0) return false;

        // Check padding zeros
        for (var i = 0; i < padEnd; i++) {
            if (DB[i] !== 0) return false;
        }
        // Check separator
        if (DB[padEnd] !== 1) return false;

        // Extract salt
        var salt = DB.slice(padEnd + 1, padEnd + 1 + sLen);

        // Build M' = 8 zero bytes || messageHash || salt
        var mPrime = [0,0,0,0,0,0,0,0].concat(hash).concat(salt);
        var HPrime = sha256(mPrime);

        // Compare H and H'
        for (var i = 0; i < hLen; i++) {
            if (H[i] !== HPrime[i]) return false;
        }
        return true;
    }

    // UTF-8 编码（兼容无 TextEncoder 的环境）
    function utf8Encode(str) {
        var bytes = [];
        for (var i = 0; i < str.length; i++) {
            var c = str.charCodeAt(i);
            if (c < 0x80) {
                bytes.push(c);
            } else if (c < 0x800) {
                bytes.push(0xc0 | (c >> 6), 0x80 | (c & 0x3f));
            } else if (c >= 0xd800 && c <= 0xdbff) {
                // surrogate pair
                var hi = c, lo = str.charCodeAt(++i);
                c = ((hi - 0xd800) << 10) + (lo - 0xdc00) + 0x10000;
                bytes.push(0xf0 | (c >> 18), 0x80 | ((c >> 12) & 0x3f), 0x80 | ((c >> 6) & 0x3f), 0x80 | (c & 0x3f));
            } else {
                bytes.push(0xe0 | (c >> 12), 0x80 | ((c >> 6) & 0x3f), 0x80 | (c & 0x3f));
            }
        }
        return bytes;
    }

    function verify(dataStr, signatureB64, publicKeyPem) {
        try {
            var key = parsePublicKey(publicKeyPem);
            var emLen = Math.ceil(key.n.toString(16).length / 2);
            var hash = sha256(utf8Encode(dataStr));

            // Decode signature
            var sigBytes = atob(signatureB64).split('').map(function(c) { return c.charCodeAt(0); });
            // Convert to BigInt
            var sigHex = sigBytes.map(function(b) { return b.toString(16).padStart(2, '0'); }).join('');
            var s = BigInt('0x' + sigHex);

            // RSA verify: m = s^e mod n
            var m = modPow(s, key.e, key.n);
            // Convert m to byte array (em)
            var mHex = m.toString(16).padStart(emLen * 2, '0');
            var em = [];
            for (var i = 0; i < mHex.length; i += 2)
                em.push(parseInt(mHex.substr(i, 2), 16));

            // PSS verification
            var hLen = 32; // SHA-256
            var pLen = emLen - hLen - 1; // DB length

            // Check trailer
            if (em[emLen - 1] !== 0xbc) return false;

            // Extract maskedDB and H
            var maskedDB = em.slice(0, pLen);
            var H = em.slice(pLen, pLen + hLen);

            // Unmask DB
            var dbMask = mgf1(H, pLen);
            var DB = [];
            for (var i = 0; i < pLen; i++) DB.push(maskedDB[i] ^ dbMask[i]);

            // RFC 8017: emBits = modBits - 1，DB 最高 (emLen*8 - emBits) 位必须为 0
            var nHex = key.n.toString(16);
            var modBits = (nHex.length - 1) * 4 + parseInt(nHex[0], 16).toString(2).length;
            var emBits = modBits - 1;
            var topBits = (emLen * 8) - emBits;
            if (topBits > 0) {
                var mask = (0xff >> topBits) & 0xff;
                DB[0] = DB[0] & mask;
            }

            // Try multiple salt lengths (PSS with variable salt length)
            var maxSaltLen = pLen - 1; // saltLen = pLen when padEnd = 0
            for (var sLen = maxSaltLen; sLen >= 0; sLen--) {
                if (verifyOneSaltLen(DB, H, hLen, pLen, hash, sLen)) return true;
            }
            return false;
        } catch (e) {
            console.error('RSA-PSS verify error:', e);
            return false;
        }
    }

    return { verify: verify };
})();
