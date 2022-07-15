<?php

namespace uuf6429\ExpressionLanguage;

use SplStack;
use Symfony\Component\ExpressionLanguage\SyntaxError;

trait TemplateStringTranslatorTrait
{
    /** @noinspection PhpDuplicateSwitchCaseBodyInspection */
    private function translateTplToEl(string $expression): string
    {
        static $IN_NUL = '-', $IN_DQS = '"', $IN_SQS = "'", $IN_TPL = '`', $IN_EXP = '$', $IN_ESC = '\\';
        $tokens = preg_split('/(`|\\$\\{|}|"|\'|\\\\)/', $expression, -1, PREG_SPLIT_DELIM_CAPTURE);
        $stateStack = new SplStack();
        $stateStack->push($IN_NUL);
        $result = '';

        foreach ($tokens as $token) {
            $currState = $stateStack->top();

            switch (true) {
                case $stateStack->count() === 0:
                    throw new SyntaxError('Unexpected end of state stack', strlen($expression), $expression);

                //<editor-fold desc="Handle Template Escaping">

                case $currState === $IN_TPL && $token === '\\':
                    $stateStack->push($IN_ESC);
                    break;

                case $currState === $IN_ESC && $token !== '':
                    $stateStack->pop();
                    $result .= $token;
                    break;

                //</editor-fold>

                //<editor-fold desc="Handle Literal Strings">

                case ($currState !== $IN_TPL && $currState !== $IN_DQS && $currState !== $IN_SQS && ($token === '"' || $token === "'")):
                    $stateStack->push($token === '"' ? $IN_DQS : $IN_SQS);
                    $result .= $token;
                    break;

                case (($token === '"' && $currState === $IN_DQS) || ($token === "'" && $currState === $IN_SQS)) :
                    $stateStack->pop();
                    $result .= $token;
                    break;

                //</editor-fold>

                //<editor-fold desc="Handle Template Strings">

                case ($token === '`' && $currState !== $IN_TPL && $currState !== $IN_DQS && $currState !== $IN_SQS) :
                    $stateStack->push($IN_TPL);
                    $result .= '("';
                    break;

                case ($token === '`' && $currState === $IN_TPL) :
                    $stateStack->pop();
                    $result .= '")';
                    break;

                //</editor-fold>

                //<editor-fold desc="Handle Template Expr">

                case ($token === '${' && $currState === $IN_TPL) :
                    $stateStack->push($IN_EXP);
                    $result .= '" ~ (';
                    break;

                case ($token === '}' && $currState === $IN_EXP) :
                    $stateStack->pop();
                    $result .= ') ~ "';
                    break;

                //</editor-fold>

                default:
                    $result .= $token;
            }
        }

        if ($stateStack->count() !== 1) {
            $reverse = [$IN_DQS => '"', $IN_SQS => "'", $IN_TPL => '`', $IN_EXP = '}'];
            throw new SyntaxError(sprintf('Expected "%s".', $reverse[$stateStack->top()]), strlen($expression), $expression);
        }

        return $result;
    }
}
