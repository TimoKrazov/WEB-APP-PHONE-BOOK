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
$tblname = 'natural_person'; // Имя таблицы
$info = array(); // Массив для вывода информации
$servername = "localhost";
$username = $_SESSION['idsess'];
$password = $_SESSION['hashpasswd'];
if ($username == 'trueuser') {
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

// Получаем список заголовков столбцов таблицы
$result = $conn->query("DESCRIBE $tblname");
if (!$result) {
    die("Ошибка запроса: " . $conn->error);
}
$fields = $result->fetch_all(MYSQLI_ASSOC);

// Получаем количество столбцов
$columns = count($fields);

// Обработка таблицы

// Добавление записи в таблицу
if (isset($_POST['last_name_add'], $_POST['first_name_add'], $_POST['street_add'], $_POST['house_add'], $_POST['apartment_add'], $_POST['fid_city_add'])) {
    $last_name_add = htmlspecialchars(stripslashes($_POST['last_name_add']));
    $first_name_add = htmlspecialchars(stripslashes($_POST['first_name_add']));
    $patronymic_add = htmlspecialchars(stripslashes($_POST['patronymic_add']));
    $street_add = htmlspecialchars(stripslashes($_POST['street_add']));
    $house_add = htmlspecialchars(stripslashes($_POST['house_add']));
    $apartment_add = htmlspecialchars(stripslashes($_POST['apartment_add']));
    $fid_city_add = intval($_POST['fid_city_add']); // ID выбранной страны

    if (!empty($last_name_add) && !empty($first_name_add) && !empty($street_add) && !empty($house_add) && !empty($apartment_add) && !empty($fid_city_add)) {
        $query_any_repeat = "SELECT * FROM $tblname WHERE last_name = ? AND first_name=? AND patronymic=? AND street = ? AND house = ? AND apartment = ? AND fid_city = ? ";
            $stmt = $conn->prepare($query_any_repeat);
            $stmt->bind_param("ssssssi", $last_name_add, $first_name_add, $patronymic_add, $street_add, $house_add, $apartment_add, $fid_city_add);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows != 0) {
                $info[] = 'Такая запись уже существует!';
        } else {
            $query_add = "INSERT INTO $tblname (last_name, first_name, patronymic, street, house, apartment, fid_city) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query_add);
            $stmt->bind_param("ssssssi", $last_name_add, $first_name_add, $patronymic_add, $street_add, $house_add, $apartment_add, $fid_city_add);
            $stmt->execute();
        }
    } else {
        $info[] = 'Не заполнены обязательные поля!';
    }
}
// Изменение записи
if (isset($_POST['last_name_upd'], $_POST['first_name_upd'], $_POST['street_upd'], $_POST['house_upd'], $_POST['apartment_upd'], $_POST['fid_city_upd'])) {
    $last_name_upd = htmlspecialchars(stripslashes($_POST['last_name_upd']));
    $first_name_upd = htmlspecialchars(stripslashes($_POST['first_name_upd']));
    $patronymic_upd = htmlspecialchars(stripslashes($_POST['patronymic_upd']));
    $street_upd = htmlspecialchars(stripslashes($_POST['street_upd']));
    $house_upd = htmlspecialchars(stripslashes($_POST['house_upd']));
    $apartment_upd = htmlspecialchars(stripslashes($_POST['apartment_upd']));
    $fid_city_upd = intval($_POST['fid_city_upd']); // ID выбранной страны

    if (!empty($last_name_upd) && !empty($first_name_upd) && !empty($street_upd) && !empty($house_upd) && !empty($apartment_upd) && !empty($fid_city_upd)) {
        $query_any_repeat = "SELECT * FROM $tblname WHERE last_name = ? AND first_name=? AND patronymic=? AND street = ? AND house = ? AND apartment = ? AND fid_city = ? AND id_person != ? ";
            $stmt = $conn->prepare($query_any_repeat);
            $stmt->bind_param("ssssssii", $last_name_upd, $first_name_upd, $patronymic_upd, $street_upd, $house_upd, $apartment_upd, $fid_city_upd, $_SESSION['updateRow']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows != 0) {
                $info[] = 'Такая запись уже существует!';
        } else {
            $query_upd = "UPDATE $tblname SET last_name = ?, first_name = ?, patronymic = ?, street = ?, house = ?, apartment = ?, fid_city = ? 
                    WHERE id_person = ?";
            $stmt = $conn->prepare($query_upd);
            $stmt->bind_param("ssssssii",$last_name_upd, $first_name_upd, $patronymic_upd, $street_upd, $house_upd, $apartment_upd, $fid_city_upd, $_SESSION['updateRow']);
            $stmt->execute();
        }
    } else {
        $info[] = 'Не заполнены обязательные поля!';
    }
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
        <h1>Таблица "Физических лиц"</h1>
        <?php
        // Получаем все страны для выпадающего списка
        // Вывод форм для редактирования
        $query_city = "SELECT c.id_city, CONCAT('Страна: ', cn.name_country, ' Город: ', c.name_city) AS FullCity
            FROM city c
            JOIN country cn ON c.fid_country = cn.id_country 
            ORDER BY name_country";
            $result_city = $conn->query($query_city);
        if (isset($_POST['input']) && $_POST['input'] == 'add') { // Добавление
            echo "<form method=\"post\" action=\"\">";
            echo "<table align=\"center\">";
            echo "<tr><td>Выбор города:</td><td><select name=\"fid_city_add\">";
            while ($row_city = $result_city->fetch_assoc()) {
                echo "<option value=\"".$row_city['id_city'] . "\">" 
                . $row_city['FullCity'] . "</option>";
            }
            echo "</select></td></tr>";
            echo "<tr><td>Введи фамилию: </td><td><input type=\"text\" size=\"70\" maxlength=\"50\" name=\"last_name_add\"/></td></tr>";
            echo "<tr><td>Введи имя: </td><td><input type=\"text\" size=\"70\" maxlength=\"50\" name=\"first_name_add\"/></td></tr>";
            echo "<tr><td>Введи отчество: </td><td><input type=\"text\" size=\"70\" maxlength=\"50\" name=\"patronymic_add\"/></td></tr>";
            echo "<tr><td>Введи название улицы: </td><td><input type=\"text\" size=\"70\" maxlength=\"50\" name=\"street_add\"/></td></tr>";
            echo "<tr><td>Введи название дома: </td><td><input type=\"text\" size=\"70\" maxlength=\"10\" name=\"house_add\"/></td></tr>";
            echo "<tr><td>Введи название квартиры: </td><td><input type=\"text\" size=\"70\" maxlength=\"10\" name=\"apartment_add\"/></td></tr>";
            echo "</select></td></tr>";
            echo "</table>";
            echo "<p class=\"centr\">";
            echo "<br/>";
            echo "<input type=\"submit\" name=\"submit\" value=\"Отправить\"/>";
            echo "<input type=\"reset\" name=\"reset\" value=\"Очистить\"/>";
            echo "</p>";
            echo "</form><br/>";
        } if (isset($_POST['input']) && $_POST['input'] == 'edit') { // Изменение
            if (!isset($_POST['chooseRow'])) {
                $info[] = 'Укажите запись!';
            } else {
                $_SESSION['updateRow'] = $_POST['chooseRow']; // Запоминаем ряд
                $query_form_upd = "SELECT * FROM $tblname WHERE id_person = ?";
                $stmt = $conn->prepare($query_form_upd);
                $stmt->bind_param("i", $_POST['chooseRow']);
                $stmt->execute();
                $result_form_upd = $stmt->get_result();
                
                echo "<form method=\"post\" action=\"\">";
                echo "<table align=\"center\">";
                $line_content = $result_form_upd->fetch_array(MYSQLI_NUM);
                echo "<tr><td>Выбор города:</td><td><select name=\"fid_city_upd\">";
                while ($row_city = $result_city->fetch_assoc()) {
                    $selected = ($row_city['id_city'] == $line_content[1]) ? 'selected' : '';
                    echo "<option value=\"".$row_city['id_city'] . "\" $selected>" 
                    . $row_city['FullCity'] . "</option>";
                }
                echo "</select></td></tr>";
                echo "<tr><td>Введи фамилию: </td><td><input type=\"text\" size=\"70\" maxlength=\"50\" name=\"last_name_upd\" value=\"" . $line_content[2] . "\"/></td></tr>";
                
                echo "<tr><td>Введи имя: </td><td><input type=\"text\" size=\"70\" maxlength=\"50\" name=\"first_name_upd\"value=\"" . $line_content[3] . "\"/></td></tr>";
                echo "<tr><td>Введи отчество: </td><td><input type=\"text\" size=\"70\" maxlength=\"50\" name=\"patronymic_upd\"value=\"" . $line_content[4] . "\"/></td></tr>";
                echo "<tr><td>Введи название улицы: </td><td><input type=\"text\" size=\"70\" maxlength=\"50\" name=\"street_upd\" value=\"" . $line_content[5] . "\"/></td></tr>";
                echo "<tr><td>Введи название дома: </td><td><input type=\"text\" size=\"70\" maxlength=\"10\" name=\"house_upd\" value=\"" . $line_content[6] . "\"/></td></tr>";
                echo "<tr><td>Введи название квартиры: </td><td><input type=\"text\" size=\"70\" maxlength=\"10\" name=\"apartment_upd\"value=\"" . $line_content[7] . "\"/></td></tr>";
                echo "</table>";
                echo "<p class=\"centr\">";
                echo "<br/>";
                echo "<input type=\"submit\" name=\"submit\" value=\"Отправить\"/>";
                echo "<input type=\"reset\" name=\"reset\" value=\"Очистить\"/>";
                echo "</p>";
                echo "</form><br/>";
                $result_form_upd->free();
            }
        } if (isset($_POST['input']) && $_POST['input'] == 'del') {
            if (!isset($_POST['chooseRow'])) {
                $info[] = 'Укажите запись!';
            } else {
                $personID = $_POST['chooseRow'];
    
                // Проверка наличия городов, связанных с этой страной
                $query_check_org = "SELECT COUNT(*) FROM number_number_person WHERE fid_person = ?";
                $stmt_check = $conn->prepare($query_check_org);
                $stmt_check->bind_param("i", $personID);
                $stmt_check->execute();
                $stmt_check->bind_result($number_per);
                $stmt_check->fetch();
                $stmt_check->close();

                
                
                if (($number_per > 0)) {
                    $info[] = 'Невозможно удалить запись!';
                } else {
                    // Удаляем страну, если нет связанных городов
                    $query_del = "DELETE FROM $tblname WHERE id_person = ?";
                    $stmt = $conn->prepare($query_del);
                    $stmt->bind_param("i", $personID);
                    $stmt->execute();
                    $info[] = 'Запись успешно удалена!';
                }
            }
        }
        
        ?>
        <!-- Панель для выбора действия над таблицей -->
        <form method="post" action="">
            <table border="0" align="center">
                <tr>
                    <td><input type="submit" name="input" value="add" /></td>
                    <td><input type="submit" name="input" value="edit" /></td>
                    <td><input type="submit" name="input" value="del" /></td>
                </tr>
            </table>
            <br/>
            <!-- Отрисовка таблицы -->
            <?php
            // Выполняем SQL-запросы для отображения содержимого таблицы
            $query_content = "
            SELECT per.id_person, per.last_name, per.first_name, per.patronymic, per.street, per.house, per.apartment, per.fid_city, c.name_city
            FROM $tblname per
            JOIN city c ON per.fid_city = c.id_city 
            ORDER BY per.fid_city";
            $result_content = $conn->query($query_content);

            echo "<table class=\"tbl\" align=\"center\">";
            echo "<tr class=\"header\">";
            echo "<th>&nbsp;</th>";
            echo "<th>ID Личности</th>";
            echo "<th>Фамилия</th>";
            echo "<th>Имя</th>";
            echo "<th>Отчество</th>";
            echo "<th>Улица</th>";
            echo "<th>Дом</th>";
            echo "<th>Квартира</th>";
            echo "<th>FID Города</th>";
            echo "<th>Название города</th>";
            echo "</tr>";

            while ($line_content = $result_content->fetch_array(MYSQLI_NUM)) {
            echo "<tr>";
            echo "<td><input type=\"radio\" name=\"chooseRow\" value=\"".$line_content[0]."\"/></td>";
            foreach ($line_content as $col_value) {
                echo "<td>".$col_value."</td>";
            }
            echo "</tr>";
            }
            echo "</table>";
            $result_content->free();
            ?>
        </form>
        <br/>
        <p class="error">
            <!-- Вывод информации об ошибках -->
            <?php
            echo implode('<br/>', $info)."\n";
            ?>
        </p>
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
