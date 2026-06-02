/**
 * Device Fingerprint Utility
 *
 * Generates a consistent SHA-256 fingerprint from stable browser properties.
 * Used to identify trusted devices for MFA bypass on recognized browsers.
 *
 * This is NOT a tracking fingerprint — it's a lightweight hash of
 * browser + OS + screen characteristics that changes when the user
 * switches devices or browsers.
 */

/**
 * Collect stable browser properties and hash them into a fingerprint.
 * @returns {Promise<string>} A hex-encoded SHA-256 fingerprint string.
 */
export async function generateDeviceFingerprint() {
    const components = [
        navigator.userAgent || '',
        navigator.language || '',
        `${screen.width}x${screen.height}`,
        String(screen.colorDepth || ''),
        Intl.DateTimeFormat().resolvedOptions().timeZone || '',
        String(navigator.hardwareConcurrency || ''),
        navigator.platform || '',
    ];

    const raw = components.join('|||');

    // Use the native Web Crypto API for SHA-256 hashing (no external dependencies)
    const encoder = new TextEncoder();
    const data = encoder.encode(raw);
    const hashBuffer = await crypto.subtle.digest('SHA-256', data);

    // Convert ArrayBuffer to hex string
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');

    return hashHex;
}
