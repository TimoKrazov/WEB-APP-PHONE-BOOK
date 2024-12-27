<?php
// Устанавливаем уровень оповещения ошибок
error_reporting(E_ALL); // Показывать все ошибки и предупреждения
session_start(); // Начинаем новую или открываем существующую сессию

// Проверяем существование параметров сессии - логина и шифрованного пароля
// Если параметров не существует, значит пересылаем на страницу входа adm.php
if (!((isset($_SESSION['idsess'])) && (isset($_SESSION['hashpasswd'])))) {
    header('Location: /adm.php'); // Пересылка на форму входа
    exit();
} else {
    $hello = "Добро пожаловать, ".$_SESSION['name']."!";
    $exitlink = "<a class=\"exitlink\" href=\"exitsess.php\">Выход с сайта</a>";
}

// Переменные
$dbname = 'book'; // Имя базы данных
$info = array(); // Массив для вывода информации
$servername = "localhost";
$username = $_SESSION['idsess'];
$password = $_SESSION['hashpasswd'];
if ($username == 'admin') {
    header('Location: /base.php'); // Пересылка на форму входа
    exit();
}

// Скрипт
$date = date("d.m.y"); // Текущая дата
$dn = date("l"); // Текущий день недели

// Соединяемся с сервером БД под сохраненными переменными сессии
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверка на ошибки соединения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Устанавливаем кодировку
$conn->set_charset("utf8");
$selected_city = isset($_GET['city']) ? $_GET['city'] :'';
$query_city = "SELECT c.id_city, CONCAT(c.name_city, ' (', cn.name_country,')') AS FullAdd
                FROM city c
                JOIN country cn ON c.fid_country = cn.id_country
                ORDER BY FullAdd";
$result_city = $conn->query($query_city);
$query_content = "";
if ($selected_city) {
// Получаем список заголовков столбцов таблицы
    $query_content = "SELECT p.last_name, p.first_name, p.patronymic, nnp.number_phone_person, CONCAT(c.name_city,' (', cn.name_country,')') AS FullAdd
                    FROM number_number_person nnp
                    JOIN natural_person p ON nnp.fid_person = p.id_person
                    JOIN city c ON p.fid_city = c.id_city
                    JOIN country cn ON c.fid_country = cn.id_country
                    WHERE c.id_city = ?
                    ORDER BY p.last_name;";
    $stmt = $conn->prepare($query_content);
    $stmt->bind_param("i", $selected_city);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = null;
}

// Отображение
echo chr(239).chr(187).chr(191);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>База данных "Телефонный справочник"</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="description" content="База данных 'Телефонный справочник'"/>
    <meta name="keywords" content="База данных, телефонный справочник, PHP, MySQL, web-программирование"/>
    <link rel="stylesheet" type="text/css" href="my-style.css"/>
</head>
<body>
<script src = 'print.js'></script>
<div id="container">
    <div id="other">
        <div id="daydata">
            <?php
            echo ("Сегодня ".$date." ".$dn."\n"); // вывод даты и дня недели
            ?>
        </div>
        <div id="hello">
            <?php
            // Если вход осуществлен, то появляется приветствие и ссылка для разлогинивания
            print $hello." -->".$exitlink."<--";
            ?>
        </div>
    </div>
    <div id="menu">
        <div><a href="index.php">Главная</a></div>
        <div><a href="adm.php" style="border-bottom: 7px solid #000066">Функции</a></div>
    </div>
    <div id="content">
        <h1>Список телефонов физических лиц города</h1>
        <form method="GET" id = "req5" action="">
            <label for="city">Выберите город:</label>
            <select name="city" id="city" onchange="this.form.submit()">
                <option value="">Выберите город</option>
                <?php
                // Отображаем список городов
                while ($row = $result_city->fetch_assoc()) {
                    $selected = $row['id_city'] == $selected_city ? 'selected' : '';
                    echo "<option value=\"" . $row['id_city'] . "\" $selected>" . $row['FullAdd'] . "</option>";
                }
                ?>
            </select>
        </form>
        <br/>
        <button onclick="printDiv()">Печать отчёта</button>
        <br/><br/>
            <!-- Отрисовка таблицы -->
            <?php
            if ($selected_city && $result->num_rows > 0) {
                // Выполняем SQL-запросы для отображения содержимого таблицы
                echo "<table class=\"tbl\" align=\"center\">";
                echo "<tr class=\"header\">";
                echo "<th>Фамилия</th>";
                echo "<th>Имя</th>";
                echo "<th>Отчество</th>";
                echo "<th>Телефонный номер</th>";
                echo "<th>Город</th>";
                echo "</tr>";

                // Выводим содержимое таблицы
                while ($line_content = $result->fetch_array(MYSQLI_NUM)) {
                    
                    
                    echo "<tr>";
                    foreach ($line_content as $col_value) {
                        echo "<td>".$col_value."</td>";
                    }
                    
                    echo "</tr>";
                }
                echo "</table>";
                $result->free();
            } elseif ($selected_city) {
                echo "<p>Нет данных для выбранного города.</p>";
            }
            ?>
        <br/>
        <p class="centr">
            <a href="/base.php">Вернуться к таблицам...</a>
        </p>
        <br/>
    </div>
</div>
</body>
</html>
<?php
// Закрываем соединение
$conn->close();
?>