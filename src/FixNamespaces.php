<?php

namespace ForkCMS;

use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class FixNamespaces extends Command
{
    private array $writeCache = [];
    private SymfonyStyle $io;
    private string $baseDir;
    private string $baseNamespace;
    private array $FQCNMap = [];

    public function __construct(string $baseDir, string $baseNamespace)
    {
        $this->baseDir = $baseDir;
        $this->baseNamespace = $baseNamespace;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('forkcms:namespaces:fix')
            ->setDescription(
                'Fix all namespaces and references after moving classes to the correct place fork Fork CMS 6'
            )
            ->addOption(
                'fix',
                null,
                InputOption::VALUE_NONE,
                'If set, it will not only tell what is wrong but also fix it'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->scan();

        if ($input->getOption('fix')) {
            $this->replaceUsages();
        } else {
            $this->outputResults();
        }

        return self::SUCCESS;
    }

    private function scan(): void
    {
        $this->getPhpFiles();
    }

    private function getPhpFiles(): void
    {
        $this->FQCNMap = [];
        $finder = new Finder();
        $finder->name('/\.php$/');

        foreach ($finder->in($this->baseDir)->files()->sortByName() as $file) {
            $path = $file->getRelativePathname();
            $newFCQN = 'ForkCMS\\' . str_replace(['/', '.php'], ['\\', ''], $path);
            try {
                $analysis = $this->analysePhpFile($file->getPathname());
            } catch (\Exception $e) {
                continue;
            }
            $analysis['FQCN'] = $analysis['namespace'] . '\\' . ($analysis['class'] ?? $analysis['interface']);

            $this->FQCNMap[$analysis['FQCN']] = [
                'path' => $file->getPathname(),
                'FQCN' => $newFCQN,
                'namespace' => implode(
                    '\\',
                    array_filter(['ForkCMS', str_replace(['/', '.php'], ['\\', ''], $file->getRelativePath())])
                ),
                'class' => $file->getBasename('.php'),
                'analysis' => $analysis,
            ];
        }
    }

    private function analysePhpFile(string $path): array
    {
        $file = fopen($path, 'rb');

        $data = [
            'namespace' => null,
            'use' => [],
            'class' => null,
            'interface' => null,
        ];
        while(!feof($file)) {
            $line = fgets($file);
            if (str_starts_with($line, 'namespace ')) {
                $data['namespace'] = str_replace(['namespace ', ';', "\n"], ['', '', ''], $line);
                continue;
            }
            if (str_starts_with($line, 'use ')) {
                $data['use'][] = strtok(str_replace(['use ', ';', "\n"], ['', '', ''], $line), ' ');
                continue;
            }
            if (str_starts_with($line, 'class ')) {
                $data['class'] = strtok(str_replace(['class ', ';', "\n"], ['', '', ''], $line), ' ');
                fclose($file);

                return $data;
            }
            if (str_starts_with($line, 'final class ')) {
                $data['class'] = strtok(str_replace(['final class ', ';', "\n"], ['', '', ''], $line), ' ');
                fclose($file);

                return $data;
            }
            if (str_starts_with($line, 'abstract class ')) {
                $data['class'] = strtok(str_replace(['abstract class ', ';', "\n"], ['', '', ''], $line), ' ');
                fclose($file);

                return $data;
            }
            if (str_starts_with($line, 'interface ')) {
                $data['interface'] = strtok(str_replace(['interface ', ';', "\n"], ['', '', ''], $line), ' ');
                fclose($file);

                return $data;
            }
        }

        throw new InvalidArgumentException('class or interface not found in :' . $path);
    }

    private function outputResults(): void
    {
        file_put_contents(__DIR__ . '/map.json', json_encode($this->FQCNMap, JSON_PRETTY_PRINT));
    }

    private function replaceUsages(): void
    {
        $replacementSearch = [];
        $replacementValue = [];
        foreach ($this->FQCNMap as $class) {
            foreach ([' ', ';', '"'] as $affix) {
                $replacementSearch[] = $class['analysis']['FQCN'] . $affix;
                $replacementValue[] = $class['FQCN'] . $affix;
            }
        }
        file_put_contents(
            __DIR__ . '/map.json',
            json_encode(array_combine($replacementSearch, $replacementValue), JSON_PRETTY_PRINT)
        );

        foreach ($this->FQCNMap as $class) {
            file_put_contents(
                $class['path'],
                str_replace(
                    'namespace ' . $class['analysis']['namespace'] . ';',
                    'namespace ' . $class['namespace'] . ';',
                    str_replace(
                        $replacementSearch,
                        $replacementValue,
                        file_get_contents($class['path'])
                    )
                )
            );
        }
    }
}
