<?php declare(strict_types=1);

namespace ApiGen\Tests\Templating\Filters\Helpers;

use ApiGen\Contracts\Parser\Reflection\Behavior\NamedInterface;
use ApiGen\Contracts\Parser\Reflection\ClassReflectionInterface;
use ApiGen\Contracts\Parser\Reflection\ConstantReflectionInterface;
use ApiGen\Contracts\Parser\Reflection\ElementReflectionInterface;
use ApiGen\Contracts\Parser\Reflection\FunctionReflectionInterface;
use ApiGen\Contracts\Parser\Reflection\MethodReflectionInterface;
use ApiGen\Contracts\Parser\Reflection\PropertyReflectionInterface;
use ApiGen\Templating\Filters\Helpers\ElementLinkFactory;
use ApiGen\Templating\Filters\Helpers\ElementUrlFactory;
use ApiGen\Templating\Filters\Helpers\LinkBuilder;
use Mockery;
use PHPUnit\Framework\TestCase;

class ElementLinkFactoryTest extends TestCase
{

    /**
     * @var ElementLinkFactory
     */
    private $elementLinkFactory;


    protected function setUp(): void
    {
        $this->elementLinkFactory = new ElementLinkFactory($this->getElementUrlFactoryMock(), new LinkBuilder);
    }


    public function testCreateForElementClass(): void
    {
        $reflectionClass = Mockery::mock(ClassReflectionInterface::class);
        $reflectionClass->shouldReceive('getName')->andReturn('SomeClass');
        $reflectionClass->shouldReceive('getDeclaringClassName')->andReturn('declaringClass');

        $this->assertSame(
            '<a href="class-link-SomeClass">SomeClass</a>',
            $this->elementLinkFactory->createForElement($reflectionClass)
        );
    }


    public function testCreateForFunction(): void
    {
        $reflectionFunction = Mockery::mock(FunctionReflectionInterface::class);
        $reflectionFunction->shouldReceive('getName')->andReturn('getSome');
        $reflectionFunction->shouldReceive('getDeclaringClassName')->andReturn('DeclaringClass');

        $this->assertSame(
            '<a href="function-link-getSome">getSome()</a>',
            $this->elementLinkFactory->createForElement($reflectionFunction)
        );
    }


    public function testCreateForConstant(): void
    {
        $reflectionConstant = Mockery::mock(ConstantReflectionInterface::class);
        $reflectionConstant->shouldReceive('getName')->andReturn('SOME_CONSTANT');
        $reflectionConstant->shouldReceive('getDeclaringClassName')->andReturnNull();
        $reflectionConstant->shouldReceive('inNamespace')->andReturn(false);

        $this->assertSame(
            '<a href="constant-link-SOME_CONSTANT"><b>SOME_CONSTANT</b></a>',
            $this->elementLinkFactory->createForElement($reflectionConstant)
        );
    }


    public function testCreateForConstantInClass(): void
    {
        $reflectionConstant = Mockery::mock(ConstantReflectionInterface::class);
        $reflectionConstant->shouldReceive('getName')->andReturn('SOME_CONSTANT');
        $reflectionConstant->shouldReceive('getDeclaringClassName')->andReturn('DeclaringClass');

        $this->assertSame(
            '<a href="constant-link-SOME_CONSTANT">DeclaringClass::<b>SOME_CONSTANT</b></a>',
            $this->elementLinkFactory->createForElement($reflectionConstant)
        );
    }


    public function testCreateForElementConstantInNamespace(): void
    {
        $reflectionConstant = Mockery::mock(ConstantReflectionInterface::class);
        $reflectionConstant->shouldReceive('getName')->andReturn('SOME_CONSTANT');
        $reflectionConstant->shouldReceive('getShortName')->andReturn('SHORT_SOME_CONSTANT');
        $reflectionConstant->shouldReceive('getDeclaringClassName')->andReturnNull();
        $reflectionConstant->shouldReceive('inNamespace')->andReturn(true);
        $reflectionConstant->shouldReceive('getNamespaceName')->andReturn('Namespace');

        $this->assertSame(
            '<a href="constant-link-SOME_CONSTANT">Namespace\<b>SHORT_SOME_CONSTANT</b></a>',
            $this->elementLinkFactory->createForElement($reflectionConstant)
        );
    }


    public function testCreateForProperty(): void
    {
        $reflectionProperty = Mockery::mock(PropertyReflectionInterface::class);
        $reflectionProperty->shouldReceive('getName')->andReturn('property');
        $reflectionProperty->shouldReceive('getDeclaringClassName')->andReturn('SomeClass');

        $this->assertSame(
            '<a href="property-link-property">SomeClass::<var>$property</var></a>',
            $this->elementLinkFactory->createForElement($reflectionProperty)
        );
    }


    public function testCreateForMethod(): void
    {
        $reflectionMethod = Mockery::mock(MethodReflectionInterface::class);
        $reflectionMethod->shouldReceive('getName')->andReturn('method');
        $reflectionMethod->shouldReceive('getDeclaringClassName')->andReturn('SomeClass');

        $this->assertSame(
            '<a href="method-link-method">SomeClass::method()</a>',
            $this->elementLinkFactory->createForElement($reflectionMethod)
        );
    }


    /**
     * @expectedException \UnexpectedValueException
     */
    public function testCreateForElementOfUnspecificType(): void
    {
        $reflectionElement = Mockery::mock(ElementReflectionInterface::class);
        $this->elementLinkFactory->createForElement($reflectionElement);
    }


    public function testCreateForElementWithCssClasses(): void
    {
        $reflectionClass = Mockery::mock(ClassReflectionInterface::class);
        $reflectionClass->shouldReceive('getName')->andReturn('SomeClass');
        $reflectionClass->shouldReceive('getDeclaringClassName')->andReturn('someElement');

        $this->assertSame(
            '<a href="class-link-SomeClass" class="deprecated">SomeClass</a>',
            $this->elementLinkFactory->createForElement($reflectionClass, ['deprecated'])
        );
    }


    private function getElementUrlFactoryMock(): Mockery\MockInterface
    {
        $elementUrlFactoryMock = Mockery::mock(ElementUrlFactory::class);
        $elementUrlFactoryMock->shouldReceive('createForClass')->andReturnUsing(
            function (NamedInterface $reflectionClass) {
                return 'class-link-' . $reflectionClass->getName();
            }
        );
        $elementUrlFactoryMock->shouldReceive('createForConstant')->andReturnUsing(
            function (NamedInterface $reflectionConstant) {
                return 'constant-link-' . $reflectionConstant->getName();
            }
        );
        $elementUrlFactoryMock->shouldReceive('createForFunction')->andReturnUsing(
            function (NamedInterface $reflectionFunction) {
                return 'function-link-' . $reflectionFunction->getName();
            }
        );
        $elementUrlFactoryMock->shouldReceive('createForProperty')->andReturnUsing(
            function (NamedInterface $reflectionProperty) {
                return 'property-link-' . $reflectionProperty->getName();
            }
        );
        $elementUrlFactoryMock->shouldReceive('createForMethod')->andReturnUsing(
            function (NamedInterface $reflectionMethod) {
                return 'method-link-' . $reflectionMethod->getName();
            }
        );
        return $elementUrlFactoryMock;
    }
}
