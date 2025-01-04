<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM users WHERE username='$username'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                
                $_SESSION['username'] = $username;
                $_SESSION['logged_in'] = true;

                
                $token = bin2hex(random_bytes(16)); 
                setcookie('auth_token', $token, time() + (86400 * 30), "/");

                
                $sql = "UPDATE users SET auth_token='$token' WHERE username='$username'";
                $conn->query($sql);

                echo "Вход успешен!";
            } else {
                echo '<span style="color: red;">&times; неправильное имя пользователя или пароль</span>';
            }
        } else {
            echo '<span style="color: red;">&times; неправильное имя пользователя или пароль</span>';
        }
    } else {
        echo "Ошибка: данные формы не получены";
    }
} else {
    echo "Ошибка: неверный метод запроса";
}
?>