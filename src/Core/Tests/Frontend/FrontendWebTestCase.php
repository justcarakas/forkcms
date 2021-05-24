<?php

namespace ForkCMS\Core\Tests\Frontend;

use ForkCMS\Core\Common\WebTestCase;

abstract class FrontendWebTestCase extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('APPLICATION')) {
            define('APPLICATION', 'Frontend');
        }
    }
}
