<?php
var_dump(1);die;
$host = 'localhost';
$db   = 'c14457_mcolmed_ru';
$user = 'c14457_mcolmed_ru';
$pass = 'SeZduJopqiwol21';
$charset = 'utf8';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// обвновляем структуру
$pdo = new PDO($dsn, $user, $pass, $opt);
$query= $pdo->query("SELECT * FROM structures WHERE path REGEXP '^[0-9]*$'");
$structures = $query->fetchAll();
$pdo = null;
$data_structures = array();
foreach ($structures as $structure) {

    if($structure['path'] == '404' || $structure['path'] == '403' || $structure['path'] == '503'){
        continue;
    }

    $data_structures[$structure['id']] = array(
        'old_path' => $structure['path'],
        'new_path' => translit($structure['name']),
        'site_id' => $structure['site_id'],
    );

}

// обвновляем структуру

// обвновляем группу
$pdo = new PDO($dsn, $user, $pass, $opt);

$query= $pdo->query("SELECT id, informationsystem_id, name, parent_id, path FROM informationsystem_groups WHERE path REGEXP '^[0-9]*$'");
$groups = $query->fetchAll();
//var_dump($groups);die;
$pdo = null;
$data_groups = array();
foreach ($groups as $group){
    //var_dump($group);die;
    $pdo = new PDO($dsn, $user, $pass, $opt);
    if($group['parent_id'] == 0){
        //var_dump(2, $group);die;
        $query= $pdo->query("SELECT infsys.structure_id, str.path, str.site_id FROM informationsystems infsys INNER JOIN structures str ON infsys.structure_id = str.id WHERE infsys.id = " . $group['informationsystem_id'] );
        $informationsystem = $query->fetch();
        //var_dump($informationsystem, $group['informationsystem_id'], translit($group['name']));die;

        $data_groups[$group['id']] = array(
            'path_new' => translit($group['name']),
            'old_url' => '/' . $informationsystem['path'] . '/' . $group['path'] . '/',
            'new_url' => '/' . $informationsystem['path'] . '/' . translit($group['name']) . '/',
            'site_id' => $informationsystem['site_id'],
        );
    }else{
        $query = $pdo->query("SELECT id, informationsystem_id, name, parent_id, path FROM informationsystem_groups WHERE id = " . $group['parent_id']);
        $parent = $query->fetch();

        $checked_parent_path = is_numeric($parent['name']);
        //var_dump($checked_parent_path);die;
        if($checked_parent_path){
            $parent_path = translit($parent['name']);
        }else{
            $parent_path = $parent['path'];
        }

        $query= $pdo->query("SELECT infsys.structure_id, str.path, str.site_id FROM informationsystems infsys INNER JOIN structures str ON infsys.structure_id = str.id WHERE infsys.id = " . $group['informationsystem_id'] );
        $informationsystem = $query->fetch();
        //var_dump($parent);die;
        $data_groups[$group['id']] = array(
            'path_new' => translit($group['name']),
            'old_url' => '/' . $informationsystem['path'] . '/' . $parent['path'] . '/' . $group['path'] . '/',
            'new_url' => '/' . $informationsystem['path'] . '/' . $parent_path . '/' . translit($group['name']) . '/',
            'site_id' => $informationsystem['site_id'],
        );


    }

    $pdo = null;
    unset($query);
    unset($informationsystem);
    unset($parent);

}


// обвновляем группу


// обновляем items
$pdo = new PDO($dsn, $user, $pass, $opt);

$query= $pdo->query("SELECT id, informationsystem_id, name, informationsystem_group_id, path FROM informationsystem_items WHERE path REGEXP '^[0-9]*$'");
$items = $query->fetchAll();
$pdo = null;
$data_items = array();

foreach ($items as $item){
    if($item['informationsystem_id'] == 2 || $item['informationsystem_id'] == 67){
        continue;
    }
    $pdo = new PDO($dsn, $user, $pass, $opt);

    $query = $pdo->query("SELECT structure_id FROM informationsystems WHERE id = " . $item['informationsystem_id'] );
    $check_empty_item = $query->fetch();

    if( $check_empty_item['structure_id'] == 0 || empty($item['name'])){
        continue;
    }else{

        if($item['informationsystem_group_id'] == 0){

            $query= $pdo->query("SELECT infsys.structure_id, str.path, str.site_id FROM informationsystems infsys INNER JOIN structures str ON infsys.structure_id = str.id WHERE infsys.id = " . $item['informationsystem_id'] );
            $informationsystem = $query->fetch();


            $data_items[$item['id']] = array(
                'path_new' => translit($item['name']),
                'old_url' => '/' . $informationsystem['path'] . '/' . $item['path'] . '/',
                'new_url' => '/' . $informationsystem['path'] . '/' . translit($item['name']) . '/',
                'site_id' => $informationsystem['site_id'],
            );

        }else{

            $query = $pdo->query("SELECT id, informationsystem_id, name, parent_id, path FROM informationsystem_groups WHERE id = " . $item['informationsystem_group_id']);
            $parent = $query->fetch();

            $checked_parent_path = is_numeric($parent['path']);

            if($checked_parent_path){

                $parent_path = translit($parent['name']);
            }else{
                $parent_path = $parent['path'];
            }

            if($parent['parent_id'] != 0){

                $query = $pdo->query("SELECT id, informationsystem_id, name, parent_id, path FROM informationsystem_groups WHERE id = " . $parent['parent_id']);
                $grand_parent = $query->fetch();

                $checked_grand_parent_path = is_numeric($grand_parent['path']);

                if($checked_grand_parent_path){
                    $grand_parent_path = translit($grand_parent['name']);
                }else{
                    $grand_parent_path = $grand_parent['path'];
                }

                $query= $pdo->query("SELECT infsys.structure_id, str.path, str.site_id FROM informationsystems infsys INNER JOIN structures str ON infsys.structure_id = str.id WHERE infsys.id = " . $item['informationsystem_id'] );
                $informationsystem = $query->fetch();

                $data_items[$item['id']] = array(
                    'path_new' => translit($item['name']),
                    'old_url' => '/' . $informationsystem['path'] . '/' . $grand_parent['path'] . '/' . $parent['path'] . '/' . $item['path'] . '/',
                    'new_url' => '/' . $informationsystem['path'] . '/' . $grand_parent_path . '/' . $parent_path . '/' . translit($item['name']) . '/',
                    'site_id' => $informationsystem['site_id'],
                );
            }else{

                $query= $pdo->query("SELECT infsys.structure_id, str.path, str.site_id FROM informationsystems infsys INNER JOIN structures str ON infsys.structure_id = str.id WHERE infsys.id = " . $item['informationsystem_id'] );
                $informationsystem = $query->fetch();


                $data_items[$item['id']] = array(
                    'path_new' => translit($item['name']),
                    'old_url' => '/' . $informationsystem['path'] . '/' . $parent['path'] . '/' . $item['path'] . '/',
                    'new_url' => '/' . $informationsystem['path'] . '/' . $parent_path . '/' . translit($item['name']) . '/',
                    'site_id' => $informationsystem['site_id'],
                );
                //var_dump($item, $parent, $data_items);die;
            }

        }

        $pdo = null;
        unset($query);
        unset($informationsystem);
        unset($parent);
    }
}
// обновляем items

$pdo = new PDO($dsn, $user, $pass, $opt);
$update_structures = $pdo->prepare('UPDATE structures SET path = ? WHERE id = ?');
$update_structures_redirects = $pdo->prepare('INSERT INTO hostdev_redirects(old_url, type, new_url, active, deleted, append,  site_id) VALUES (? , 0, ?, 1, 0, 0, ?)');
//$pdo->prepare('UPDATE structures SET path = ? WHERE id = ?');

foreach ($data_structures as $id=>$data_structure) {

    $update_structures->execute(array($data_structure['new_path'],$id));
    $update_structures_redirects->execute(array('/' . $data_structure['old_path'] . '/', '/' . $data_structure['new_path']  . '/', $data_structure['site_id']));

}
$pdo = null;
unset($data_structures);

$pdo = new PDO($dsn, $user, $pass, $opt);

$update_groups = $pdo->prepare('UPDATE informationsystem_groups SET path = ? WHERE id = ?');
$update_groups_redirects = $pdo->prepare('INSERT INTO hostdev_redirects(old_url, type, new_url, active, deleted, append,  site_id) VALUES (? , 0, ?, 1, 0, 0, ?)');

foreach ($data_groups as $id=>$group){

    $update_groups->execute(array($group['path_new'],$id));
    $update_groups_redirects->execute(array($group['old_url'], $group['new_url'], $group['site_id']));

}
unset($data_groups);

$update_items = $pdo->prepare('UPDATE informationsystem_items SET path = ? WHERE id = ?');
$update_items_redirects = $pdo->prepare('INSERT INTO hostdev_redirects(old_url, type, new_url, active, deleted, append,  site_id) VALUES (? , 0, ?, 1, 0, 0, ?)');

foreach ($data_items as $id=>$item){

    $update_items->execute(array($item['path_new'],$id));
    $update_items_redirects->execute(array($item['old_url'], $item['new_url'], $item['site_id']));

}

$pdo = null;

unset($data_items);

var_dump(32423);die;





function translit($s) {
    $converter = array(
        'а' => 'a',   'б' => 'b',   'в' => 'v',
        'г' => 'g',   'д' => 'd',   'е' => 'e',
        'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
        'и' => 'i',   'й' => 'y',   'к' => 'k',
        'л' => 'l',   'м' => 'm',   'н' => 'n',
        'о' => 'o',   'п' => 'p',   'р' => 'r',
        'с' => 's',   'т' => 't',   'у' => 'u',
        'ф' => 'f',   'х' => 'h',   'ц' => 'c',
        'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
        'ь' => '',  'ы' => 'y',   'ъ' => '',
        'э' => 'e',   'ю' => 'yu',  'я' => 'ya',

        'А' => 'A',   'Б' => 'B',   'В' => 'V',
        'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
        'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
        'И' => 'I',   'Й' => 'Y',   'К' => 'K',
        'Л' => 'L',   'М' => 'M',   'Н' => 'N',
        'О' => 'O',   'П' => 'P',   'Р' => 'R',
        'С' => 'S',   'Т' => 'T',   'У' => 'U',
        'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
        'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
        'Ь' => '',  'Ы' => 'Y',   'Ъ' => '',
        'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
    );
    $s = (string) $s; // преобразуем в строковое значение
    $s = strip_tags($s); // убираем HTML-теги
    $s = str_replace(array("\n", "\r"), " ", $s); // убираем перевод каретки
    //var_dump($s);die;
    //$s = preg_replace("/\s+/", ' ', $s); // удаляем повторяющие пробелы
    $s = trim($s); // убираем пробелы в начале и конце строки
    $s = strtr($s, $converter);
    $s = strtolower($s); // переводим строку в нижний регистр (иногда надо задать локаль)
    $s = str_replace(array( "-"), " ", $s); // заменяем пробелы знаком минус
    $s = preg_replace("/[^0-9a-z-_ ]/i", "", $s); // очищаем строку от недопустимых символов
    $s = str_replace(array("   ", "  ", " ",), "-", $s); // заменяем пробелы знаком минус

    return $s; // возвращаем результат
}

