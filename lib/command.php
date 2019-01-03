<?php

use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class rex_cache_warmup_command extends rex_console_command
{
    protected function configure()
    {
        $this
            ->setName('cache:warmup')
            ->setDescription('Pre-generates cache files for pages and images')
            ->setHelp('Generates cache files for all pages and used images in advance to improve the initial website performance.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);
        $io->title('Cache-Warmup');

        // define progress bar
        ProgressBar::setFormatDefinition('custom', "%message:-21s%  %bar%  %current%/%max% ");

        // set error handler
        $errors = [];
        set_error_handler(function ($errno, $errstr, $errfile, $errline) use (&$errors) {
            $errors[] = rex_error_handler::getErrorType($errno).": $errstr in $errfile on line $errline";
            return true;
        }, E_WARNING|E_USER_WARNING|E_NOTICE|E_USER_NOTICE|E_DEPRECATED|E_USER_DEPRECATED|E_STRICT);

        // define wording
        $words = array(
            'pages' => array('page', 'clang'),
            'images' => array('image', 'mediatype')
        );

        // prepare cache items
        $items = cache_warmup_selector::prepareCacheItems(false, false);
        foreach ($items as $k => $v) {

            // init generator
            $generatorClass = 'cache_warmup_generator_' . $k;
            if (class_exists($generatorClass)) {
                $generator = new $generatorClass();

                // init progress bar
                if (!$io->isVerbose()) {
                    $progressBar = new ProgressBar($output, $v['count']);
                    $progressBar->setFormat('custom');
                    $progressBar->setBarCharacter('<fg=green>▓</>');
                    $progressBar->setProgressCharacter('▓');
                    $progressBar->setEmptyBarCharacter('░');
                    $progressBar->setRedrawFrequency(ceil($v['count'] / 60));
                    $progressBar->setBarWidth(60);
                    $progressBar->setMessage("Generating {$k}…");
                    $progressBar->start();
                }
                else {
                    $io->writeln("Generating {$k}…");
                    $io->newLine();
                }

                // generate cache files
                $counter = 0;
                foreach ($v['items'] as $item) {
                    ++$counter;
                    $generator->generateCache(array($item));

                    if ($io->isVerbose()) {
                        // verbose
                        $current = str_pad($counter, strlen((string) $v['count']), " ", STR_PAD_LEFT);
                        $io->writeln("{$current}/{$v['count']} - generated {$words[$k][0]} <fg=magenta>{$item[0]}</> with {$words[$k][1]} <fg=green>{$item[1]}</>");
                    }
                    else {
                        $progressBar->advance();
                    }
                }

                // finish progress bar
                if (!$io->isVerbose()) {
                    $progressBar->finish();
                }

                $io->newLine(2);
            }
        }

        // report
        if ($io->isVerbose()) {
            $errors = array_unique($errors);
            dump($errors);
        }

        $io->newLine();
        $io->success('Finished warmup.');
    }
}
