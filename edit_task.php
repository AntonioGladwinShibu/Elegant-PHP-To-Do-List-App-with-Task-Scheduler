<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "todoapp";
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $heading = trim($_POST["heading"]);
    $description = trim($_POST["description"]);
    $days = isset($_POST["days"]) ? implode(",", $_POST["days"]) : '';
    if (!empty($heading) && !empty($description)) {
        $stmt = $conn->prepare("UPDATE tasks SET heading=?, description=?, days_set=? WHERE id=?");
        $stmt->bind_param("sssi", $heading, $description, $days, $id);
        $stmt->execute();
        $stmt->close();
        header("Location: todo.php");
        exit;
    }
}
$result = $conn->query("SELECT * FROM tasks WHERE id = $id");
if (!$result || $result->num_rows === 0) {
    echo "Task not found.";
    exit;
}
$task = $result->fetch_assoc();
$selected_days = explode(',', $task['days_set']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Task</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #fff3e0; padding: 40px; }
        .container { max-width: 700px; background: #fff; padding: 30px; margin: auto; border-radius: 16px; }
        h2 { background: #ff7043; color: white; padding: 15px; border-radius: 12px; text-align: center; }
        input[type="text"], textarea { width: 100%; padding: 12px; margin-top: 10px; border-radius: 8px; }
        button { background: #26c6da; color: white; padding: 12px 25px; border-radius: 8px; margin-top: 20px; }
        .back-link { text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Edit Task</h2>
    <form method="POST">
        <label>Heading:</label>
        <input type="text" name="heading" value="<?= htmlspecialchars($task['heading']) ?>" required>
        <label>Description:</label>
        <textarea name="description" rows="4" required><?= htmlspecialchars($task['description']) ?></textarea>
        <label>Days to Do:</label><br>
        <?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $day): ?>
            <label>
                <input type="checkbox" name="days[]" value="<?= $day ?>" <?= in_array($day, $selected_days) ? 'checked' : '' ?>> <?= $day ?>
            </label>
        <?php endforeach; ?>
        <br><br>
        <button type="submit">Update Task</button>
    </form>
    <div class="back-link"><a href="todo.php">← Back</a></div>
</div>
</body>
</html>
