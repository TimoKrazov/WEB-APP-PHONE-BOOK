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
$selected_org = isset($_GET['organization']) ? $_GET['organization'] :'';
$selected_number = isset($_GET['number']) ? $_GET['number'] :'';
if ($selected_org == '') {
    $selected_org = null;
}
if ($selected_number == '') {
    $selected_number = null;
}
$query_content = "";

if ($selected_org || $selected_number) {
// Получаем список заголовков столбцов таблицы
    if ($selected_number != null) {
        $query_content = "SELECT o.name_organization, nno.number_phone_organization, o.street, o.house, c.name_city, cat.name_category
                        FROM organization o
                        LEFT JOIN number_number_organization nno ON o.id_organization = nno.fid_organization
                        JOIN city c ON o.fid_city = c.id_city
                        JOIN category cat ON o.fid_category = cat.id_category
                        WHERE o.name_organization LIKE CONCAT('%', COALESCE(?, o.name_organization), '%') AND
                        nno.number_phone_organization LIKE CONCAT('%', ?, '%')  ";
        $stmt = $conn->prepare($query_content);
        $stmt->bind_param("ss", $selected_org, $selected_number);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $query_content = "SELECT o.name_organization, nno.number_phone_organization, o.street, o.house, c.name_city, cat.name_category
                    FROM organization o
                    LEFT JOIN number_number_organization nno ON o.id_organization = nno.fid_organization
                    JOIN city c ON o.fid_city = c.id_city
                    JOIN category cat ON o.fid_category = cat.id_category
                    WHERE o.name_organization LIKE CONCAT('%', COALESCE(?, o.name_organization), '%')";
        $stmt = $conn->prepare($query_content);
        $stmt->bind_param("s", $selected_org);
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
        <h1>Информация об организациях</h1>
        <form method="GET" action="">
            <table border = 0, align="center">
                <tr>
                    <td>
                        <label for="organization">Выберите организацию:</label>
                        <input type="text" name="organization" maxlength="50" id="organization" value="<?php echo htmlspecialchars($selected_org); ?>">
                    </td>
                    <td>
                    <label for="number">Введите номер телефона:</label>
                    <input type="text" name="number" maxlength="20" id="number" value="<?php echo htmlspecialchars($selected_number); ?>">
                    </td>
                </tr>
                <tr>
                    <td>
                        <button type="submit">Поиск</button>
                    </td>
                </tr>
            </table>
        </form>
        <br/>
        <button onclick="printDiv()">Печать отчёта</button>
        <br/><br/>

            <!-- Отрисовка таблицы -->
            <?php
            if (($selected_org || $selected_number) && $result->num_rows > 0) {
                // Выполняем SQL-запросы для отображения содержимого таблицы
                echo "<table class=\"tbl\" align=\"center\">";
                echo "<tr class=\"header\">";
                echo "<th>Организация</th>";
                echo "<th>Телефонный номер</th>";
                echo "<th>Улица</th>";
                echo "<th>Дом</th>";
                echo "<th>Город</th>";
                echo "<th>Рубрика</th>";
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