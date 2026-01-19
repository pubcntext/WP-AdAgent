<?php
/**
 * Encryption Class
 *
 * Handles API key encryption and decryption.
 *
 * @package WP_AdAgent
 * @since   1.0.0
 */

namespace WP_AdAgent\Utils;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Encryption Class
 *
 * @since 1.0.0
 */
class Encryption {

    /**
     * Encryption method.
     *
     * @since 1.0.0
     * @var string
     */
    const METHOD = 'aes-256-cbc';

    /**
     * Get encryption key.
     *
     * @since 1.0.0
     * @return string Encryption key.
     */
    private static function get_key() {
        // Use WordPress auth key as base
        if ( defined( 'AUTH_KEY' ) && AUTH_KEY ) {
            return hash( 'sha256', AUTH_KEY . 'wp_adagent_encryption' );
        }

        // Fallback to a site-specific key
        return hash( 'sha256', get_site_url() . 'wp_adagent_encryption_fallback' );
    }

    /**
     * Encrypt a string.
     *
     * @since 1.0.0
     * @param string $data Data to encrypt.
     * @return string Encrypted data (base64 encoded).
     */
    public static function encrypt( $data ) {
        if ( empty( $data ) ) {
            return '';
        }

        // Check if OpenSSL is available
        if ( ! function_exists( 'openssl_encrypt' ) ) {
            // Fallback to base64 encoding (not secure, but works)
            return base64_encode( $data );
        }

        $key    = self::get_key();
        $iv     = openssl_random_pseudo_bytes( openssl_cipher_iv_length( self::METHOD ) );
        $cipher = openssl_encrypt( $data, self::METHOD, $key, 0, $iv );

        if ( false === $cipher ) {
            return base64_encode( $data );
        }

        // Combine IV and cipher text
        return base64_encode( $iv . '::' . $cipher );
    }

    /**
     * Decrypt a string.
     *
     * @since 1.0.0
     * @param string $data Encrypted data (base64 encoded).
     * @return string Decrypted data.
     */
    public static function decrypt( $data ) {
        if ( empty( $data ) ) {
            return '';
        }

        $decoded = base64_decode( $data );

        if ( false === $decoded ) {
            return $data;
        }

        // Check if it's an OpenSSL encrypted string
        if ( strpos( $decoded, '::' ) === false ) {
            // It's just base64 encoded (fallback or old data)
            return $decoded;
        }

        // Check if OpenSSL is available
        if ( ! function_exists( 'openssl_decrypt' ) ) {
            return $decoded;
        }

        $key   = self::get_key();
        $parts = explode( '::', $decoded, 2 );

        if ( count( $parts ) !== 2 ) {
            return $decoded;
        }

        list( $iv, $cipher ) = $parts;

        $decrypted = openssl_decrypt( $cipher, self::METHOD, $key, 0, $iv );

        if ( false === $decrypted ) {
            return $decoded;
        }

        return $decrypted;
    }

    /**
     * Hash a string (one-way).
     *
     * @since 1.0.0
     * @param string $data Data to hash.
     * @return string Hashed data.
     */
    public static function hash( $data ) {
        return hash( 'sha256', $data . self::get_key() );
    }

    /**
     * Generate a random token.
     *
     * @since 1.0.0
     * @param int $length Token length.
     * @return string Random token.
     */
    public static function generate_token( $length = 32 ) {
        if ( function_exists( 'random_bytes' ) ) {
            return bin2hex( random_bytes( $length / 2 ) );
        }

        if ( function_exists( 'openssl_random_pseudo_bytes' ) ) {
            return bin2hex( openssl_random_pseudo_bytes( $length / 2 ) );
        }

        // Fallback
        return wp_generate_password( $length, false );
    }
}
