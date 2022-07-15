<?php

namespace uuf6429\ExpressionLanguage;

use Throwable;

$instantiable = static function ($class) {
    try {
        new $class();
        return true;
    } catch (Throwable $ex) {
        return false;
    }
};

if ($instantiable(Shims\ExpressionLanguageWithTplStrSF6::class)) {
    class ExpressionLanguageWithTplStr extends Shims\ExpressionLanguageWithTplStrSF6
    {
    }
} elseif ($instantiable(Shims\ExpressionLanguageWithTplStrSF5::class)) {
    class ExpressionLanguageWithTplStr extends Shims\ExpressionLanguageWithTplStrSF5
    {
    }
} elseif ($instantiable(Shims\ExpressionLanguageWithTplStrSF4::class)) {
    class ExpressionLanguageWithTplStr extends Shims\ExpressionLanguageWithTplStrSF4
    {
    }
}
