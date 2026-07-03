<?php
// ---------------------------------------------------------------------
// Time-based One-Time Password (TOTP) helpers for admin two-factor auth.
// RFC 6238 / RFC 4226 compatible with Google Authenticator, Authy, etc.
// Self-contained: no external libraries or network calls required.
// ---------------------------------------------------------------------

function mfa_base32_alphabet() {
    return 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
}

// Generate a random base32 secret (default 16 chars = 80 bits of entropy).
function mfa_generate_secret($length = 16) {
    $alphabet = mfa_base32_alphabet();
    $secret = '';
    for ($i = 0; $i < $length; $i++) {
        $secret .= $alphabet[random_int(0, 31)];
    }
    return $secret;
}

// Decode a base32 secret into its raw binary key.
function mfa_base32_decode($secret) {
    $alphabet = mfa_base32_alphabet();
    $secret = strtoupper(preg_replace('/[^A-Z2-7]/', '', (string) $secret));
    if ($secret === '') {
        return '';
    }

    $bits = '';
    $length = strlen($secret);
    for ($i = 0; $i < $length; $i++) {
        $value = strpos($alphabet, $secret[$i]);
        if ($value === false) {
            continue;
        }
        $bits .= str_pad(decbin($value), 5, '0', STR_PAD_LEFT);
    }

    $bytes = '';
    $bitLength = strlen($bits);
    for ($i = 0; $i + 8 <= $bitLength; $i += 8) {
        $bytes .= chr(bindec(substr($bits, $i, 8)));
    }
    return $bytes;
}

// HMAC-based One-Time Password for a given counter (RFC 4226).
function mfa_hotp($key_binary, $counter) {
    if ($key_binary === '') {
        return '';
    }

    // 8-byte big-endian counter.
    $binCounter = pack('N', ($counter >> 32) & 0xffffffff) . pack('N', $counter & 0xffffffff);
    $hash = hash_hmac('sha1', $binCounter, $key_binary, true);
    $offset = ord($hash[strlen($hash) - 1]) & 0x0f;
    $truncated = ((ord($hash[$offset]) & 0x7f) << 24)
        | ((ord($hash[$offset + 1]) & 0xff) << 16)
        | ((ord($hash[$offset + 2]) & 0xff) << 8)
        | (ord($hash[$offset + 3]) & 0xff);

    return str_pad((string) ($truncated % 1000000), 6, '0', STR_PAD_LEFT);
}

// Current TOTP code for a base32 secret.
function mfa_totp($secret, $time = null, $step = 30) {
    $time = $time ?? time();
    $counter = intdiv($time, $step);
    return mfa_hotp(mfa_base32_decode($secret), $counter);
}

// Verify a submitted code, allowing a +/- window for clock drift.
function mfa_verify($secret, $code, $window = 1, $step = 30) {
    $code = preg_replace('/\D/', '', (string) $code);
    if (strlen($code) !== 6) {
        return false;
    }

    $key = mfa_base32_decode($secret);
    if ($key === '') {
        return false;
    }

    $counter = intdiv(time(), $step);
    for ($i = -$window; $i <= $window; $i++) {
        $candidate = mfa_hotp($key, $counter + $i);
        if ($candidate !== '' && hash_equals($candidate, $code)) {
            return true;
        }
    }
    return false;
}

// otpauth:// URI used by authenticator apps (and QR generators).
function mfa_otpauth_uri($secret, $label, $issuer = 'AI-Solutions') {
    return 'otpauth://totp/' . rawurlencode($issuer . ':' . $label)
        . '?secret=' . $secret
        . '&issuer=' . rawurlencode($issuer)
        . '&algorithm=SHA1&digits=6&period=30';
}

// Format a secret in readable 4-char groups for manual entry.
function mfa_format_secret($secret) {
    return trim(chunk_split((string) $secret, 4, ' '));
}
