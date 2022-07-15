<!-- TODO WIP -->

# 🪡 Template Strings
for Symfony Expression Language (4-6)

[![CI](https://github.com/uuf6429/expression-language-tplstring/actions/workflows/ci.yml/badge.svg)](https://github.com/uuf6429/expression-language-tplstring/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/uuf6429/expression-language-tplstring/branch/main/graph/badge.svg)](https://codecov.io/gh/uuf6429/expression-language-tplstring)
[![Minimum PHP Version](https://img.shields.io/badge/php-%5E7%20%7C%20%5E8-8892BF.svg)](https://php.net/)
[![License](https://poser.pugx.org/uuf6429/expression-language-tplstring/license)](https://github.com/uuf6429/expression-language-tplstring/blob/main/LICENSE)
[![Latest Stable Version](https://poser.pugx.org/uuf6429/expression-language-tplstring/version)](https://packagist.org/packages/uuf6429/expression-language-tplstring)
[![Latest Unstable Version](https://poser.pugx.org/uuf6429/expression-language-tplstring/v/unstable)](https://packagist.org/packages/uuf6429/expression-language-tplstring)

<!-- TODO WIP -->

> **What looks like a dot, a cross and a wave, and does the same thing?**
>
> It's the concatenation operator, of course!
>
> PHP uses a dot/period (`.`), many languages including javascript use `+`,
> whereas [Symfony Expression Language](https://github.com/symfony/expression-language) uses the tilde (`~`).

This library provides a translation layer on top of Expression Language that converts template strings in ES6 format* to
valid expression.
While an updated Expression Language subclass is provided for convenience, you don't have to use it, and you can use the
provided trait instead.

*\* only ES6 string interpolation (with any expressions and nesting) is supported; f.e. tagged templates are not.*

## 🔌 Installation

As always, the recommended and easiest way to install this library is through [Composer](https://getcomposer.org/):

```bash
composer require "uuf6429/expression-language-tplstring"
```

## 🚀 Usage

If you do not plan on extending Symfony Expression Language class, you can use the provided drop-in:

```php
$el = new \uuf6429\ExpressionLanguage\ExpressionLanguageWithTplStr();
$el->evaluate('`hello ${name}!`', ['name'=>'mars']); // => hello mars!
```

Otherwise, you can subclass the desired Expression Language class and `use` the provided trait:
```php
class MyEL extends \uuf6429\ExpressionLanguage\ExpressionLanguageWithArrowFunc
{
    use \uuf6429\ExpressionLanguage\TemplateStringTranslatorTrait;
    
    public function compile($expression, array $names = [])
    {
        if (!$expression instanceof \Symfony\Component\ExpressionLanguage\ParsedExpression) {
            $expression = $this->translateTplToEl($expression);
        }

        return parent::compile($expression, $names);
    }

    public function evaluate($expression, array $values = [])
    {
        if (!$expression instanceof \Symfony\Component\ExpressionLanguage\ParsedExpression) {
            $expression = $this->translateTplToEl($expression);
        }

        return parent::evaluate($expression, $values);
    }
}

$el = new MyEL();
$el->evaluate(
    'users.map((user) -> { `hello ${user.name}!` }).join(` `)',
    [
        'users' => new \Illuminate\Support\Collection([
            (object)['name' => 'John', 'surname' => 'Doe'],
            (object)['name' => 'Jane', 'surname' => 'Roe'],
        ])
    ]
); // => hello John! hello Jane!
```

<!-- TODO WIP -->
