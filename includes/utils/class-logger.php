<?php
/**
 * Logger Class
 *
 * Handles debug logging for the plugin.
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
 * Logger Class
 *
 * @since 1.0.0
 */
class Logger {

    /**
     * Log levels.
     *
     * @since 1.0.0
     * @var array
     */
    const LEVELS = array(
        'debug'   => 0,
        'info'    => 1,
        'warning' => 2,
        'error'   => 3,
    );

    /**
     * Minimum log level.
     *
     * @since 1.0.0
     * @var string
     */
    private static $min_level = 'info';

    /**
     * Log a debug message.
     *
     * @since 1.0.0
     * @param string $message Log message.
     * @param array  $context Additional context.
     */
    public static function debug( $message, $context = array() ) {
        self::log( 'debug', $message, $context );
    }

    /**
     * Log an info message.
     *
     * @since 1.0.0
     * @param string $message Log message.
     * @param array  $context Additional context.
     */
    public static function info( $message, $context = array() ) {
        self::log( 'info', $message, $context );
    }

    /**
     * Log a warning message.
     *
     * @since 1.0.0
     * @param string $message Log message.
     * @param array  $context Additional context.
     */
    public static function warning( $message, $context = array() ) {
        self::log( 'warning', $message, $context );
    }

    /**
     * Log an error message.
     *
     * @since 1.0.0
     * @param string $message Log message.
     * @param array  $context Additional context.
     */
    public static function error( $message, $context = array() ) {
        self::log( 'error', $message, $context );
    }

    /**
     * Log an API call.
     *
     * @since 1.0.0
     * @param string $endpoint    API endpoint.
     * @param string $method      HTTP method.
     * @param int    $status_code Response status code.
     */
    public static function api_call( $endpoint, $method, $status_code ) {
        self::info(
            sprintf( 'API %s %s returned %d', $method, $endpoint, $status_code ),
            array(
                'type'        => 'api_call',
                'endpoint'    => $endpoint,
                'method'      => $method,
                'status_code' => $status_code,
            )
        );
    }

    /**
     * Log a message.
     *
     * @since 1.0.0
     * @param string $level   Log level.
     * @param string $message Log message.
     * @param array  $context Additional context.
     */
    private static function log( $level, $message, $context = array() ) {
        // Check if logging is enabled
        if ( ! WP_ADAGENT_DEBUG ) {
            return;
        }

        // Check log level
        if ( self::LEVELS[ $level ] < self::LEVELS[ self::$min_level ] ) {
            return;
        }

        // Sanitize message - never log sensitive data
        $message = self::sanitize_message( $message );

        // Build log entry
        $log_entry = sprintf(
            '[WP-AdAgent] [%s] %s',
            strtoupper( $level ),
            $message
        );

        // Add context if not empty
        if ( ! empty( $context ) ) {
            $log_entry .= ' | Context: ' . wp_json_encode( self::sanitize_context( $context ) );
        }

        // Write to WordPress debug log
        if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
            error_log( $log_entry );
        }

        /**
         * Fires after a log entry is written.
         *
         * @since 1.0.0
         * @param string $level   Log level.
         * @param string $message Log message.
         * @param array  $context Log context.
         */
        do_action( 'wp_adagent_log', $level, $message, $context );
    }

    /**
     * Sanitize log message to remove sensitive data.
     *
     * @since 1.0.0
     * @param string $message The message to sanitize.
     * @return string Sanitized message.
     */
    private static function sanitize_message( $message ) {
        // Patterns for sensitive data
        $patterns = array(
            '/api[_-]?key["\s:=]+["\']?([a-zA-Z0-9_-]+)["\']?/i' => 'api_key: [REDACTED]',
            '/bearer\s+([a-zA-Z0-9_.-]+)/i'                      => 'Bearer [REDACTED]',
            '/authorization["\s:=]+["\']?([^"\']+)["\']?/i'      => 'authorization: [REDACTED]',
            '/password["\s:=]+["\']?([^"\']+)["\']?/i'           => 'password: [REDACTED]',
        );

        foreach ( $patterns as $pattern => $replacement ) {
            $message = preg_replace( $pattern, $replacement, $message );
        }

        return $message;
    }

    /**
     * Sanitize context array to remove sensitive data.
     *
     * @since 1.0.0
     * @param array $context The context array.
     * @return array Sanitized context.
     */
    private static function sanitize_context( $context ) {
        $sensitive_keys = array(
            'api_key',
            'apiKey',
            'api-key',
            'password',
            'secret',
            'token',
            'authorization',
            'auth',
        );

        foreach ( $context as $key => $value ) {
            if ( in_array( strtolower( $key ), $sensitive_keys, true ) ) {
                $context[ $key ] = '[REDACTED]';
            } elseif ( is_array( $value ) ) {
                $context[ $key ] = self::sanitize_context( $value );
            }
        }

        return $context;
    }

    /**
     * Set minimum log level.
     *
     * @since 1.0.0
     * @param string $level Minimum log level.
     */
    public static function set_min_level( $level ) {
        if ( isset( self::LEVELS[ $level ] ) ) {
            self::$min_level = $level;
        }
    }

    /**
     * Get logs from debug.log file.
     *
     * @since 1.0.0
     * @param int $lines Number of lines to retrieve.
     * @return array Log lines.
     */
    public static function get_recent_logs( $lines = 100 ) {
        $log_file = WP_CONTENT_DIR . '/debug.log';

        if ( ! file_exists( $log_file ) || ! is_readable( $log_file ) ) {
            return array();
        }

        $logs = array();
        $file = new \SplFileObject( $log_file, 'r' );
        $file->seek( PHP_INT_MAX );
        $total_lines = $file->key();

        $start = max( 0, $total_lines - $lines );
        $file->seek( $start );

        while ( ! $file->eof() ) {
            $line = $file->fgets();
            if ( strpos( $line, '[WP-AdAgent]' ) !== false ) {
                $logs[] = trim( $line );
            }
        }

        return array_slice( $logs, -$lines );
    }
}
