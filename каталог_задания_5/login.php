<?php

    // Подключаем файл с настройками базы данных
    include 'db_credentials.php';
    try {
        $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD,
            [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    } catch (PDOException $e) {
        echo 'Подключение не удалось: ' . $e->getMessage();
        exit;
    }
    // Проверяем, если форма была отправлена
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Получаем введенные пользователем логин и пароль
        $login = $_POST['login'];
        $password = $_POST['password'];

        // Выполняем запрос к базе данных, чтобы найти пользователя с указанным логином
        $stmt = $db->prepare("SELECT * FROM Users WHERE username = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Если пользователь найден и хеш пароля совпадает, устанавливаем сессию для него
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['username'] = $login;
            header("Location: form.php"); 
            exit();
        } else {
            header("Location: index.php"); 
            exit();
        }
    }