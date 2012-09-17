<?php

namespace Queensbridge\Console;

use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Download a git repository.
 */
class Downloader
{
    private $path;

    private $cwd;

    public function __construct($path, $cwd = null)
    {
        $this->cwd = $cwd ? $cwd : getcwd();
        $this->path = $path;
    }

    public function fetch($url)
    {
        $fs = new Filesystem();

        if (!$fs->exists($this->path)) {
            $builder = new ProcessBuilder(array('git'));
            $builder->add('clone')
                    ->add($url)
                    ->add($this->path)
                    ->setTimeout(null)
                    ->setWorkingDirectory($this->cwd);

            $process = $builder->getProcess();
            $process->run(function ($type, $buffer) {
                if ('out' == $type) {
                    echo $buffer;
                }
            });

            if (!$process->isSuccessful()) {
                throw new \RuntimeException($process->getErrorOutput());
            }
        } else {
            $this->clean();
        }
    }

    public function branch($name)
    {
        $builder = new ProcessBuilder(array('git'));
        $builder->add('rev-parse')
                ->add('--abbrev-ref')
                ->add('HEAD')
                ->setWorkingDirectory($this->path);

        $process = $builder->getProcess();
        $process->run();

        if (strpos($process->getOutput(), $name)) {
            return;
        }

        $builder = new ProcessBuilder(array('git'));
        $builder->add('checkout')
                ->add($name)
                ->add('-b')
                ->add($name)
                ->setWorkingDirectory($this->path);

        $process = $builder->getProcess();
        $process->run(function ($type, $buffer) {
            if ('out' == $type) {
                echo $buffer;
            }
        });

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        /*
        $builder = new ProcessBuilder(array('git'));
        $builder->add('rev-parse')
                ->add('--abbrev-ref')
                ->add('HEAD')
                ->setWorkingDirectory($this->path);

        $process = $builder->getProcess();
        $process->run();

        if ($name === $process->getOutput()) {
            return true;
        }

        return false;
         */
    }

    /**
     * Cleans the WordPress directory.
     */
    public function clean()
    {
        $builder = new ProcessBuilder(array('git'));
        $builder->add('clean')
                ->add('-d')
                ->add('-f')
                ->add('-x')
                ->setWorkingDirectory($this->path);

        $process = $builder->getProcess();
        $process->run(function ($type, $buffer) {
            if ('out' == $type) {
                echo $buffer;
            }
        });

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }
    }
}
