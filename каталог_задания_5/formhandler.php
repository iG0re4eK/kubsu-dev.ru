<?php
session_start();
include 'db_credentials.php';

try {
    $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD,
        [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    echo 'Подключение не удалось: ' . $e->getMessage();
    exit;
}

$expiration_time = time() + 365 * 24 * 60 * 60;

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = strip_tags($data);
    $data = htmlspecialchars($data);
    return $data;
}

$fullname = "";
$email = "";
$phone = "";
$bio = "";
$gender = "";
$contact = "";
$selected_languages = [];

$errors = [];

$nameReg = "/^[а-яА-ЯёЁa-zA-Z]+ [а-яА-ЯёЁa-zA-Z]+ ?[а-яА-ЯёЁa-zA-Z]+$/u";
$emailReg = "/^(?:[a-z0-9]+(?:[-_.]?[a-z0-9]+)?@[a-z0-9_.-]+(?:\.?[a-z0-9]+)?\.[a-z]{2,5})$/i";
$phoneReg = "/^(\s*)?(\+)?([- _():=+]?\d[- _():=+]?){10,14}(\s*)?$/";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = test_input($_POST['fullname']);
    $email = test_input($_POST['email']);
    $phone = test_input($_POST['phone']);
    $bio = test_input($_POST['bio']);
    $dob = test_input($_POST['dob']);
    $gender = $_POST['gender'];
    $contact = $_POST['contact'];
    $selected_languages = $_POST['languages'];
    
    if(empty($fullname)){
        $errors['fullname']='<small style="color:red;">Поле не должно быть пустым</small>';
    }
    else if(!preg_match($nameReg,$fullname)){
        $errors['fullname']='<small style="color:red;">Поле должно содержать как минимум фамилию и имя на русском языке</small>';
    }
    else{
        setcookie('fullname', $_POST['fullname'], $expiration_time, '/');
    }
    
    if(empty($phone)){
        $errors['phone']='<small style="color:red;">Поле не должно быть пустым</small>';
    }
    else if(!preg_match($phoneReg,$phone)){
        $errors['phone']='<small style="color:red;">Номер телефона должен быть введен корректно</small>';
    }
    else{
        setcookie('phone', $_POST['phone'], $expiration_time, '/');
    }
    
    if(empty($email)){
        $errors['email']='<small style="color:red;">Поле не должно быть пустым</small>';
    }
    else if(!preg_match($emailReg,$email)){
        $errors['email']='<small style="color:red;">Почта должна быть введена корректно</small>';
    }
    else{
        setcookie('email', $_POST['email'], $expiration_time, '/');
    }
    
    if(empty($dob)){
        $errors['dob'] = '<small style="color:red;">Укажите дату рождения</small>';
    }
    else{
        setcookie('dob', $_POST['dob'], $expiration_time, '/');
    }

    if (empty($selected_languages)) {
        $errors['languages']='<small style="color:red;">Выберите хотя бы один язык</small>';
    }
    else{
        setcookie('selected_languages', json_encode($selected_languages), $expiration_time, '/');
    }
    
    if (empty($gender)) {
        $errors['gender']='<small style="color:red;">Выберите пол</small>';
    }
    else{
        if($gender == 'male') {
            setcookie('radio1', true, $expiration_time, '/');
            setcookie('radio2', false, $expiration_time, '/');
        } elseif($gender == 'female') {
            setcookie('radio1', false, $expiration_time, '/');
            setcookie('radio2', true, $expiration_time, '/');
        }
    }

    if(empty($bio)){
        $errors['bio']='<small style="color:red;">Поле должно быть заполнено</small>';
    }
    else{
        setcookie('bio', $_POST['bio'], $expiration_time, '/');
    }

    if(empty($contact)){
        $errors['contact']='<small style="color:red;">Дайте согласие на обработку данных</small>';
    }
    else{
        if($contact == 'on') {
            setcookie('contact', 'true', $expiration_time, "/");
        } else{
            setcookie('contact', 'false', $expiration_time, "/");
        }
    }
    
    if (!empty($errors)) {
        setcookie('errors', json_encode($errors), 0, '/');
        header("Location: index.php");
        exit();
    }
    else{
        setcookie('errors', '', time() - 3600, '/');
        if (!isset($_SESSION['username'])) {
            $generated_username = 'user_' . rand(1000, 9999);
            $length = 10;
            $random_bytes = random_bytes($length); 
            $password = bin2hex($random_bytes); 
            $generated_password = password_hash($password, PASSWORD_DEFAULT); 
            $stmt = $db->prepare("INSERT INTO Users (fullname, phone, email, dob, gender, bio, username, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$fullname, $phone, $email, $dob, $gender, $bio, $generated_username, $generated_password]);
            $user_id = $db->lastInsertId();

            foreach ($selected_languages as $language_id) {
                $stmt = $db->prepare("INSERT INTO UserProgrammingLanguages (user_id, lang_id) VALUES (?, ?)");
                $stmt->execute([$user_id, $language_id]);
            }

            $_SESSION['username'] = $generated_username;
            $_SESSION['password'] = $password;

            echo $_SESSION['username'];
            echo "<br> </br>";
            echo $_SESSION['password'];
            echo "<br> </br>";
            echo 'Данные успешно сохранены.';
        }
        else{
            $stmt = $db->prepare("SELECT * FROM Users WHERE username = ?");
            $stmt->execute([$_SESSION['username']]);
            $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing_user) {
                $user_id = $existing_user['user_id'];
            
                // Удаляем языки, которые были у пользователя ранее, но не встречаются в новом списке
                $existing_languages_stmt = $db->prepare("SELECT lang_id FROM UserProgrammingLanguages WHERE user_id = ?");
                $existing_languages_stmt->execute([$user_id]);
                $existing_languages = $existing_languages_stmt->fetchAll(PDO::FETCH_COLUMN);
            
                $languages_to_delete = array_diff($existing_languages, $selected_languages);
                foreach ($languages_to_delete as $language_id) {
                    $delete_stmt = $db->prepare("DELETE FROM UserProgrammingLanguages WHERE user_id = ? AND lang_id = ?");
                    $delete_stmt->execute([$user_id, $language_id]);
                }
            
                // Добавляем новые языки, которые есть в новом списке, но отсутствуют у пользователя
                $languages_to_add = array_diff($selected_languages, $existing_languages);
                foreach ($languages_to_add as $language_id) {
                    $insert_stmt = $db->prepare("INSERT INTO UserProgrammingLanguages (user_id, lang_id) VALUES (?, ?)");
                    $insert_stmt->execute([$user_id, $language_id]);
                }
                
                // Проверяем, совпадают ли данные из формы с данными в базе данных
                if ($existing_user['fullname'] !== $fullname || 
                    $existing_user['phone'] !== $phone || 
                    $existing_user['email'] !== $email || 
                    $existing_user['dob'] !== $dob || 
                    $existing_user['gender'] !== $gender || 
                    $existing_user['bio'] !== $bio) {
                    // Если данные изменились, обновляем запись в базе данных
                    $update_user_stmt = $db->prepare("UPDATE Users SET fullname = ?, phone = ?, email = ?, dob = ?, gender = ?, bio = ? WHERE username = ?");
                    $update_user_stmt->execute([$fullname, $phone, $email, $dob, $gender, $bio, $_SESSION['username']]);
                    
                }
                echo 'Спасибо за предоставленные данные! :)';
                
            } else {
                echo 'Ошибка: Пользователь с логином ' . $_SESSION['username'] . ' не найден.';
            }
        }
    }
}