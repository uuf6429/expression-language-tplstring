<?php

namespace uuf6429\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage as SymfonyExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ParsedExpression;

class ExpressionLanguageWithTplStr extends SymfonyExpressionLanguage
{
    use TemplateStringTranslatorTrait;

    public function compile($expression, array $names = [])
    {
        if (!$expression instanceof ParsedExpression) {
            $expression = $this->translateTplToEl($expression);
        }

        return parent::compile($expression, $names);
    }

    public function evaluate($expression, array $values = [])
    {
        if (!$expression instanceof ParsedExpression) {
            $expression = $this->translateTplToEl($expression);
        }

        return parent::evaluate($expression, $values);
    }
}
