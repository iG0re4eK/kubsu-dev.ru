<?php
include('db_credentials.php');

try {
    $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD,
        [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    echo 'Подключение не удалось: ' . $e->getMessage();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];

    $nameRegex = '/^[а-яА-ЯёЁa-zA-Z]+ [а-яА-ЯёЁa-zA-Z]+ ?[а-яА-ЯёЁa-zA-Z]+$/u';
    $phoneRegex = '/^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$/';
    $emailRegex = '/^(([^<>()[\].,;:\s@"]+(\.[^<>()[\].,;:\s@"]+)*)|(".+"))@(([^<>()[\].,;:\s@"]+\.)+[^<>()[\].,;:\s@"]{2,})$/';

    if (empty($_POST['fullname']) || !preg_match($nameRegex, $_POST['fullname'])) {
        $errors['fullname'] = 'Заполните корректно ФИО (допустимы буквы, пробелы, тире, запятые, точки, вопросительные и восклицательные знаки)';
    }

    if (empty($_POST['phone']) || !preg_match($phoneRegex, $_POST['phone'])) {
        $errors['phone'] = 'Заполните корректно телефон (допустим формат: +123-456-78-90)';
    }

    if (empty($_POST['email']) || !preg_match($emailRegex, $_POST['email'])) {
        $errors['email'] = 'Заполните корректно email.';
    }

    if (empty($_POST['dob']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['dob'])) {
        $errors['dob'] = 'Заполните корректно дату рождения.';
    }

    if (empty($_POST['gender'])) {
        $errors['gender'] = 'Выберите пол.';
    }

    if (empty($_POST['languages'])) {
        $errors['languages'] = 'Выберите хотя бы один язык программирования.';
    }

    if (empty($_POST['bio']) || !preg_match('/^[а-яА-ЯёЁa-zA-Z0-9\s.,!?-]+$/', $_POST['bio'])) {
        $errors['bio'] = 'Заполните корректно биографию (допустимы буквы, цифры, пробелы, запятые, точки, вопросительные и восклицательные знаки)';
    }

    if (!empty($errors)) {
        setcookie('form_errors', serialize($errors), 0, '/');
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        if (isset($_POST['gender'])) {
            setcookie('gender', $_POST['gender'], time() + (365 * 24 * 60 * 60), '/'); 
        }
        if (isset($_POST['dob'])) {
            setcookie('dob', $_POST['dob'], time() + (365 * 24 * 60 * 60), '/'); 
        }

        setcookie('form_data', serialize($_POST), time() + (365 * 24 * 60 * 60), '/'); 
        
        $stmt = $db->prepare("INSERT INTO Users (fullname, phone, email, dob, gender, bio) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['fullname'], $_POST['phone'], $_POST['email'], $_POST['dob'], $_POST['gender'], $_POST['bio']]);
        $user_id = $db->lastInsertId(); 

        foreach ($_POST['languages'] as $language_id) {
            $stmt = $db->prepare("INSERT INTO UserProgrammingLanguages (user_id, lang_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $language_id]);
        }

        echo 'Данные успешно сохранены.';
    }
}

$errors = isset($_COOKIE['form_errors']) ? unserialize($_COOKIE['form_errors']) : [];
setcookie('form_errors', '', time() - 3600, '/');
?>
