<?php declare(strict_types=1);

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

date_default_timezone_set('UTC');

spl_autoload_register(function ($class) {
    if (strpos($class, 'Monolog\\') === 0) {
        $classPath = strtr($class, '\\', DIRECTORY_SEPARATOR) . '.php';
        $path = __DIR__ . '/../src/' . $classPath;
        if (file_exists($path)) {
            require $path;
        } else {
            $path = __DIR__ . '/' . $classPath;
            if (file_exists($path)) {
                require $path;
            }
        }
    } else if (strpos($class, 'Psr\\') === 0) {
        require strtr($class, '\\', DIRECTORY_SEPARATOR) . '.php';
    }
});

// B.C. for PSR Log's old inheritance
// see https://github.com/php-fig/log/pull/52
if (!class_exists('\\PHPUnit_Framework_TestCase', true)) {
    class_alias('\\PHPUnit\\Framework\\TestCase', '\\PHPUnit_Framework_TestCase');
}
