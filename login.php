<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login']) && isset($_POST['password'])) {
        $login = $_POST['login'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM Users WHERE login='$login'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['login'] = $login;
                $_SESSION['logged_in'] = true;

                
                setcookie('login', $login, time() + (86400 * 30), "/");

                $token = bin2hex(random_bytes(16));
                setcookie('auth_token', $token, time() + (86400 * 30), "/");

                $sql = "UPDATE Users SET auth_token='$token' WHERE login='$login'";
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
