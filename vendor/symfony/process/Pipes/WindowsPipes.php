<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Process\Pipes;

use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

/**
 * WindowsPipes implementation uses temporary files as handles.
 *
 * @see https://bugs.php.net/51800
 * @see https://bugs.php.net/65650
 *
 * @author Romain Neutron <imprec@gmail.com>
 *
 * @internal
 */
class WindowsPipes extends AbstractPipes
{
    private $files = [];
    private $fileHandles = [];
    private $lockHandles = [];
    private $readBytes = [
        Process::STDOUT => 0,
        Process::STDERR => 0,
    ];
    private $haveReadSupport;

<<<<<<< HEAD
    public function __construct($input, bool $haveReadSupport)
    {
        $this->haveReadSupport = $haveReadSupport;
=======
    public function __construct($input, $haveReadSupport)
    {
        $this->haveReadSupport = (bool) $haveReadSupport;
>>>>>>> f8060a2572be4182d51fd7b5a4dfc24f66368b6e

        if ($this->haveReadSupport) {
            // Fix for PHP bug #51800: reading from STDOUT pipe hangs forever on Windows if the output is too big.
            // Workaround for this problem is to use temporary files instead of pipes on Windows platform.
            //
            // @see https://bugs.php.net/51800
            $pipes = [
                Process::STDOUT => Process::OUT,
                Process::STDERR => Process::ERR,
            ];
            $tmpDir = sys_get_temp_dir();
            $lastError = 'unknown reason';
            set_error_handler(function ($type, $msg) use (&$lastError) { $lastError = $msg; });
            for ($i = 0;; ++$i) {
                foreach ($pipes as $pipe => $name) {
                    $file = sprintf('%s\\sf_proc_%02X.%s', $tmpDir, $i, $name);

                    if (!$h = fopen($file.'.lock', 'w')) {
                        if (file_exists($file.'.lock')) {
                            continue 2;
                        }
                        restore_error_handler();
                        throw new RuntimeException('A temporary file could not be opened to write the process output: '.$lastError);
                    }
                    if (!flock($h, \LOCK_EX | \LOCK_NB)) {
                        continue 2;
                    }
                    if (isset($this->lockHandles[$pipe])) {
                        flock($this->lockHandles[$pipe], \LOCK_UN);
                        fclose($this->lockHandles[$pipe]);
                    }
                    $this->lockHandles[$pipe] = $h;

<<<<<<< HEAD
                    if (!($h = fopen($file, 'w')) || !fclose($h) || !$h = fopen($file, 'r')) {
=======
                    if (!fclose(fopen($file, 'w')) || !$h = fopen($file, 'r')) {
>>>>>>> f8060a2572be4182d51fd7b5a4dfc24f66368b6e
                        flock($this->lockHandles[$pipe], \LOCK_UN);
                        fclose($this->lockHandles[$pipe]);
                        unset($this->lockHandles[$pipe]);
                        continue 2;
                    }
                    $this->fileHandles[$pipe] = $h;
                    $this->files[$pipe] = $file;
                }
                break;
            }
            restore_error_handler();
        }

        parent::__construct($input);
    }

<<<<<<< HEAD
    public function __sleep(): array
    {
        throw new \BadMethodCallException('Cannot serialize '.__CLASS__);
    }

    public function __wakeup()
    {
        throw new \BadMethodCallException('Cannot unserialize '.__CLASS__);
    }

=======
>>>>>>> f8060a2572be4182d51fd7b5a4dfc24f66368b6e
    public function __destruct()
    {
        $this->close();
    }

    /**
     * {@inheritdoc}
     */
<<<<<<< HEAD
    public function getDescriptors(): array
=======
    public function getDescriptors()
>>>>>>> f8060a2572be4182d51fd7b5a4dfc24f66368b6e
    {
        if (!$this->haveReadSupport) {
            $nullstream = fopen('NUL', 'c');

            return [
                ['pipe', 'r'],
                $nullstream,
                $nullstream,
            ];
        }

        // We're not using pipe on Windows platform as it hangs (https://bugs.php.net/51800)
        // We're not using file handles as it can produce corrupted output https://bugs.php.net/65650
        // So we redirect output within the commandline and pass the nul device to the process
        return [
            ['pipe', 'r'],
            ['file', 'NUL', 'w'],
            ['file', 'NUL', 'w'],
        ];
    }

    /**
     * {@inheritdoc}
     */
<<<<<<< HEAD
    public function getFiles(): array
=======
    public function getFiles()
>>>>>>> f8060a2572be4182d51fd7b5a4dfc24f66368b6e
    {
        return $this->files;
    }

    /**
     * {@inheritdoc}
     */
<<<<<<< HEAD
    public function readAndWrite(bool $blocking, bool $close = false): array
=======
    public function readAndWrite($blocking, $close = false)
>>>>>>> f8060a2572be4182d51fd7b5a4dfc24f66368b6e
    {
        $this->unblock();
        $w = $this->write();
        $read = $r = $e = [];

        if ($blocking) {
            if ($w) {
                @stream_select($r, $w, $e, 0, Process::TIMEOUT_PRECISION * 1E6);
            } elseif ($this->fileHandles) {
                usleep(Process::TIMEOUT_PRECISION * 1E6);
            }
        }
        foreach ($this->fileHandles as $type => $fileHandle) {
            $data = stream_get_contents($fileHandle, -1, $this->readBytes[$type]);

            if (isset($data[0])) {
                $this->readBytes[$type] += \strlen($data);
                $read[$type] = $data;
            }
            if ($close) {
                ftruncate($fileHandle, 0);
                fclose($fileHandle);
                flock($this->lockHandles[$type], \LOCK_UN);
                fclose($this->lockHandles[$type]);
                unset($this->fileHandles[$type], $this->lockHandles[$type]);
            }
        }

        return $read;
    }

    /**
     * {@inheritdoc}
     */
<<<<<<< HEAD
    public function haveReadSupport(): bool
=======
    public function haveReadSupport()
>>>>>>> f8060a2572be4182d51fd7b5a4dfc24f66368b6e
    {
        return $this->haveReadSupport;
    }

    /**
     * {@inheritdoc}
     */
<<<<<<< HEAD
    public function areOpen(): bool
=======
    public function areOpen()
>>>>>>> f8060a2572be4182d51fd7b5a4dfc24f66368b6e
    {
        return $this->pipes && $this->fileHandles;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        parent::close();
        foreach ($this->fileHandles as $type => $handle) {
            ftruncate($handle, 0);
            fclose($handle);
            flock($this->lockHandles[$type], \LOCK_UN);
            fclose($this->lockHandles[$type]);
        }
        $this->fileHandles = $this->lockHandles = [];
    }
}
