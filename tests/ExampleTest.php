<?php

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * @covers \App\Calculator::add
     */
    public function testAdditionWorks()
    {
        $this->assertEquals(4, 2 + 2);
    }

    /**
     * @covers \App\StringHelper::contains
     */
    public function testStringContains()
    {
        $this->assertStringContainsString('foo', 'foobar');
    }
}
