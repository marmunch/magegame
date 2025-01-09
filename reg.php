<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login']) && isset($_POST['password'])) {
        $login = $_POST['login'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $sql = "SELECT * FROM Users WHERE login='$login'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo '<span style="color: red;">&times; такой пользователь уже существует</span>';
        } else {
            $sql = "INSERT INTO Users (login, password) VALUES ('$login', '$password')";
            if ($conn->query($sql) === TRUE) {
                echo "Регистрация успешна!";
            } else {
                echo "Ошибка: " . $sql . "<br>" . $conn->error;
            }
        }
    } else {
        echo "Ошибка: данные формы не получены";
    }
} else {
    echo "Ошибка: неверный метод запроса";
}
?>