<?php

namespace ForkCMS\Core\Common\ForkCMS\Utility\Module\CopyContentToOtherLocale;

interface CopyModuleContentToOtherLocaleHandlerInterface
{
    public function handle(CopyModuleContentToOtherLocaleInterface $command): void;
}
