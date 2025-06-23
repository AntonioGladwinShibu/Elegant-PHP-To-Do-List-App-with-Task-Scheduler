<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "todoapp";
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $heading = trim($_POST["heading"]);
    $description = trim($_POST["description"]);
    $days = isset($_POST["days"]) ? implode(",", $_POST["days"]) : '';
    $is_enabled = 1;
    if (!empty($heading) && !empty($description)) {
        $stmt = $conn->prepare("INSERT INTO tasks (heading, description, days_set, is_enabled) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $heading, $description, $days, $is_enabled);
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM tasks WHERE id = $id");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Mark as Done
if (isset($_GET['done'])) {
    $id = intval($_GET['done']);
    $conn->query("UPDATE tasks SET status='done' WHERE id = $id");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Toggle
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $current = $conn->query("SELECT is_enabled FROM tasks WHERE id = $id")->fetch_assoc()['is_enabled'];
    $new = $current ? 0 : 1;
    $conn->query("UPDATE tasks SET is_enabled = $new WHERE id = $id");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$tasks_result = $conn->query("SELECT * FROM tasks ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TODO LIST</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f1f1f1; padding: 30px; }
        .container { max-width: 750px; margin: auto; background: #fff; padding: 30px; border-radius: 16px; }
        h2 { text-align: center; background: #3949ab; color: white; padding: 12px; border-radius: 10px; }
        input[type="text"], textarea { width: 100%; padding: 10px; margin: 10px 0; }
        button { background: #1e88e5; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; }
        .task { border-left: 5px solid #8e24aa; padding: 15px; background: #f3e5f5; margin-top: 20px; border-radius: 10px; }
        .task.done { text-decoration: line-through; color: #888; }
        .actions a { margin-right: 10px; padding: 6px 10px; text-decoration: none; border-radius: 6px; }
        .done-btn { background: #4caf50; color: white; }
        .delete-btn { background: #f44336; color: white; }
        .toggle-btn { background: #ff9800; color: white; }
        .edit-btn { background: #00bcd4; color: white; }
    </style>
</head>
<body>
<div class="container">
    <h2>TO-DO LIST</h2>
    <form method="POST">
        <input type="text" name="heading" placeholder="Task Heading" required>
        <textarea name="description" rows="4" placeholder="Task Description" required></textarea>
        <label>Days to Do:</label><br>
        <?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $day): ?>
            <label><input type="checkbox" name="days[]" value="<?= $day ?>"> <?= $day ?></label>
        <?php endforeach; ?>
        <br><br>
        <button type="submit">Add Task</button>
    </form>

    <?php if ($tasks_result && $tasks_result->num_rows > 0): ?>
        <?php while ($task = $tasks_result->fetch_assoc()): ?>
            <div class="task <?= $task['status'] === 'done' ? 'done' : '' ?>" style="<?= !$task['is_enabled'] ? 'opacity: 0.5;' : '' ?>">
                <h4><?= htmlspecialchars($task["heading"]) ?></h4>
                <p><?= nl2br(htmlspecialchars($task["description"])) ?></p>
                <div>Days: <?= htmlspecialchars($task["days_set"]) ?></div>
                <small>Created at: <?= $task["created_at"] ?></small><br>
                <div class="actions">
                    <?php if ($task['status'] === 'pending' && $task['is_enabled']): ?>
                        <a class="done-btn" href="?done=<?= $task['id'] ?>">Mark as Done</a>
                    <?php endif; ?>
                    <a class="edit-btn" href="edit_task.php?id=<?= $task['id'] ?>">Edit</a>
                    <a class="toggle-btn" href="?toggle=<?= $task['id'] ?>"><?= $task['is_enabled'] ? 'Disable' : 'Enable' ?></a>
                    <a class="delete-btn" href="?delete=<?= $task['id'] ?>" onclick="return confirm('Delete this task?');">Delete</a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No tasks added yet.</p>
    <?php endif; ?>
</div>
</body>
</html>
