<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Project;
use User;

// Всички класове се зареждат от bootstrap.php

class ProjectModelTest extends TestCase
{
    private $projectModel;
    private $testUserId;
    private $testProjectId;

    protected function setUp(): void
    {
        $this->projectModel = new Project();
        $this->setupTestData();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestData();
    }

    private function setupTestData()
    {
        $db = \Database::getConnection();
        $stmt = $db->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
        $password = password_hash('test123', PASSWORD_DEFAULT);
        $stmt->execute(['Test', 'User', 'testproject@test.com', $password, 'user']);
        $this->testUserId = $db->lastInsertId();
    }

    private function cleanupTestData()
    {
        $db = \Database::getConnection();
        
        if ($this->testProjectId) {
            $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
            $stmt->execute([$this->testProjectId]);
        }

        if ($this->testUserId) {
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$this->testUserId]);
        }
    }

    public function testCreateProject()
    {
        $projectId = $this->projectModel->create('Test Project', $this->testUserId);
        
        $this->assertIsInt($projectId);
        $this->assertGreaterThan(0, $projectId);
        
        $this->testProjectId = $projectId;
    }

    public function testCreateDuplicateProject()
    {
        // Създаване на първи проект
        $projectId1 = $this->projectModel->create('Duplicate Test', $this->testUserId);
        $this->testProjectId = $projectId1;
        
        // Опит за създаване на дубликат
        $projectId2 = $this->projectModel->create('Duplicate Test', $this->testUserId);
        
        $this->assertFalse($projectId2);
    }

    public function testGetById()
    {
        $projectId = $this->projectModel->create('Test Project Get', $this->testUserId);
        $this->testProjectId = $projectId;
        
        $project = $this->projectModel->getById($projectId);
        
        $this->assertNotFalse($project);
        $this->assertEquals('Test Project Get', $project['name']);
        $this->assertEquals($this->testUserId, $project['owner_id']);
    }

    public function testGetByOwner()
    {
        $projectId = $this->projectModel->create('Test Project Owner', $this->testUserId);
        $this->testProjectId = $projectId;
        
        $projects = $this->projectModel->getByOwner($this->testUserId);
        
        $this->assertIsArray($projects);
        $this->assertGreaterThan(0, count($projects));
    }

    public function testUpdateProject()
    {
        $projectId = $this->projectModel->create('Test Project Update', $this->testUserId);
        $this->testProjectId = $projectId;
        
        $result = $this->projectModel->update($projectId, 'Updated Project Name');
        
        $this->assertTrue($result);
        
        $project = $this->projectModel->getById($projectId);
        $this->assertEquals('Updated Project Name', $project['name']);
    }

    public function testGetPaginatedByOwner()
    {
        // Създаване на няколко проекта
        for ($i = 1; $i <= 5; $i++) {
            $projectId = $this->projectModel->create("Test Project $i", $this->testUserId);
            if ($i == 1) {
                $this->testProjectId = $projectId;
            }
        }
        
        $projects = $this->projectModel->getPaginatedByOwner($this->testUserId, 1, 3);
        
        $this->assertIsArray($projects);
        $this->assertLessThanOrEqual(3, count($projects));
    }

    public function testGetCountByOwner()
    {
        // Създаване на проекти
        for ($i = 1; $i <= 3; $i++) {
            $projectId = $this->projectModel->create("Count Test $i", $this->testUserId);
            if ($i == 1) {
                $this->testProjectId = $projectId;
            }
        }
        
        $count = $this->projectModel->getCountByOwner($this->testUserId);
        
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(3, $count);
    }

    public function testCanBeCompleted()
    {
        $projectId = $this->projectModel->create('Test Project Complete', $this->testUserId);
        $this->testProjectId = $projectId;
        
        // Проект без задачи трябва да може да се завърши
        $canComplete = $this->projectModel->canBeCompleted($projectId);
        
        $this->assertTrue($canComplete);
    }

    public function testCompleteProject()
    {
        $projectId = $this->projectModel->create('Test Project Complete', $this->testUserId);
        $this->testProjectId = $projectId;
        
        $result = $this->projectModel->complete($projectId);
        
        $this->assertTrue($result);
        
        $project = $this->projectModel->getById($projectId);
        $this->assertEquals('completed', $project['status']);
    }
}

