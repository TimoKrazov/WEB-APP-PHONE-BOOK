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
$tblname = 'number_number_organization'; // Имя таблицы
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
if (isset($_POST['fid_organization_add'], $_POST['number_phone_add'] )) {
    $number_phone_add = htmlspecialchars(stripslashes($_POST['number_phone_add']));
    $fid_organization_add = intval($_POST['fid_organization_add']);

    if (!empty($number_phone_add) && !empty($fid_organization_add)) {
        $query_any_repeat = "SELECT * FROM $tblname WHERE number_phone_organization = ? AND fid_organization = ?";
        $stmt = $conn->prepare($query_any_repeat);
        $stmt->bind_param("si", $number_phone_add, $fid_organization_add);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows != 0) {
            $info[] = 'Такая запись уже существует!';
        } else {
            $query_add = "INSERT INTO $tblname (number_phone_organization, fid_organization) VALUES (?, ?)";
            $stmt = $conn->prepare($query_add);
            $stmt->bind_param("si", $number_phone_add, $fid_organization_add);
            $stmt->execute();
        }
    } else {
        $info[] = 'Не заполнены обязательные поля!';
    }
}
// Изменение записи
if (isset($_POST['fid_organization_upd'], $_POST['number_phone_upd'] )) {
    $number_phone_upd = htmlspecialchars(stripslashes($_POST['number_phone_upd']));
    $fid_organization_upd = intval($_POST['fid_organization_upd']);

    if (!empty($number_phone_upd) && !empty($fid_organization_upd)) {
        $query_any_repeat = "SELECT * FROM $tblname WHERE number_phone_organization = ? AND fid_organization = ? AND id_numbers != ?";
        $stmt = $conn->prepare($query_any_repeat);
        $stmt->bind_param("sii", $number_phone_upd, $fid_organization_upd, $_SESSION['updateRow']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows != 0) {
            $info[] = 'Такая запись уже существует!';
        } else {
            $query_upd = "UPDATE $tblname SET number_phone_organization = ?, fid_organization = ? WHERE id_numbers = ?";
            $stmt = $conn->prepare($query_upd);
            $stmt->bind_param("sii", $number_phone_upd, $fid_organization_upd, $_SESSION['updateRow']);
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
        <h1>Таблица "Номеров телефонов организации"</h1>
        <?php
        // Получаем все страны для выпадающего списка
        // Вывод форм для редактирования
        $query_org = "SELECT o.id_organization, CONCAT('Страна: ', cn.name_country, ' Город: ', c.name_city, ' Улица, Дом: ', o.street, ' ', o.house, ' Название организации:', o.name_organization) AS FullAdd
            FROM organization o
            JOIN city c ON o.fid_city = c.id_city 
            JOIN country cn ON c.fid_country = cn.id_country 
            ORDER BY name_country, name_city";
            $result_org = $conn->query($query_org);
        if (isset($_POST['input']) && $_POST['input'] == 'add') { // Добавление
            echo "<form method=\"post\" action=\"\">";
            echo "<table align=\"center\">";
            echo "<tr><td>Выбор организации:</td><td><select name=\"fid_organization_add\">";
            while ($row_org = $result_org->fetch_assoc()) {
                echo "<option value=\"".$row_org['id_organization'] . "\">" 
                . $row_org['FullAdd'] . "</option>";
            }
            echo "</select></td></tr>";
            echo "<tr><td>Введи номер телефона: </td><td><input type=\"text\" size=\"70\" maxlength=\"20\" name=\"number_phone_add\"/></td></tr>";
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
                $query_form_upd = "SELECT * FROM $tblname WHERE id_numbers = ?";
                $stmt = $conn->prepare($query_form_upd);
                $stmt->bind_param("i", $_POST['chooseRow']);
                $stmt->execute();
                $result_form_upd = $stmt->get_result();
                
                echo "<form method=\"post\" action=\"\">";
                echo "<table align=\"center\">";
                $line_content = $result_form_upd->fetch_array(MYSQLI_NUM);
                echo "<tr><td>Выбор организации:</td><td><select name=\"fid_organization_upd\">";
                while ($row_org = $result_org->fetch_assoc()) {
                    $selected = ($row_org['id_organization'] == $line_content[1]) ? 'selected' : '';
                    echo "<option value=\"".$row_org['id_organization'] . "\" $selected>" 
                    . $row_org['FullAdd'] . "</option>";
                }
                echo "</select></td></tr>";
                echo "<tr><td>Введи номер телефона: </td><td><input type=\"text\" size=\"70\" maxlength=\"20\" name=\"number_phone_upd\" value=\"" . $line_content[2] . "\"/></td></tr>";
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
                $numberID = $_POST['chooseRow'];

                $query_del = "DELETE FROM $tblname WHERE id_numbers = ?";
                $stmt = $conn->prepare($query_del);
                $stmt->bind_param("i", $numberID);
                $stmt->execute();
                $info[] = 'Запись успешно удалена!';
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
            SELECT p.id_numbers, p.number_phone_organization, p.fid_organization, o.name_organization
            FROM $tblname p
            JOIN organization o ON p.fid_organization = o.id_organization
            ORDER BY p.fid_organization";
            $result_content = $conn->query($query_content);

            echo "<table class=\"tbl\" align=\"center\">";
            echo "<tr class=\"header\">";
            echo "<th>&nbsp;</th>";
            echo "<th>ID записи</th>";
            echo "<th>Номер телефона организации</th>";
            echo "<th>FID организации</th>";
            echo "<th>Название организации</th>";
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
