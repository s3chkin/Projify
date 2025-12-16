<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Auth;

// Всички класове се зареждат от bootstrap.php

class AuthModelTest extends TestCase
{
    private $authModel;
    private $testUserId;
    private $testEmail = 'authtest@test.com';
    private $testPassword = 'test123456';

    protected function setUp(): void
    {
        $this->authModel = new Auth();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestData();
    }

    private function cleanupTestData()
    {
        if ($this->testUserId) {
            $db = \Database::getConnection();
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$this->testUserId]);
        }
    }

    public function testRegister()
    {
        $userId = $this->authModel->register(
            'Auth',
            'Test',
            $this->testEmail,
            $this->testPassword,
            'user'
        );
        
        $this->assertIsInt($userId);
        $this->assertGreaterThan(0, $userId);
        
        $this->testUserId = $userId;
    }

    public function testRegisterDuplicateEmail()
    {
        // Създаване на първи потребител
        $userId1 = $this->authModel->register(
            'Auth',
            'Test',
            $this->testEmail,
            $this->testPassword,
            'user'
        );
        $this->testUserId = $userId1;
        
        // Опит за създаване на дубликат
        $this->expectException(\PDOException::class);
        $userId2 = $this->authModel->register(
            'Auth',
            'Test2',
            $this->testEmail, // Същият имейл
            $this->testPassword,
            'user'
        );
    }

    public function testLogin()
    {
        // Създаване на потребител
        $userId = $this->authModel->register(
            'Auth',
            'Test',
            $this->testEmail,
            $this->testPassword,
            'user'
        );
        $this->testUserId = $userId;
        
        // Влизане
        $user = $this->authModel->login($this->testEmail, $this->testPassword);
        
        $this->assertNotFalse($user);
        $this->assertEquals($this->testEmail, $user['email']);
        $this->assertEquals('Auth', $user['first_name']);
    }

    public function testLoginWithWrongPassword()
    {
        // Създаване на потребител
        $userId = $this->authModel->register(
            'Auth',
            'Test',
            $this->testEmail,
            $this->testPassword,
            'user'
        );
        $this->testUserId = $userId;
        
        // Опит за влизане с грешна парола
        $user = $this->authModel->login($this->testEmail, 'wrongpassword');
        
        $this->assertFalse($user);
    }

    public function testLoginWithNonExistentEmail()
    {
        $user = $this->authModel->login('nonexistent@test.com', 'password');
        
        $this->assertFalse($user);
    }

    public function testGetUserById()
    {
        // Създаване на потребител
        $userId = $this->authModel->register(
            'Auth',
            'Test',
            $this->testEmail,
            $this->testPassword,
            'user'
        );
        $this->testUserId = $userId;
        
        // Извличане на потребителя
        $user = $this->authModel->getUserById($userId);
        
        $this->assertNotFalse($user);
        $this->assertEquals($this->testEmail, $user['email']);
    }

    public function testGetUserByEmail()
    {
        // Създаване на потребител
        $userId = $this->authModel->register(
            'Auth',
            'Test',
            $this->testEmail,
            $this->testPassword,
            'user'
        );
        $this->testUserId = $userId;
        
        // Извличане на потребителя по имейл
        $user = $this->authModel->getUserByEmail($this->testEmail);
        
        $this->assertNotFalse($user);
        $this->assertEquals($userId, $user['id']);
    }
}

