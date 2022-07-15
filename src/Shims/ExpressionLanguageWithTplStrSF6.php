<?php

namespace uuf6429\ExpressionLanguage\Shims;

use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage as SymfonyExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ParsedExpression;
use uuf6429\ExpressionLanguage\TemplateStringTranslatorTrait;

class ExpressionLanguageWithTplStrSF6 extends SymfonyExpressionLanguage // TODO
{
    use TemplateStringTranslatorTrait;

    public function compile(Expression|string $expression, array $names = []): string
    {
        if (!$expression instanceof ParsedExpression) {
            $expression = $this->translateTplToEl($expression);
        }

        return parent::compile($expression, $names);
    }

    public function evaluate(Expression|string $expression, array $values = []): mixed
    {
        if (!$expression instanceof ParsedExpression) {
            $expression = $this->translateTplToEl($expression);
        }

        return parent::evaluate($expression, $values);
    }
}
