<?php
session_start();
include 'db.php';

// Проверка куки
if (isset($_COOKIE['auth_token'])) {
    $token = $_COOKIE['auth_token'];

    $sql = "SELECT * FROM users WHERE auth_token='$token'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['username'] = $row['username'];
        $_SESSION['logged_in'] = true;
    } else {
        // Удаление недействительной куки
        setcookie('auth_token', '', time() - 3600, "/");
        header("Location: login.html"); // Перенаправление на страницу входа
        exit();
    }
} else {
    header("Location: login.html"); // Перенаправление на страницу входа
    exit();
}

// Получение данных из сессии
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Защищенная страница</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
    <div class="wrapper">
        <h1>Добро пожаловать, <?php echo htmlspecialchars($username); ?>!</h1>
        <p>Это защищенная страница. Только авторизованные пользователи могут видеть это содержимое.</p>
        <a href="logout.php">Выйти</a>
    </div>
</body>
</html>