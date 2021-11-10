<?php


use App\Container;

class ContainerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     * @testdox Esto prueba que siempre sea una unica instancia
     */
    public function caseOne()
    {
        $container = Container::getInstance();
        $container2 = Container::getInstance();

        $this->assertInstanceOf(Container::class, $container);
        $this->assertSame($container, $container2);
    }

    /**
     * @test
     * @testdox retorne un closure
     */
    public function caseTwo()
    {
        $container = Container::getInstance();

        $container->bind("closure", function () {
            return "result";
        });

        $this->assertEquals("result", $container->make('closure'));
    }

    /**
     * @test
     */
    public function caseThree()
    {
        $container = Container::getInstance();
        $container->bind("foo", "Foo");
        $container->bind("barInterface", function () {
            $fooBar = new FooBar();
            $baz = new Baz($fooBar);
            return new Bar($baz);
        } );

        $this->assertInstanceOf("Foo", $container->make('foo'));
    }
}


class Foo {
    public function __construct(BarInterface $bar, Baz $baz)
    {
        //
    }
}

class Bar implements BarInterface{
    public function __construct(Baz $baz)
    {
        //
    }

    public function one()
    {
        // TODO: Implement one() method.
    }
}

class BarTwo implements BarInterface {
    public function __construct(FooBar $baz)
    {
        //
    }

    public function one()
    {
        // TODO: Implement one() method.
    }
}

class Baz {
    public function __construct(FooBar $fooBar, $params = [])
    {
        //
    }
}

class FooBar {

}

interface BarInterface {
    public function one();
}

