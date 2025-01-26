<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login']) && isset($_POST['password'])) {
        $login = $_POST['login'];
        $password = hash('sha256', $_POST['password']);

        $stmt = $conn->prepare("SELECT * FROM users WHERE login = :login");
        $stmt->bindParam(':login', $login);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            echo '<span style="color: red;">&times; такой пользователь уже существует</span>';
        } else {
            $stmt = $conn->prepare("INSERT INTO users (login, password) VALUES (:login, :password)");
            $stmt->bindParam(':login', $login);
            $stmt->bindParam(':password', $password);
            if ($stmt->execute()) {
                echo "Регистрация успешна!";
            } else {
                echo "Ошибка: " . $stmt->errorInfo()[2];
            }
        }
    } else {
        echo "Ошибка: данные формы не получены";
    }
} else {
    echo "Ошибка: неверный метод запроса";
}
?>