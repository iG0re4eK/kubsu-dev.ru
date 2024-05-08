<?php

    // Подключаем файл с настройками базы данных
    include 'db_credentials.php';
    
    // Проверяем, если форма была отправлена
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if(isset($_POST['enterAcc'])) {
            // Получаем введенные пользователем логин и пароль
            $login = $_POST['login'];
            $password = $_POST['password'];

            // Выполняем запрос к базе данных, чтобы найти пользователя с указанным логином
            $stmt = $db->prepare("SELECT * FROM Users WHERE username = ? AND password = ? AND admin = 1");
            $stmt->execute([$login, $password]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Если пользователь найден и хеш пароля совпадает, устанавливаем сессию для него
            if ($user) {
                if ($user['admin'] == 1) {
                    // Пользователь найден и является администратором
                    $_SESSION['username'] = $login;
                    header("Location: adminPage.php"); 
                    exit();
                } else {
                    // Пользователь найден, но не является администратором
                    $_SESSION['username'] = $login;
                    header("Location: form.php"); 
                    exit();
                }
            } else {
                // Пользователь не найден
                header("Location: index.php"); 
                exit();
            }
        } elseif(isset($_POST['createAcc'])) {
            header("Location: form.php");
            exit();
        }
        
    }