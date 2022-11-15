<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Process;

/**
 * An executable finder specifically designed for the PHP executable.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class PhpExecutableFinder
{
    private $executableFinder;

    public function __construct()
    {
        $this->executableFinder = new ExecutableFinder();
    }

    /**
     * Finds The PHP executable.
     *
<<<<<<< HEAD
     * @return string|false
     */
    public function find(bool $includeArgs = true)
    {
        if ($php = getenv('PHP_BINARY')) {
            if (!is_executable($php)) {
                $command = '\\' === \DIRECTORY_SEPARATOR ? 'where' : 'command -v';
                if ($php = strtok(exec($command.' '.escapeshellarg($php)), \PHP_EOL)) {
                    if (!is_executable($php)) {
                        return false;
                    }
                } else {
                    return false;
                }
            }

            if (@is_dir($php)) {
                return false;
            }

            return $php;
        }

        $args = $this->findArguments();
        $args = $includeArgs && $args ? ' '.implode(' ', $args) : '';

        // PHP_BINARY return the current sapi executable
        if (\PHP_BINARY && \in_array(\PHP_SAPI, ['cgi-fcgi', 'cli', 'cli-server', 'phpdbg'], true)) {
=======
     * @param bool $includeArgs Whether or not include command arguments
     *
     * @return string|false The PHP executable path or false if it cannot be found
     */
    public function find($includeArgs = true)
    {
        $args = $this->findArguments();
        $args = $includeArgs && $args ? ' '.implode(' ', $args) : '';

        // HHVM support
        if (\defined('HHVM_VERSION')) {
            return (getenv('PHP_BINARY') ?: \PHP_BINARY).$args;
        }

        // PHP_BINARY return the current sapi executable
        if (\PHP_BINARY && \in_array(\PHP_SAPI, ['cli', 'cli-server', 'phpdbg'], true)) {
>>>>>>> f8060a2572be4182d51fd7b5a4dfc24f66368b6e
            return \PHP_BINARY.$args;
        }

        if ($php = getenv('PHP_PATH')) {
<<<<<<< HEAD
            if (!@is_executable($php) || @is_dir($php)) {
=======
            if (!@is_executable($php)) {
>>>>>>> f8060a2572be4182d51fd7b5a4dfc24f66368b6e
                return false;
            }

            return $php;
        }

        if ($php = getenv('PHP_PEAR_PHP_BIN')) {
<<<<<<< HEAD
            if (@is_executable($php) && !@is_dir($php)) {
=======
            if (@is_executable($php)) {
>>>>>>> f8060a2572be4182d51fd7b5a4dfc24f66368b6e
                return $php;
            }
        }

<<<<<<< HEAD
        if (@is_executable($php = \PHP_BINDIR.('\\' === \DIRECTORY_SEPARATOR ? '\\php.exe' : '/php')) && !@is_dir($php)) {
=======
        if (@is_executable($php = \PHP_BINDIR.('\\' === \DIRECTORY_SEPARATOR ? '\\php.exe' : '/php'))) {
>>>>>>> f8060a2572be4182d51fd7b5a4dfc24f66368b6e
            return $php;
        }

        $dirs = [\PHP_BINDIR];
        if ('\\' === \DIRECTORY_SEPARATOR) {
            $dirs[] = 'C:\xampp\php\\';
        }

        return $this->executableFinder->find('php', false, $dirs);
    }

    /**
     * Finds the PHP executable arguments.
     *
<<<<<<< HEAD
     * @return array
=======
     * @return array The PHP executable arguments
>>>>>>> f8060a2572be4182d51fd7b5a4dfc24f66368b6e
     */
    public function findArguments()
    {
        $arguments = [];
<<<<<<< HEAD
        if ('phpdbg' === \PHP_SAPI) {
=======

        if (\defined('HHVM_VERSION')) {
            $arguments[] = '--php';
        } elseif ('phpdbg' === \PHP_SAPI) {
>>>>>>> f8060a2572be4182d51fd7b5a4dfc24f66368b6e
            $arguments[] = '-qrr';
        }

        return $arguments;
    }
}
