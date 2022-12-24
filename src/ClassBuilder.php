<?php

namespace uuf6429\ExpressionLanguage;

use JetBrains\PhpStorm\Language;
use ReflectionException;
use ReflectionMethod;

class ClassBuilder
{
    /**
     * @var string
     */
    private $parent;

    /**
     * @var string
     */
    private $child;

    /**
     * @var string
     */
    private $usedTraits = [];

    /**
     * @var string
     */
    private $usedImports = [];

    /**
     * @var array<string, string>
     */
    private $overriddenMethods = [];

    public static function create(): ClassBuilder
    {
        return new self();
    }

    public function class(string $fqn): ClassBuilder
    {
        $this->child = '\\' . ltrim($fqn, '\\');
        return $this;
    }

    public function extend(string $fqn): ClassBuilder
    {
        $this->parent = '\\' . ltrim($fqn, '\\');
        return $this;
    }

    public function use(string $fqn): ClassBuilder
    {
        $this->usedTraits[] = '\\' . ltrim($fqn, '\\');
        return $this;
    }

    public function import(string $fqn): ClassBuilder
    {
        $this->usedImports[] = '\\' . ltrim($fqn, '\\');
        return $this;
    }

    public function override(
        string $method,
        #[Language('InjectablePHP')]
        string $body
    ): ClassBuilder {
        $this->overriddenMethods[$method] = $body;
        return $this;
    }

    public function build()
    {
        list($class, $namespace) = array_map('strrev', explode('\\', strrev($this->child), 2)) + [''];

        $codeLines = [
            '',
            sprintf('namespace %s;', ltrim($namespace, '\\')),
            '',
        ];

        foreach ($this->usedImports as $import) {
            $codeLines[] = "use $import;";
        }

        $codeLines[] = '';

        $codeLines[] = "class $class extends $this->parent";
        $codeLines[] = '{';

        foreach ($this->usedTraits as $trait) {
            $codeLines[] = "    use $trait;";
        }

        foreach ($this->overriddenMethods as $method => $body) {
            $codeLines[] = '';
            $codeLines[] = $this->extractSignature($this->parent, $method);
            $codeLines[] = '    {';
            $codeLines[] = $body;
            $codeLines[] = '    }';
        }

        $codeLines[] = '}';

        eval(implode("\n", $codeLines));
    }

    /**
     * @param string $class
     * @param string $method
     * @return string|null
     */
    private function extractSignature(string $class, string $method)
    {
        try {
            $code = file_get_contents(
                (new ReflectionMethod($class, $method))->getFileName()
            );

            return preg_match("/[^}]+function {$method}[^{]+/", $code, $matches)
                ? $matches[0]
                : null;
        } catch (ReflectionException $ex) {
            return null;
        }
    }
}
