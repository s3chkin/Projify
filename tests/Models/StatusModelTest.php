<?php

namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use Status;

// Всички класове се зареждат от bootstrap.php

class StatusModelTest extends TestCase
{
    private $statusModel;
    private $testStatusId;

    protected function setUp(): void
    {
        $this->statusModel = new Status();
    }

    protected function tearDown(): void
    {
        if ($this->testStatusId) {
            $db = \Database::getConnection();
            $stmt = $db->prepare("DELETE FROM statuses WHERE id = ?");
            $stmt->execute([$this->testStatusId]);
        }
    }

    public function testGetAll()
    {
        $statuses = $this->statusModel->getAll();
        
        $this->assertIsArray($statuses);
    }

    public function testGetById()
    {
        // Създаване на тестов статус
        $db = \Database::getConnection();
        $stmt = $db->prepare("INSERT INTO statuses (name, order_index) VALUES (?, ?)");
        $stmt->execute(['Test Status', 999]);
        $this->testStatusId = $db->lastInsertId();
        
        $status = $this->statusModel->getById($this->testStatusId);
        
        $this->assertNotFalse($status);
        $this->assertEquals('Test Status', $status['name']);
    }

    public function testGetByName()
    {
        // Създаване на тестов статус
        $db = \Database::getConnection();
        $stmt = $db->prepare("INSERT INTO statuses (name, order_index) VALUES (?, ?)");
        $stmt->execute(['Unique Status Name', 998]);
        $this->testStatusId = $db->lastInsertId();
        
        $status = $this->statusModel->getByName('Unique Status Name');
        
        $this->assertNotFalse($status);
        $this->assertEquals('Unique Status Name', $status['name']);
    }

    public function testIsValidTransition()
    {
        // Създаване на два статуса
        $db = \Database::getConnection();
        $stmt = $db->prepare("INSERT INTO statuses (name, order_index) VALUES (?, ?)");
        $stmt->execute(['Status 1', 1]);
        $statusId1 = $db->lastInsertId();
        
        $stmt->execute(['Status 2', 2]);
        $statusId2 = $db->lastInsertId();
        
        // Преход между съседни статуси трябва да е валиден
        $isValid = $this->statusModel->isValidTransition($statusId1, $statusId2);
        
        $this->assertTrue($isValid);
        
        // Почистване
        $stmt = $db->prepare("DELETE FROM statuses WHERE id IN (?, ?)");
        $stmt->execute([$statusId1, $statusId2]);
    }
}

