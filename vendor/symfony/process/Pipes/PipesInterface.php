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

/**
 * PipesInterface manages descriptors and pipes for the use of proc_open.
 *
 * @author Romain Neutron <imprec@gmail.com>
 *
 * @internal
 */
interface PipesInterface
{
<<<<<<< HEAD
    public const CHUNK_SIZE = 16384;

    /**
     * Returns an array of descriptors for the use of proc_open.
     */
    public function getDescriptors(): array;
=======
    const CHUNK_SIZE = 16384;

    /**
     * Returns an array of descriptors for the use of proc_open.
     *
     * @return array
     */
    public function getDescriptors();
>>>>>>> f8060a2572be4182d51fd7b5a4dfc24f66368b6e

    /**
     * Returns an array of filenames indexed by their related stream in case these pipes use temporary files.
     *
     * @return string[]
     */
<<<<<<< HEAD
    public function getFiles(): array;
=======
    public function getFiles();
>>>>>>> f8060a2572be4182d51fd7b5a4dfc24f66368b6e

    /**
     * Reads data in file handles and pipes.
     *
     * @param bool $blocking Whether to use blocking calls or not
     * @param bool $close    Whether to close pipes if they've reached EOF
     *
     * @return string[] An array of read data indexed by their fd
     */
<<<<<<< HEAD
    public function readAndWrite(bool $blocking, bool $close = false): array;

    /**
     * Returns if the current state has open file handles or pipes.
     */
    public function areOpen(): bool;

    /**
     * Returns if pipes are able to read output.
     */
    public function haveReadSupport(): bool;
=======
    public function readAndWrite($blocking, $close = false);

    /**
     * Returns if the current state has open file handles or pipes.
     *
     * @return bool
     */
    public function areOpen();

    /**
     * Returns if pipes are able to read output.
     *
     * @return bool
     */
    public function haveReadSupport();
>>>>>>> f8060a2572be4182d51fd7b5a4dfc24f66368b6e

    /**
     * Closes file handles and pipes.
     */
    public function close();
}
