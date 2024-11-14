<?php

namespace App\Helpers;

class EnvConfig {
    /**
     * Get an environment variable from either $_ENV or getenv()
     * 
     * @param string $key The environment variable name
     * @param mixed $default Optional default value if not found
     * @return mixed The environment variable value or default
     */
    public static function get(string $key, $default = null) {
        return $_ENV[$key] ?? getenv($key) ?? $default;
    }

    /**
     * Get multiple environment variables at once
     * 
     * @param array $keys Array of keys to fetch
     * @param array $defaults Optional array of default values keyed by variable name
     * @return array Array of environment variables with their values
     */
    public static function getMultiple(array $keys, array $defaults = []) {
        $values = [];
        foreach ($keys as $key) {
            $values[$key] = self::get($key, $defaults[$key] ?? null);
        }
        return $values;
    }

    /**
     * Check if an environment variable exists
     * 
     * @param string $key The environment variable name
     * @return bool
     */
    public static function has(string $key): bool {
        return isset($_ENV[$key]) || getenv($key) !== false;
    }

    /**
     * Get environment variable as boolean
     * 
     * @param string $key The environment variable name
     * @param bool $default Optional default value
     * @return bool
     */
    public static function getBool(string $key, bool $default = false): bool {
        $value = self::get($key);
        if ($value === null) {
            return $default;
        }
        return in_array(strtolower($value), ['true', '1', 'yes', 'on'], true);
    }
}
