<?php

namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use Comment;
use Task;
use Project;
use Status;
use User;

// Всички класове се зареждат от bootstrap.php

class CommentModelTest extends TestCase
{
    private $commentModel;
    private $taskModel;
    private $testUserId;
    private $testProjectId;
    private $testTaskId;
    private $testStatusId;

    protected function setUp(): void
    {
        $this->commentModel = new Comment();
        $this->taskModel = new Task();
        $this->setupTestData();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestData();
    }

    private function setupTestData()
    {
        $db = \Database::getConnection();
        
        // Създаване на потребител
        $stmt = $db->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
        $password = password_hash('test123', PASSWORD_DEFAULT);
        $stmt->execute(['Comment', 'Test', 'commenttest@test.com', $password, 'user']);
        $this->testUserId = $db->lastInsertId();

        // Създаване на проект
        $stmt = $db->prepare("INSERT INTO projects (name, owner_id) VALUES (?, ?)");
        $stmt->execute(['Comment Test Project', $this->testUserId]);
        $this->testProjectId = $db->lastInsertId();

        // Намиране на статус
        $statusModel = new Status();
        $statuses = $statusModel->getAll();
        if (!empty($statuses)) {
            $this->testStatusId = $statuses[0]['id'];
        } else {
            $stmt = $db->prepare("INSERT INTO statuses (name, order_index) VALUES (?, ?)");
            $stmt->execute(['To Do', 1]);
            $this->testStatusId = $db->lastInsertId();
        }

        // Създаване на задача
        $this->testTaskId = $this->taskModel->create(
            $this->testProjectId,
            'Comment Test Task',
            'Description',
            $this->testStatusId,
            null,
            null,
            null,
            null,
            $this->testUserId
        );
    }

    private function cleanupTestData()
    {
        $db = \Database::getConnection();
        
        if ($this->testTaskId) {
            $stmt = $db->prepare("DELETE FROM comments WHERE task_id = ?");
            $stmt->execute([$this->testTaskId]);
            
            $stmt = $db->prepare("DELETE FROM tasks WHERE id = ?");
            $stmt->execute([$this->testTaskId]);
        }

        if ($this->testProjectId) {
            $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
            $stmt->execute([$this->testProjectId]);
        }

        if ($this->testUserId) {
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$this->testUserId]);
        }
    }

    public function testCreate()
    {
        $commentId = $this->commentModel->create($this->testTaskId, $this->testUserId, 'Test comment text');
        
        $this->assertIsInt($commentId);
        $this->assertGreaterThan(0, $commentId);
    }

    public function testGetByTask()
    {
        // Създаване на коментари
        $commentId1 = $this->commentModel->create($this->testTaskId, $this->testUserId, 'Comment 1');
        $commentId2 = $this->commentModel->create($this->testTaskId, $this->testUserId, 'Comment 2');
        
        $comments = $this->commentModel->getByTask($this->testTaskId);
        
        $this->assertIsArray($comments);
        $this->assertGreaterThanOrEqual(2, count($comments));
    }

    public function testDelete()
    {
        // Създаване на коментар
        $commentId = $this->commentModel->create($this->testTaskId, $this->testUserId, 'Comment to delete');
        
        // Изтриване
        $result = $this->commentModel->delete($commentId);
        
        $this->assertTrue($result);
        
        // Проверка че е изтрит
        $comments = $this->commentModel->getByTask($this->testTaskId);
        $found = false;
        foreach ($comments as $comment) {
            if ($comment['id'] == $commentId) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);
    }
}

