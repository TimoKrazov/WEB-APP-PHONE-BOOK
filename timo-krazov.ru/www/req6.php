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
$selected_cat = isset($_GET['category']) ? $_GET['category'] :'';
$query_cat = "SELECT id_category, name_category
                FROM category
                ORDER BY name_category";
$result_cat = $conn->query($query_cat);
$query_content = "";
if ($selected_city && $selected_cat) {
// Получаем список заголовков столбцов таблицы
    $query_content = "SELECT o.name_organization, nno.number_phone_organization, cat.name_category, CONCAT(c.name_city,' (', cn.name_country,')') AS FullAdd
                    FROM number_number_organization nno
                    JOIN organization o ON nno.fid_organization = o.id_organization
                    JOIN city c ON o.fid_city = c.id_city
                    JOIN country cn ON c.fid_country = cn.id_country
                    JOIN category cat ON o.fid_category = cat.id_category
                    WHERE c.id_city = ? AND cat.id_category = ?
                    ORDER BY o.name_organization";
    $stmt = $conn->prepare($query_content);
    $stmt->bind_param("ii", $selected_city, $selected_cat);
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
        <h1>Телефоны организаций города определенной рубрики</h1>
        <form method="GET" action="">
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
            <label for="category">Выберите рубрику:</label>
            <select name="category" id="category" onchange="this.form.submit()">
                <option value="">Выберите рубрику</option>
                <?php
                // Отображаем список городов
                while ($row = $result_cat->fetch_assoc()) {
                    $selected = $row['id_category'] == $selected_cat ? 'selected' : '';
                    echo "<option value=\"" . $row['id_category'] . "\" $selected>" . $row['name_category'] . "</option>";
                }
                ?>
            </select>
        </form>

            <!-- Отрисовка таблицы -->
            <?php
            if ($selected_city && $selected_cat && $result->num_rows > 0) {
                // Выполняем SQL-запросы для отображения содержимого таблицы
                echo "<table class=\"tbl\" align=\"center\">";
                echo "<tr class=\"header\">";
                echo "<th>Организация</th>";
                echo "<th>Телефонный номер</th>";
                echo "<th>Рубрика</th>";
                echo "<th>Город</th>";
                echo "</tr>";

                // Выводим содержимое таблицы
                while ($line_content = $result->fetch_array(MYSQLI_NUM)) {
                    // Подсчет учеников в каждом классе
                    /*$query_count_pupil = "SELECT COUNT(*) FROM pupil WHERE pupil.CountryID = ?";
                    $stmt = $conn->prepare($query_count_pupil);
                    $stmt->bind_param("i", $line_content[0]);
                    $stmt->execute();
                    $result_count_pupil = $stmt->get_result();
                    $line_count_pupil = $result_count_pupil->fetch_array(MYSQLI_NUM);*/
                    
                    echo "<tr>";
                    foreach ($line_content as $col_value) {
                        echo "<td>".$col_value."</td>";
                    }
                    //echo "<td>".$line_count_pupil[0]."</td>";
                    echo "</tr>";
                }
                echo "</table>";
                $result->free();
            } elseif ($selected_city && $selected_cat) {
                echo "<p>Нет данных для выбранных значений.</p>";
            } elseif ($selected_city) {
                echo "<p>Выберите рубрику.</p>";
            } elseif ($selected_cat) {
                echo "<p>Выберите город.</p>";
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