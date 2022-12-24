<?php

namespace uuf6429\ExpressionLanguage;

use JetBrains\PhpStorm\Language;
use ReflectionException;
use ReflectionMethod;

/**
 * @codeCoverageIgnore
 */
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

    public function buildClass(): string
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
            if (($sig = $this->extractSignature($this->parent, $method)) !== null) {
                $codeLines[] = '';
                array_push($codeLines, ...$sig);
                $codeLines[] = '    {';
                $codeLines[] = "        $body";
                $codeLines[] = '    }';
            }
        }

        $codeLines[] = '}';

        return implode("\n", $codeLines);
    }

    public function createClass()
    {
        eval($this->buildClass());
    }

    /**
     * @param string $class
     * @param string $method
     * @return string[]|null
     */
    private function extractSignature(string $class, string $method)
    {
        try {
            $code = file_get_contents(
                (new ReflectionMethod($class, $method))->getFileName()
            );

            return preg_match("/([^}]+function {$method}[^{]+?)\\n\\s+?{/", $code, $matches)
                ? array_filter(explode("\n", $matches[1]))
                : null;
        } catch (ReflectionException $ex) {
            return null;
        }
    }
}
