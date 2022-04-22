<?php

namespace ForkCMS\Core\Installer;

use ForkCMS\Modules\Extensions\Domain\Module\ModuleInstaller;

final class CoreInstaller extends ModuleInstaller
{
    public const IS_REQUIRED = true;
    public const IS_VISIBLE_IN_OVERVIEW = false;

    public function install(): void
    {
        $this->setSetting(
            'date_formats_short',
            [
                // php format  => IntlDateFormatter format (https://unicode-org.github.io/icu/userguide/format_parse/datetime/index#datetimepatterngenerator)
                'j/n/Y' => 'd/M/yyyy',
                'j-n-Y' => 'd-M-yyyy',
                'j.n.Y' => 'd.M.yyyy',
                'n/j/Y' => 'M/d/yyyy',
                'n-j-Y' => 'M-d-yyyy',
                'n.j.Y' => 'M.d.yyyy',
                'd/m/Y' => 'dd/MM/yyyy',
                'd-m-Y' => 'dd-MM-yyyy',
                'd.m.Y' => 'dd.MM.yyyy',
                'm/d/Y' => 'MM/dd/yyyy',
                'm-d-Y' => 'MM-dd-yyyy',
                'm.d.Y' => 'MM.dd.yyyy',
                'j/n/y' => 'd/M/yy',
                'j-n-y' => 'd-M-yy',
                'j.n.y' => 'd.M.yy',
                'n/j/y' => 'M/d/yy',
                'n-j-y' => 'M-d-yy',
                'n.j.y' => 'M.d.yy',
                'd/m/y' => 'dd/MM/yy',
                'd-m-y' => 'dd-MM-yy',
                'd.m.y' => 'dd.MM.yy',
                'm/d/y' => 'MM/dd/yy',
                'm-d-y' => 'MM-dd-yy',
                'm.d.y' => 'MM.dd.yy',
            ]
        );
        $this->setSetting(
            'date_formats_long',
            [
                // php format  => IntlDateFormatter format (https://unicode-org.github.io/icu/userguide/format_parse/datetime/index#datetimepatterngenerator)
                'j F Y' => 'd MMMM yyyy',
                'D j F Y' => 'EEE d MMMM yyyy',
                'l j F Y' => 'EEEE d MMMM yyyy',
                'j F, Y' => 'd MMMM, yyyy',
                'D j F, Y' => 'EEE d MMMM, yyyy',
                'l j F, Y' => 'EEEE d MMMM, yyyy',
                'd F Y' => 'dd MMMM yyyy',
                'd F, Y' => 'dd MMMM, yyyy',
                'F j Y' => 'MMMM d yyyy',
                'D F j Y' => 'EEE MMMM d yyyy',
                'l F j Y' => 'EEEE MMMM d yyyy',
                'F d, Y' => 'MMMM dd, yyyy',
                'D F d, Y' => 'EEE MMMM dd, yyyy',
                'l F d, Y' => 'EEEE MMMM dd, yyyy',
            ]
        );
        $this->setSetting(
            'time_formats',
            [
                // php format  => IntlDateFormatter format (https://unicode-org.github.io/icu/userguide/format_parse/datetime/index#datetimepatterngenerator)
                'H:i' => 'kk:mm',
                'g:i a' => 'h:mm a',
                'H:i:s' => 'kk:mm:ss',
                'g:i:s a' => 'h:mm:ss a',
            ]
        );
        $this->setSetting(
            'number_formats',
            [
                'comma_nothing' => '10000,25',
                'dot_nothing' => '10000.25',
                'dot_comma' => '10,000.25',
                'comma_dot' => '10.000,25',
                'dot_space' => '10000.25',
                'comma_space' => '10 000,25',
            ]
        );
    }
}
