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
        new InputOption('ttl', 't', InputOption::VALUE_OPTIONAL, 'ttl', 300), // 5 minutes
        new InputOption('dry-run', null, InputOption::VALUE_NONE, ''),
        new InputOption('without-hashed-file', null, InputOption::VALUE_NONE, ''),
        new InputOption('hashed-file-ttl', null, InputOption::VALUE_OPTIONAL, '', 60*60*24*30*6) //6 months
    ))
    ->setDescription('Clear old cache files')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
        $ttl = $input->getOption('ttl');
        $dryRun = $input->getOption('dry-run');
        $dryRunText = '';
        if ($dryRun) {
            $dryRunText = ' (dry-run)';
        }

        $finder = new Finder();
        $fs = new Filesystem();
        $finder->files()
            ->notName('/\$[0-9a-f]+\.json$/')
            ->date('until '.$ttl.' sec ago')
            ->in($app['cache_dir']);
        foreach ($finder as $file) {
            $output->writeln('<info>Remove '.$file->getRealpath().$dryRunText.'</info>');
            if (!$dryRun) {
                $fs->remove($file);
            }
        }

        if (!$input->getOption('without-hashed-file')) {
            $hashedFileFinder = new Finder();
            $hashedFileTtl = $input->getOption('hashed-file-ttl');
            $hashedFileFinder->files()
                ->name('/\$[0-9a-f]+\.json$/')
                ->date('until '.$hashedFileTtl.' sec ago')
                ->in($app['cache_dir']);
            foreach ($hashedFileFinder as $file) {
                $output->writeln('<info>Remove '.$file->getRealpath().$dryRunText.'</info>');
                if (!$dryRun) {
                    $fs->remove($file);
                }
            }
        }
    });

$console
    ->register('cache:clear-all')
    ->setDefinition(array(
        new InputOption('dry-run', null, InputOption::VALUE_NONE, ''),
    ))
    ->setDescription('Clear all cache files')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
        $dryRun = $input->getOption('dry-run');
        $dryRunText = '';
        if ($dryRun) {
            $dryRunText = ' (dry-run)';
        }
        $finder = new Finder();
        $fs = new Filesystem();
        $finder->files()->in($app['cache_dir']);
        foreach ($finder as $file) {
            $output->writeln('<info>Remove '.$file->getRealpath().$dryRunText.'</info>');
            if (!$dryRun) {
                $fs->remove($file);
            }
        }
    });

$console->run();
