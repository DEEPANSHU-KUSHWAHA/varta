<?php
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function testAdditionWorks()
    {
        $this->assertEquals(4, 2 + 2);
    }

    public function testStringContains()
    {
        $this->assertStringContainsString('Varta', 'Welcome to Varta');
    }
}
