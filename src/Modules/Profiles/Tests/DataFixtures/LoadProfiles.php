<?php

namespace ForkCMS\Modules\Profiles\Tests\DataFixtures;

use ForkCMS\Modules\Profiles\Tests\DataFixtures\LoadProfilesProfile;
use SpoonDatabase;

/**
 * @deprecated remove this in Fork 6, just use the one from the Backend
 */
class LoadProfiles
{
    public function load(SpoonDatabase $database): void
    {
        (new LoadProfilesProfile())->load($database);
    }
}
