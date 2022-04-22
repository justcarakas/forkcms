<?php

namespace ForkCMS\Modules\Internationalisation\Domain\Twig;

use DateTimeInterface;
use DateTimeZone;
use ForkCMS\Modules\Backend\Domain\User\User;
use ForkCMS\Modules\Extensions\Domain\Module\ModuleName;
use ForkCMS\Modules\Extensions\Domain\Module\ModulesSettings;
use ForkCMS\Modules\Internationalisation\Domain\Locale\InstalledLocale;
use ForkCMS\Modules\Internationalisation\Domain\Locale\InstalledLocaleRepository;
use ForkCMS\Modules\Internationalisation\Domain\Locale\Locale;
use IntlDateFormatter;
use NumberFormatter;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Extra\Intl\IntlExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class ForkIntlExtension extends AbstractExtension
{
    public function __construct(
        private readonly InstalledLocaleRepository $installedLocaleRepository,
        private readonly ModulesSettings $modulesSettings,
        private readonly Security $security,
    ) {
    }

    /** @return TwigFilter[] */
    public function getFilters(): array
    {
        $filters = array_map(
            function (TwigFilter $filter) {
                if ($filter->getCallable() === null) {
                    return $filter;
                }

                return new TwigFilter(
                    $filter->getName(),
                    [0 => $this, $filter->getCallable()[1]],
                    ['needs_environment' => $filter->needsEnvironment()]
                );
            },

            $this->getIntlExtension('formatDateTime', Locale::default())->getFilters()
        );

        foreach ($filters as $filter) {
            if (!str_contains($filter->getName(), 'format_')) {
                continue;
            }

            $filters[] = new TwigFilter(
                'user_' . $filter->getName(),
                [0 => $this, str_replace('format', 'formatUser', $filter->getCallable()[1])],
                ['needs_environment' => $filter->needsEnvironment()]
            );
        }

        $filters[] = new TwigFilter(
            'format_longdatetime',
            [$this, 'formatLongDateTime'],
            ['needs_environment' => true]
        );
        $filters[] = new TwigFilter(
            'format_longdate',
            [$this, 'formatLongDate'],
            ['needs_environment' => true]
        );

        $filters[] = new TwigFilter(
            'user_format_longdatetime',
            [$this, 'formatUserLongDateTime'],
            ['needs_environment' => true]
        );
        $filters[] = new TwigFilter(
            'user_format_longdate',
            [$this, 'formatUserLongDate'],
            ['needs_environment' => true]
        );

        return $filters;
    }

    /** @return TwigFunction[] */
    public function getFunctions(): array
    {
        return array_map(function (TwigFunction $function) {
            if ($function->getCallable() === null) {
                return $function;
            }

            return new TwigFunction(
                $function->getName(),
                [0 => $this, $function->getCallable()[1]],
            );
        }, $this->getIntlExtension('formatDateTime', Locale::default())->getFunctions());
    }

    /** @param array<string, mixed> $arguments */
    public function __call(string $name, array $arguments): mixed
    {
        $locale = null;
        foreach ($arguments as $argument) {
            if (is_string($argument)) {
                $locale = Locale::tryFrom($argument);

                if ($locale !== null) {
                    break;
                }
            }
        }
        $function = str_replace('User', '', $name);

        return $this->getIntlExtension($name, $locale ?? Locale::default())->$function(...$arguments);
    }

    private function getIntlExtension(string $function, Locale $locale): IntlExtension
    {
        $cacheKey = $function . $locale->value;
        static $extensions = [];

        if (array_key_exists($cacheKey, $extensions)) {
            return $extensions[$cacheKey];
        }

        $user = str_contains($function, 'User') ? $this->security->getUser() : null;
        $dateFormatType = str_contains($function, 'LongDate') ? 'long' : 'short';
        $installedLocale = $this->getInstalledLocale($locale);

        if (str_contains($function, 'Date') && !str_contains($function, 'Time')) {
            $order = '%1$s';
        } elseif (!str_contains($function, 'Date') && str_contains($function, 'Time')) {
            $order = '%2$s';
        } else {
            $order = $installedLocale->getSetting('date_time_order');

            if ($user instanceof User) {
                $order = $user->getSetting('date_time_order', $order);
            }
        }

        $extensions[$cacheKey] = new IntlExtension(
            $this->createIntlDateFormatter($installedLocale, $order, $dateFormatType, $user),
            $this->createNumberFormatter($installedLocale, $user)
        );

        return $extensions[$cacheKey];
    }

    private function createNumberFormatter(InstalledLocale $locale, ?User $user): NumberFormatter
    {
        $cacheKey = $locale->getLocale()->value . ($user ? 'user' : '');
        static $numberFormatterCache = [];

        if (array_key_exists($cacheKey, $numberFormatterCache)) {
            return $numberFormatterCache[$cacheKey];
        }

        $numberFormat = $locale->getSetting('number_format');
        if ($user instanceof User) {
            $numberFormat = $user->getSetting('number_format', $numberFormat);
        }

        $numberFormatter = new NumberFormatter(null, NumberFormatter::DECIMAL, '#,##0.####################');
        $separatorSymbols = array_map(
            static fn (string $separator): string => str_replace(
                ['comma', 'dot', 'space', 'nothing'],
                [',', '.', ' ', ''],
                $separator
            ),
            explode('_', $numberFormat)
        );
        $numberFormatter->setSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL, $separatorSymbols[1]);
        $numberFormatter->setSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL, $separatorSymbols[0]);

        $numberFormatterCache[$cacheKey] = $numberFormatter;

        return $numberFormatter;
    }

    private function createIntlDateFormatter(
        InstalledLocale $locale,
        string $order,
        string $dateFormatType = 'short',
        ?User $user = null
    ): IntlDateFormatter {
        static $dateFormatter = [];
        $cacheKey = $locale->getLocale()->value . $order . $dateFormatType . ($user ? 'user' : '');
        if (array_key_exists($cacheKey, $dateFormatter)) {
            return $dateFormatter[$cacheKey];
        }
        $timeFormatKey = $locale->getSetting('time_format');
        $dateFormatKey = $locale->getSetting('date_format_' . $dateFormatType);

        if ($user instanceof User) {
            $timeFormatKey = $user->getSetting('time_format', $timeFormatKey);
            $dateFormatKey = $user->getSetting('date_format_' . $dateFormatType, $dateFormatKey);
        }

        $coreModule = ModuleName::core();
        $dateFormat = $this->modulesSettings->get($coreModule, 'date_formats_' . $dateFormatType)[$dateFormatKey];
        $timeFormat = $this->modulesSettings->get($coreModule, 'time_formats')[$timeFormatKey];

        $dateFormatter[$cacheKey] = new IntlDateFormatter(
            null,
            null,
            null,
            null,
            null,
            sprintf($order, $dateFormat, $timeFormat)
        );

        return $dateFormatter[$cacheKey];
    }

    /**
     * @param DateTimeInterface|string|null $date A date or null to use the current time
     * @param DateTimeZone|string|false|null $timezone The target timezone, null to use the default, false to leave unchanged
     */
    public function formatLongDateTime(
        Environment $env,
        mixed $date,
        ?string $dateFormat = 'medium',
        ?string $timeFormat = 'medium',
        string $pattern = '',
        mixed $timezone = null,
        string $calendar = 'gregorian',
        string $locale = null
    ): string {
        return $this->getIntlExtension(
            'formatLongDateTime',
            Locale::tryFrom($locale) ?? Locale::default()
        )->formatDateTime(
            $env,
            $date,
            $dateFormat,
            $timeFormat,
            $pattern,
            $timezone,
            $calendar,
            $locale
        );
    }

    /**
     * @param DateTimeInterface|string|null $date A date or null to use the current time
     * @param DateTimeZone|string|false|null $timezone The target timezone, null to use the default, false to leave unchanged
     */
    public function formatLongDate(
        Environment $env,
        mixed $date,
        ?string $dateFormat = 'medium',
        string $pattern = '',
        mixed $timezone = null,
        string $calendar = 'gregorian',
        string $locale = null
    ): string {
        return $this->getIntlExtension(
            'formatLongDate',
            Locale::tryFrom($locale) ?? Locale::default()
        )->formatDate(
            $env,
            $date,
            $dateFormat,
            $pattern,
            $timezone,
            $calendar,
            $locale
        );
    }

    /**
     * @param DateTimeInterface|string|null $date A date or null to use the current time
     * @param DateTimeZone|string|false|null $timezone The target timezone, null to use the default, false to leave unchanged
     */
    public function formatUserLongDateTime(
        Environment $env,
        mixed $date,
        ?string $dateFormat = 'medium',
        ?string $timeFormat = 'medium',
        string $pattern = '',
        mixed $timezone = null,
        string $calendar = 'gregorian',
        string $locale = null
    ): string {
        return $this->getIntlExtension(
            'formatUserLongDateTime',
            Locale::tryFrom($locale) ?? Locale::default()
        )->formatDateTime(
            $env,
            $date,
            $dateFormat,
            $timeFormat,
            $pattern,
            $timezone,
            $calendar,
            $locale
        );
    }

    /**
     * @param DateTimeInterface|string|null $date A date or null to use the current time
     * @param DateTimeZone|string|false|null $timezone The target timezone, null to use the default, false to leave unchanged
     */
    public function formatUserLongDate(
        Environment $env,
        mixed $date,
        ?string $dateFormat = 'medium',
        string $pattern = '',
        mixed $timezone = null,
        string $calendar = 'gregorian',
        string $locale = null
    ): string {
        return $this->getIntlExtension(
            'formatUserLongDate',
            Locale::tryFrom($locale) ?? Locale::default()
        )->formatDate(
            $env,
            $date,
            $dateFormat,
            $pattern,
            $timezone,
            $calendar,
            $locale
        );
    }

    public function getInstalledLocale(Locale $locale): InstalledLocale
    {
        static $cache = [];
        if (array_key_exists($locale->value, $cache)) {
            return $cache[$locale->value];
        }

        $cache[$locale->value] = $this->installedLocaleRepository->find($locale->value);

        return $cache[$locale->value];
    }
}
