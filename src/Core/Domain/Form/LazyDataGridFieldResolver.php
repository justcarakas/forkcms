<?php

declare(strict_types=1);

namespace ForkCMS\Core\Domain\Form;

use ForkCMS\Core\Domain\Util\Ensure;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Lets a form type decide, per DataGridType field, whether to build the (potentially expensive)
 * underlying DataGrid eagerly or defer it to a lazily-loaded turbo-frame - e.g. a DataGrid living in
 * a form tab that isn't shown by default shouldn't run its query on every page load, only once a
 * user actually opens that tab.
 */
final readonly class LazyDataGridFieldResolver
{
    public const string QUERY_PARAMETER = '_lazyDataGridField';

    public function __construct(private RequestStack $requestStack)
    {
    }

    public function isRequested(string $fieldName): bool
    {
        return $this->requestStack->getCurrentRequest()?->query->get(self::QUERY_PARAMETER) === $fieldName;
    }

    public function lazySrc(string $fieldName): string
    {
        $request = Ensure::isNotNull($this->requestStack->getCurrentRequest());
        $separator = str_contains($request->getRequestUri(), '?') ? '&' : '?';

        return $request->getRequestUri() . $separator . self::QUERY_PARAMETER . '=' . $fieldName;
    }
}
