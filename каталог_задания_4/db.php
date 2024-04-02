<?php
include('db_credentials.php');

try {
    $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD,
        [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    echo 'Подключение не удалось: ' . $e->getMessage();
    exit;
}

// Функция для установки куки с сообщением об ошибке
function set_error_cookie($field, $message) {
    setcookie('form_error_' . $field, $message, 0, '/');
}

// Функция для установки куки с данными формы
function set_form_data_cookie($field, $value) {
    setcookie('form_data_' . $field, $value, time() + (365 * 24 * 60 * 60), '/');
}

// Функция для проверки данных формы и установки куков с ошибками и данными формы
function validate_and_set_cookies($field, $value, $regex, $error_message) {
    if (!preg_match($regex, $value)) {
        set_error_cookie($field, $error_message);
        set_form_data_cookie($field, $value);
        return false;
    }
    return true;
}

// Проверка, был ли запрос методом POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Проверка и сохранение ФИО
    $fullname = $_POST['fullname'];
    $fullname_regex = '/^[а-яА-ЯёЁa-zA-Z]+ [а-яА-ЯёЁa-zA-Z]+ ?[а-яА-ЯёЁa-zA-Z]+$/u';
    $fullname_error_message = 'Заполните корректно ФИО (допустимы буквы, пробелы, тире, запятые, точки, вопросительные и восклицательные знаки)';
    $fullname_valid = validate_and_set_cookies('fullname', $fullname, $fullname_regex, $fullname_error_message);

    // Проверка и сохранение телефона
    $phone = $_POST['phone'];
    $phone_regex = '/^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$/';
    $phone_error_message = 'Заполните корректно телефон (допустим формат: +123-456-78-90)';
    $phone_valid = validate_and_set_cookies('phone', $phone, $phone_regex, $phone_error_message);

    // Проверка и сохранение email
    $email = $_POST['email'];
    $email_regex = '/^(([^<>()[\].,;:\s@"]+(\.[^<>()[\].,;:\s@"]+)*)|(".+"))@(([^<>()[\].,;:\s@"]+\.)+[^<>()[\].,;:\s@"]{2,})$/';
    $email_error_message = 'Заполните корректно email.';
    $email_valid = validate_and_set_cookies('email', $email, $email_regex, $email_error_message);

    // Далее продолжайте также для других полей формы...

    // Если все поля формы прошли валидацию, сохраните данные в куки на год
    if ($fullname_valid && $phone_valid && $email_valid /*&& другие поля*/) {
        setcookie('form_data_fullname', $fullname, time() + (365 * 24 * 60 * 60), '/');
        setcookie('form_data_phone', $phone, time() + (365 * 24 * 60 * 60), '/');
        setcookie('form_data_email', $email, time() + (365 * 24 * 60 * 60), '/');
        // Сохраните остальные поля формы...
    }

    // Перенаправление на страницу с формой
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Вывод ошибок над формой
if (!empty($_COOKIE)) {
    foreach ($_COOKIE as $name => $value) {
        if (strpos($name, 'form_error_') === 0) {
            $field = substr($name, strlen('form_error_'));
            echo "<p style='color: red;'>{$value}</p>";
            // Очистите куки с ошибками после их вывода
            setcookie($name, '', time() - 3600, '/');
        }
    }
}

// Вывод значений полей формы из куков
$fullname_value = isset($_COOKIE['form_data_fullname']) ? $_COOKIE['form_data_fullname'] : '';
$phone_value = isset($_COOKIE['form_data_phone']) ? $_COOKIE['form_data_phone'] : '';
$email_value = isset($_COOKIE['form_data_email']) ? $_COOKIE['form_data_email'] : '';

// Вывод HTML-формы с подсвечиванием полей с ошибками
echo "<form method='POST' action='{$_SERVER['PHP_SELF']}'>";
echo "<input type='text' name='fullname' value='{$fullname_value}' style='border: 1px solid " . (isset($_COOKIE['form_error_fullname']) ? 'red' : 'black') . ";'>";
echo "<input type='text' name='phone' value='{$phone_value}' style='border: 1px solid " . (isset($_COOKIE['form_error_phone']) ? 'red' : 'black') . ";'>";
echo "<input type='text' name='email' value='{$email_value}' style='border: 1px solid " . (isset($_COOKIE['form_error_email']) ? 'red' : 'black') . ";'>";
// Выведите остальные поля формы...
echo "<button type='submit'>Отправить</button>";
echo "</form>";
?>
