<?php

namespace ForkCMS\Core\Tests\Frontend;

use ForkCMS\Core\Tests\WebTestCase;

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
