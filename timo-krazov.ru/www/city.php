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
$tblname = 'city'; // Имя таблицы
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
if (isset($_POST['name_city_add'], $_POST['phone_city_code_add'], $_POST['fid_country_add'])) {
    $name_city_add = htmlspecialchars(stripslashes($_POST['name_city_add']));
    $phone_city_code_add = htmlspecialchars(stripslashes($_POST['phone_city_code_add']));
    $fid_country_add = intval($_POST['fid_country_add']); // ID выбранной страны

    if (!empty($name_city_add) && !empty($phone_city_code_add)&& !empty($fid_country_add)) {
        $query_any_repeat = "SELECT * FROM $tblname WHERE name_city = ? AND fid_country = ?";
        $stmt = $conn->prepare($query_any_repeat);
        $stmt->bind_param("si", $name_city_add, $fid_country_add);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows != 0) {
            $info[] = 'Такая запись уже существует!';
        } else {
            $query_add = "INSERT INTO $tblname (name_city, phone_city_code, fid_country) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query_add);
            $stmt->bind_param("ssi", $name_city_add, $phone_city_code_add, $fid_country_add);
            $stmt->execute();
        }
    } else {
        $info[] = 'Не заполнены обязательные поля!';
    }
}

// Изменение записи
if (isset($_POST['name_city_upd'], $_POST['phone_city_code_upd'], $_POST['fid_country_upd'])) {
    $name_city_upd = htmlspecialchars(stripslashes($_POST['name_city_upd']));
    $phone_city_code_upd = htmlspecialchars(stripslashes($_POST['phone_city_code_upd']));
    $fid_country_upd = intval($_POST['fid_country_upd']); // ID выбранной страны

    if (!empty($name_city_upd) && !empty($phone_city_code_upd) && !empty($fid_country_upd)) {
        $query_any_repeat = "SELECT * FROM $tblname WHERE name_city = ? AND fid_country = ? AND id_city != ?";
        $stmt = $conn->prepare($query_any_repeat);
        $stmt->bind_param("sii", $name_city_upd, $fid_country_upd, $_SESSION['updateRow']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows != 0) {
            $info[] = 'Такая запись уже существует!';
        } else {
            $query_upd = "UPDATE $tblname SET name_city = ?, phone_city_code = ?, fid_country = ? WHERE id_city = ?";
            $stmt = $conn->prepare($query_upd);
            $stmt->bind_param("ssii", $name_city_upd, $phone_city_code_upd, $fid_country_upd, $_SESSION['updateRow']);
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
        <h1>Таблица "Города"</h1>
        <?php
        // Получаем все страны для выпадающего списка
        $query_countries = "SELECT id_country, name_country FROM country ORDER BY name_country";
        $result_countries = $conn->query($query_countries);
        // Вывод форм для редактирования
        if (isset($_POST['input']) && $_POST['input'] == 'add') { // Добавление
            echo "<form method=\"post\" action=\"\">";
            echo "<table align=\"center\">";
            
                    
            echo "<tr>";
            echo "<td>Название страны: </td>";
            echo "<td><select name=\"fid_country_add\">";
            while ($row_country = $result_countries->fetch_assoc()) {
                echo "<option value=\"" . $row_country['id_country'] . "\">" . $row_country['name_country'] . "</option>";
            }
            echo "</select></td>";
            echo "</tr>";
            echo "<tr><td>Введи название города: </td><td><input type=\"text\" maxlength=\"60\" size=\"70\" name=\"name_city_add\"/></td></tr>";
            echo "<tr><td>Введи телефонный код города: </td><td><input type=\"text\" maxlength=\"10\" pattern=\"[0-9()+]+\" oninput=\"this.value = this.value.replace(/[^0-9()+ -]/g, '')\" title=\"Разрешены 0-9,  (, ), +, -, \" size=\"70\" name=\"phone_city_code_add\"/></td></tr>";
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
                $query_form_upd = "SELECT * FROM $tblname WHERE id_city = ?";
                $stmt = $conn->prepare($query_form_upd);
                $stmt->bind_param("i", $_POST['chooseRow']);
                $stmt->execute();
                $result_form_upd = $stmt->get_result();
                
                echo "<form method=\"post\" action=\"\">";
                echo "<table align=\"center\">";
                $line_content = $result_form_upd->fetch_array(MYSQLI_NUM);
                
                echo "<td>Страна</td>";
                echo "<td><select name=\"fid_country_upd\">";
                while ($row_country = $result_countries->fetch_assoc()) {
                    // Устанавливаем выбранную страну
                    $selected = ($row_country['id_country'] == $line_content[1]) ? 'selected' : '';
                    echo "<option value=\"" . $row_country['id_country'] . "\" $selected>" . $row_country['name_country'] . "</option>";
                }
                echo "</select></td>";
                echo "<tr><td>Введи название города: </td><td><input type=\"text\" size=\"70\" maxlength=\"60\" name=\"name_city_upd\" value=\"" . $line_content[2] . "\"/></td></tr>";
                echo "<tr><td>Введи телефонный код города: </td><td><input type=\"text\" size=\"70\" maxlength=\"10\" pattern=\"[0-9()+]+\" oninput=\"this.value = this.value.replace(/[^0-9()+ -]/g, '')\" title=\"Разрешены 0-9,  (, ), +, -, \" name=\"phone_city_code_upd\" value=\"" . $line_content[3] . "\"/></td></tr>";

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
                $cityId = $_POST['chooseRow'];
    
                // Проверка наличия городов, связанных с этой страной
                $query_check_individuals = "SELECT COUNT(*) FROM natural_person WHERE fid_city = ?";
                $stmt_check = $conn->prepare($query_check_individuals);
                $stmt_check->bind_param("i", $cityId);
                $stmt_check->execute();
                $stmt_check->bind_result($natur_city);
                $stmt_check->fetch();
                $stmt_check->close();

                // Проверка наличия городов, связанных с этой страной
                $query_check_organizations = "SELECT COUNT(*) FROM organization WHERE fid_city = ?";
                $stmt_check = $conn->prepare($query_check_organizations);
                $stmt_check->bind_param("i", $cityId);
                $stmt_check->execute();
                $stmt_check->bind_result($org_city);
                $stmt_check->fetch();
                $stmt_check->close();
                if (($natur_city > 0) || ($org_city > 0)) {
                    $info[] = 'Невозможно удалить запись!';
                } else {
                    // Удаляем страну, если нет связанных городов
                    $query_del = "DELETE FROM $tblname WHERE id_city = ?";
                    $stmt = $conn->prepare($query_del);
                    $stmt->bind_param("i", $cityId);
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
            SELECT c.id_city, c.name_city, c.phone_city_code, c.fid_country, cn.name_country 
            FROM $tblname c 
            JOIN country cn ON c.fid_country = cn.id_country 
            ORDER BY c.fid_country";
            $result_content = $conn->query($query_content);

            echo "<table class=\"tbl\" align=\"center\">";
            echo "<tr class=\"header\">";
            echo "<th>&nbsp;</th>";
            echo "<th>ID Города</th>";
            echo "<th>Название Города</th>";
            echo "<th>Телефонный код</th>";
            echo "<th>FID Страны</th>";
            echo "<th>Название Страны</th>";
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
