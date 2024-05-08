<?php
    // Подключение к базе данных
    include 'db_credentials.php';

    

    // Получение идентификатора пользователя из запроса
    $user_id = $_GET['user_id'];

    // Получение данных пользователя из базы данных
    $stmt = $db->prepare("SELECT * FROM Users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Обработка отправленной формы для обновления данных пользователя
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Получение новых данных из формы
        $new_fullname = $_POST['fullname'];
        $new_phone = $_POST['phone'];
        $new_email = $_POST['email'];
        // Добавьте другие поля, если необходимо

        // Выполнение запроса на обновление данных пользователя в базе данных
        $stmt = $db->prepare("UPDATE Users SET fullname = ?, phone = ?, email = ? WHERE user_id = ?");
        $stmt->execute([$new_fullname, $new_phone, $new_email, $user_id]);
        header("Location: user_profile.php?user_id=$user_id");
        
        
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Данные пользователя</title>
</head>
<body>
    <h1>Редактировать данные пользователя</h1>
    <form action="" method="POST">
        <label>Имя:</label>
        <input type="text" name="fullname" value="<?= $user['fullname'] ?>">
        <label>Телефон:</label>
        <input type="text" name="phone" value="<?= $user['phone'] ?>">
        <label>Email:</label>
        <input type="email" name="email" value="<?= $user['email'] ?>">
        <!-- Добавьте другие поля, если необходимо -->
        <button type="submit">Сохранить изменения</button>
    </form>
</body>
</html>
