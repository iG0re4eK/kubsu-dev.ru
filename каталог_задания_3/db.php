<?php
// Подключение к базе данных
$user = 'u67431'; //  логин
$pass = '6979325'; //  пароль
$db = new PDO('mysql:host=localhost;dbname=u67431', $user, $pass,
    [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); // Подключение к базе данных

// Проверка, был ли запрос методом POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Проверка наличия обязательных полей и их валидация
    $errors = [];

    if (empty($_POST['fullname'])) {
        $errors[] = 'Заполните ФИО.';
    }

    if (empty($_POST['phone'])) {
        $errors[] = 'Заполните телефон.';
    }

    if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Заполните корректный email.';
    }

    if (empty($_POST['dob'])) {
        $errors[] = 'Заполните дату рождения.';
    }

    if (empty($_POST['gender'])) {
        $errors[] = 'Выберите пол.';
    }

    if (empty($_POST['languages'])) {
        $errors[] = 'Выберите хотя бы один язык программирования.';
    }

    if (empty($_POST['bio'])) {
        $errors[] = 'Заполните биографию.';
    }

    // Если есть ошибки, выводим их
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo $error . '<br>';
        }
    } else {
        
        $stmt = $db->prepare("INSERT INTO Users (fullname, phone, email, dob, gender, bio) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['fullname'], $_POST['phone'], $_POST['email'], $_POST['dob'], $_POST['gender'], $_POST['bio']]);
        $user_id = $db->lastInsertId(); // Получаем идентификатор пользователя

        
        foreach ($_POST['languages'] as $language_id) {
            $stmt = $db->prepare("INSERT INTO UserProgrammingLanguages (user_id, lang_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $language_id]);
        }

        echo 'Данные успешно сохранены.';
    }
}
?>



