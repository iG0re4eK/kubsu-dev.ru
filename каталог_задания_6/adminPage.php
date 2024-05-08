<?php
    // Подключение к базе данных
    include 'db_credentials.php';

    
    

    // Запрос к базе данных для выборки всех данных из таблицы Users
    $stmt = $db->query("SELECT * FROM Users");
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $db->query("
        SELECT lang_id, COUNT(user_id) AS num_users
        FROM UserProgrammingLanguages
        GROUP BY lang_id
    ");
    $statistics = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Страница администратора</title>
</head>
<body>

<h1>Данные пользователей</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Имя</th>
                <th>Телефон</th>
                <th>Email</th>
                <th>Дата рождения</th>
                <th>Пол</th>
                <th>Биография</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['user_id'] ?></td>
                    <td><?= $user['fullname'] ?></td>
                    <td><?= $user['phone'] ?></td>
                    <td><?= $user['email'] ?></td>
                    <td><?= $user['dob'] ?></td>
                    <td><?= $user['gender'] ?></td>
                    <td><?= $user['bio'] ?></td>
                    <td><a href="user_profile.php?user_id=<?= $user['user_id'] ?>">Редактировать</a></td>
                    <td><a href="delete_user.php?user_id=<?= $user['user_id'] ?>">Удалить</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <h1>Статистика по языкам программирования</h1>
    <table>
        <thead>
            <tr>
                <th>Язык программирования</th>
                <th>Количество пользователей</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($statistics as $row): ?>
                <tr>
                    <td><?= $row['lang_id'] ?></td>
                    <td><?= $row['num_users'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>