<?php

namespace Noud\UtilBundle\Filter;

use ApiPlatform\Core\Api\FilterInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

final class RelatedFilter extends AbstractContextAwareFilter implements FilterInterface
{
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        // otherwise filter is applied to order and page as well
        if (
            !$this->isPropertyEnabled($property, $resourceClass) ||
            !$this->isPropertyMapped($property, $resourceClass)
        ) {
            return;
        }

        $properties = explode('.', $property);
        if (1 === count($properties)) {
            $parameterName = $queryNameGenerator->generateParameterName($property);

            $valueArray[] = $value;

            $expr = $queryBuilder->expr();
            $queryBuilder
                ->andWhere(
                    $expr->in(
                        sprintf('o.%s.value', $properties[0]),
                        $valueArray
                    )
                )
            ;
        } else if (1 < count($properties)) {
            $parameterName = $queryNameGenerator->generateParameterName($property);
            $queryBuilder
                ->join(sprintf('o.%s', $properties[0]), 'p')
                ->andWhere(sprintf('p.%s LIKE :%s', $properties[1], $parameterName))
                ->setParameter($parameterName, '%' . $value . '%');
        }
    }

    public function getDescription(string $resourceClass): array
    {
        return [];
    }
}
