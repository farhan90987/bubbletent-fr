<?php

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\Metadata\Driver;

use MarketPress\German_Market\JMS\Serializer\Exception\InvalidMetadataException;
use MarketPress\German_Market\JMS\Serializer\Expression\CompilableExpressionEvaluatorInterface;
use MarketPress\German_Market\JMS\Serializer\Expression\Expression;

trait ExpressionMetadataTrait
{
    /**
     * @var CompilableExpressionEvaluatorInterface
     */
    private $expressionEvaluator;

    /**
     * @return Expression|string
     *
     * @throws InvalidMetadataException
     */
    private function parseExpression(string $expression, array $names = [])
    {
        if (null === $this->expressionEvaluator) {
            return $expression;
        }

        try {
            return $this->expressionEvaluator->parse($expression, array_merge(['context', 'property_metadata', 'object'], $names));
        } catch (\LogicException $e) {
            throw new InvalidMetadataException(sprintf('Can not parse the expression "%s"', $expression), 0, $e);
        }
    }
}
