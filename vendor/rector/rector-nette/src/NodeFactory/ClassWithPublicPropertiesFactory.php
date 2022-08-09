<?php

declare (strict_types=1);
namespace Rector\Nette\NodeFactory;

use PhpParser\Builder\Class_ as ClassBuilder;
use PhpParser\Builder\Property;
use PhpParser\Builder\TraitUse;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
/**
 * @see \Rector\Nette\Tests\NodeFactory\ClassWithPublicPropertiesFactory\ClassWithPublicPropertiesFactoryTest
 */
final class ClassWithPublicPropertiesFactory
{
    /**
     * @param string $fullyQualifiedName fully qualified name of new class
     * @param array<string, array{type: string, nullable?: bool}> $properties
     * @param string|null $parent fully qualified name of parent class
     * @param string[] $traits list of fully qualified names of traits used in class
     * @return \PhpParser\Node\Stmt\Namespace_|\PhpParser\Node\Stmt\Class_
     */
    public function createNode(string $fullyQualifiedName, array $properties, ?string $parent, array $traits)
    {
        $namespaceParts = \explode('\\', \ltrim($fullyQualifiedName, '\\'));
        $className = \array_pop($namespaceParts);
        $namespace = \implode('\\', $namespaceParts);
        $namespaceBuilder = null;
        if ($namespace !== '') {
            $namespaceBuilder = new \PhpParser\Builder\Namespace_($namespace);
        }
        $classBuilder = new ClassBuilder($className);
        if ($parent !== null && $parent !== '') {
            $classBuilder->extend($this->fixFullyQualifiedName($parent));
        }
        foreach ($traits as $trait) {
            $classBuilder->addStmt(new TraitUse($this->fixFullyQualifiedName($trait)));
        }
        foreach ($properties as $propertyName => $propertySettings) {
            $propertyType = $propertySettings['type'];
            $nullable = $propertySettings['nullable'] ?? \false;
            if ($nullable) {
                $propertyType = new NullableType($propertyType);
            }
            $propertyBuilder = new Property($propertyName);
            $propertyBuilder->setType($propertyType);
            $classBuilder->addStmt($propertyBuilder);
        }
        if ($namespaceBuilder !== null) {
            $namespaceBuilder->addStmt($classBuilder);
            return $namespaceBuilder->getNode();
        }
        return $classBuilder->getNode();
    }
    private function fixFullyQualifiedName(string $fullyQualifiedName) : string
    {
        return '\\' . \ltrim($fullyQualifiedName, '\\');
    }
}
