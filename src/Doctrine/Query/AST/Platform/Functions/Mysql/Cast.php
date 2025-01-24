<?php
declare(strict_types=1);

namespace App\Doctrine\Query\AST\Platform\Functions\Mysql;

use App\Doctrine\Query\AST\Platform\Functions\PlatformFunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\SqlWalker;
use App\Doctrine\Query\AST\Functions\Cast as DqlFunction;

class Cast extends PlatformFunctionNode
{
    public function getSql(SqlWalker $sqlWalker): string
    {
        /** @var Node $value */
        $value = $this->parameters[DqlFunction::PARAMETER_KEY];
        $type  = $this->parameters[DqlFunction::TYPE_KEY];

        $type = \strtolower($type);
        $isBoolean = $type === 'bool' || $type === 'boolean';
        if ($type === 'char') {
            $type = 'char(1)';
        } elseif ($type === 'string' || $type === 'text' || $type === 'uuid') {
            $type = 'char';
        } elseif ($type === 'int' || $type === 'integer' || $isBoolean) {
            $type = 'signed';
        } elseif ($type === 'bigint') {
            $type = 'unsigned';
        }

        $expression = 'CAST(' . $this->getExpressionValue($value, $sqlWalker) . ' AS ' . $type . ')';

        if ($isBoolean) {
            $expression .= ' <> 0';
        }

        return $expression;
    }
}
