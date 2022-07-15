<?php

namespace uuf6429\ExpressionLanguage;

use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Throwable;

class TemplateStringTest extends TestCase
{
    /**
     * @dataProvider validStringTemplateDataProvider
     */
    public function testValidStringTemplateScenario(string $expr, array $vars, string $expectedExpr, $expectedData)
    {
        $el = new ExpressionLanguageWithTplStr();

        $actualExpr = $el->compile($expr, array_keys($vars));
        $actualData = $el->evaluate($expr, $vars);

        $this->assertSame(
            ['expr' => $expectedExpr, 'data' => $expectedData],
            ['expr' => $actualExpr, 'data' => $actualData]
        );
    }

    public function validStringTemplateDataProvider(): array
    {
        return [
            'simple string template' => [
                '$expr' => 'v1 ~ ` + ${v2} is ${v3}`',
                '$vars' => ['v1' => 1, 'v2' => 2, 'v3' => 3],
                '$expectedExpr' => '($v1 . ((((" + " . $v2) . " is ") . $v3) . ""))',
                '$expectedData' => '1 + 2 is 3',
            ],
            'template in template' => [
                '$expr' => 'v1 ~ ` + ${v2} is ${v3 ? `${v3}` : "false"}`',
                '$vars' => ['v1' => 1, 'v2' => 2, 'v3' => 3],
                '$expectedExpr' => '($v1 . ((((" + " . $v2) . " is ") . (($v3) ? ((("" . $v3) . "")) : ("false"))) . ""))',
                '$expectedData' => '1 + 2 is 3',
            ],
            'closing bracket in string in template should be allowed' => [
                '$expr' => '`${v1 ~ "}"}`',
                '$vars' => ['v1' => '1'],
                '$expectedExpr' => '(("" . ($v1 . "}")) . "")',
                '$expectedData' => '1}',
            ],
            'backtick in string is ignored' => [
                '$expr' => '" ` "',
                '$vars' => [],
                '$expectedExpr' => '" ` "',
                '$expectedData' => ' ` ',
            ],
            'backticks can be escaped' => [
                '$expr' => '` \` `',
                '$vars' => [],
                '$expectedExpr' => '" ` "',
                '$expectedData' => ' ` ',
            ],
            'opening interpolation can can be escaped' => [
                '$expr' => '` \${} `',
                '$vars' => [],
                '$expectedExpr' => '" \${} "',
                '$expectedData' => ' ${} ',
            ],
            'closing interpolation is lazy' => [
                '$expr' => '` ${v1}} `',
                '$vars' => ['v1' => 'a'],
                '$expectedExpr' => '((" " . $v1) . "} ")',
                '$expectedData' => ' a} ',
            ],
        ];
    }

    /**
     * @dataProvider invalidStringTemplateDataProvider
     */
    public function testInvalidStringTemplateScenario(string $expr, Throwable $expectedException)
    {
        $el = new ExpressionLanguageWithTplStr();

        $this->expectException(get_class($expectedException));
        $this->expectExceptionMessage($expectedException->getMessage());

        $el->compile($expr, []);
    }

    public function invalidStringTemplateDataProvider(): array
    {
        return [
            'missing final backtick' => [
                '$expr' => $expr = 'v1 ~ ` + ${v2}',
                '$expectedException' => new SyntaxError('Expected "`"', 14, $expr),
            ],
            'missing final quote' => [
                '$expr' => $expr = "'`",
                '$expectedException' => new SyntaxError('Expected "\'"', 2, $expr),
            ],
        ];
    }
}
