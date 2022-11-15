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

<<<<<<< HEAD
use Symfony\Component\Process\Exception\LogicException;
=======
>>>>>>> f8060a2572be4182d51fd7b5a4dfc24f66368b6e
use Symfony\Component\Process\Exception\RuntimeException;

/**
 * PhpProcess runs a PHP script in an independent process.
 *
 *     $p = new PhpProcess('<?php echo "foo"; ?>');
 *     $p->run();
 *     print $p->getOutput()."\n";
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class PhpProcess extends Process
{
    /**
     * @param string      $script  The PHP script to run (as a string)
     * @param string|null $cwd     The working directory or null to use the working dir of the current PHP process
     * @param array|null  $env     The environment variables or null to use the same environment as the current PHP process
     * @param int         $timeout The timeout in seconds
<<<<<<< HEAD
     * @param array|null  $php     Path to the PHP binary to use with any additional arguments
     */
    public function __construct(string $script, string $cwd = null, array $env = null, int $timeout = 60, array $php = null)
    {
        if (null === $php) {
            $executableFinder = new PhpExecutableFinder();
            $php = $executableFinder->find(false);
            $php = false === $php ? null : array_merge([$php], $executableFinder->findArguments());
=======
     * @param array       $options An array of options for proc_open
     */
    public function __construct($script, $cwd = null, array $env = null, $timeout = 60, array $options = null)
    {
        $executableFinder = new PhpExecutableFinder();
        if (false === $php = $executableFinder->find(false)) {
            $php = null;
        } else {
            $php = array_merge([$php], $executableFinder->findArguments());
>>>>>>> f8060a2572be4182d51fd7b5a4dfc24f66368b6e
        }
        if ('phpdbg' === \PHP_SAPI) {
            $file = tempnam(sys_get_temp_dir(), 'dbg');
            file_put_contents($file, $script);
            register_shutdown_function('unlink', $file);
            $php[] = $file;
            $script = null;
        }
<<<<<<< HEAD

        parent::__construct($php, $cwd, $env, $script, $timeout);
    }

    /**
     * {@inheritdoc}
     */
    public static function fromShellCommandline(string $command, string $cwd = null, array $env = null, $input = null, ?float $timeout = 60)
    {
        throw new LogicException(sprintf('The "%s()" method cannot be called when using "%s".', __METHOD__, self::class));
=======
        if (null !== $options) {
            @trigger_error(sprintf('The $options parameter of the %s constructor is deprecated since Symfony 3.3 and will be removed in 4.0.', __CLASS__), \E_USER_DEPRECATED);
        }

        parent::__construct($php, $cwd, $env, $script, $timeout, $options);
    }

    /**
     * Sets the path to the PHP binary to use.
     */
    public function setPhpBinary($php)
    {
        $this->setCommandLine($php);
>>>>>>> f8060a2572be4182d51fd7b5a4dfc24f66368b6e
    }

    /**
     * {@inheritdoc}
     */
<<<<<<< HEAD
    public function start(callable $callback = null, array $env = [])
=======
    public function start(callable $callback = null/*, array $env = []*/)
>>>>>>> f8060a2572be4182d51fd7b5a4dfc24f66368b6e
    {
        if (null === $this->getCommandLine()) {
            throw new RuntimeException('Unable to find the PHP executable.');
        }
<<<<<<< HEAD
=======
        $env = 1 < \func_num_args() ? func_get_arg(1) : null;
>>>>>>> f8060a2572be4182d51fd7b5a4dfc24f66368b6e

        parent::start($callback, $env);
    }
}
