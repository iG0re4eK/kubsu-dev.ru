<?php
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
$flag=0;

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
        $stmt = $db->prepare("INSERT INTO Users (fullname, phone, email, dob, gender, bio) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$fullname, $phone, $email, $dob, $gender, $bio]);
        $user_id = $db->lastInsertId();

        foreach ($_POST['languages'] as $language_id) {
            $stmt = $db->prepare("INSERT INTO UserProgrammingLanguages (user_id, lang_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $language_id]);
        }

        echo 'Данные успешно сохранены.';

        
    }


    
}