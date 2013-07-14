<?php

require_once __DIR__.'/vendor/autoload.php';

$app = require(__DIR__.'/app.php');

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;

$console = new Application();

$console
    ->register('cache:clear-old-file')
    ->setDefinition(array(
        new InputOption('ttl', 't', InputOption::VALUE_OPTIONAL, 'ttl', 300)
    ))
    ->setDescription('Clear old cache files')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
        $ttl = $input->getOption('ttl');
        $finder = new Finder();
        $fs = new Filesystem();
        $finder->files()
            ->notName('/\$[0-9a-f]+\.json$/')
            ->date('until '.$ttl.' sec ago')
            ->in($app['cache_dir']);
        foreach ($finder as $file) {
            $output->writeln('<info>Remove '.$file->getRealpath().'</info>');
            $fs->remove($file);
        }
    });

$console
    ->register('cache:clear-all')
    ->setDescription('Clear all cache files')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
        $finder = new Finder();
        $fs = new Filesystem();
        $finder->files()->in($app['cache_dir']);
        foreach ($finder as $file) {
            $output->writeln('<info>Remove '.$file->getRealpath().'</info>');
            $fs->remove($file);
        }
    });

$console->run();
