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
//  require('fpdf/fpdf.php');    // PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF 
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
// if (isset($_POST['download_pdf'])) {             //PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF 
//     // Создаем PDF
//     $pdf = new FPDF();
//     $pdf->AddPage();
    
//     // Подключаем шрифт с поддержкой кириллицы
//     $pdf->AddFont('Arial', '', 'arial.php');
//     $pdf->SetFont('Arial', '', 12);

//     // Заголовок таблицы
//     $header = array('Фамилия', 'Имя', 'Отчество', 'Телефон', 'Улица', 'Дом', 'Квартира', 'Город');
//     $widths = array(20, 20, 20, 20, 30, 20, 20, 20);

//     // Добавляем заголовок таблицы
//     foreach ($header as $key => $col) {
//         $pdf->Cell($widths[$key], 10, iconv('UTF-8', 'CP1251',$col), 1, 0, 'C');
//     }
//     $pdf->Ln();
//     // Заполняем таблицу данными
//     if ($result && $result->num_rows > 0) {
//         while ($row = $result->fetch_assoc()) {
//             $pdf->Cell($widths[0], 10, iconv('UTF-8', 'CP1251',$row['last_name']), 1);
//             $pdf->Cell($widths[1], 10, iconv('UTF-8', 'CP1251',$row['first_name']), 1);
//             $pdf->Cell($widths[2], 10, iconv('UTF-8', 'CP1251',$row['patronymic']), 1);
//             $pdf->Cell($widths[3], 10, iconv('UTF-8', 'CP1251',$row['number_phone_person']), 1);
//             $pdf->Cell($widths[4], 10, iconv('UTF-8', 'CP1251',$row['street']), 1);
//             $pdf->Cell($widths[5], 10, iconv('UTF-8', 'CP1251',$row['house']), 1);
//             $pdf->Cell($widths[6], 10, iconv('UTF-8', 'CP1251',$row['apartment']), 1);
//             $pdf->Cell($widths[7], 10, iconv('UTF-8', 'CP1251',$row['name_city']), 1);
//             $pdf->Ln();
//         }
//     } else {
//         $pdf->Cell(190, 10, iconv('UTF-8', 'CP1251','Нет данных для выбранных значений.'), 1, 0, 'C');
//     }

//     // Вывод PDF в браузер
//     $pdf->Output('I', 'Телефонный_справочник.pdf');
//     exit;
// }

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
        <h1>Информация об физических лицах</h1>
        <form method="GET" action="">
            <table border = "0" align = 'center'>
                <tr>
                    <td><label for="last_name">Введите фамилию:</label>
                    <input type="text" name="last_name" maxlength="50" id="last_name" value="<?php echo htmlspecialchars($selected_last); ?>"></td>
                    <td><label for="street">Введите улицу:</label>
                    <input type="text" name="street" maxlength="50" id="street" value="<?php echo htmlspecialchars($selected_street); ?>"></td>
                </tr>
                <tr>
                    <td><label for="house">Введите дом:</label>
                    <input type="text" name="house" maxlength="10" id="house" value="<?php echo htmlspecialchars($selected_house); ?>"></td>
                    
                    <td><label for="apartment">Введите квартиру:</label>
                    <input type="text" name="apartment" maxlength="10" id="apartment" value="<?php echo htmlspecialchars($selected_apa); ?>"></td>
                </tr>
                <tr>
                    <td><label for="number">Введите номер телефона:</label>
                    <input type="text" name="number" maxlength="20" id="number" value="<?php echo htmlspecialchars($selected_number); ?>"></td>
                    
                    <td><button type="submit">Поиск</button></td>
                </tr>
            </table>
        </form>   <!--PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF PDF -->
            <!-- <form method="POST" action="">
            <button type="submit" name="download_pdf">Скачать PDF</button>
        </form> -->
        <br/>
        <button onclick="printDiv()">Печать отчёта</button>
        <br/><br/>
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
        </form>
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