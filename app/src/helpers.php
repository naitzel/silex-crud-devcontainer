<?php

/**
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>.
 */

/**
 * Helpers.
 */
if (!function_exists('base_path')) {
    /**
     * @param string $path
     *
     * @return string
     */
    function base_path($path = '')
    {
        return realpath(__DIR__.DIRECTORY_SEPARATOR.'..').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}

if (!function_exists('web_path')) {
    /**
     * @param string $path
     *
     * @return string
     */
    function web_path($path = '')
    {
        return base_path('public').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}

if (!function_exists('view_path')) {
    /**
     * @param string $path
     *
     * @return string
     */
    function view_path($path = '')
    {
        return base_path('views').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}

if (!function_exists('var_path')) {
    /**
     * @param string $path
     *
     * @return string
     */
    function var_path($path = '')
    {
        return base_path('var').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}

if (!function_exists('log_path')) {
    /**
     * @param string $path
     *
     * @return string
     */
    function log_path($path = '')
    {
        return var_path('logs').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}

if (!function_exists('cache_path')) {
    /**
     * @param string $path
     *
     * @return string
     */
    function cache_path($path = '')
    {
        return var_path('cache').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}
