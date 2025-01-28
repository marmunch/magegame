<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login']) && isset($_POST['password'])) {
        $login = $_POST['login'];
        $password = hash('sha256', $_POST['password']);

        $stmt = $conn->prepare("SELECT * FROM Users WHERE login = :login");
        $stmt->bindParam(':login', $login);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            if ($password === $result['password']) {
                $_SESSION['login'] = $login;
                $_SESSION['logged_in'] = true;

                setcookie('login', $login, time() + (86400 * 30), "/");

                $token = bin2hex(random_bytes(16));
                setcookie('auth_token', $token, time() + (86400 * 30), "/");

                $stmt = $conn->prepare("UPDATE Users SET auth_token = :token WHERE login = :login");
                $stmt->bindParam(':token', $token);
                $stmt->bindParam(':login', $login);
                $stmt->execute();

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