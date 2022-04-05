<?php

namespace ForkCMS\Modules\Extensions\Console;

use ForkCMS\Modules\Extensions\Domain\Theme\Theme;
use ForkCMS\Modules\Extensions\Domain\Theme\ThemeRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'forkcms:extensions:active-theme',
    description: 'Show the active theme',
)]
final class ActiveThemeCommand extends Command
{
    public function __construct(private readonly ?ThemeRepository $themeRepository = null)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Theme|null $theme */
        $theme = $this->themeRepository->findOneBy(['active' => true]);
        $output->write($theme?->getName() ?? $this->themeRepository->findInstallable()[0]->name);

        return self::SUCCESS;
    }
}
