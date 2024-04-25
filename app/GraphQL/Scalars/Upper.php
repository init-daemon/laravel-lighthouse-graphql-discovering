<?php declare(strict_types=1);

namespace App\GraphQL\Scalars;

use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType;

final class Upper extends ScalarType
{
    public function serialize(mixed $value): mixed
    {
        return strtoupper($value);
    }

    public function parseValue(mixed $value): mixed
    {
        return $value;
    }
    public function parseLiteral(Node $valueNode, ?array $variables = null): mixed
    {
        return $valueNode;  
    }
}
