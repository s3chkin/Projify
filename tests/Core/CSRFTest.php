<?php

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use CSRF;
use Session;

require_once __DIR__ . '/../../app/core/Session.php';
require_once __DIR__ . '/../../app/core/CSRF.php';

class CSRFTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testGenerateToken()
    {
        $token = CSRF::generateToken();
        
        $this->assertIsString($token);
        $this->assertEquals(64, strlen($token)); // 32 bytes = 64 hex characters
    }

    public function testGetToken()
    {
        $token1 = CSRF::getToken();
        $token2 = CSRF::getToken();
        
        // Токенът трябва да е същият при повторно извикване
        $this->assertEquals($token1, $token2);
    }

    public function testValidateToken()
    {
        $token = CSRF::generateToken();
        
        $isValid = CSRF::validateToken($token);
        
        $this->assertTrue($isValid);
    }

    public function testValidateInvalidToken()
    {
        CSRF::generateToken();
        
        $isValid = CSRF::validateToken('invalid_token');
        
        $this->assertFalse($isValid);
    }

    public function testValidateEmptyToken()
    {
        $isValid = CSRF::validateToken('');
        
        $this->assertFalse($isValid);
    }

    public function testGetTokenField()
    {
        $field = CSRF::getTokenField();
        
        $this->assertStringContainsString('<input', $field);
        $this->assertStringContainsString('name="csrf_token"', $field);
        $this->assertStringContainsString('type="hidden"', $field);
    }

    public function testTokenUniqueness()
    {
        $token1 = CSRF::generateToken();
        $_SESSION = []; // Почистване
        $token2 = CSRF::generateToken();
        
        // Различните токени трябва да са различни
        $this->assertNotEquals($token1, $token2);
    }
}

