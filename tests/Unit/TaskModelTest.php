<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Task;
use Project;
use Status;
use User;

// Всички класове се зареждат от bootstrap.php

class TaskModelTest extends TestCase
{
    private $taskModel;
    private $projectModel;
    private $statusModel;
    private $userModel;
    private $testProjectId;
    private $testUserId;
    private $testStatusId;

    protected function setUp(): void
    {
        $this->taskModel = new Task();
        $this->projectModel = new Project();
        $this->statusModel = new Status();
        $this->userModel = new User();
        
        // Създаване на тестови данни
        $this->setupTestData();
    }

    protected function tearDown(): void
    {
        // Почистване на тестовите данни
        $this->cleanupTestData();
    }

    private function setupTestData()
    {
        // Създаване на тестов потребител
        $db = \Database::getConnection();
        $stmt = $db->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
        $password = password_hash('test123', PASSWORD_DEFAULT);
        $stmt->execute(['Test', 'User', 'test@test.com', $password, 'user']);
        $this->testUserId = $db->lastInsertId();

        // Създаване на тестов проект
        $stmt = $db->prepare("INSERT INTO projects (name, owner_id) VALUES (?, ?)");
        $stmt->execute(['Test Project', $this->testUserId]);
        $this->testProjectId = $db->lastInsertId();

        // Намиране на първия статус
        $statuses = $this->statusModel->getAll();
        if (!empty($statuses)) {
            $this->testStatusId = $statuses[0]['id'];
        } else {
            // Създаване на тестов статус
            $stmt = $db->prepare("INSERT INTO statuses (name, order_index) VALUES (?, ?)");
            $stmt->execute(['To Do', 1]);
            $this->testStatusId = $db->lastInsertId();
        }
    }

    private function cleanupTestData()
    {
        $db = \Database::getConnection();
        
        // Изтриване на задачи
        if ($this->testProjectId) {
            $stmt = $db->prepare("DELETE FROM tasks WHERE project_id = ?");
            $stmt->execute([$this->testProjectId]);
        }

        // Изтриване на проект
        if ($this->testProjectId) {
            $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
            $stmt->execute([$this->testProjectId]);
        }

        // Изтриване на потребител
        if ($this->testUserId) {
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$this->testUserId]);
        }
    }

    public function testCreateTask()
    {
        $taskId = $this->taskModel->create(
            $this->testProjectId,
            'Test Task',
            'Test Description',
            $this->testStatusId,
            null,
            '2024-01-01',
            '2024-01-31',
            3,
            $this->testUserId
        );

        $this->assertIsInt($taskId);
        $this->assertGreaterThan(0, $taskId);
    }

    public function testCreateTaskWithInvalidDates()
    {
        $taskId = $this->taskModel->create(
            $this->testProjectId,
            'Test Task',
            'Test Description',
            $this->testStatusId,
            null,
            '2024-01-31',
            '2024-01-01', // Крайната дата е преди началната
            null,
            $this->testUserId
        );

        $this->assertFalse($taskId);
    }

    public function testCreateTaskWithInvalidPriority()
    {
        $taskId = $this->taskModel->create(
            $this->testProjectId,
            'Test Task',
            'Test Description',
            $this->testStatusId,
            null,
            null,
            null,
            5, // Невалиден приоритет (трябва да е 1-4)
            $this->testUserId
        );

        $this->assertFalse($taskId);
    }

    public function testGetByProject()
    {
        // Създаване на задача
        $taskId = $this->taskModel->create(
            $this->testProjectId,
            'Test Task',
            'Test Description',
            $this->testStatusId,
            null,
            null,
            null,
            null,
            $this->testUserId
        );

        // Извличане на задачите
        $tasks = $this->taskModel->getByProject($this->testProjectId);

        $this->assertIsArray($tasks);
        $this->assertGreaterThan(0, count($tasks));
        
        $found = false;
        foreach ($tasks as $task) {
            if ($task['id'] == $taskId) {
                $found = true;
                $this->assertEquals('Test Task', $task['title']);
                break;
            }
        }
        $this->assertTrue($found);
    }

    public function testGetById()
    {
        // Създаване на задача
        $taskId = $this->taskModel->create(
            $this->testProjectId,
            'Test Task',
            'Test Description',
            $this->testStatusId,
            null,
            null,
            null,
            null,
            $this->testUserId
        );

        // Извличане на задачата
        $task = $this->taskModel->getById($taskId);

        $this->assertNotFalse($task);
        $this->assertEquals('Test Task', $task['title']);
        $this->assertEquals('Test Description', $task['description']);
        $this->assertEquals($this->testProjectId, $task['project_id']);
    }

    public function testUpdateTask()
    {
        // Създаване на задача
        $taskId = $this->taskModel->create(
            $this->testProjectId,
            'Test Task',
            'Test Description',
            $this->testStatusId,
            null,
            null,
            null,
            null,
            $this->testUserId
        );

        // Обновяване на задачата
        $result = $this->taskModel->update(
            $taskId,
            'Updated Task',
            'Updated Description',
            $this->testStatusId,
            null,
            '2024-02-01',
            '2024-02-28',
            2
        );

        $this->assertTrue($result);

        // Проверка на обновената задача
        $task = $this->taskModel->getById($taskId);
        $this->assertEquals('Updated Task', $task['title']);
        $this->assertEquals('Updated Description', $task['description']);
        $this->assertEquals(2, $task['priority']);
    }

    public function testDeleteTask()
    {
        // Създаване на задача
        $taskId = $this->taskModel->create(
            $this->testProjectId,
            'Test Task',
            'Test Description',
            $this->testStatusId,
            null,
            null,
            null,
            null,
            $this->testUserId
        );

        // Изтриване на задачата
        $result = $this->taskModel->delete($taskId);

        $this->assertTrue($result);

        // Проверка че задачата е изтрита
        $task = $this->taskModel->getById($taskId);
        $this->assertFalse($task);
    }

    public function testGetByAssignee()
    {
        // Създаване на задача с назначен потребител
        $taskId = $this->taskModel->create(
            $this->testProjectId,
            'Test Task',
            'Test Description',
            $this->testStatusId,
            $this->testUserId,
            null,
            null,
            null,
            $this->testUserId
        );

        // Извличане на задачите на потребителя
        $tasks = $this->taskModel->getByAssignee($this->testUserId);

        $this->assertIsArray($tasks);
        $this->assertGreaterThan(0, count($tasks));
    }

    public function testGetOverdue()
    {
        // Създаване на просрочена задача
        $taskId = $this->taskModel->create(
            $this->testProjectId,
            'Overdue Task',
            'Test Description',
            $this->testStatusId,
            null,
            '2024-01-01',
            '2020-01-01', // Просрочена дата
            null,
            $this->testUserId
        );

        // Извличане на просрочените задачи
        $overdueTasks = $this->taskModel->getOverdue($this->testProjectId);

        $this->assertIsArray($overdueTasks);
    }
}

