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

    // Функция для проверки запрещенных символов
    function containsInvalidCharacters($input) {
        return preg_match('/[^\wа-яёА-ЯЁ\s.,!?-]/u', $input);
    }

    // Проверка ФИО
    if (empty($_POST['fullname']) || containsInvalidCharacters($_POST['fullname'])) {
        $errors['fullname'] = 'Заполните корректно ФИО (допустимы буквы, пробелы, тире, запятые, точки, вопросительные и восклицательные знаки)';
    }

    // Проверка телефона
    if (empty($_POST['phone']) || !preg_match('/^\+?\d{3}-?\d{3}-?\d{2}-?\d{2}$/', $_POST['phone'])) {
        $errors['phone'] = 'Заполните корректно телефон (допустим формат: +123-456-78-90)';
    }

    // Проверка email
    if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Заполните корректно email.';
    }

    // Проверка даты рождения
    if (empty($_POST['dob']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['dob'])) {
        $errors['dob'] = 'Заполните корректно дату рождения.';
    }

    // Проверка пола
    if (empty($_POST['gender'])) {
        $errors['gender'] = 'Выберите пол.';
    }

    // Проверка выбранных языков программирования
    if (empty($_POST['languages'])) {
        $errors['languages'] = 'Выберите хотя бы один язык программирования.';
    }

    // Проверка биографии
    if (empty($_POST['bio']) || containsInvalidCharacters($_POST['bio'])) {
        $errors['bio'] = 'Заполните корректно биографию (допустимы буквы, цифры, пробелы, запятые, точки, вопросительные и восклицательные знаки)';
    }

    // Если есть ошибки, сохраняем их в Cookies и перезагружаем страницу с формой
    if (!empty($errors)) {
        setcookie('form_errors', serialize($errors), 0, '/');
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        
        $stmt = $db->prepare("INSERT INTO Users (fullname, phone, email, dob, gender, bio) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['fullname'], $_POST['phone'], $_POST['email'], $_POST['dob'], $_POST['gender'], $_POST['bio']]);
        $user_id = $db->lastInsertId(); // Получаем идентификатор пользователя

        
        foreach ($_POST['languages'] as $language_id) {
            $stmt = $db->prepare("INSERT INTO UserProgrammingLanguages (user_id, lang_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $language_id]);
        }

        // Сохранение успешно введенных данных в Cookies на один год
        setcookie('form_data', serialize($_POST), time() + 31536000, '/');
        
        echo 'Данные успешно сохранены.';
    }
}

// Если есть ошибки из предыдущей отправки формы, выводим их
$errors = isset($_COOKIE['form_errors']) ? unserialize($_COOKIE['form_errors']) : [];
// Удаляем Cookies с ошибками
setcookie('form_errors', '', time() - 3600, '/');
?>
