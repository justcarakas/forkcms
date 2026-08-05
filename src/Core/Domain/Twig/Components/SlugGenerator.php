<?php

declare(strict_types=1);

namespace ForkCMS\Core\Domain\Twig\Components;

use ForkCMS\Modules\Frontend\Domain\Meta\MetaRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

/**
 * Renders no visible markup of its own - slug_generator_controller.js projects the slug this
 * generates into the real "slug" form field and the on-page URL preview, so meta_widget's and
 * title_widget's existing layout don't need to change to use this.
 */
#[AsLiveComponent]
final class SlugGenerator
{
    use DefaultActionTrait;

    #[LiveProp]
    public string $slug = '';

    #[LiveProp]
    public string $generateSlugCallbackClass = '';

    #[LiveProp]
    public string $generateSlugCallbackMethod = 'slugify';

    /**
     * A PHP-serialized array (matching Meta.js/GenerateSlug's previous AJAX contract) rather than a
     * plain array LiveProp, since callers (e.g. RevisionType) pass value objects like Locale in here
     * that a JSON-hydrated array prop can't reconstruct.
     */
    #[LiveProp]
    public string $generateSlugCallbackParameters = 'a:0:{}';

    public function __construct(private readonly MetaRepository $metaRepository)
    {
    }

    #[LiveAction]
    public function generate(#[LiveArg] string $value): void
    {
        $parameters = @unserialize($this->generateSlugCallbackParameters, ['allowed_classes' => true]);

        $this->slug = $this->metaRepository->generateSlug(
            $value,
            $this->generateSlugCallbackClass,
            $this->generateSlugCallbackMethod,
            is_array($parameters) ? $parameters : []
        );
    }
}
