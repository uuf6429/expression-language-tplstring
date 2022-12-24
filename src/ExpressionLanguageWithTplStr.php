<?php

namespace uuf6429\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage as SymfonyExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ParsedExpression as SymfonyParsedExpression;

ClassBuilder::create()
    ->import(SymfonyParsedExpression::class)
    ->class(ExpressionLanguageWithTplStr::class)
    ->extend(SymfonyExpressionLanguage::class)
    ->use(TemplateStringTranslatorTrait::class)
    ->override('compile', '
        return parent::compile($this->convertExpression($expression), $names);
    ')
    ->override('evaluate', '
        return parent::evaluate($this->convertExpression($expression), $values);
    ')
    ->override('parse', '
        return parent::parse($this->convertExpression($expression), $names);
    ')
    ->override('lint', '
        parent::lint($this->convertExpression($expression), $names);
    ')
    ->build();
