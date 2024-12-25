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
$selected_last = isset($_GET['last_name']) ? $_GET['last_name'] :'';
$selected_street = isset($_GET['street']) ? $_GET['street'] :'';
$selected_house = isset($_GET['house']) ? $_GET['house'] :'';
$selected_apa = isset($_GET['apartment']) ? $_GET['apartment'] :'';
$selected_number = isset($_GET['number']) ? $_GET['number'] :'';


if ($selected_last == '') {
    $selected_last = null;
}

if ($selected_street == '') {
    $selected_street = null;
}
if ($selected_house == '') {
    $selected_house = null;
}
if ($selected_apa == '') {
    $selected_apa = null;
}
if ($selected_number == '') {
    $selected_number = null;
}
$query_content = "";
if ($selected_last || $selected_street || $selected_house || $selected_apa || $selected_number) {
// Получаем список заголовков столбцов таблицы

    if ($selected_number != null) {
        $query_content = "SELECT p.last_name, p.first_name, p.patronymic, nnp.number_phone_person, p.street, p.house, p.apartment, c.name_city
                        FROM natural_person p
                        LEFT JOIN number_number_person nnp ON p.id_person = nnp.fid_person
                        JOIN city c ON p.fid_city = c.id_city
                        WHERE p.last_name LIKE CONCAT('%', COALESCE(?, p.last_name), '%') AND
                        p.street LIKE CONCAT('%', COALESCE(?, p.street), '%') AND
                        p.house LIKE CONCAT('%', COALESCE(?, p.house), '%') AND
                        p.apartment LIKE CONCAT('%', COALESCE(?, p.apartment), '%') AND
                        nnp.number_phone_person LIKE CONCAT('%', ?, '%') ";
        $stmt = $conn->prepare($query_content);
        $stmt->bind_param("sssss", $selected_last, $selected_street, $selected_house, $selected_apa, $selected_number);
        $stmt->execute();
        $result = $stmt->get_result();
    }
    else {
        $query_content = "SELECT p.last_name, p.first_name, p.patronymic, nnp.number_phone_person, p.street, p.house, p.apartment, c.name_city
                        FROM natural_person p
                        LEFT JOIN number_number_person nnp ON p.id_person = nnp.fid_person
                        JOIN city c ON p.fid_city = c.id_city
                        WHERE p.last_name LIKE CONCAT('%', COALESCE(?, p.last_name), '%') AND
                        p.street LIKE CONCAT('%', COALESCE(?, p.street), '%') AND
                        p.house LIKE CONCAT('%', COALESCE(?, p.house), '%') AND
                        p.apartment LIKE CONCAT('%', COALESCE(?, p.apartment), '%')
                         ";
        $stmt = $conn->prepare($query_content);
        $stmt->bind_param("ssss", $selected_last, $selected_street, $selected_house, $selected_apa);
        $stmt->execute();
        $result = $stmt->get_result();
    }
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
        <h1>Информация об физических лицах</h1>
        <form method="GET" action="">
            <label for="last_name">Выберите фамилию:</label>
            <input type="text" name="last_name" maxlength="50" id="last_name" value="<?php echo htmlspecialchars($selected_last); ?>">
            <br/><br/>
            <label for="street">Выберите улицу:</label>
            <input type="text" name="street" maxlength="50" id="street" value="<?php echo htmlspecialchars($selected_street); ?>">
            <br/><br/>
            <label for="house">Выберите дом:</label>
            <input type="text" name="house" maxlength="10" id="house" value="<?php echo htmlspecialchars($selected_house); ?>">
            <br/><br/>
            <label for="apartment">Выберите квартиру:</label>
            <input type="text" name="apartment" maxlength="10" id="apartment" value="<?php echo htmlspecialchars($selected_apa); ?>">
            <br/><br/>
            <label for="number">Выберите номер телефона:</label>
            <input type="text" name="number" maxlength="20" id="number" value="<?php echo htmlspecialchars($selected_number); ?>">
            <br/><br/>
            <button type="submit">Поиск</button>
        </form>
            
            <!-- Отрисовка таблицы -->
            <?php
            if (($selected_last || $selected_street || $selected_house || $selected_apa || $selected_number) && $result->num_rows > 0) {
                // Выполняем SQL-запросы для отображения содержимого таблицы
                echo "<table class=\"tbl\" align=\"center\">";
                echo "<tr class=\"header\">";
                echo "<th>Фамилия</th>";
                echo "<th>Имя</th>";
                echo "<th>Отчество</th>";
                echo "<th>Телефонный номер</th>";
                echo "<th>Улица</th>";
                echo "<th>Дом</th>";
                echo "<th>Квартира</th>";
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
            } else {
                echo "<p>Нет данных для выбранных значений.</p>";
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