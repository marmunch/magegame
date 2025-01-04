<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $sql = "SELECT * FROM users WHERE username='$username'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo '<span style="color: red;">&times; такой пользователь уже существует</span>';
        } else {
            $sql = "INSERT INTO users (username, password) VALUES ('$username', '$password')";
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