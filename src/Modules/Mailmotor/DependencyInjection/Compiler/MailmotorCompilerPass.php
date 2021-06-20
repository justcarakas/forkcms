<?php

namespace ForkCMS\Modules\Mailmotor\DependencyInjection\Compiler;

use ForkCMS\Modules\Extensions\Domain\ModuleSetting\ModuleSettingRepository;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MailmotorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        try {
            // @TODO check if this is still needed
            if ($container->has(ModuleSettingRepository::class) && !is_a($container->get(ModuleSettingRepository::class), 'stdClass')) {
                // we must set these parameters to be usable
                $container->setParameter(
                    'mailmotor.mail_engine',
                    $container->get(ModuleSettingRepository::class)->get('Mailmotor', 'mail_engine', 'not_implemented')
                );
                $container->setParameter(
                    'mailmotor.api_key',
                    $container->get(ModuleSettingRepository::class)->get('Mailmotor', 'api_key')
                );
                $container->setParameter(
                    'mailmotor.list_id',
                    $container->get(ModuleSettingRepository::class)->get('Mailmotor', 'list_id')
                );
            } else {
                // @TODO check if this is still the case
                // When in fork cms installer, we don't have the service fork.settings
                // but we must set the parameters
                // we must set these parameters to be usable
                $container->setParameter('mailmotor.mail_engine', 'not_implemented');
                $container->setParameter('mailmotor.api_key', null);
                $container->setParameter('mailmotor.list_id', null);
            }
        } catch (\Exception $e) {
            // this might fail in the test so we have this as fallback
            // we must set these parameters to be usable
            $container->setParameter('mailmotor.mail_engine', 'not_implemented');
            $container->setParameter('mailmotor.api_key', null);
            $container->setParameter('mailmotor.list_id', null);
        }
    }
}
