<?php

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Session;

require_once __DIR__ . '/../../app/core/Session.php';

class SessionTest extends TestCase
{
    protected function setUp(): void
    {
        // Стартиране на сесия за тестове
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Почистване на сесията
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        // Почистване на сесията
        $_SESSION = [];
    }

    public function testSetAndGet()
    {
        Session::set('test_key', 'test_value');
        
        $value = Session::get('test_key');
        
        $this->assertEquals('test_value', $value);
    }

    public function testHas()
    {
        Session::set('test_key', 'test_value');
        
        $this->assertTrue(Session::has('test_key'));
        $this->assertFalse(Session::has('non_existent_key'));
    }

    public function testDelete()
    {
        Session::set('test_key', 'test_value');
        Session::delete('test_key');
        
        $this->assertFalse(Session::has('test_key'));
        $this->assertNull(Session::get('test_key'));
    }

    public function testGetNonExistentKey()
    {
        $value = Session::get('non_existent_key');
        
        $this->assertNull($value);
    }

    public function testMultipleValues()
    {
        Session::set('key1', 'value1');
        Session::set('key2', 'value2');
        Session::set('key3', 'value3');
        
        $this->assertEquals('value1', Session::get('key1'));
        $this->assertEquals('value2', Session::get('key2'));
        $this->assertEquals('value3', Session::get('key3'));
    }
}



