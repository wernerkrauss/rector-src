<?php

declare(strict_types=1);

namespace Rector\Laravel\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\MethodName;
use Rector\RemovingStatic\NodeFactory\TestingClassMethodFactory;

/**
 * @see https://github.com/laravel/framework/issues/26450#issuecomment-449401202
 * @see https://github.com/laravel/framework/commit/055fe52dbb7169dc51bd5d5deeb05e8da9be0470#diff-76a649cb397ea47f5613459c335f88c1b68e5f93e51d46e9fb5308ec55ded221
 *
 * @see \Rector\Laravel\Tests\Rector\Class_\AddMockConsoleOutputFalseToConsoleTestsRector\AddMockConsoleOutputFalseToConsoleTestsRectorTest
 */
final class AddMockConsoleOutputFalseToConsoleTestsRector extends AbstractRector
{
    /**
     * @var PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;

    /**
     * @var TestingClassMethodFactory
     */
    private $testingClassMethodFactory;

    public function __construct(
        PropertyFetchAnalyzer $propertyFetchAnalyzer,
        TestingClassMethodFactory $testingClassMethodFactory
    ) {
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->testingClassMethodFactory = $testingClassMethodFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Add "$this->mockConsoleOutput = false"; to console tests that work with output content',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\TestCase;

final class SomeTest extends TestCase
{
    public function test(): void
    {
        $this->assertEquals('content', \trim((new Artisan())::output()));
    }
}
CODE_SAMPLE

                    ,
                    <<<'CODE_SAMPLE'
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\TestCase;

final class SomeTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->mockConsoleOutput = false;
    }

    public function test(): void
    {
        $this->assertEquals('content', \trim((new Artisan())::output()));
    }
}
CODE_SAMPLE
                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isObjectType($node, 'Illuminate\Foundation\Testing\TestCase')) {
            return null;
        }

        if (! $this->isTestingConsoleOutput($node)) {
            return null;
        }

        // has setUp with property `$mockConsoleOutput = false`
        if ($this->hasMockConsoleOutputFalse($node)) {
            return null;
        }

        $assign = $this->createAssign();

        $setUpClassMethod = $node->getMethod(MethodName::SET_UP);
        if ($setUpClassMethod === null) {
            $setUpClassMethod = $this->testingClassMethodFactory->createSetUpMethod($assign);
            $node->stmts = array_merge([$setUpClassMethod], (array) $node->stmts);
        } else {
            $setUpClassMethod->stmts = array_merge((array) $setUpClassMethod->stmts, [new Expression($assign)]);
        }

        return $node;
    }

    private function isTestingConsoleOutput(Class_ $class): bool
    {
        return (bool) $this->betterNodeFinder->findFirst((array) $class->stmts, function (Node $node): bool {
            return $this->isStaticCallNamed($node, 'Illuminate\Support\Facades\Artisan', 'output');
        });
    }

    private function hasMockConsoleOutputFalse(Class_ $class): bool
    {
        return (bool) $this->betterNodeFinder->findFirst($class, function (Node $node): bool {
            if ($node instanceof Assign) {
                if (! $this->propertyFetchAnalyzer->isLocalPropertyFetchName($node->var, 'mockConsoleOutput')) {
                    return false;
                }

                return $this->isFalse($node->expr);
            }

            return false;
        });
    }

    private function createAssign(): Assign
    {
        $propertyFetch = new PropertyFetch(new Variable('this'), 'mockConsoleOutput');
        return new Assign($propertyFetch, $this->createFalse());
    }
}
