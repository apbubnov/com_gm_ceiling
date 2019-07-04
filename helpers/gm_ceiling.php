<?php
/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     SpectralEye <Xander@spectraleye.ru>
 * @copyright  2016 SpectralEye
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
/**
 * Class Gm_ceilingFrontendHelper
 *
 * @since  1.6
 */
/* включаем библиотеку для формирования PDF */
include($_SERVER['DOCUMENT_ROOT'] . "/libraries/mpdf/mpdf.php");
const VERDICT_STATUSES = [4,5,6,7,8,10,11,12,13,14,16,17,19,23,24,25,26,27,28,29];
/* функция для применения маржи */
function margin($value, $margin) {
    try {
        if($margin == 100){
            $margin = 99;
        }
        return ($value * 100 / (100 - $margin));
    }
    catch(Exception $e)
    {
        Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
    }
}
/* функция для применения сразу двойной маржи */
function double_margin($value, $margin1, $margin2) {
    try {
        return margin(margin($value, $margin1), $margin2);
    }
    catch(Exception $e)
    {
        Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
    }
}
/**
 * @license https://github.com/apbubnov/com_gm_ceiling/wiki/DealerPrice
 * @author CEH4TOP
 * @param $price
 * @param $margin
 * @param $objectDealerPrice
 * @return float|int
 */
function dealer_margin($price, $margin, $objectDealerPrice) {
    try {
        $result = 0;
        if (!empty($objectDealerPrice)) {
            $objectDealerPrice->value = floatval($objectDealerPrice->value);
            $objectDealerPrice->price = floatval($objectDealerPrice->price);
            switch ($objectDealerPrice->type)
            {
                case 0: $result = $price; break;
                case 1: $result = $objectDealerPrice->price; break;
                case 2: $result = $price + $objectDealerPrice->value; break;
                case 3: $result = $price + $price * $objectDealerPrice->value / 100; break;
                case 4: $result = $objectDealerPrice->price + $objectDealerPrice->value; break;
                case 5: $result = $objectDealerPrice->price + $objectDealerPrice->price * $objectDealerPrice->value / 100; break;
            }
        }
        else {
            $result = $price;
        }
        return ($margin > 0) ? margin($result, $margin) : $result;
    }
    catch(Exception $e)
    {
        Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
    }
 }

function delete_string_characters($string)
{
    try {
        $string = mb_ereg_replace('[^А-ЯЁа-яёA-Za-z0-9\-\@\.\_\s]', '', $string);
        $string = str_replace(array("\r","\n"), '', $string);
        $string = mb_ereg_replace('[\s]+', ' ', $string);
        $string = trim($string);
        return $string;
    }
    catch(Exception $e)
    {
        Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
    }
}

class Gm_ceilingHelpersGm_ceiling
{
    public static function add_error_in_log($error, $file, $func, $args)
    {
        try {
            $date = date("Y-m-d H:i:s");
            $req_str = '';
            foreach (getallheaders() as $name => $value) {
                $req_str .= "$name: $value\n";
            }
            $args_str = print_r($args, true);
            $get_str = print_r($_GET, true);
            $post_str = print_r($_POST, true);
            $f = fopen('php://input', 'r');
            $input = stream_get_contents($f);
            $file = array_pop(explode('com_gm_ceiling', $file));
            $ip = $_SERVER['REMOTE_ADDR'];
            $user = JFactory::getUser();
            $user_str = $user->id.' '.$user->name;
            $db = JFactory::getDbo();
            $date = $db->escape($date);
            $error = $db->escape($error);
            $file = $db->escape($file);
            $func = $db->escape($func);
            $args_str = $db->escape($args_str);
            $req_str = $db->escape($req_str);
            $ip = $db->escape($ip);
            $get_str = $db->escape($get_str);
            $post_str = $db->escape($post_str);
            $input = $db->escape($input);
            $user_str = $db->escape($user_str);
            $query = $db->getQuery(true);
            $query
                ->insert('`errors_log`')
                ->columns('`date_time`, `error`, `file`, `function`, `args`, `request_headers`, `ip`, `get`, `post`, `php_input`, `user`')
                ->values("'$date', '$error', '$file', '$func', '$args_str', '$req_str', '$ip', '$get_str', '$post_str', '$input', '$user_str'");
            $db->setQuery($query);
        }
        catch(Exception $e) {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            die("Что-то пошло не так...");
        }
        try {
            $db->execute();
        }
        catch(Exception $e) {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$query."\n----------\n", FILE_APPEND);
            die("Что-то пошло не так...");
        }
        $code = $db->insertid();
        throw new Exception('Ошибка!', $code);
    }

    public static function margin($value, $margin) {
        return margin($value, $margin);
    }
    public static function double_margin($value, $margin1, $margin2) {
        return double_margin($value, $margin1, $margin2);
    }
    public static function dealer_margin($price, $margin, $objectDealerPrice) {
        return dealer_margin($price, $margin, $objectDealerPrice);
    }
    /**
     * Get an instance of the named modelt
     *
     * @param   string $name Model name
     *
     * @return null|object
     */
    public static function getModel($name)
    {
        try {
            $model = null;
            // If the file exists, let's
            if (file_exists(JPATH_SITE . '/components/com_gm_ceiling/models/' . strtolower($name) . '.php')) {
                require_once JPATH_SITE . '/components/com_gm_ceiling/models/' . strtolower($name) . '.php';
                $model = JModelLegacy::getInstance($name, 'Gm_ceilingModel');
            }
            return $model;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public static function getController($name)
    {
        try {
            $controller = null;
            // If the file exists, let's
            if (file_exists(JPATH_SITE . '/components/com_gm_ceiling/controllers/' . strtolower($name) . '.php')) {
                require_once JPATH_SITE . '/components/com_gm_ceiling/controllers/' . strtolower($name) . '.php';
                $controller = JModelLegacy::getInstance($name, 'Gm_ceilingController');
            }
            return $controller;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    /**
     * Gets the files attached to an item
     *
     * @param   int $pk The item's id
     *
     * @param   string $table The table's name
     *
     * @param   string $field The field's name
     *
     * @return  array  The files
     */
    public static function getFiles($pk, $table, $field)
    {
        try {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select($field)
                ->from($table)
                ->where('id = ' . (int)$pk);
            $db->setQuery($query);
            return explode(',', $db->loadResult());
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public static function registerUser($FIO, $phone, $email, $client_id, $type = null)
    {
        try {
            jimport('joomla.user.helper');
            if(empty($type)){
                $type = 1;
            }
            $phone = mb_ereg_replace('[^\d]', '', $phone);
            if (mb_substr($phone, 0, 1) == '9' && strlen($phone) == 10)
            {
                $phone = '7'.$phone;
            }
            if (strlen($phone) != 11)
            {
                throw new Exception('Неверный формат номера телефона.');
            }
            if (mb_substr($phone, 0, 1) != '7')
            {
                $phone = substr_replace($phone, '7', 0, 1);
            }

            $data = array(
                "name" => $FIO,
                "username" => $phone,
                "password" => $phone,
                "password2" => $phone,
                "email" => $email,
                "groups" => array(2, 14),
                "phone" => $phone,
                "block" => 0,
                "dealer_type" => $type
            );
            $user = new JUser;
            if (!$user->bind($data)) {
                throw new Exception($user->getError());
            }
            if (!$user->save()) {
                throw new Exception($user->getError());
            }
            $userID = $user->id;
            $user =& JUser::getInstance((int)$userID);
            if($type == 3 || $type == 2|| $type == 7|| $type == 8){
                $post['dealer_id'] = 1;
            }
            else {
                $post['dealer_id'] = $userID;
            }

            $post['associated_client'] = $client_id;
            if (!$user->bind($post)) return false;
            if (!$user->save()) return false;
            JFactory::getApplication()->enqueueMessage("Добавлен новый дилер!");
            $margin_model = self::getModel('Dealer_info');
            $mount_model = self::getModel('mount');
            $gm_margin = $margin_model->getDataById(1);
            if ($type == 3 || $type == 2 || $type == 7|| $type == 8)
            {
                $gm_mount = $mount_model->getDataAll(1);
                $margin_model->save($gm_margin->dealer_canvases_margin,$gm_margin->dealer_components_margin,$gm_margin->dealer_mounting_margin,$gm_margin->gm_canvases_margin,$gm_margin->gm_components_margin,$gm_margin->gm_mounting_margin,$userID,$gm_margin->discount);
            }
            else
            {
                $gm_mount = $mount_model->getDataAll(0);
                $margin_model->save(0,0,0,$gm_margin->gm_canvases_margin,$gm_margin->gm_components_margin,$gm_margin->gm_mounting_margin,$userID,$gm_margin->discount);
            }
                $gm_mount->user_id = $userID;
                $mount_model->insert($gm_mount);

            return $userID;
        }
        catch(Exception $e)
        {
            if (!empty($client_id)) {
                Gm_ceilingHelpersGm_ceiling::getModel('Client')->delete($client_id);
            }
            die($e->getMessage().' ');
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    /*  основная функция для расчета стоимости потолка
        $from_db - 0,1 флаг, брать ли данные калькуляции из БД
        $calculation_id - id калькуляции в БД
        $save - 0,1 флаг, чтобы сохранить калькуляцию в БД
        $ajax - 0,1 флаг AJAX-запроса
        $pdf - 0,1 флаг формирования PDF
        $print_components - 0,1 флаг возвращения расчета при вызове в переменную
    */
    public static function calculate($from_db, $calculation_id, $save, $pdf, $del_flag, $need_mount,$gm_mounters=null){
        try {
            $jinput = JFactory::getApplication()->input;
            /*____________________Модели_______________________*/
            $canvases_model = Gm_ceilingHelpersGm_ceiling::getModel('canvases');
            $calculation_model = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
            /*_________________________________________________*/

            $calculation_data = $calculation_model->getData($calculation_id);
            $calculation_data2 = (array) $calculation_model->getDataById($calculation_id);
            foreach ($calculation_data as $key => $item) {
                if (empty($item) && array_key_exists($key, $calculation_data2))
                    $data[$key] = $calculation_data2[$key];
                else
                    $data[$key] = $item;
            }
            if(!$from_db){
                   //Получаем данные
                $data_form = $jinput->getArray(array(
                    'jform' => array(
                        'id' => 'int', //id потолка
                        'n6' => 'int', //Со вставкой
                        'n7' => 'string', //Крепление в плитку
                        'n8' => 'string', //Крепление в керамогранит
                        'n9' => 'int', //Углы
                        'n10' => 'string', //Криволинейный участок
                        'n11' => 'string', //Внутренний вырез
                        'n12' => 'int', //Упрощенные люстры
                        'n13' => 'int', //Упрощенные светильники
                        'n14' => 'int', //Упрощенные обводы труб
                        'n15' => 'int', //Шторный карниз
                        'n16' => 'int', //Скрытый карниз
                        'n17' => 'string', //Закладная брусом
                        'n18' => 'string', //Укрепление стен
                        'n20' => 'string', //Разделитель
                        'n20_1' => 'string', //отбойник
                        'n21' => 'string', //Пожарная сигнализация
                        'n22' => 'int',
                        'n22_1' => 'string',
                        'n23' => 'int',
                        'n24' => 'string', //Сложность к месту доступа
                        'n26' => 'int',
                        'n27' => 'string',
                        'n28' => 'int',
                        'remove_n28' => 'string',
                        'n29' => 'string',
                        'n30' => 'string',
                        'n31' => 'string',
                        'n32' => 'int',
                        'n33' => 'int',//люк
                        'n33_2' => 'string',//обход люка
                        'n34' => 'string', //диодная лента
                        'n34_2' => 'int', // блок питания
                        'n35' => 'string',//контурный профиль
                        'n36' => 'string',//перегарпунка
                        'n37_square' => 'string', //плозадь фотопечати
                        'n37_cost' => 'string', // цена фотопечати
                        'n38' => 'int', //ремонт потолка
                        'n39' => 'string',
                        'n40' => 'int',
                        'n41' => 'int',
                        'n42' => 'int',
                        'n43' => 'int',
                        'n44' => 'string',
                        'niche' => 'int',
                        'height'=>'int',
                        'dop_krepezh' => 'string', //Доп. крепеж
                        'calculation_title' => 'string',
                        'color' => 'string', //цвет
                        'details' => 'string', //цвет
                        'offcut_square' => 'string',
                        'discount' => 'int',
                        'need_metiz' => 'int',
                        'need_cuts' => 'int',
                        'scaffolding'=>'int'
                        // 'rek' => 'int',
                        // 'proizv' => 'string'
                    )
                ));


                $data_form = $data_form['jform'];
                //throw new Exception(print_r($data_form,true));
                foreach ($data_form as $key => $value)
                {
                    if (mb_ereg('^[\d]+[\,]{1}[\d]+$', $value)) {
                        $value = str_replace(',', '.', $value);
                    }
                    if (mb_ereg('^[\d]+[\.]{1}[\d]+$', $value) || mb_ereg('^[\d]+$', $value) || $value == '') {
                        $value = floatval($value);
                    }
                    //если не гмменеджер и не нужны обрезки ставим что нужны(костыль из-за того что нет на странице и он принимает как ноль)
                    if($key == "need_cuts"){
                        if(empty($value) && !in_array('16',JFactory::getUser()->groups)){
                            $value = 1;
                        }
                    }
                    $data[$key] = $value;

                }
            }

            if(!empty($data['n3_id'])){
                $canvasData = $canvases_model->getFilteredItemsCanvas("`a`.`id` =". $data['n3_id']);
                $data['n1'] = $canvasData[0]->texture_id;
            }
            else{
                $data['n1'] = null;
            }

            //ecola
            $ecola_count = $jinput->get('ecola_count', array(), 'ARRAY');
            $ecola_type = $jinput->get('light_color', array(), 'ARRAY');
            $ecola_lamps = $jinput->get('light_lamp_color', array(), 'ARRAY');
            $n26 = array();
            if (count($ecola_count) >= 1 && !empty($ecola_count[0])) {
                foreach ($ecola_count as $key => $each) {
                    if ($ecola_count[$key] != "")
                        $n26[] = array(
                            $ecola_count[$key],
                            $ecola_type[$key],
                            $ecola_lamps[$key]
                        );
                }
                $data['n26'] = json_encode($n26);
            }
            //Получаем массив из переменной светильников
            $n13_count = $jinput->get('n13_count', array(), 'ARRAY');
            $n13_type = $jinput->get('n13_type', array(), 'ARRAY');
            $n13_ring = $jinput->get('n13_ring', array(), 'ARRAY');
            $n13 = array();
            if (count($n13_count) >= 1 && !empty($n13_count[0])) {
                foreach ($n13_count as $key => $each) {
                    if ($n13_count[$key] != "")
                        $n13[] = array(
                            $n13_count[$key],
                            $n13_type[$key],
                            $n13_ring[$key]
                        );
                }
                $data['n13'] = json_encode($n13);
            }
            //Получаем массив из переменной обвода труб
            $n14_count = $jinput->get('n14_count', array(), 'ARRAY');
            $n14_type = $jinput->get('n14_type', array(), 'ARRAY');
            $n14 = array();
            if (count($n14_count) >= 1 && !empty($n14_count[0])) {
                foreach ($n14_count as $key => $each) {
                    $n14[] = array(
                        $n14_count[$key],
                        $n14_type[$key]
                    );
                }
                $data['n14'] = json_encode($n14);
            }
            //провода
            $n19_count = $jinput->get('n19_count',array(),"ARRAY");
            $n19_type = $jinput->get('n19_type', array(), 'ARRAY');
            if (count($n19_count) >= 1 && !empty($n19_count[0])) {
                foreach ($n19_count as $key => $each) {
                    $n19[] = array(
                        $n19_count[$key],
                        $n19_type[$key]
                    );
                }
                $data['n19'] = json_encode($n19);
            }
            //Получаем массив из переменной вентиляции
            $n22_count = $jinput->get('n22_count', array(), 'ARRAY');
            $n22_type = $jinput->get('n22_type', array(), 'ARRAY');
            $n22_diam = $jinput->get('n22_diam', array(), 'ARRAY');
            $n22 = array();
            if (count($n22_count) >= 1 && !empty($n22_count[0])) {
                foreach ($n22_count as $key => $each) {
                    $n22[] = array(
                        $n22_count[$key],
                        $n22_type[$key],
                        $n22_diam[$key]
                    );
                }
                $data['n22'] = json_encode($n22);
            }
            $n23_count = $jinput->get('n23_count', array(), 'ARRAY');
            $n23_size = $jinput->get('n23_size', array(), 'ARRAY');
            $n23 = array();
            if (count($n23_count) >= 1 && !empty($n23_count[0])) {
                foreach ($n23_count as $key => $each) {
                    $n23[] = array(
                        $n23_count[$key],
                        $n23_size[$key]
                    );
                }
                $data['n23'] = json_encode($n23);
            }
            $n15_count = $jinput->get('n15_count', array(), 'ARRAY');
            $n15_type = $jinput->get('n15_type', array(), 'ARRAY');
            $n15_size = $jinput->get('n15_size', array(), 'ARRAY');
            $n15 = array();
            if (count($n15_count) >= 1 && !empty($n15_count[0])) {
                foreach ($n15_count as $key => $each) {
                    if ($n15_count[$key] != "")
                        $n15[] = array(
                            $n15_count[$key],
                            $n15_type[$key],
                            $n15_size[$key]
                        );
                }
                $data['n15'] = json_encode($n15);
            }
            $n29_count = $jinput->get('n29_count', array(), 'ARRAY');
            $n29_type = $jinput->get('n29_type', array(), 'ARRAY');
            //$n29_profil = $jinput->get('n29_profil', array(), 'ARRAY');
            $n29 = array();
            if (count($n29_count) >= 1 && !empty($n29_count[0])) {
                foreach ($n29_count as $key => $each) {
                    if ($n29_count[$key] != "")
                        $n29[] = array(
                            $n29_count[$key],
                            $n29_type[$key]
                            //$n29_profil[$key]
                        );
                }
                $data['n29'] = json_encode($n29);
            }

            //фотопечать
            $n37 = (object)array("square"=>$data['n37_square'],"cost"=>$data['n37_cost']);
            //Получаем массив из переменной дополнительных комплектующих со склада
            $components_title_stock = $jinput->get('components_stock_title', '-', 'ARRAY');
            if ($components_title_stock == '-')
                $components_title_stock = $_POST['components_stock_title'];
            $components_value_stock = $jinput->get('components_stock_value', '-', 'ARRAY');
            if ($components_value_stock == '-')
                $components_value_stock = $_POST['components_stock_value'];
            $components_stock = array();
            if ($components_title_stock !== '-' && !empty($components_title_stock)) {
                foreach ($components_title_stock as $key => $title) {
                    if (!empty($title) && $components_value_stock[$key]) {
                        $components_stock[] = array(
                            'title' => $title,
                            'value' => $components_value_stock[$key]
                        );
                    }
                }
            }
            //может отвалиться
            $data['components_stock'] = json_encode($components_stock, JSON_UNESCAPED_UNICODE);
            //Получаем массив из переменной дополнительных комплектующих
            $extra_components_title = $jinput->get('extra_components_title', '-', 'ARRAY');
            $extra_components_value = $jinput->get('extra_components_value', '-', 'ARRAY');
            $extra_components = array();
            if ($extra_components_title !== '-') {
                foreach ($extra_components_title as $key => $title) {
                    if (!empty($title) && $extra_components_value[$key]) {
                        $extra_components[] = array(
                            'title' => $title,
                            'value' => $extra_components_value[$key]
                        );
                    }
                }
            }
            //может отвалиться
            $data['extra_components'] = json_encode($extra_components, JSON_UNESCAPED_UNICODE);
            //Получаем массив из переменной дополнительных монтажных работ
            $extra_mounting_title = $jinput->get('extra_mounting_title', '-', 'ARRAY');
            $extra_mounting_value = $jinput->get('extra_mounting_value', '-', 'ARRAY');
            $extra_mounting = array();
            if ($extra_mounting_title !== '-') {
                foreach ($extra_mounting_title as $key => $title) {
                    if (!empty($title) && $extra_mounting_value[$key]) {
                        $extra_mounting[] = array(
                            'title' => $title,
                            'value' => $extra_mounting_value[$key]
                        );
                    }
                }
            }
            $data['extra_mounting'] = json_encode($extra_mounting, JSON_UNESCAPED_UNICODE);
            if(!empty($n37->square)&&!empty($n37->cost)) {
                $data['n37'] = json_encode($n37);
            }
            else{
                $data['n37'] = null;
            }
            $data["need_mount"] = $need_mount;
            //Получаем объект дилера
            /*Сделано, что бы при расчете ГМ в проекте дилера цены были дилерские*/
            $data['dealer_id'] = $ProjectData = self::getModel("project")->getData($data["project_id"])->dealer_id;
            if (gettype($data) == "array")
            {
                if (empty($data['dealer_id'])) {
                    $dealer = JFactory::getUser(2);
                } else {
                    $dealer = JFactory::getUser($data['dealer_id']);
                }
            } else {
                if (empty($data->dealer_id)) {
                    $dealer = JFactory::getUser(2);
                } else {
                    $dealer = JFactory::getUser($data->dealer_id);
                }
            }
            //счиатем работы ГМ
            $guild_data = self::calculate_guild_jobs(null,$data);
            $data["guild_data"] = $guild_data;
            if(!empty($data['n1'])){
                //если не перегарпунка, то счиатаем полотно
                if(empty(floatval($data['n36']))) {
                    //cчитаем полотно
                    $canvases_data = self::calculate_canvases(null, $data);
                    //считаем обрезки
                    if($canvas->texture_id == 29){
                        $data['need_cuts'] = 0;//если ткань не считаем образки
                    }
                    if($data['need_cuts']) {
                        $offcut_square_data = self::calculate_offcut(null, $data);
                    }
                }
            }
           //считаем комплектующие
            $components_data = self::calculate_components(null,$data,$del_flag);
            if(!empty($components_data)){
                $calcsCompModel = self::getModel('Calcs_components');
                $calcsCompModel->save($data['id'],$components_data);
            }
            //считаем монтаж
            $data["need_mount_extra"] = !empty((array) json_decode($data['extra_mounting']));
            $mounting_data = self::calculate_mount($del_flag,null,$data,null,$gm_mounters);
            //Итоговая сумма компонентов
            $min_sum = 200;
            //Прибавляем к подсчету комплектующие
            $components_sum = 0;
            $gm_components_sum = 0;
            $dealer_components_sum = 0;
            foreach ($components_data as $component_item) {
                $components_sum += $component_item['self_dealer_total'];
                $gm_components_sum += $component_item['gm_total'];
                $dealer_components_sum += $component_item['dealer_total'];
            }
            $total_with_gm_dealer_margin  = $mounting_data['total_with_gm_dealer_margin'];
            $total_with_gm_dealer_margin_guild = $mounting_data['total_with_gm_dealer_margin_guild'];
            $total_dealer_mounting = $mounting_data['total_dealer_mounting'];
            //Получаем скидку
            $new_discount = $data['discount'];
            //Сюда забиваем ответ в JSON
            $ajax_return = array();
            /*если минимальный заказ,то полотно 200 рублей, обрезки и работу цеха не считаем*/
            if($canvases_data['min_self_dealer_total']){
                $data['canvases_sum'] = $min_sum;
                $canvases_data['self_dealer_total'] = $canvases_data['min_self_dealer_total'];
                $canvases_data['dealer_total'] = $canvases_data['min_dealer_total'];
                $offcut_square_data['dealer_total'] = 0;
                $offcut_square_data['self_dealer_total'] = 0;
                $total_with_gm_dealer_margin_guild = 0;
                $data['guild_data']['total_dealer_guild'] = 0;
                $data["guild_data"]["total_gm_guild"] = 0;
            }
            $ajax_return['canv_arr'] = $canvases_data;
            $ajax_return['comp_arr'] = $components_data;
            $ajax_return['total_sum'] = round($canvases_data['dealer_total'] + $offcut_square_data['dealer_total'] + $dealer_components_sum + $total_with_gm_dealer_margin + $total_with_gm_dealer_margin_guild + $data['guild_data']['total_dealer_guild'], 2);
            $ajax_return['project_discount'] = $new_discount;
            $ajax_return['canvases_sum'] = $canvases_data['self_dealer_total'] + $offcut_square_data['self_dealer_total'] + $data["guild_data"]["total_gm_guild"];
            $ajax_return['components_sum'] = $components_sum;
            $ajax_return['mounting_sum'] = $total_dealer_mounting;
            $ajax_return['dealer_components_sum'] = $dealer_components_sum;
            $ajax_return['dealer_canvases_sum'] = $canvases_data['dealer_total'] + $offcut_square_data['dealer_total'] + $data["guild_data"]["total_dealer_guild"];
            $ajax_return['dealer_mounting_sum'] = $total_with_gm_dealer_margin + $total_with_gm_dealer_margin_guild;
            $ajax_return['mounting_arr'] = $mounting_data;
            $data['canvases_sum'] = $canvases_data['self_dealer_total'] + $offcut_square_data['self_dealer_total'] + $data["guild_data"]["total_gm_guild"];
            $data['components_sum'] = $components_sum;
            $data['dealer_canvases_sum'] = $canvases_data['dealer_total'] + $offcut_square_data['dealer_total'] + $data['guild_data']['total_dealer_guild'];
            $data['dealer_components_sum'] = $dealer_components_sum;
            $data['mounting_sum'] = $total_dealer_mounting;
            $data['project_discount'] = $dealer->discount;
            $data["state"] = 1;
            $data["checked_out"] = 0;
            $data["checked_out_time"] = "00.00.0000 00:00";
            $data["created_by"] = $user->id;
            $data["modified_by"] = $user->id;
            /*Временный костыль*/
            if (!empty($data["id"]))
            {
                $temp_calculation_model = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
                $temp_calculation_data = $temp_calculation_model->getData($data["id"]);
                if (empty($data["n1"])) $data["n1"] = $temp_calculation_data->n1;
                if (empty($data["n2"])) $data["n2"] = $temp_calculation_data->n2;
                if (empty($data["n3"])) $data["n3"] = $temp_calculation_data->n3;
                if (empty($data["calc_data"])) $data["calc_data"] = $temp_calculation_data->calc_data;
                if (empty($data["cut_data"])) $data["cut_data"] = $temp_calculation_data->cut_data;
                if (empty($data["original_sketch"])) $data['original_sketch'] = $temp_calculation_data->original_sketch;
                if (empty($data["calculation_title"])) $data['calculation_title'] = $temp_calculation_data->calculation_title;
                if (empty($data["n13"])) $data['n13'] = json_encode($temp_calculation_data->n13);
            }
            /*-----------------------------------------------------------------------------*/
            if (empty($data['calculation_title']) || $data['calculation_title'] == "Без имени")
            {
                $index = $calculation_model->getCalcIndex($data['project_id']);
                $data['calculation_title'] = "Потолок $index";
            }
            //Сохранение калькуляции
            $calculation_model = Gm_ceilingHelpersGm_ceiling::getModel('CalculationForm', 'Gm_ceilingModel');
            if ($save == 1)
            {
                $ajax_return['id'] = $calculation_model->save($data, $del_flag);
            }

            //Пошла печать PDF
            if ($pdf == 1) {//наряд монтажной бригаде
                if($need_mount || $data["need_mount_extra"]){
                    self::create_mount_estimate_by_stage($data['id'],null,1);
                }
                if(!empty($data['n1'])){
                     //PDF раскроя
                    self::create_cut_pdf(null,$data);
                    //для менеджера
                }
                self::create_manager_estimate(1,null,$data,$canvases_data,$offcut_square_data,$guild_data);

                //клиентская смета
                self::create_client_single_estimate($need_mount,null,$data,$components_data,$canvases_data,$offcut_square_data,$guild_data,$mounting_data);
            }
            $return = json_encode($ajax_return);

            return $return;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public static function create_client_single_estimate($need_mount,$calc_id=null,$data=null,$components_data = null,$canvases_data = null,$offcut_square_data = null,$guild_data = null,
    $mounting_data = null){
        try {
            $html = self::create_client_single_estimate_html($need_mount,$calc_id,$data,$components_data,$canvases_data,$offcut_square_data,$guild_data,$mounting_data);
            if(empty($calc_id)){
                $calc_id = $data['id'];
            }
            $sheets_dir = $_SERVER['DOCUMENT_ROOT'] . '/costsheets/';
            $filename = md5($calc_id . "client_single") . ".pdf";
            Gm_ceilingHelpersGm_ceiling::save_pdf($html, $sheets_dir . $filename, "A4");
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public static function create_client_single_estimate_html($need_mount,$calc_id=null,$data=null,$components_data = null,$canvases_data = null,$offcut_square_data = null,$guild_data = null,
    $mounting_data = null){
        try {
            if(empty($canvases_data)){
                if(!empty($calc_id)){
                    $canvases_data = self::calculate_canvases($calc_id);
                }

            }

            if(empty($components_data)){
                $components_data = self::calculate_components($calc_id,null,0);
            }
            if(empty($guild_data)){
                $guild_data = self::calculate_guild_jobs($calc_id)['guild_data'];
            }
            else $guild_data = $guild_data['guild_data'];
            if(empty($mounting_data)){
                $mounting_data = self::calculate_mount(0,$calc_id,null);
            }
            if(!empty($calc_id)){
                $calculation_model = self::getModel('calculation');
                $data = get_object_vars($calculation_model->getData($calc_id));
            }
            if(empty($offcut_square_data)){
                if($data['need_cuts']) {
                    if (!empty($calc_id)) {
                        $offcut_square_data = self::calculate_offcut($calc_id);
                    }
                }
            }
            $dealer = JFactory::getUser($data['dealer_id']);
            $dealer_components_sum = 0;
            foreach ($components_data as $component_item) {
                $dealer_components_sum += $component_item['dealer_total'];
            }
            if($canvases_data['min_dealer_total']){
                $canvases_data['dealer_total'] = $canvases_data['min_dealer_total'];
                $offcut_square_data['dealer_total'] = 0;
            }
            $total_with_gm_dealer_margin = $mounting_data['total_with_gm_dealer_margin'];
            $total_with_gm_dealer_margin_guild = $mount_data['total_with_gm_dealer_margin_guild'];
            $guild_data_itog = $data['guild_data']['total_dealer_guild'];
            $new_total = round($canvases_data['dealer_total'] + $offcut_square_data['dealer_total'] + $dealer_components_sum + $total_with_gm_dealer_margin + $total_with_gm_dealer_margin_guild + $guild_data_itog, 2);
            $new_total_discount = round($new_total * (1 - ($data['discount'] / 100)), 2);
            $html = '<h1>Смета по материалам и комплектующим</h1>';
            $html .= "<h1>Название: " . $data['calculation_title'] . "</h1>";
            $html .= '<div class = "date_lbl"><h2>Дата:' . date("d.m.Y") . '</h2></div>';
            $html .= '<table class = "tbl_contacts">';
            $html .= '<tr>';
            $html .= '<td></td><td>' . $dealer->name . '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td>Телефон:</td><td>' . $dealer->username . '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td>email:</td><td>' . $dealer->email . '</td>';
            $html .= '</tr>';
            $html .= '</table>';
            $html .= '<table class = "no_border">';
            $html .= '<tr>';
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/calculation_images/" . md5("calculation_sketch_client" . $data['id']) . ".svg"))
                $html .= '<td>Схема потолка:</td> <td><img src="' . $_SERVER['DOCUMENT_ROOT'] . "/calculation_images/" . md5("calculation_sketch_client" . $data['id']) . '.svg" align="right" width="200" height="200"/></td>';
            $html .= '</tr>';
            $html .= '</table>';
            $html .= '<h2>Общее: ' . $new_total . ' руб.</h2>';
            if ($data['discount'] != 0){
                $html .= '<h2>Общее (со скидкой): <strong>' . $new_total_discount . ' руб.</strong></h2>';
            }
            $html .= '<table border="0" cellspacing="0" width="100%">
                        <tbody>
                            <tr>
                                <th>Наименование</th>
                                <th class="center">Цена, руб.</th>
                                <th class="center">Кол-во</th>
                                <th class="center">Стоимость, руб.</th></tr>';
            if ($data['n3_id']) {
                if ($data['color'] > 0) {
                    $color_model = Gm_ceilingHelpersGm_ceiling::getModel('color');
                    $color = $color_model->getData($data['color']);
                    $name = $canvases_data['title'] . ", цвет: " . $color->colors_title;
                } else {
                    $name = $canvases_data['title'];
                    }
                $html .= '<tr>';
                $html .= '<td>' . $name . '</td>';
                $html .= '<td class="center">' . round($canvases_data['dealer_price'], 2) . '</td>';
                $html .= '<td class="center">' . $canvases_data['quantity'] . '</td>';
                $html .= '<td class="center">' . $canvases_data['dealer_total'] . '</td>';
                $html .= '</tr>';
            }
            if ($data['n3_id'] && $data['offcut_square'] > $data['n4']*0.5 && $data['need_cuts']) {
                $name = $offcut_square_data['title'];
                $html .= '<tr>';
                $html .= '<td>' . $name . '</td>';
                $html .= '<td class="center">' . round($offcut_square_data['dealer_price'], 2) . '</td>';
                $html .= '<td class="center">' . $offcut_square_data['quantity'] . '</td>';
                $html .= '<td class="center">' . $offcut_square_data['dealer_total'] . '</td>';
                $html .= '</tr>';
            }
            foreach ($components_data as $key => $item) {
                    if ($item['quantity'] > 0 && $item['quantity'] > 0.0) {
                        $html .= '<tr>';
                        $html .= '<td>' . $item['title'] . '</td>';
                        $html .= '<td class="center">' . round($item['dealer_price'], 2) . '</td>';
                        $html .= '<td class="center">' . $item['quantity'] . '</td>';
                        $html .= '<td class="center">' . $item['dealer_total'] . '</td>';
                        $html .= '</tr>';
                    }
                }
            foreach ($guild_data as $item) {
                $html .= '<tr>';
                $html .= '<td>' . $item['title'] . '</td>';
                $html .= '<td class="center">' . round($item['dealer_salary'], 2) . '</td>';
                $html .= '<td class="center">' . $item['quantity'] . '</td>';
                $html .= '<td class="center">' . $item['dealer_salary_total']. '</td>';
                $html .= '</tr>';
            }
            $html .= '<tr><th colspan="3" class="right">Итого, руб:</th><th class="center">' . round($canvases_data['dealer_total'] + $offcut_square_data['dealer_total'] + $dealer_components_sum + $total_with_gm_dealer_margin_guild + $guild_data_itog, 2) . '</th></tr>';
            $html .= '</tbody></table><p>&nbsp;</p>';
            if ($need_mount || $data["need_mount_extra"]) {
                $html .= '<h1>Смета по монтажным работам</h1>
                        <h2>Дата: ' . date("d.m.Y") . '</h2>
                        <table border="0" cellspacing="0" width="100%">
                        <tbody><tr><th>Наименование</th><th class="center">Цена, руб.</th><th class="center">Кол-во</th><th class="center">Стоимость, руб.</th></tr>';
                foreach ($mounting_data['mounting_data'] as $item) {
                    $html .= '<tr>';
                    $html .= '<td>' . $item['title'] . '</td>';
                    $html .= '<td class="center">' . round($item['price_with_gm_dealer_margin'], 2) . '</td>';
                    $html .= '<td class="center">' . $item['quantity'] . '</td>';
                    $html .= '<td class="center">' . $item['total_with_gm_dealer_margin'] . '</td>';
                    $html .= '</tr>';

                }
                $html .= '<tr><th colspan="3" class="right">Итого, руб:</th><th class="center">' . round($total_with_gm_dealer_margin, 2) . '</th></tr>';
                $html .= '</tbody></table><p>&nbsp;</p>';
            }
            if ($data['discount'] != 0)
                $html .= '<div style="text-align: right; font-weight: bold;">ИТОГО: ' . $new_total . ' руб. - ' . $data['discount'] . '% = <span style="background: #14D100; color: #fff;">' . $new_total_discount . ' руб.</span></div>';
            else $html .= '<div style="text-align: right; font-weight: bold;">ИТОГО: ' . $new_total . ' руб.</div>';
            return $html;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public static function create_client_common_estimate($project_id,$calc_ids = null){
        try {
            $sheets_dir = $_SERVER['DOCUMENT_ROOT'] . '/costsheets/';
            $project_model = self::getModel('project');
            $project = $project_model->getData($project_id);
            $calculations_model = self::getModel('calculations');
            if($project->project_mounter != 0){
                $names = $calculations_model->FindAllMounters($project->project_mounter);
                $brigade = JFactory::getUser($project->project_mounter);
            }
            $calculations = $calculations_model->new_getProjectItems($project_id);
            if(!empty($calc_ids)){
                foreach($calculations as $key => $calculation){
                    if(!in_array($calculation->id,$calc_ids)){
                        unset($calculations[$key]);
                    }
                }
            }
            $transport = self::calculate_transport($project_id);
            $client_contacts_model = self::getModel('client_phones');
            $client_contacts = $client_contacts_model->getItemsByClientId($project->id_client);
            for($i=0;$i<count($client_contacts);$i++){
                $phones .= $client_contacts[$i]->phone . (($i < count($client_contacts) - 1) ? " , " : " ");
            }
            $html = ' <h1>Номер договора: ' . $project_id . '</h1><br>';
            $html .= '<h2>Дата: ' . date("d.m.Y") . '</h2>';
            if(isset($brigade)){
                $html .= '<h2>Монтажная бригада: ' . $brigade->name . '</h2>';
                $html .= "<h2>Состав монтажной бригады: </h2>";
                for($i=0;$i<count($names);$i++){
                    $brigade_names .= $names[$i]->name . (($i < count($names) - 1) ? " , " : " ");
                }
                $html .= $brigade_names;
                $html .= "<br>";
            }
            $html .= "<h2>Адрес: </h2>" . $project->project_info . "<br>";
            $jdate = new JDate(JFactory::getDate($project->project_mounting_date));
            $html .= "<h2>Дата монтажа: </h2>" . $jdate->format('d.m.Y  H:i') . "<br>";
            $html .= '<h2>Краткая информация по выбранным(-ому) потолкам(-у): </h2>';
            $html .= '<table border="0" cellspacing="0" width="100%">
                        <tbody>
                            <tr>
                                <th>Название</th>
                                <th class="center">Площадь, м<sup>2</sup>.</th>
                                <th class="center">Периметр, м </th>
                                <th class="center">Стоимость, руб.</th>
                            </tr>';
            foreach ($calculations as $calc) {
                $calc_itog_sum = $calc->dealer_components_sum;
                $calc_itog_sum += $calc->dealer_canvases_sum;
                $calc_itog_sum += double_margin($calc->mounting_sum, $project->gm_mounting_margin, $project->dealer_mounting_margin);
                $calc_itog_sum = round($calc_itog_sum, 2);
                $calc_itog_discount_sum = round($calc_itog_sum * (100 - $calc->discount) / 100, 2);
                $html .= '<tr>';
                $html .= '<td>' . $calc->calculation_title . '</td>';
                $html .= '<td class="center">' . $calc->n4 . '</td>';
                $html .= '<td class="center">' . $calc->n5 . '</td>';
                $html .= '<td class="center">' . $calc_itog_sum . '</td>';
                $html .= '</tr>';
                $sum += $calc_itog_sum;
                $discount_sum +=$calc_itog_discount_sum;
            }
            $html .= '<tr><th colspan="3" class="right">Итого, руб:</th><th class="center">' . $sum . '</th></tr>';
            $html .= '</tbody></table><p>&nbsp;</p>';
            $html .= '<h2>Транспортные расходы: </h2>';
            $html .= '<table border="0" cellspacing="0" width="100%">
                        <tbody>
                            <tr>
                                <th>Вид транспорта</th>
                                <th class="center">Кол-во км<sup>2</sup>.</th>
                                <th class="center">Кол-во выездов  </th>
                                <th class="center">Стоимость, руб.</th>
                            </tr>';
            $html .= '<tr>';
            $html .= '<td>' . $transport['transport']. '</td>';
            $html .= '<td class="center">' . $transport['distance'] . '</td>';
            $html .= '<td class="center">' . $transport['distance_col'] . '</td>';
            $html .= '<td class="center">' . $transport['client_sum'] . '</td>';
            $html .= '</tr>';
            $html .= '</tbody></table><p>&nbsp;</p>';
            $html .= '<div style="text-align: right; font-weight: bold;"> ИТОГО: ' . round($transport['client_sum'] + $sum, 2) . ' руб.</div>';
            $html .= '<div style="text-align: right; font-weight: bold;"> ИТОГО СО СКИДКОЙ: ' . round($transport['client_sum'] + $discount_sum, 2) . ' руб.</div>';
            $html .= '</tbody></table><p>&nbsp;</p><br>';
            //$html .= "<pagebreak />";
            $array = [];
            $array[] = $html;
            foreach($calculations as $calc){
                $array[] = $_SERVER["DOCUMENT_ROOT"] . "/costsheets/" . md5($calc->id . "client_single") . ".pdf";
                /*
                $need_mount = ($calc->mounting_sum > 0);
                $html .= self::create_client_single_estimate_html($need_mount,$calc->id);
                */
            }
            $filename = md5($project->id . "client_common") . ".pdf";
            Gm_ceilingHelpersGm_ceiling::save_pdf($array, $sheets_dir . $filename, "A4");
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public static function calculate_components($calc_id=null,$data=null,$del_flag=0){
        try {
            if (!empty($calc_id)) {
                $calculation_model = self::getModel('calculation');
                $calculation_data = $calculation_model->getData($calc_id);
                $canvases_model = Gm_ceilingHelpersGm_ceiling::getModel('canvases');
                foreach ($calculation_data as $key => $item) {
                    $data[$key] = $item;
                }
                $data['n3'] = $calculation_data->n3_id;
                if (!empty($data['n3'])) {
                    $canvasData = $canvases_model->getFilteredItemsCanvas("`a`.`id` =" . $data['n3']);
                    $data['n1'] = $canvasData[0]->texture_id;
                }

            }

            $dealer_info = JFactory::getUser($data['dealer_id']);
            if ($dealer_info->dealer_id == 1) {
                $dealer_info_components = $dealer_info->getComponentsPrice($dealer_info->dealer_id);
            } else {
                $dealer_info_components = $dealer_info->getComponentsPrice();
            }
            $margins = self::get_margin($data['project_id']);
            $gm_components_margin = $margins['gm_components_margin'];
            $dealer_components_margin = $margins['dealer_components_margin'];
            $components_model = Gm_ceilingHelpersGm_ceiling::getModel('components');
            $components_list = $components_model->getFilteredItems();
            foreach ($components_list as $i => $component) {
                $components[$component->id] = $component;
            }
            $component_count = array();
            foreach ($components as $key => $value) {
                $component_count[$key] = 0;
            }

            //периметр ТОЛЬКО ДЛЯ ПВХ
            $filter = "`co`.`title` LIKE('%3,5 * 51%') AND `c`.`title` LIKE('%Саморез%') ";
            $items_9 = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title` LIKE('%6 * 51%') AND `c`.`title` LIKE('%Дюбель%') ";
            $items_5 = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title` LIKE('%ПВХ (2,5 м)%') AND `c`.`title` LIKE('%Багет%') ";
            $items_11 = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title` LIKE('%потолочный аллюм%') AND `c`.`title` LIKE('%Багет%') ";
            $items_236 = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title` LIKE('%стеновой аллюм%') AND `c`.`title` LIKE('%Багет%') ";
            $items_239 = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title` LIKE('%для парящих пот аллюм%') AND `c`.`title` LIKE('%Багет%') ";
            $items_559 = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title`  LIKE('%303 белая%') AND `c`.`title` LIKE('%Вставка%') ";
            $items_vstavka_bel = $components_model->getFilteredItems($filter);

            if ($data['color'] > 0) {
                $color_model1 = Gm_ceilingHelpersGm_ceiling::getModel('colors');
                $color1 = $color_model1->getColorTitle($data['color']);
                $name1 = $color1->title;
                $filter = "`co`.`title`  LIKE('%" . $name1 . "%') AND `c`.`title` LIKE('%Вставка%') ";
                $items_vstavka = $components_model->getFilteredItems($filter);
                if (empty($items_vstavka[0])) $items_vstavka = $items_vstavka_bel;
            }
            $filter = "`co`.`title`  LIKE('%п/сф 3,5*9,5%') AND `c`.`title` LIKE('%Саморез%') ";
            $items_10 = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title`  LIKE('%круглая%') AND `c`.`title` LIKE('%Платформа под люстру%') ";
            $items_16 = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title`  LIKE('%6*50%') AND `c`.`title` LIKE('%Шуруп-полукольцо%') ";
            $items_556 = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title`  LIKE('35') AND `c`.`title` LIKE('%Круглое кольцо%') ";
            $items_58 = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title`  LIKE('%П 60%') AND `c`.`title` LIKE('%Подвес прямой %') ";
            $items_3 = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title`  LIKE('%2,5 мм%') AND `c`.`title` LIKE('%Клеммная колодка%') ";
            $items_2 = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title`  LIKE('%40*50%') AND `c`.`title` LIKE('%Брус%') ";
            $items_1 = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title`  LIKE('%3,5*35%') AND `c`.`title` LIKE('%Саморез%') ";
            $items_8 = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title`  LIKE('%4,2 * 102%') AND `c`.`title` LIKE('%Саморез%') ";
            $items_6 = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title`  LIKE('%в разд 303 гриб%') AND `c`.`title` LIKE('%Вставка%') ";
            $items_14 = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title`  LIKE('%для парящих потолков%') AND `c`.`title` LIKE('%Вставка%') ";
            $items_38 = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title`  LIKE('%15 * 12,5 см.%') AND `c`.`title` LIKE('%Кронштейн%') ";
            $items_430 = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title`  LIKE('%разделительный аллюм%') AND `c`.`title` LIKE('%Багет%') ";
            $items_35 = $components_model->getFilteredItems($filter);

            $filter = "`c`.`title` LIKE('%Гарпун%') ";
            $items_360 = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title`  LIKE('%70*100 мм%') AND `c`.`title` LIKE('%Платформа для карнизов%') ";
            $items_495 = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title`  LIKE('%Декскор 2,5%') AND `c`.`title` LIKE('%Багет%') ";
            $items_233 = $components_model->getFilteredItems($filter);

            $filter = "`c`.`title` LIKE('%Переход уровня%') ";
            $items_659 = $components_model->getFilteredItems($filter);

            $filter = "`c`.`title` LIKE('%Переход уровня с нишей%') ";
            $items_660 = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title`  LIKE('%ПВХ%') AND `c`.`title` LIKE('%Профиль%')";
            $items_654 = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title`  LIKE('%аллюм%') AND `c`.`title` LIKE('%Отбойник%')";
            $items_64 = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title`  LIKE('%10 * 12.5 см.%') AND `c`.`title` LIKE('%Кронштейн%')";
            $items_726 = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title`  LIKE('%100%') AND `c`.`title` LIKE('%Пластик%')";
            $items_727 = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title`  LIKE('%150%') AND `c`.`title` LIKE('%Пластик%')";
            $items_728 = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title`  LIKE('%200%') AND `c`.`title` LIKE('%Пластик%')";
            $items_729 = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title`  LIKE('%телевизионный%') AND `c`.`title` LIKE('%Кабель%')";
            $items_730 = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title`  LIKE('%ПЛ75(Прозет)%') AND `c`.`title` LIKE('%Профиль%')";
            $items_731 = $components_model->getFilteredItems($filter); //профиль ЛЭД

            $filter = "`co`.`title`  LIKE('%КП%') AND `c`.`title` LIKE('%Профиль%')";
            $items_732 = $components_model->getFilteredItems($filter); // профиль КП

            $filter = "`co`.`title`  LIKE('%металлич 90*90*65%') AND `c`.`title` LIKE('%Кронштейн%')";
            $items_733 = $components_model->getFilteredItems($filter); //уголок

            $filter = "`co`.`title` like ('%?%') and `c`.`title` like ('%МДФ%')";
            $items_734 = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title` like ('%Контурный%') and `c`.`title` like ('%Профиль%')";
            $items_735 = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title` like ('%для шторного карниза%') and `c`.`title` like ('%Лента%')";
            $items_cornice_lenta = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title` like ('%для шторного карниза(упак. 2шт)%') and `c`.`title` like ('%Закругления%')";
            $items_cornice_round = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title` like ('%Deskor стеновой%') and `c`.`title` like ('%Профиль%')";
            $wall_bag_tiss = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title` like ('%Deskor потолочный%') and `c`.`title` like ('%Профиль%')";
            $ceiling_bag_tiss = $components_model->getFilteredItems($filter);

            $filter = "`co`.`title` like ('%Отбойник АЛ Z-образный%') and `c`.`title` like ('%Профиль%')";
            $otb = $components_model->getFilteredItems($filter);
            if (!is_array($data['n13'])) $n13_costyl = json_decode($data['n13']);
            else $n13_costyl = $data['n13'];
            //throw new Exception(gettype($n13_costyl[0]));
            $cnt_costyl = 0;
            if (!empty($n13_costyl)) {
                foreach ($n13_costyl as $item) {
                    $cnt_costyl += (gettype($item) == "array") ? $item[0] : $item->n13_count;

                }
            }
            //$component_count[$items_4[0]->id] += $cnt_costyl/2;
            //багет для ПВХ
            if (!empty($data['n1']) && $data['n1'] != 29) {
                if ($data['n28'] != 0 && $data['need_metiz']) {
                    $component_count[$items_9[0]->id] += $data['n5'] * 10;
                    $component_count[$items_5[0]->id] += $data['n5'] * 10;
                }
                if ($data['n28'] == 3) {
                    $component_count[$items_11[0]->id] += $data['n5'];
                } elseif ($data['n28'] == 1) {
                    $component_count[$items_236[0]->id] += $data['n5'];
                } elseif ($data['n28'] == 2) {
                    $component_count[$items_239[0]->id] += $data['n5'];
                }
            }
            //багет для ткани
            if (!empty($data['n1']) && $data['n1'] == 29) {
                if ($data['n28'] != 0 && $data['need_metiz']) {
                    $component_count[$items_9[0]->id] += $data['n5'] * 10;
                    $component_count[$items_5[0]->id] += $data['n5'] * 10;
                }
                if ($data['n28'] == 2 || $data['n28'] == 3) {
                    $component_count[$ceiling_bag_tiss[0]->id] += $data['n5'];
                }
                if ($data['n28'] == 1) {
                    $component_count[$wall_bag_tiss[0]->id] += $data['n5'];
                }
            }
            // внутренний вырез на месте
            if ($data['n11'] > 0) {
                $component_count[$items_1[0]->id] += $data['n11']; //брус
                if ($data['n1'] == 29) {
                    $component_count[$ceiling_bag_tiss[0]->id] += $data['n11']; //потолочный багет дескор
                } else {
                    $component_count[$items_236[0]->id] += $data['n11']; //потолочный аллюм багет
                }
                $component_count[$items_430[0]->id] += $data['n11'] * 2;
                if ($data['need_metiz']) {
                    $component_count[$items_8[0]->id] += $data['n11'] * 22;
                    $component_count[$items_5[0]->id] += $data['n11'] * 16;
                }
                $component_count[$items_360[0]->id] += $data['n11'];
                if (!empty($data['n6'])) {
                    $component_count[$data['n6']] += $data['n11'];
                } else $component_count[$items_vstavka_bel[0]->id] += $data['n11'];
            }
            //внутренний вырез в цеху
            if ($data['n31'] > 0 && $data['n28']) {
                if ($data['need_metiz']) {
                    $component_count[$items_9[0]->id] += $data['n31'] * 10;
                    $component_count[$items_5[0]->id] += $data['n31'] * 10;
                }
                $n31_count = ceil($data['n31']);
                if (!empty($data['n6'])) {
                    $component_count[$data['n6']] += $n31_count;
                } else $component_count[$items_vstavka_bel[0]->id] += $n31_count;
                $component_count[$items_11[0]->id] += $data['n31'];


            }
            if (!empty($data['n1']) && $data['n1'] != 29 && $data['n6']) {

                $component_count[$data['n6']] += $data['n5'] + 0.5; //запас 0.5м
            }
            //люстры
            if ($data['need_metiz']) {
                $component_count[$items_5[0]->id] += $data['n12'] * 3;
                $component_count[$items_9[0]->id] += $data['n12'] * 3;
                $component_count[$items_10[0]->id] += $data['n12'] * 8;
            }
            $component_count[$items_16[0]->id] += $data['n12'];
            $component_count[$items_58[0]->id] += $data['n12'];

            if ($data['need_metiz']) {
                $component_count[$items_3[0]->id] += $data['n12'] * 2;
            }
            if ($data['n12'] > 0) {
                $component_count[$items_2[0]->id] += $data['n12'] * 2;
            }
            if ($del_flag == 1) {
                $calcform_model = Gm_ceilingHelpersGm_ceiling::getModel('calculationform');
                $n13 = json_decode($data['n13']);
                $n19 = json_decode($data['n19']);
                $n26 = json_decode($data['n26']);
                $n22 = json_decode($data['n22']);
                $n14 = json_decode($data['n14']);
                $n23 = json_decode($data['n23']);
                $n15 = json_decode($data['n15']);
                $n29 = json_decode($data['n29']);
                if (!empty($n19) && count($n19) > 0) {
                    foreach ($n19 as $wire) {
                        $total_n19_count += $wire[0];
                        $component_count[$wire[1]] += $wire[0];
                    }
                }
                if (!empty($n29) && count($n29) > 0) {
                    foreach ($n29 as $profil) {
                        if ($profil[0] > 0) {
                            switch ($profil[1]) {
                                case 12 :
                                case 13 :
                                    $component_count[$items_659[0]->id] += $profil[0];
                                    break;
                                case 15:
                                case 16:
                                    $component_count[$items_660[0]->id] += $profil[0];
                                    break;
                                case 17:
                                    $component_count[$items_731[0]->id] += $profil[0];
                                    $component_count[$items_733[0]->id] += $profil[0] * 3;
                                    $component_count[$items_734[0]->id] += $profil[0];
                                    break;
                                case 18:
                                    $component_count[$items_732[0]->id] += $profil[0];
                                    $component_count[$items_733[0]->id] += $profil[0] * 3;
                                    $component_count[$items_734[0]->id] += $profil[0];
                                    break;
                            }


                        }
                    }
                }
                $k = 0;
                if (!empty($n26) && count($n26) > 0) {
                    foreach ($n26 as $ecola) {
                        if ($ecola[0] > 0) {
                            $component_count[$ecola[1]] += $ecola[0];
                            $component_count[$ecola[2]] += $ecola[0];
                            $k += $ecola[0];
                        }
                    }
                }
                if (!empty($n13) && count($n13) > 0) {
                    $svet_count = 0;
                    foreach ($n13 as $lamp) {
                        $fix_components = $calcform_model->components_list_n13_n22($lamp[1], $lamp[2]);
                        foreach ($fix_components as $comp) {

                            if ($comp['id'] != 4) {/* Костыль */
                                $component_count[$comp['id']] += ($comp['count'] * $lamp[0]);
                            }

                        }

                        if ($lamp[1] == 2 && $lamp[2] == 66) {
                            $component_count[66] -= $k;
                            if ($component_count[66] < 0) $component_count[66] = 0;
                        }
                        $svet_count += $lamp[0];
                    }
                    $component_count[$items_2[0]->id] += $svet_count;
                    if ($data['need_metiz']) {
                        $component_count[$items_3[0]->id] += $svet_count;
                    }
                }
                //вентиляция
                if (!empty($n22) && count($n22) > 0) {
                    foreach ($n22 as $ventilation) {
                        $ventilation_components = $calcform_model->components_list_n13_n22($ventilation[1], $ventilation[2]);
                        foreach ($ventilation_components as $comp) $component_count[$comp['id']] += ($comp['count'] * $ventilation[0]);
                    }
                }
                if (!empty($n14) && count($n14) > 0) {
                    foreach ($n14 as $truba) {
                        if ($truba[0] > 0) $component_count[$truba[1]] += $truba[0];
                    }

                }
                if (!empty($n23) && count($n23) > 0) {
                    foreach ($n23 as $diffuzor) {
                        if ($diffuzor[0] > 0) $component_count[$diffuzor[1]] += $diffuzor[0];
                    }
                }
                if (!empty($n15) && count($n15) > 0) {
                    foreach ($n15 as $cornice) {
                        if ($cornice[0] > 0) $component_count[$cornice[2]] += $cornice[0];
                    }
                }
            } else {
                $calcform_model = Gm_ceilingHelpersGm_ceiling::getModel('calculationform');
                $n13 = $data['n13'];
                $n26 = $data['n26'];
                $k = 0;
                if (count($n26) > 0) {
                    foreach ($n26 as $ecola) {
                        if ($ecola->n26_count > 0) {
                            $component_count[$ecola->n26_illuminator] += $ecola->n26_count;
                            $component_count[$ecola->n26_lamp] += $ecola->n26_count;
                            $k += $ecola->n26_count;
                        }
                    }
                }
                if (count($n13) > 0 && $n13 != 0) {
                    $svet_count = 0;
                    foreach ($n13 as $lamp) {
                        $fix_components = $calcform_model->components_list_n13_n22($lamp->n13_type, $lamp->n13_size);
                        foreach ($fix_components as $comp) $component_count[$comp['id']] += ($comp['count'] * $lamp->n13_count);
                        if ($lamp->n13_type == 2 && $lamp->n13_size == 66) {
                            $component_count[66] -= $k;
                        }
                        if ($component_count[66] < 0) $component_count[66] = 0;
                    }
                    $svet_count += $lamp->n13_count;
                    $component_count[$items_2[0]->id] += $svet_count;
                    if ($data['need_metiz']) {
                        $component_count[$items_3[0]->id] += $svet_count;
                    }
                }
                $n19 = $data['n19'];
                if (count($n19) > 0) {
                    foreach ($n19 as $wire) {
                        $total_n19_count += $wire->count;
                        $component_count[$wire->wire_id] += $wire->count;
                    }
                }
                //вентиляция
                $n22 = $data['n22'];
                if (count($n22) > 0) {
                    foreach ($n22 as $ventilation) {
                        $ventilation_components = $calcform_model->components_list_n13_n22($ventilation->n22_type, $ventilation->n22_size);
                        foreach ($ventilation_components as $comp) $component_count[$comp['id']] += ($comp['count'] * $ventilation->n22_count);
                    }
                }
                $n14 = $data['n14'];
                if (count($n14) > 0) {
                    foreach ($n14 as $truba) {
                        if ($truba->n14_count > 0) $component_count[$truba->n14_size] += $truba->n14_count;
                    }
                }
                $n23 = $data['n23'];
                if (count($n23) > 0) {
                    foreach ($n23 as $diffuzor) {
                        if ($diffuzor->n23_count > 0) $component_count[$diffuzor->n23_size] += $diffuzor->n23_count;
                    }
                }
                $n15 = $data['n15'];
                if (count($n15) > 0) {
                    foreach ($n15 as $cornice) {
                        if ($cornice->n15_count > 0) $component_count[$cornice->n15_size] += $cornice->n15_count;
                    }
                }
                $n29 = $data['n29'];
                if (count($n29) > 0) {
                    foreach ($n29 as $profil) {
                        if ($profil->n29_count > 0) {
                            switch ($profil->n29_type) {
                                case 12:
                                case 13:
                                    $component_count[$items_659[0]->id] += $profil->n29_count;
                                    break;
                                case 15:
                                case 16:
                                    $component_count[$items_660[0]->id] += $profil->n29_count;
                                case 17:
                                    $component_count[$items_731[0]->id] += $profil->n29_count;
                                    $component_count[$items_733[0]->id] += $profil->n29_count * 3;
                                    $component_count[$items_734[0]->id] += $profil->n29_count;
                                    break;
                                case 18:
                                    $component_count[$items_732[0]->id] += $profil->n29_count;
                                    $component_count[$items_733[0]->id] += $profil->n29_count * 3;
                                    $component_count[$items_734[0]->id] += $profil->n29_count;
                                    break;
                            }
                        }
                    }
                }
            }
            if ($data['n30'] > 0) {
                $component_count[$items_559[0]->id] += $data['n30'];

                if ($data['n28'] == 3) {
                    if ($component_count[$items_11[0]->id] > $data['n30']) {
                        $component_count[$items_11[0]->id] -= $data['n30'];
                    } else {
                        $component_count[$items_11[0]->id] = 0;
                    }
                } elseif ($data['n28'] == 1) {
                    if ($component_count[$items_236[0]->id] > $data['n30']) {
                        $component_count[$items_236[0]->id] += $data['n30'];
                    } else {
                        $component_count[$items_236[0]->id] = 0;
                    }

                } elseif ($data['n28'] == 2) {
                    if ($component_count[$items_239[0]->id] > $data['n30']) {
                        $component_count[$items_239[0]->id] -= $data['n30'];
                    } else {
                        $component_count[$items_239[0]->id] = 0;
                    }

                }
            }
            //карниз
            if($data['n16'] == 0) {
                $component_count[$items_1[0]->id] += $data['n27'];
                if($data['need_metiz']){
                    $component_count[$items_3[0]->id] += $data['n27'] * 3;
                    $component_count[$items_5[0]->id] += $data['n27'] * 6;
                    $component_count[$items_8[0]->id] += $data['n27'] * 9;
                    $component_count[$items_9[0]->id] += $data['n27'] * 6;
                }
            }
            //скрытый карниз
            if ($data['n16']) {
                switch ($data['niche']){
                    case 1 :
                        $component_count[$items_1[0]->id] += $data['n27'];      //брус
                        $component_count[$items_11[0]->id] += $data['n27'];     //Багет
                        $component_count[$items_726[0]->id] += $data['n27']*2;  //меб.уголок 100*125
                        $component_count[$items_14[0]->id] += $data['n27'];     //вставка в разд
                        break;
                    case 2 :
                        $component_count[$items_1[0]->id] += $data['n27'];
                        $component_count[$items_11[0]->id] += $data['n27'];
                        $component_count[$items_64[0]->id] += $data['n27'];     //отбойник
                        $component_count[$items_726[0]->id] += $data['n27']*2;
                        break;
                    case 3:
                        $component_count[$items_1[0]->id] += $data['n27'];
                        $component_count[$items_726[0]->id] += $data['n27']*2;
                        $component_count[$items_236[0]->id] += $data['n27'];    //потлочн аллюм багет
                        $component_count[$items_727[0]->id] += $data['n27'];    //пластик
                        break;
                    case 4:
                        $component_count[$items_1[0]->id] += $data['n27'];
                        $component_count[$items_726[0]->id] += $data['n27']*2;
                        $component_count[$items_236[0]->id] += $data['n27'];
                        $component_count[$items_728[0]->id] += $data['n27'];    //пластик
                        break;
                    case 5:
                        $component_count[$items_1[0]->id] += $data['n27'];
                        $component_count[$items_726[0]->id] += $data['n27']*2;
                        $component_count[$items_236[0]->id] += $data['n27'];
                        $component_count[$items_729[0]->id] += $data['n27'];    //пластик
                        break;
                }
               /* $component_count[$items_430[0]->id] += $data['n27'] * 2;
                $component_count[$items_8[0]->id] += $data['n27'] * 4;*/
            }
            //лючок
            if($data['n33']>0){
                $component_count[$items_1[0]->id] += $data['n33'];
                $component_count[$items_726[0]->id] += $data['n33']*2;
            }

            //Люк
            if($data['n33_2']>0){
                $component_count[$items_1[0]->id] += $data['n33_2'];
                $component_count[$items_236[0]->id] += $data['n33_2'];
                $component_count[$items_726[0]->id] += $data['n33_2']*2;
                $component_count[$items_730[0]->id] += $data['n33_2'];
            }
            //контурный профиль
            if($data['n35'] > 0){
                $component_count[$items_735[0]->id] += $data['n35'];
                if ($data['n28'] == 3){
                    if($component_count[$items_11[0]->id] > $data['n35']){
                        $component_count[$items_11[0]->id] -= $data['n35'];
                    }
                    else{
                        $component_count[$items_11[0]->id] = 0;
                    }
                }
                elseif ($data['n28'] == 1){
                    if($component_count[$items_236[0]->id] > $data['n35']){
                        $component_count[$items_236[0]->id] += $data['n35'];
                    }
                    else{
                        $component_count[$items_236[0]->id] = 0;
                    }

                }
                elseif ($data['n28'] == 2) {
                    if($component_count[$items_239[0]->id] > $data['n35']){
                        $component_count[$items_239[0]->id] -= $data['n35'];
                    }
                    else{
                        $component_count[$items_239[0]->id] = 0;
                    }

                }
            }
            //керамогранит
            if($data['n8'] > 0){
                //вычитаем из обычного профидя количество которое будет обагечено алюминиевым
                if ($data['n28'] == 3){
                    if($component_count[$items_11[0]->id] > $data['n8']){
                        $component_count[$items_11[0]->id] -= $data['n8'];
                    }
                    else{
                        $component_count[$items_11[0]->id] = 0;
                    }
                }
                elseif ($data['n28'] == 1){
                    if($component_count[$items_236[0]->id] > $data['n8']){
                        $component_count[$items_236[0]->id] += $data['n8'];
                    }
                    else{
                        $component_count[$items_236[0]->id] = 0;
                    }

                }
                elseif ($data['n28'] == 2) {
                    if($component_count[$items_239[0]->id] > $data['n8']){
                        $component_count[$items_239[0]->id] -= $data['n8'];
                    }
                    else{
                        $component_count[$items_239[0]->id] = 0;
                    }

                }

                if($data['n8'] == $data['n5'] && $data['n30'] > 0) // если керамогранит по всемy периметру и есть парящий потолок
                {
                    //вычитаем из кол-ва керамогранита количество парящего
                    $n8_val = $data['n8'] - $data['n30'];
                }
                else{
                    $n8_val = $data['n8'];
                }
                $component_count[$items_236[0]->id] += $n8_val;
                $component_count[$items_1[0]->id] += $n8_val;
                $component_count[$items_430[0]->id] += $n8_val*2;
                if($data['need_metiz']) {
                    $component_count[$items_5[0]->id] += $n8_val * 4;
                    $component_count[$items_9[0]->id] += $n8_val * 4;
                    $component_count[$items_8[0]->id] += $n8_val * 4;
                }
            }
            //лента для карниза
            //контурный профиль
            if($data['n39'] > 0){
                $component_count[$items_cornice_lenta[0]->id] += $data['n39'];

            }
            if($data['n40'] > 0){
                $component_count[$items_cornice_round[0]->id] += $data['n40'];

            }
            //закладная брусом
            $component_count[$items_1[0]->id] += $data['n17'];
            $component_count[$items_430[0]->id] += $data['n17'] * 2;
            if($data['need_metiz']) {
                $component_count[$items_5[0]->id] += $data['n17'] * 6;
                $component_count[$items_9[0]->id] += $data['n17'] * 6;
                $component_count[$items_8[0]->id] += $data['n17'] * 6;
            }
            //укрепление стен
            $component_count[$items_1[0]->id] += $data['n18'];
            if($data['need_metiz']) {
                $component_count[$items_6[0]->id] += $data['n18'] * 3;
                $component_count[$items_5[0]->id] += $data['n18'] * 3;
            }
            $component_count[$items_430[0]->id] += $data['n18'] * 2;
            if($data['need_metiz']) {
                $component_count[$items_9[0]->id] += $total_n19_count * 2;
                $component_count[$items_5[0]->id] += $total_n19_count * 2;
            }
            //разделитель ТОЛЬКО ДЛЯ ПВХ
            if (!empty($data['n1']) &&  $data['n1'] != 29) {
                $component_count[$items_1[0]->id] += $data['n20'];
                if($data['need_metiz']) {
                    $component_count[$items_6[0]->id] += $data['n20'] * 3;
                    $component_count[$items_9[0]->id] += $data['n20'] * 20;
                    $component_count[$items_5[0]->id] += $data['n20'] * 3;
                }
                $component_count[$items_14[0]->id] += $data['n20'];
                $n20_count = intval($data['n20'] / 2.5);
                if ((double)($data['n20'] * 10 % 25) > 0) {
                    $n20_count++;
                }
                $component_count[$items_35[0]->id] += $n20_count * 2.5;
            }
            //Отбойник
            $component_count[$items_1[0]->id] += $data['n20_1'];
            $component_count[$otb[0]->id] += $data['n20_1'];
            if($data['need_metiz']) {
                $component_count[$items_6[0]->id] += $data['n20_1'] * 3;
                $component_count[$items_9[0]->id] += $data['n20_1'] * 20;
                $component_count[$items_5[0]->id] += $data['n20_1'] * 3;
            }

            $n20_1_count = intval($data['n20_1'] / 2.5);
            if ((double)($data['n20_1'] * 10 % 25) > 0) {
                $n20_1_count++;
            }
            $component_count[$items_35[0]->id] += $n20_1_count * 2.5;
            //Дополнительный крепеж
            if($data['need_metiz']) {
                $component_count[$items_9[0]->id] += $data['dop_krepezh'] * 10;
            }
            if ($data['n1'] == 29)
            {
                $component_count[$wall_bag_tiss[0]->id] += $data['dop_krepezh'] / 2;
            }
            else{
                $component_count[$items_11[0]->id] += $data['dop_krepezh'] / 2;
            }

            //пожарная сигнализация
            $component_count[$items_495[0]->id] += $data['n21'];
            $component_count[$items_58[0]->id] += $data['n21'];
            if($data['need_metiz']) {
                $component_count[$items_3[0]->id] += $data['n21'] * 3;
                $component_count[$items_5[0]->id] += $data['n21'] * 3;
                $component_count[$items_9[0]->id] += $data['n21'] * 3;
                $component_count[$items_10[0]->id] += $data['n21'] * 6;
            }
            if ($data['n21'] > 0) {
                $component_count[$items_2[0]->id] += $data['n21'] * 2;
            }
            //просчет доп компонентов со склада
            $components_stock = json_decode($data['components_stock']);
            if (!empty($components_stock)) foreach ($components_stock as $comp_stock) {
                $component_count[$comp_stock->title] += $comp_stock->value;
            }
            //---------------------------------- ВОЗВРАЩАЕМ СТОИМОСТЬ КОМПЛЕКТУЮЩИХ --------------------------------------//
            //Сюда считаем итоговую сумму компонентов
            $components_data = array();
            foreach ($component_count as $key => $cost) {
                if(self::rounding($cost, $components[$key]->count_sale) > 0){
                    $component_item = array();
                    $component_item['title'] = $components[$key]->full_name;                            //Название комплектующего
                    $component_item['unit'] = $components[$key]->component_unit;                        //В чем измеряется
                    $component_item['id'] = $components[$key]->id;
                    $component_item['component_id'] = $components[$key]->component_id;                                     //ID
                    $component_item['quantity'] = self::rounding($cost, $components[$key]->count_sale); // Округление
                    $component_item['stack'] = 0;                                                       //Флаг, складывать ли этот компонент при сложении калькуляций
                    $component_item['self_price'] = $components[$key]->price;                           //cебестоимость
                    $component_item['self_total'] = round($component_item['self_price'] * $component_item['quantity'], 2);//Кол-во * Себестоимость
                    //Стоимость с маржой ГМ (для дилера)
                    $component_item['gm_price'] = margin($component_item['self_price'], $gm_components_margin);
                    //Кол-во * Стоимость с маржой ГМ (для дилера)
                    $component_item['gm_total'] = round($component_item['quantity'] * $component_item['gm_price'], 2);
                    //Стоимость с маржой ГМ и дилера (для клиента)
                    $component_item['self_dealer_price'] = dealer_margin($component_item['gm_price'], 0, $dealer_info_components[$component_item['id']]);
                        //Кол-во * Стоимость с маржой ГМ и дилера (для клиента)
                    $component_item['self_dealer_total'] = round($component_item['quantity'] * $component_item['self_dealer_price'], 2);
                    $component_item['dealer_price'] = dealer_margin($component_item['gm_price'], $dealer_components_margin, $dealer_info_components[$component_item['id']]);
                        //Кол-во * Стоимость с маржой ГМ и дилера (для клиента)
                    $component_item['dealer_total'] = round($component_item['quantity'] * $component_item['dealer_price'], 2);
                    $components_data[] = $component_item;
                }
            }
            //добавляем щепотку дополнительных комплектующих
            $extra_components = json_decode($data['extra_components']);
            if (!empty($extra_components)) {
                foreach ($extra_components as $extra_component) {
                    $component_item = array();
                    $component_item['title'] = $extra_component->title;                                        //Название комплектующего
                    $component_item['unit'] = "шт.";                                                            //В чем измеряется
                    $component_item['id'] = 0;                                                                  //ID
                    $component_item['quantity'] = 1;
                    $component_item['stack'] = 1;
                    $component_item['self_price'] = $extra_component->value;                                    //Себестоимость
                    $component_item['self_total'] = round($component_item['self_price'] * $component_item['quantity'], 2);//Кол-во * Себестоимость
                    //Стоимость с маржой ГМ (для дилера)
                    $component_item['gm_price'] = margin($component_item['self_price'], $gm_components_margin);
                    //Кол-во * Стоимость с маржой ГМ (для дилера)
                    $component_item['gm_total'] = round($component_item['quantity'] * $component_item['gm_price'], 2);
                    //Стоимость с маржой ГМ и дилера (для клиента)
                    $component_item['self_dealer_price'] = dealer_margin($component_item['gm_price'], 0, $dealer_info_components[$component_item['id']]);
                    //Кол-во * Стоимость с маржой ГМ и дилера (для клиента)
                    $component_item['self_dealer_total'] = round($component_item['quantity'] * $component_item['self_dealer_price'], 2);
                    $component_item['dealer_price'] = dealer_margin($component_item['gm_price'], $dealer_components_margin, $dealer_info_components[$component_item['id']]);
                    //Кол-во * Стоимость с маржой ГМ и дилера (для клиента)
                    $component_item['dealer_total'] = round($component_item['quantity'] * $component_item['dealer_price'], 2);
                    $components_data[] = $component_item;
                }
            }
            return $components_data;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public static function get_margin($project_id=null){
        try {
            $result = array();
            if (!empty($project_id)) {
                $project_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
                $project = $project_model->getData($project_id);
                $gm_canvases_margin = $project->gm_canvases_margin;            //Маржа ГМ на полотно
                $gm_components_margin = $project->gm_components_margin;            //Маржа ГМ на комплектующие
                $gm_mounting_margin = $project->gm_mounting_margin;            //Маржа ГМ на монтажные работы
                $dealer_canvases_margin = $project->dealer_canvases_margin;    //Маржа дилера на полотно
                $dealer_components_margin = $project->dealer_components_margin;    //Маржа дилера на комплектующие
                $dealer_mounting_margin = $project->dealer_mounting_margin;    //Маржа дилера на монтажные работы

            } else {
                $dealer_info = Gm_ceilingHelpersGm_ceiling::getModel('dealer_info');
                $dealer_marg = $dealer_info->getData();
                //Или назначаем маржи из настроек дилера
                $gm_canvases_margin = $dealer_marg->gm_canvases_margin;            //Маржа ГМ на полотно
                $gm_components_margin = $dealer_marg->gm_components_margin;            //Маржа ГМ на комплектующие
                $gm_mounting_margin = $dealer_marg->gm_mounting_margin;            //Маржа ГМ на монтажные работы
                $dealer_canvases_margin = $dealer_marg->dealer_canvases_margin;    //Маржа дилера на полотно
                $dealer_components_margin = $dealer_marg->dealer_components_margin;    //Маржа дилера на комплектующие
                $dealer_mounting_margin = $dealer_marg->dealer_mounting_margin;
                //Маржа дилера на монтажные работы
            }
            $result ['gm_canvases_margin'] = $gm_canvases_margin;
            $result ['gm_components_margin'] = $gm_components_margin;
            $result ['gm_mounting_margin'] = $gm_mounting_margin;
            $result ['dealer_canvases_margin'] = $dealer_canvases_margin;
            $result ['dealer_components_margin'] = $dealer_components_margin;
            $result ['dealer_mounting_margin'] = $dealer_mounting_margin;
            return $result;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public static function calculate_canvases($calc_id=null,$data=null){
        try {
            $calculation_model = self::getModel('calculation');
            if(!empty($calc_id)){
                $data = get_object_vars($calculation_model->getData($calc_id));
            } else if (!empty($data["id"])) {
                $data = get_object_vars($calculation_model->getData($data["id"]));
            }
            $calc_id = $data["id"];
            $margins = self::get_margin($data['project_id']);
            $gm_canvases_margin = $margins['gm_canvases_margin'];
            $dealer_canvases_margin = $margins['dealer_canvases_margin'];
            $dealer_info = JFactory::getUser($data['dealer_id']);
            if($dealer_info->dealer_id == 1){
                $dealer_info_canvases = $dealer_info->getCanvasesPrice($dealer_info->dealer_id);
            }
            else{
                $dealer_info_canvases = $dealer_info->getCanvasesPrice();
            }
            //Получаем прайс-лист полотен
            $canvases_model = Gm_ceilingHelpersGm_ceiling::getModel('canvases');
            $canvases_list = $canvases_model->getFilteredItemsCanvas();
            $canvases = [];
            foreach ($canvases_list as $i => $canvas)
                $canvases[$canvas->id] = $canvas;
            $canvases_data = array();
            $canvas_id = (empty($data["n3_id"])) ? $data["n3"] : $data["n3_id"];
            $canvasesData = $canvases_model->getFilteredItemsCanvas("`a`.`id` = $canvas_id");
            if($canvasesData[0]->texture_id == 29){
                $cutCanvas = $canvases_model->getCutArea($data['id']);
                $data['n4'] = $cutCanvas->canvas_area;
            }
            if (is_null($canvasesData[0]->color_id))
            {
                $color_title = '303';
            }
            else
            {
                $color_title = $canvasesData[0]->color_title;
            }
            $facture = $canvasesData[0]->texture_title;
            $width = floatval($canvasesData[0]->width) * 100;
            $name = $canvasesData[0]->name;
            $canvases_data['id'] = $canvas_id;
            $canvases_data['title'] = $facture.'-'.$color_title.'-'.$width.' '.$name;
            $canvases_data['sub_title'] = $facture.'-'.$color_title;
            $canvases_data['width'] = $width;
            $canvases_data['manufacturer'] = $name;
            $canvases_data['quantity'] = $data['n4'];
            $canvases_data['self_price'] = round($canvases[$canvas_id]->price, 2);                                    //Себестоимость по основному прайсу
            $canvases_data['self_total'] = round($data['n4'] * $canvases_data['self_price'], 2);
            //Стоимость с маржой ГМ (для дилера)
            $canvases_data['gm_price'] = margin($canvases_data['self_price'], $gm_canvases_margin);
            //Кол-во * Стоимость с маржой ГМ (для дилера)
            $canvases_data['gm_total'] = round($data['n4'] * $canvases_data['gm_price'], 2);
            //себестоимость дилера, с учетом его прайса
            $canvases_data['self_dealer_price'] = dealer_margin($canvases_data['gm_price'], 0, $dealer_info_canvases[$canvas_id]);
            //Кол-во * себестоимость дилера
            $canvases_data['self_dealer_total'] = round($data['n4'] * $canvases_data['self_dealer_price'], 2);
            //дилерская цена для клиента
            $canvases_data['dealer_price'] = dealer_margin($canvases_data['gm_price'], $dealer_canvases_margin, $dealer_info_canvases[$canvas_id]);
            //Кол-во * дилерскую цену (для клиента)
            $canvases_data['dealer_total'] = round($data['n4'] * $canvases_data['dealer_price'], 2);
            $offcut_data = self::calculate_offcut($calc_id);

            $min_sum = 200;//минимальная сумма заказа
            $guild_cost = self::calculate_guild_jobs($calc_id)['total_gm_guild'];
            if ($canvases_data['self_dealer_total'] + $offcut_data['self_dealer_total'] + $guild_cost < $min_sum) {
                $canvases_data['min_self_dealer_total'] = $min_sum;
                $canvases_data['min_dealer_total'] = margin($min_sum, $dealer_canvases_margin);
            }
            return $canvases_data;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public static function calculate_offcut($calc_id=null,$data = null){
        try {
            $calculation_model = self::getModel('calculation');
            if(!empty($calc_id)){
                $data = get_object_vars($calculation_model->getData($calc_id));
            } else if (!empty($data["id"])) {
                $data = get_object_vars($calculation_model->getData($data["id"]));
            }
            $data['n3'] = $data['n3_id'];
            $dealer_info = JFactory::getUser($data['dealer_id']);
            if($dealer_info->dealer_id == 1){
                $dealer_info_canvases = $dealer_info->getCanvasesPrice($dealer_info->dealer_id);
            }
            else{
                $dealer_info_canvases = $dealer_info->getCanvasesPrice();
            }
            $margins = self::get_margin($data['project_id']);
            $gm_canvases_margin = $margins['gm_canvases_margin'];
            $dealer_canvases_margin = $margins['dealer_canvases_margin'];
            $canvas_id = (empty($data["n3_id"])) ? $data["n3"] : $data["n3_id"];
            $canvases_model = Gm_ceilingHelpersGm_ceiling::getModel('canvases');
            $canvasData = $canvases_model->getFilteredItemsCanvas("`a`.`id` = $canvas_id");
            $canvas = $canvasData[0];
            $offcut_square_data = array();
            if ($canvas_id && $data['offcut_square'] > $data['n4']*0.5) {
                $offcut_square_data['title'] = "Количество обрезков";                                                                //Название фактуры и полотна
                $offcut_square_data['quantity'] = $data['offcut_square'];                                                            //Кол-во
                $offcut_square_data['self_price'] = round($canvas->price * 0.5, 2);                                    //Себестоимость
                $offcut_square_data['self_total'] = round($data['offcut_square'] * $offcut_square_data['self_price'], 2);            //Кол-во * Себестоимость
                //Стоимость с маржой ГМ (для дилера)
                $offcut_square_data['gm_price'] = round(margin($canvas->price, $gm_canvases_margin) * 0.5, 2);
                //Кол-во * Стоимость с маржой ГМ (для дилера)
                $offcut_square_data['gm_total'] = round($data['offcut_square'] * $offcut_square_data['gm_price'], 2);
                //Стоимость с маржой ГМ и дилера (для клиента)
                $offcut_square_data['self_dealer_price'] = round(dealer_margin($canvas->price, 0, $dealer_info_canvases[$canvas_id]) * 0.5, 2);
                //Кол-во * Стоимость с маржой ГМ и дилера (для клиента)
                $offcut_square_data['self_dealer_total'] = round($data['offcut_square'] * $offcut_square_data['self_dealer_price'], 2);
                $offcut_square_data['dealer_price'] = round(dealer_margin($canvas->price, $dealer_canvases_margin, $dealer_info_canvases[$canvas_id]) * 0.5, 2);
                //Кол-во * Стоимость с маржой ГМ и дилера (для клиента)
                $offcut_square_data['dealer_total'] = round($data['offcut_square'] * $offcut_square_data['dealer_price'], 2);
            }
            return $offcut_square_data;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public static function calculate_guild_jobs($calc_id=null,$data=null){
        try {
            if(!empty($calc_id)){
                $calc_model = self::getModel('calculation');
                $data = get_object_vars($calc_model->getData($calc_id));
                $data['n3'] = $data['n3_id'];
            }
            $mount_model = self::getModel('mount');

            $results = $mount_model->getDataAll(1);
            $margin = self::get_margin($data['project_id']);
            $guild_data = array();
            $canvases_model = Gm_ceilingHelpersGm_ceiling::getModel('canvases');
            if(!empty($data['n3'])){
                $canvasData = $canvases_model->getFilteredItemsCanvas("`a`.`id` =". $data['n3_id']);
                $data['n1'] = $canvasData[0]->texture_id;
            }
            if (!empty($data['n1']) &&  $data['n1'] != 29 && $data['n9'] > 6) {
                //Обработка 1 угла
                $gm_mp20 = margin($results->mp20, $margin['gm_canvases_margin']);
                $dealer_mp20 = margin($gm_mp20, $margin['dealer_canvases_margin']);
                $guild_data[] = array(
                    "title" => "Обработка 1 угла",                                                   //Название
                    "quantity" => $data['n9'] - 6,                                                   //Кол-во
                    "gm_salary" => $gm_mp20,                                                         //Себестоимость монтажа ГМ (зарплата монтажников)
                    "gm_salary_total" => ($data['n9'] - 6) * $gm_mp20,                               //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                    "dealer_salary" => $dealer_mp20,                                                 //Себестоимость монтажа дилера (зарплата монтажников)
                    "dealer_salary_total" => ($data['n9'] - 6) * $dealer_mp20                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                );
            }
            if ( $data['n31'] > 0) {
                //внутренний вырез ТОЛЬКО ДЛЯ ПВХ
                $gm_mp22 = margin($results->mp22, $margin['gm_canvases_margin']);
                $dealer_mp22 = margin($gm_mp22, $margin['dealer_canvases_margin']);
                $guild_data[] = array(
                    "title" => "Внутренний вырез(в цеху)",                                           //Название
                    "quantity" => $data['n31'],                                                      //Кол-во
                    "gm_salary" => $gm_mp22,                                                         //Себестоимость монтажа ГМ (зарплата монтажников)
                    "gm_salary_total" => $data['n31'] * $gm_mp22,                                    //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                    "dealer_salary" => $dealer_mp22,                                                 //Себестоимость монтажа дилера (зарплата монтажников)
                    "dealer_salary_total" => $data['n31'] * $dealer_mp22                             //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                );
            }
            if($data['n10'] > 0){
                //криволинейный участок
                $gm_mp21 = margin($results->mp21, $margin['gm_canvases_margin']);
                $dealer_mp21 = margin($gm_mp21, $margin['dealer_canvases_margin']);
                $guild_data[] = array(
                    "title" => "Криволинейный участок",                                              //Название
                    "quantity" => $data['n10'],                                                      //Кол-во
                    "gm_salary" => $gm_mp21,                                                         //Себестоимость монтажа ГМ (зарплата монтажников)
                    "gm_salary_total" => $data['n10'] * $gm_mp21,                                    //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                    "dealer_salary" => $dealer_mp21,                                                 //Себестоимость монтажа дилера (зарплата монтажников)
                    "dealer_salary_total" => $data['n10'] * $dealer_mp21                             //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                );
            }
            if($data['n36'] > 0){
                $gm_mp35 = margin($results->mp35, $margin['gm_canvases_margin']);
                $dealer_mp35 = margin($gm_mp35, $margin['dealer_canvases_margin']);
                $guild_data[] = array(
                    "title" => "Перегарпунка",                                                       //Название
                    "quantity" => $data['n36'],                                                      //Кол-во
                    "gm_salary" => $gm_mp35,                                                         //Себестоимость монтажа ГМ (зарплата монтажников)
                    "gm_salary_total" => $data['n36'] * $gm_mp35,                                    //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                    "dealer_salary" => $dealer_mp35,                                                 //Себестоимость монтажа дилера (зарплата монтажников)
                    "dealer_salary_total" => $data['n36'] * $dealer_mp35                             //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                );
            }
            if($data['n43'] > 0){
                $gm_mp72 = margin($results->mp72, $margin['gm_canvases_margin']);
                $dealer_mp72 = margin($gm_mp72, $margin['dealer_canvases_margin']);
                $guild_data[] = array(
                    "title" => "Переделка углов",                                                    //Название
                    "quantity" => $data['n43'],                                                      //Кол-во
                    "gm_salary" => $gm_mp72,                                                         //Себестоимость монтажа ГМ (зарплата монтажников)
                    "gm_salary_total" => $data['n43'] * $gm_mp72,                                    //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                    "dealer_salary" => $dealer_mp72,                                                 //Себестоимость монтажа дилера (зарплата монтажников)
                    "dealer_salary_total" => $data['n43'] * $dealer_mp72                             //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                );
            }
            if(!empty($data['n37'])){
                $n37 = json_decode($data['n37']);
                if(!empty($n37->square) && !empty($n37->cost)){
                    $gm_price = round($n37->cost/$n37->square,2);
                    $dealer_price = margin($gm_price, $margin['dealer_canvases_margin']);
                    $guild_data[] = array(
                        "title" => "Фотопечать",                                                     //Название
                        "quantity" => $n37->square,                                                  //Кол-во
                        "gm_salary" => $gm_price,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $n37->cost,                                             //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $dealer_price ,                                           //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $n37->square * $dealer_price                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                    );
                }
            }
            $result = [];
            $result['guild_data'] = $guild_data;
            foreach ($guild_data as $guild) {
                $result['total_gm_guild'] += $guild['gm_salary_total'];
                $result['total_dealer_guild'] += $guild['dealer_salary_total'];
                $result['total_with_gm_margin_guild'] += $guild['total_with_gm_margin'];
                $result['total_with_gm_dealer_margin_guild'] += $guild['total_with_gm_dealer_margin'];
                $result['total_with_dealer_margin_guild'] += $guild['total_with_dealer_margin'];
            }

            return $result;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    /*  основная функция для расчета стоимости монтажа
        $del_flag 0 - не удалать светильники, трубы и т.д что хранится в др. таблицах
        $calc_id - id калькуляции в БД
        $data - массив данных для просчета, если новый просчет
    */
    public static function calculate_mount($del_flag,$calc_id=null,$data=null,$service=null){
        try {
            $user = JFactory::getUser();
            $groups = $user->get('groups');
            $mount_model = self::getModel('mount');
            $calculation_model = self::getModel('calculation');
            $components_model = Gm_ceilingHelpersGm_ceiling::getModel('components');
            $components_list = $components_model->getFilteredItems();
            foreach ($components_list as $i => $component) {
                $components[$component->id] = $component;
            }
            $transport = 0;
            $calculation_data = null;
            if(empty($calc_id)){
                $project_id = $data['project_id'];
            }
            else {
                $calculation_data = (array) $calculation_model->getData($calc_id);
                $calculation_data2 = (array) $calculation_model->getDataById($calc_id);
                foreach ($calculation_data as $key => $item) {
                    if ((empty($item) || strripos($key, "n") == 0) && array_key_exists($key, $calculation_data2))
                        $calculation_data[$key] = $calculation_data2[$key];
                }

                $calculation_data["extra_mounting_array"] = array();
                $extra_mounting_decode = json_decode($calculation_data["extra_mounting"]);
                if (!empty($extra_mounting_decode)) {
                    foreach ($extra_mounting_decode as $extra_mounting) {
                        $calculation_data["extra_mounting_array"][] = $extra_mounting;
                    }
                }
                $calculation_data["need_mount_extra"] = !empty($calculation_data["extra_mounting_array"]);
                if (!$calculation_data["need_mount_extra"] && empty($calculation_data['need_mount'])) {
                    $calculation_data["need_mount"] = 1;
                }
                $project_id = $calculation_data['project_id'];
            }
            $project_model = self::getModel('project');
            $client_id = $project_model->getData($project_id)->id_client;
            if(!empty($client_id)){
                $client_model = self::getModel('client');
                $dealer = JFactory::getUser($client_model->getClientById($client_id)->dealer_id);
                if($dealer->dealer_type == 7){
                    $dealer_id = $dealer->id;
                }
                else{
                    $dealer_id = $dealer->dealer_id;
                }
                if(empty($dealer_id)){
                    $dealer_id = 1;
                }
            }
            else{
                $dealer_id = 1;
            }
            //Если существующая калькуляция
            if(!empty($calc_id)){
                foreach ($calculation_data as $key => $item) {
                    $data[$key] = $item;
                }
                $n13 = $data['n13'];
                $n26 = $data['n26'];
                $n22 = $data['n22'];
                $n14 = $data['n14'];
                $n23 = $data['n23'];
                $n15 = $data['n15'];
                $n29 = $data['n29'];
            }

            $gm_mount  = $mount_model->getDataAll(1);
            if((!empty($service) && $service == "service")){
                $data['need_mount'] = 2;
            }

            if($data['need_mount'] == 1){
                $results = $mount_model->getDataAll($dealer_id);

            }
            if($data['need_mount'] == 2){
                $results = $mount_model->getDataAll(0);
                /*array_walk($results, function(&$mp,$key){
                    /*обагечивание и натяжка наценка 20%
                    if($key == "mp1" || $key == "mp31" || $key == "mp32" || $key == "mp47" ){
                        $mp += $mp*0.2;
                    }
                    */
                    /*все по 20%
                    if(mb_ereg('mp[\d]+',$key)){
                        $mp += $mp*0.2;
                    }
                });*/
            }
            if($service == "serviceSelf"){
                $results = $mount_model->getDataAll(1);
            }
            if(!empty($data['n3_id'])){
                $canvases_model = Gm_ceilingHelpersGm_ceiling::getModel('canvases');
                $canvasData = $canvases_model->getFilteredItemsCanvas("`a`.`id` =". $data['n3_id']);
                $data['n1'] = $canvasData[0]->texture_id;
            }
            $count_round_lamp = 0;
            $count_square_lamp = 0;
            $count_ventilation = 0;
            $count_ventilation_1 = 0;
            $count_diffuzor = 0;
            $count_pipe = 0;
            $count_big_pipe = 0;
            $count_profil_1 = 0;
            $count_profil_2 = 0;
            $count_profil_3 = 0;
            $count_profil_4 = 0;
            if(empty($calc_id)){
                if (!is_array($data['n13'])) $n13 = json_decode($data['n13']);
                else $n13 = $data['n13'];
                if (!is_array($data['n26'])) $n26 = json_decode($data['n26']);
                else $n26 = $data['n26'];
                if (!is_array($data['n22'])) $n22 = json_decode($data['n22']);
                else $n22 = $data['n22'];
                if (!is_array($data['n14'])) $n14 = json_decode($data['n14']);
                else $n14 = $data['n14'];
                if (!is_array($data['n23'])) $n23 = json_decode($data['n23']);
                else $n23 = $data['n23'];
                if (!is_array($data['n15'])) $n15 = json_decode($data['n15']);
                else $n15 = $data['n15'];
                if (!is_array($data['n29'])) $n29 = json_decode($data['n29']);
                else $n29 = $data['n29'];
            }

            $mounting_data = [];

            if ($data["need_mount"]) {
                if (/*!empty($data['n1']) &&  $data['n1'] != 29 &&*/ $data['n11'] > 0) {
                    //внутренний вырез
                    $mounting_data[] = array(
                        "title" => "Закладная под внутренний вырез ",                                              //Название
                        "quantity" => $data['n11'],                                                                //Кол-во
                        "gm_salary" => $gm_mount->mp48,                                                            //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $data['n11'] * $gm_mount->mp48,                                       //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp48,                                                         //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $data['n11'] * $results->mp48,                                    //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        "stage" => 2
                    );
                    $mounting_data[] = array(
                        "title" => "Внутренний вырез ",                                                            //Название
                        "quantity" => $data['n11'],                                                                //Кол-во
                        "gm_salary" => $gm_mount->mp48_2,                                                          //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $data['n11'] * $gm_mount->mp48_2,                                     //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp48_2,                                                       //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $data['n11'] * $results->mp48_2,                                  //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        "stage" => 3
                    );
                }
                if ((is_array($n13) || is_object($n13)) && count($n13) > 0) {
                    foreach ($n13 as $svet) {
                        switch (gettype($svet)) {
                            case 'object':
                                if ($svet->n13_count > 0) {
                                    if($svet->n13_type == 2){
                                        $count_round_lamp += $svet->n13_count;
                                    }
                                    if($svet->n13_type == 3){
                                        $count_square_lamp += $svet->n13_count;
                                    }
                                }
                                break;

                            default:
                                 if ($svet[0] > 0) {
                                    if($svet[1] == 2){
                                        $count_round_lamp += $svet[0];
                                    }
                                    if($svet[1] == 3){
                                        $count_square_lamp += $svet[0];
                                    }
                                }
                                break;
                        }
                    }
                }
                if ((is_array($n22) || is_object($n22)) && count($n22) > 0) {
                    foreach ($n22 as $ventilation) {
                        switch (gettype($ventilation)) {
                            case 'object':
                                if ($ventilation->n22_count > 0 && ($ventilation->n22_type == 5 || $ventilation->n22_type == 6)) {
                                    $count_ventilation += $ventilation->n22_count;
                                }
                                if ($ventilation->n22_count > 0 && ($ventilation->n22_type == 7 || $ventilation->n22_type == 8)) {
                                    $count_ventilation_1 += $ventilation->n22_count;
                                }
                                break;

                            default:
                               if ($ventilation[0] > 0 && ($ventilation[1] == 5 || $ventilation[1] == 6)) {
                                    $count_ventilation += $ventilation[0];
                                }
                                if ($ventilation[0] > 0 && ($ventilation[1] == 7 || $ventilation[1] == 8)) {
                                    $count_ventilation_1 += $ventilation[0];
                                }
                                break;
                        }

                    }
                }
                if($data['n22_1'] > 0){
                    $mounting_data[] = array(
                        "title" => "Монтаж пластикового короба(вытяжка)",                                    //Название
                        "quantity" => $data['n22_1'],                                                        //Кол-во
                        "gm_salary" => $gm_mount->mp62,                                                      //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $data['n22_1'] * $gm_mount->mp62,                               //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp62,                                                   //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $data['n22_1'] * $results->mp62,                            //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        "stage"=>2
                    );
                }

                if($data['n41'] > 0){
                    $mounting_data[] = array(
                        "title" => "Демонтаж потолка",                                                       //Название
                        "quantity" => $data['n41'],                                                   //Кол-во
                        "gm_salary" => $gm_mount->mp70,                                                      //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" =>$data['n41'] * $gm_mount->mp70,                           //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp70,                                                   //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" =>$data['n41'] * $results->mp70,                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        "stage"=>1
                    );
                }
                if($data['n42'] > 0){
                    $mounting_data[] = array(
                        "title" => "Вытяжка(наклейка кольца)",                                                       //Название
                        "quantity" => $data['n42'],                                                   //Кол-во
                        "gm_salary" => $gm_mount->mp71,                                                      //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" =>$data['n42'] * $gm_mount->mp71,                           //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp71,                                                   //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" =>$data['n42'] * $results->mp71,                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        "stage"=>2
                    );
                }
                if($data['remove_n28'] > 0){
                    $mounting_data[] = array(
                        "title" => "Демонтаж старого профиля",                                               //Название
                        "quantity" => $data['remove_n28'],                                                   //Кол-во
                        "gm_salary" => $gm_mount->mp63,                                                      //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" =>$data['remove_n28'] * $gm_mount->mp63,                           //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp63,                                                   //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" =>$data['remove_n28'] * $results->mp63,                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        "stage"=>1
                    );
                }
                if ((is_array($n23) || is_object($n23)) && count($n23) > 0) {
                    foreach ($n23 as $diffuzor) {
                        switch (gettype($diffuzor)) {
                            case 'object':
                               if ($diffuzor->n23_count > 0) {
                                    $count_diffuzor += $diffuzor->n23_count;
                                }
                                break;
                            default:
                               if ($diffuzor[0] > 0) {
                                    $count_diffuzor += $diffuzor[0];
                                }
                                break;
                        }
                    }
                }
                if ((is_array($n14) || is_object($n14)) && count($n14) > 0) {
                    foreach ($n14 as $truba) {
                        switch (gettype($truba)) {
                            case 'object':
                                if ($truba->n14_count > 0) {
                                    $size_str = $components[$truba->n14_type]->title;
                                    $size = preg_replace("/[^-0-9]/", '', $size_str);
                                    $size_arr = explode('-',$size);
                                    (empty($size_arr[1])) ? $diam = $size_arr[0] : $diam = $size_arr[1];
                                    if($diam > 100){
                                        $count_big_pipe += $truba->n14_count;
                                    }
                                    else{
                                        $count_pipe += $truba->n14_count;
                                    }
                                }
                                break;

                            default:
                               if ($truba[0] > 0) {
                                    $size_str = $components[$truba[1]]->title;
                                    $size = preg_replace("/[^-0-9]/", '', $size_str);
                                    $size_arr = explode('-',$size);
                                    (empty($size_arr[1])) ? $diam = $size_arr[0] : $diam = $size_arr[1];
                                    if($diam > 100){
                                        $count_big_pipe += $truba[0];
                                    }
                                    else{
                                        $count_pipe += $truba[0];
                                    }
                                }
                                break;
                        }

                    }
                }
                if ((is_array($n29) || is_object($n29)) && count($n29) > 0) {
                    foreach ($n29 as $profil) {
                        switch (gettype($profil)) {
                            case 'object':
                                if ($profil->n29_count > 0 && $profil->n29_type == 12) {
                                    $count_profil_1 += $profil->n29_count;
                                }
                                if ($profil->n29_count > 0 && $profil->n29_type == 13) {
                                    $count_profil_2 += $profil->n29_count;
                                }
                                if ($profil->n29_count > 0 && $profil->n29_type == 15) {
                                    $count_profil_3 += $profil->n29_count;
                                }
                                if ($profil->n29_count > 0 && $profil->n29_type == 16) {
                                    $count_profil_4 += $profil->n29_count;
                                }
                                if ($profil->n29_count > 0 && $profil->n29_type == 17) {
                                    $count_profil_5 += $profil->n29_count;
                                }
                                if ($profil->n29_count > 0 && $profil->n29_type == 18) {
                                    $count_profil_6 += $profil->n29_count;
                                }
                                break;

                            default:
                               if ($profil[0] > 0 && $profil[1] == 12) {
                                    $count_profil_1 += $profil[0];
                                }
                                if ($profil[0] > 0 && $profil[1] == 13) {
                                    $count_profil_2 += $profil[0];
                                }
                                if ($profil[0] > 0 && $profil[1] == 15) {
                                    $count_profil_3 += $profil[0];
                                }
                                 if ($profil[0] > 0 && $profil[1] == 16) {
                                    $count_profil_4 += $profil[0];
                                }
                                if ($profil[0] > 0 && $profil[1] == 17) {
                                    $count_profil_5 += $profil[0];
                                }
                                if ($profil[0] > 0 && $profil[1] == 18) {
                                    $count_profil_6 += $profil[0];
                                }
                                break;
                        }
                    }
                }
                //только для ПВХ
                if (!empty($data['n1']) &&  $data['n1'] != 29) {
                    if ($data['height'] == 1 || $data['height'] == 2) {
                        $name = "высота >3м";
                        $mp1 = $results->mp1 + 10;
                        $mp31 = $results->mp31 + 10;
                        $mp32 = $results->mp32 + 10;
                        $gm_mp1 = $gm_mount->mp1 + 10;
                        $gm_mp31 = $gm_mount->mp31 + 10;
                        $gm_mp32 = $gm_mount->mp32 + 10;
                    } else {
                        $name = "высота <3м ";
                        $mp1 = $results->mp1;
                        $mp31 = $results->mp31;
                        $mp32 = $results->mp32;
                        $gm_mp1 = $gm_mount->mp1;
                        $gm_mp31 = $gm_mount->mp31;
                        $gm_mp32 = $gm_mount->mp32;

                    }
                    $n5val = $data['n5'];
                    if ($data['n16']) {
                        $n5val = $data['n5'] - $data['n27'];
                    }

                    //вычитаем из периметра потолка крепление в керамогранит
                    $n5val = (($n5val - $data['n8']) > 0) ? $n5val - $data['n8'] : 0;
                    $n5val = (($n5val - $data['n20']) > 0) ? $n5val - $data['n20'] : 0;
                    $n5val = (($n5val - $data['n30']) > 0) ? $n5val - $data['n30'] : 0;
                    $n5val = (($n5val - $data['n35']) > 0) ? $n5val - $data['n35'] : 0;
                    if ($n5val) {
                        //выбор типа профиля
                        if ($data['n28'] == 3) {
                            $mounting_data[] = array(
                                "title" => "Монтаж стенового багета ПВХ ($name)",                          //Название
                                "quantity" => $n5val,                                                      //Кол-во
                                "gm_salary" => $gm_mp1,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                                "gm_salary_total" => $n5val * $gm_mp1,                                     //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                                "dealer_salary" => $mp1,                                                   //Себестоимость монтажа дилера (зарплата монтажников)
                                "dealer_salary_total" => $n5val * $mp1,                                    //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                                "stage" => 2
                            );
                        }
                        //периметр
                        if ($data['n28'] == 1) {
                            $mounting_data[] = array(
                                "title" => "Монтаж потолочного алюминиевого багета ($name)",               //Название
                                "quantity" => $n5val,                                                      //Кол-во
                                "gm_salary" => $gm_mp31,                                                   //Себестоимость монтажа ГМ (зарплата монтажников)
                                "gm_salary_total" => $n5val * $gm_mp31,                                    //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                                "dealer_salary" => $mp31,                                                  //Себестоимость монтажа дилера (зарплата монтажников)
                                "dealer_salary_total" => $n5val * $mp31,                                   //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                                "stage" => 2
                            );
                        }
                        //периметр
                        if ($data['n28'] == 2) {
                            $mounting_data[] = array(
                                "title" => "Монтаж стенового алюминиевого багета ($name)",                                                                    //Название
                                "quantity" => $n5val,                                                      //Кол-во
                                "gm_salary" => $gm_mp32,                                                   //Себестоимость монтажа ГМ (зарплата монтажников)
                                "gm_salary_total" => $n5val * $gm_mp32,                                    //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                                "dealer_salary" => $mp32,                                                  //Себестоимость монтажа дилера (зарплата монтажников)
                                "dealer_salary_total" => $n5val * $mp32,                                   //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                                "stage" => 2
                            );
                        }
                    }
                    $dealerType = JFactory::getUser($dealer_id)->dealer_type;
                    $n4val = ($dealerType == 7) ? $data['n4'] : $data['n5'];
                    if ($n4val > 0) {
                        $mounting_data[] = array(
                            "title" => "Натяжка полотна (ПВХ)",                                                  //Название
                            "quantity" => $n4val,                                                          //Кол-во
                            "gm_salary" => $gm_mount->mp47,                                                //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $n4val * $gm_mount->mp47,                                 //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp47,                                             //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $n4val * $results->mp47,                              //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                            "stage" => 3
                        );
                    }
                }
               //только для ткани
                if ($data['n1'] == 29) {
                    if($data['height'] == 1 || $data['height'] == 2){
                        $name = "высота >3м";
                        $mp68 = $results->mp68 + 10;
                        $mp69 = $results->mp69 + 10;
                        $gm_mp68 = $gm_mount->mp68 + 10;
                        $gm_mp69 = $gm_mount->mp69 + 10;
                    }
                    else{
                        $name = "высота <3м ";
                        $mp68 = $results->mp68;
                        $mp69 = $results->mp69;
                        $gm_mp68 = $gm_mount->mp68;
                        $gm_mp69 = $gm_mount->mp69;

                    }
                    //обагечивание
                    if ($data['n5'] > 0) {
                        $n5val = $data['n5'];
                        if ($data['n28'] == 3 || $data['n28'] == 2) {
                            $mounting_data[] = array(
                                "title" => "Монтаж стенового багета(ткань) ($name)",                                              //Название
                                "quantity" => $n5val,                                     //Кол-во
                                "gm_salary" => $gm_mp68,                                           //Себестоимость монтажа ГМ (зарплата монтажников)
                                "gm_salary_total" => $n5val * $gm_mp68,                       //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                                "dealer_salary" => $mp68,                                       //Себестоимость монтажа дилера (зарплата монтажников)
                                "dealer_salary_total" => $n5val * $mp68,                    //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                                "stage" => 2
                            );
                        }
                        if ($data['n28'] == 1) {
                            $mounting_data[] = array(
                                "title" => "Монтаж потолочного багета(ткань) ($name)",                                              //Название
                                "quantity" => $n5val,                                     //Кол-во
                                "gm_salary" => $gm_mp69,                                          //Себестоимость монтажа ГМ (зарплата монтажников)
                                "gm_salary_total" => $n5val * $gm_mp69,                                            //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                                "dealer_salary" => $mp69,                                                        //Себестоимость монтажа дилера (зарплата монтажников)
                                "dealer_salary_total" => $n5val * $mp69,                                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                                "stage" => 2
                            );
                        }
                        if ($n5val > 0) {
                            $mounting_data[] = array(
                                "title" => "Натяжка полотна (Ткань)",                                                                    //Название
                                "quantity" => $n5val,                                                                //Кол-во
                                "gm_salary" => $gm_mount->mp33,                                                                //Себестоимость монтажа ГМ (зарплата монтажников)
                                "gm_salary_total" => $n5val * $gm_mount->mp33,                                            //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                                "dealer_salary" => $results->mp33,                                                        //Себестоимость монтажа дилера (зарплата монтажников)
                                "dealer_salary_total" => $n5val * $results->mp33,                                     //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                                "stage"=>3
                            );
                        }
                    }
                }
                //----------------------------- Общие просчеты и для ПВХ и для ткани ----------------------------------------
                if($data['n33']>0){
                    $mounting_data[] = array(
                        "title" => "Монтаж лючка ",                                                        //Название
                        "quantity" => $data['n33'],                                                        //Кол-во
                        "gm_salary" => $gm_mount->mp49,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $data['n33'] * $gm_mount->mp49,                               //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp49,                                                 //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $data['n33'] * $results->mp49,                            //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        "stage"=>2
                    );
                }
                if($data['n33_2']>0){
                    $mounting_data[] = array(
                        "title" => "Обход люка ",                                                          //Название
                        "quantity" => $data['n33_2'],                                                      //Кол-во
                        "gm_salary" => $gm_mount->mp50,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $data['n33_2'] * $gm_mount->mp50,                             //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp50,                                                 //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $data['n33_2'] * $results->mp50,                          //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        "stage"=>2
                    );
                }
                if($data['n34']>0){
                    $mounting_data[] = array(
                        "title" => "Установка диодной ленты",                                              //Название
                        "quantity" => $data['n34'],                                                        //Кол-во
                        "gm_salary" => $gm_mount->mp51,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $data['n34'] * $gm_mount->mp51,                               //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp51,                                                 //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $data['n34'] * $results->mp51,                            //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        "stage"=>2
                    );
                }
                if($data['n34_2']>0){
                    $mounting_data[] = array(
                        "title" => "Монтаж и подключение блока питания",                                   //Название
                        "quantity" => $data['n34_2'],                                                      //Кол-во
                        "gm_salary" => $gm_mount->mp52,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $data['n34_2'] * $gm_mount->mp52,                             //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp52,                                                 //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $data['n34_2'] * $results->mp52,                          //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        "stage"=>2
                    );
                }
                if($data['n35']>0){
                    $mounting_data[] = array(
                        "title" => "Монтаж контурного профиля",                                            //Название
                        "quantity" => $data['n35'],                                                        //Кол-во
                        "gm_salary" => $gm_mount->mp61,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $data['n35'] * $gm_mount->mp61,                               //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp61,                                                 //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $data['n35'] * $results->mp61,                            //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        "stage"=>2
                    );
                }
                if($data['n38']>0){
                    $mounting_data[] = array(
                        "title" => "Ремонт потолка",                                                       //Название
                        "quantity" => $data['n38'],                                                        //Кол-во
                        "gm_salary" => $gm_mount->mp64,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $data['n38'] * $gm_mount->mp64,                               //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp64,                                                 //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $data['n38'] * $results->mp64,                            //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        "stage"=>2
                    );
                }
                if($data['height'] == 1){
                    $mounting_data[] = array(
                        "title" => "Высота от 4 до 5 метров",                                              //Название
                        "quantity" => 1,                                                                   //Кол-во
                        "gm_salary" => $gm_mount->mp65,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $gm_mount->mp65,                                              //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp65,                                                 //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $results->mp65,                                           //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        "stage" => 2
                    );
                }
                if($data['height'] == 2){
                    $mounting_data[] = array(
                        "title" => "Высота более 5 метров",                                                //Название
                        "quantity" => 1,                                                                   //Кол-во
                        "gm_salary" => $gm_mount->mp66,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $gm_mount->mp66,                                              //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp66,                                                 //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $results->mp66,                                           //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        "stage" => 2
                    );
                }
                if($data['scaffolding'] == 1){
                    $mounting_data[] = array(
                        "title" => "Сборка/разборка лесов",                                                //Название
                        "quantity" => 1,                                                                   //Кол-во
                        "gm_salary" => $gm_mount->mp67,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $gm_mount->mp67,                                              //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp67,                                                 //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $results->mp67,                                           //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        "stage" => 2
                    );
                }
                if ($data['n31'] > 0) {
                    $mounting_data[] = array(
                        "title" => "Периметр (внутренний вырез) (ПВХ)",                                                                    //Название
                        "quantity" => $data['n31'],                                                    //Кол-во
                        "gm_salary" => $gm_mount->mp1,                                                 //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $data['n31'] * $gm_mount->mp1,                            //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp1,                                              //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $data['n31'] * $results->mp1,                         //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        "stage" => 3
                    );
                    if ($data['color'] > 0) {
                        $color_model = Gm_ceilingHelpersGm_ceiling::getModel('color');
                        $color = $color_model->getData($data['color']);
                        $name = "Вставка (внутренний вырез), цвет: " . $color->colors_title . " (ПВХ)";
                    } else {
                        $name = "Вставка (внутренний вырез) (ПВХ)";
                    }
                    $mounting_data[] = array(
                        "title" => $name,                                                              //Название
                        "quantity" => $data['n31'],                                                    //Кол-во
                        "gm_salary" => $gm_mount->mp10,                                                //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $data['n31'] * $gm_mount->mp10,                           //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp10,                                             //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $data['n31'] * $results->mp10,                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        "stage" => 4
                    );
                }
                //со вставкой
                if ($data['n6'] > 0 || $data['n20'] > 0) {
                    if ($data['color'] > 0) {
                        $color_model = Gm_ceilingHelpersGm_ceiling::getModel('color');
                        $color = $color_model->getData($data['color']);
                        $name = "Вставка, цвет: " . $color->colors_title." ";
                    } else {
                        $name = "Вставка ";
                    }
                    $quantity = ($data['n6']>0) ? $data['n5'] + $data['n20'] : $data['n20'];
                    $mounting_data[] = array(
                        "title" => $name,                                                              //Название
                        "quantity" => $quantity,                                                       //Кол-во
                        "gm_salary" => $gm_mount->mp10,                                                //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $quantity * $gm_mount->mp10,                              //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp10,                                             //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $quantity * $results->mp10,                           //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        "stage"=> 4
                    );
                }
                //установка люстры
                if ($data['n12'] > 0) {
                    $mounting_data[] = array(
                        "title" => "Установка закладной под люстру ",                     //Название
                        "quantity" => $data['n12'],//$count_lust,                                      //Кол-во
                        "gm_salary" => $gm_mount->mp2,//max($gm->mp4, $gm->mp5),                       //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $gm_mount->mp2 * $data['n12'],                            //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp2,//max($dealer->mp4, $dealer->mp5),            //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $data['n12'] * $results->mp2,                         //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        "stage"=>2
                    );
                    $mounting_data[] = array(
                        "title" => "Установка люстры ",                                   //Название
                        "quantity" => $data['n12'],//$count_lust,                                      //Кол-во
                        "gm_salary" => $gm_mount->mp2_2,//max($gm->mp4, $gm->mp5),                     //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $gm_mount->mp2_2 * $data['n12'],                          //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp2_2,//max($dealer->mp4, $dealer->mp5),          //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $data['n12'] * $results->mp2_2,                       //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        "stage"=>3
                    );
                }
                if($data['n16']){
                    $cornice = "Закладная под шторный карниз / Скрытый ";
                    $cornice1 = "Установка шторного карниза в бетон/ Скрытый ";
                }
                else {
                    $cornice = "Закладная под шторный карниз / Обычный ";
                    $cornice1 = "Установка шторного карниза / Обычный ";
                }
                //Установка светильников
                if (count($n13) > 0) {
                    if ($count_round_lamp > 0) {
                        $mounting_data[] = array(
                            "title" => "Установка закладной под круглые светильники ",      //Название
                            "quantity" => $count_round_lamp,                                             //Кол-во
                            "gm_salary" => $gm_mount->mp4,                                               //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_round_lamp * $gm_mount->mp4,                     //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp4,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_round_lamp * $results->mp4,                  //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                            "stage"=>2
                        );
                    }
                    if ($count_square_lamp > 0) {
                        $mounting_data[] = array(
                            "title" => "Установка закладной под квадратные светильники ",   //Название
                            "quantity" => $count_square_lamp,                                            //Кол-во
                            "gm_salary" => $gm_mount->mp5,                                               //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_square_lamp * $gm_mount->mp5,                    //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp5,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_square_lamp * $results->mp5,                 //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                            "stage"=>2
                        );
                    }
                    $count_lamp = $count_square_lamp+$count_round_lamp;
                    if($count_lamp > 0){
                        $mounting_data[] = array(
                            "title" => "Установка светильников ",                           //Название
                            "quantity" => $count_lamp,                                                   //Кол-во
                            "gm_salary" => $gm_mount->mp4_2,                                             //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_lamp * $gm_mount->mp4_2,                         //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp4_2,                                          //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_lamp * $results->mp4_2,                      //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                            "stage"=>3

                        );
                    }
                }
                if (count($n22) > 0) {
                    if ($count_ventilation > 0) {
                        $mounting_data[] = array(
                            "title" => "Установка вентиляции ",                             //Название
                            "quantity" => $count_ventilation,                                            //Кол-во
                            "gm_salary" => $gm_mount->mp16,                                              //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_ventilation * $gm_mount->mp16,                   //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp16,                                           //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_ventilation * $results->mp16,                //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                            "stage"=>3
                        );
                    }
                    if ($count_ventilation_1 > 0) {
                        $mounting_data[] = array(
                            "title" => "Установка электровытяжки ",                         //Название
                            "quantity" => $count_ventilation_1,                                          //Кол-во
                            "gm_salary" => $gm_mount->mp12,                                              //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_ventilation_1 * $gm_mount->mp12,                 //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp12,                                           //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_ventilation_1 * $results->mp12,              //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                            "stage"=>3
                        );
                    }
                }
                // установка диффузора
                if (count($n23) > 0) {
                    if ($count_diffuzor > 0) {
                        $mounting_data[] = array(
                            "title" => "Установка диффузора ",                              //Название
                            "quantity" => $count_diffuzor,                                               //Кол-во
                            "gm_salary" => $gm_mount->mp19,                                              //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_diffuzor * $gm_mount->mp19,                      //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp19,                                           //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_diffuzor * $results->mp19,                   //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                            "stage"=> 3
                        );
                    }
                }
                //обвод трубы
                if (count($n14) > 0) {
                    if ($count_pipe > 0) {
                        $mounting_data[] = array(
                            "title" => "Обвод трубы (<100мм) ",                             //Название
                            "quantity" => $count_pipe,                                                   //Кол-во
                            "gm_salary" => $gm_mount->mp8,                                               //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_pipe * $gm_mount->mp8,                           //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp8,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_pipe * $results->mp8,                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                            "stage"=> 3
                        );
                    }
                    if ($count_big_pipe > 0) {
                        $mounting_data[] = array(
                            "title" => "Обвод трубы (>100мм) ",                             //Название
                            "quantity" => $count_big_pipe,                                               //Кол-во
                            "gm_salary" => $gm_mount->mp7,                                               //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_big_pipe * $gm_mount->mp7,                       //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp7,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_big_pipe * $results->mp7,                    //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                            "stage"=> 3
                        );
                    }
                }
                if (count($n29) > 0) {
                    if ($count_profil_1 > 0) {
                        $mounting_data[] = array(
                            "title" => "Переход уровня по прямой ",                          //Название
                            "quantity" => $count_profil_1,                                                //Кол-во
                            "gm_salary" => $gm_mount->mp23,                                               //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_profil_1 * $gm_mount->mp23,                       //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp23,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_profil_1 * $results->mp23,                    //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                            "stage"=> 2
                        );
                    }
                    if ($count_profil_2 > 0) {
                        $mounting_data[] = array(
                            "title" => "Переход уровня по кривой ",                          //Название
                            "quantity" => $count_profil_2,                                                //Кол-во
                            "gm_salary" => $gm_mount->mp24,                                               //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_profil_2 * $gm_mount->mp24,                       //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp24,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_profil_2 * $results->mp24,                    //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                            "stage"=> 2
                        );
                    }
                    if ($count_profil_3 > 0) {
                        $mounting_data[] = array(
                            "title" => "Переход уровня по прямой с нишей ",                  //Название
                            "quantity" => $count_profil_3,                                                //Кол-во
                            "gm_salary" => $gm_mount->mp25,                                               //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_profil_3 * $gm_mount->mp25,                       //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp25,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_profil_3 * $results->mp25,                    //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                            "stage"=> 2
                        );
                    }
                    if ($count_profil_4 > 0) {
                        $mounting_data[] = array(
                            "title" => "Переход уровня по кривой с нишей ",                  //Название
                            "quantity" => $count_profil_4,                                                //Кол-во
                            "gm_salary" => $gm_mount->mp26,                                               //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_profil_4 * $gm_mount->mp26,                       //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp26,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_profil_4 * $results->mp26,                    //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                            "stage"=> 2
                        );
                    }
                    if ($count_profil_5 > 0) {
                        $mounting_data[] = array(
                            "title" => "Второй уровень безщелевой с натяжкой LED",                         //Название
                            "quantity" => $count_profil_5,                                                 //Кол-во
                            "gm_salary" => $gm_mount->mp58,                                                //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_profil_5 * $gm_mount->mp58,                        //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp58,                                             //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_profil_5 * $results->mp58,                     //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                            "stage"=> 2
                        );
                    }
                    if ($count_profil_6 > 0) {
                        $mounting_data[] = array(
                            "title" => "Второй уровень безщелевой с натяжкой ",                            //Название
                            "quantity" => $count_profil_6,                                                 //Кол-во
                            "gm_salary" => $gm_mount->mp59,                                                //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_profil_6 * $gm_mount->mp59,                        //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp59,                                             //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_profil_6 * $results->mp59,                     //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                            "stage"=> 2
                        );
                    }
                }
                //шторный карниз
                if ($data['n27'] > 0) {
                    if($data['n16'] == 0) {
                        $mounting_data[] = array(
                            "title" => $cornice,                                                           //Название
                            "quantity" => $data['n27'],                                                    //Кол-во
                            "gm_salary" => $gm_mount->mp60,                                                //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $data['n27'] * $gm_mount->mp60,                           //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp60,                                             //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $data['n27'] * $results->mp60,                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                            "stage" => 2
                        );
                        $mounting_data[] = array(
                            "title" => $cornice1,                                                          //Название
                            "quantity" => $data['n27'],                                                    //Кол-во
                            "gm_salary" => $gm_mount->mp11_2,                                              //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $data['n27'] * $gm_mount->mp11_2,                         //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp11_2,                                           //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $data['n27'] * $results->mp11_2,                      //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                            "stage" => 3
                        );
                    }
                    else{
                        switch($data['niche']){
                            case 1:
                                $nicheTitle = "Монтаж открытой ниши под шторный карниз";
                                $mountPrice = $results->mp53;
                                $gm_mountPrice = $gm_mount->mp53;
                                break;
                            case 2:
                                $nicheTitle = "Монтаж закрытой ниши под шторный карниз";
                                $mountPrice = $results->mp54;
                                $gm_mountPrice = $gm_mount->mp54;
                                $mounting_data[] = array(
                                    "title" => "Натяжка закрытой ниши",                                     //Название
                                    "quantity" => $data['n27'],                                             //Кол-во
                                    "gm_salary" => $gm_mount->mp56,                                         //Себестоимость монтажа ГМ (зарплата монтажников)
                                    "gm_salary_total" => $data['n27'] * $gm_mount->mp56,                    //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                                    "dealer_salary" => $results->mp56,                                      //Себестоимость монтажа дилера (зарплата монтажников)
                                    "dealer_salary_total" => $data['n27'] * $results->mp56,                 //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                                    "stage" => 2
                                );
                                break;
                            case 3:
                            case 4:
                            case 5:
                                $nicheTitle = "Монтаж ниши с пластиком";
                                $mountPrice = $results->mp55;
                                $gm_mountPrice = $gm_mount->mp55;
                                $mounting_data[] = array(
                                    "title" => "Монтаж пластика под плинтус",                               //Название
                                    "quantity" => $data['n27'],                                             //Кол-во
                                    "gm_salary" => $gm_mount->mp57,                                         //Себестоимость монтажа ГМ (зарплата монтажников)
                                    "gm_salary_total" => $data['n27'] * $gm_mount->mp57,                    //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                                    "dealer_salary" => $results->mp57,                                      //Себестоимость монтажа дилера (зарплата монтажников)
                                    "dealer_salary_total" => $data['n27'] * $results->mp57,                 //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                                    "stage" => 3
                                );
                                break;
                        }
                        $mounting_data[] = array(
                            "title" => $nicheTitle,                                                         //Название
                            "quantity" => $data['n27'],                                                     //Кол-во
                            "gm_salary" => $gm_mountPrice,                                                  //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $data['n27'] * $gm_mountPrice,                             //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $mountPrice,                                                 //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $data['n27'] * $mountPrice,                            //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                            "stage" => 2
                        );
                        $mounting_data[] = array(
                            "title" => $cornice1,                                                           //Название
                            "quantity" => $data['n27'],                                                     //Кол-во
                            "gm_salary" => $gm_mount->mp11_3,                                               //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $data['n27'] * $gm_mount->mp11_3,                          //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp11_3,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $data['n27'] * $results->mp11_3,                       //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                            "stage" => 3
                        );
                    }
                }
                //закладная брусом
                if ($data['n17'] > 0) {
                    $mounting_data[] = array(
                        "title" => "Закладная брусом ",                                       //Название
                        "quantity" => $data['n17'],                                                        //Кол-во
                        "gm_salary" => $gm_mount->mp11,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $data['n17'] * $gm_mount->mp11,                               //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp11,                                                 //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $data['n17'] * $results->mp11,                            //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        "stage"=> 2
                    );
                }
                //пожарная сигнализация
                if ($data['n21'] > 0) {
                    $mounting_data[] = array(
                        "title" => "Пожарная сигнализация ",                                  //Название
                        "quantity" => $data['n21'],                                                        //Кол-во
                        "gm_salary" => $gm_mount->mp6,                                                     //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $data['n21'] * $gm_mount->mp6,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp6,                                                  //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $data['n21'] * $results->mp6,                             //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        "stage"=> 3
                    );
                }
                //парящий потолок
                if ($data['n30'] > 0) {
                    $mounting_data[] = array(
                        "title" => "Парящий потолок ",                                        //Название
                        "quantity" => $data['n30'],                                                        //Кол-во
                        "gm_salary" => $gm_mount->mp30,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $data['n30'] * $gm_mount->mp30,                               //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp30,                                                 //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $data['n30'] * $results->mp30,                            //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        "stage"=> 2
                    );
                }
                //разделитель
                if ($data['n20'] > 0) {
                    $mounting_data[] = array(
                        "title" => "Разделитель ",                                            //Название
                        "quantity" => $data['n20'],                                                        //Кол-во
                        "gm_salary" => $gm_mount->mp9,                                                     //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $data['n20'] * $gm_mount->mp9,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp9,                                                  //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $data['n20'] * $results->mp9,                             //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        "stage"=> 2
                    );
                }
                //Отбойник
                if ($data['n20_1'] > 0) {
                    $mounting_data[] = array(
                        "title" => "Отбойник",                                               //Название
                        "quantity" => $data['n20_1'],                                                      //Кол-во
                        "gm_salary" => $gm_mount->mp9_1,                                                   //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $data['n20_1'] * $gm_mount->mp9_1,                            //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp9_1,                                                //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $data['n20_1'] * $results->mp9_1,                         //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        "stage"=> 2
                    );
                }
                if ($data['n32'] > 0) {
                    $mounting_data[] = array(
                        "title" => "Слив воды",                                                        //Название
                        "quantity" => $data['n32'],                                                        //Кол-во
                        "gm_salary" => $gm_mount->mp27,                                                        //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $data['n32'] * $gm_mount->mp27,                                    //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp27,                                                //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $data['n32'] * $results->mp27,
                        "stage"=> 2//Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                    );
                }
                //крепление в плитку
                if ($data['n7'] > 0) {
                    $mounting_data[] = array(
                        "title" => "Крепление в плитку",                                                        //Название
                        "quantity" => $data['n7'],                                                                //Кол-во
                        "gm_salary" => $gm_mount->mp13,                                                                //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $data['n7'] * $gm_mount->mp13,                                            //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp13,                                                        //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $data['n7'] * $results->mp13,                                    //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        "stage"=> 2
                    );
                }
                //крепление в керамогранит
                if ($data['n8'] > 0) {
                    $mounting_data[] = array(
                        "title" => "Крепление в керамогранит",                                                    //Название
                        "quantity" => $data['n8'],                                                                //Кол-во
                        "gm_salary" => $gm_mount->mp14,                                                                //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $data['n8'] * $gm_mount->mp14,                                            //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp14,                                                        //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $data['n8'] * $results->mp14,                                   //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                         "stage"=> 2
                    );
                }
                //укрепление стены
                if ($data['n18'] > 0) {
                    $mounting_data[] = array(
                        "title" => "Укрепление стены",                                                    //Название
                        "quantity" => $data['n18'],                                                        //Кол-во
                        "gm_salary" => $gm_mount->mp15,                                                        //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $data['n18'] * $gm_mount->mp15,                                    //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp15,                                                //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $data['n18'] * $results->mp15,                            //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        "stage"=> 2
                    );
                }
                //сложность доступа к месту установки
                if ($data['n24'] > 0) {
                    $mounting_data[] = array(
                        "title" => "Сложность доступа",                                                    //Название
                        "quantity" => $data['n24'],                                                        //Кол-во
                        "gm_salary" => $gm_mount->mp17,                                                        //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $data['n24'] * $gm_mount->mp17,                                    //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp17,                                                //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $data['n24'] * $results->mp17,                            //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                         "stage"=> 3
                    );
                }
                if($data['n44'] > 0){
                    $mounting_data[] = array(
                        "title" => "Монтаж с пылесосом",                                                    //Название
                        "quantity" => $data['n44'],                                                        //Кол-во
                        "gm_salary" => $gm_mount->mp73,                                                        //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $data['n44'] * $gm_mount->mp73,                                    //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp73,                                                //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $data['n44'] * $results->mp73,                            //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        "stage"=> 1
                    );
                }
                //Доп монтаж
                if ($data['dop_krepezh'] > 0) {
                    $mounting_data[] = array(
                        "title" => "Дополнительный крепеж",                                                //Название
                        "quantity" => $data['dop_krepezh'],                                                //Кол-во
                        "gm_salary" => $gm_mount->mp18,                                                        //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $data['dop_krepezh'] * $gm_mount->mp18,                            //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp18,                                                //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $data['dop_krepezh'] * $results->mp18,                   //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        "stage"=> 3
                    );
                }
                //Дополнительный монтаж
                $extra_mounting = json_decode($data['extra_mounting']);
                if (!empty($extra_mounting)) {
                    foreach ($extra_mounting as $extra_mount) {
                        $mounting_data[] = array(
                            "title" => $extra_mount->title,                                                        //Название
                            "quantity" => 1,                                                                    //Кол-во
                            "gm_salary" => $extra_mount->value,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $extra_mount->value,                                            //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $extra_mount->value,                                                //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $extra_mount->value,                                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                            "stage"=> 3
                        );
                    }
                }
            }
            $margins = self::get_margin($data['project_id']);
            $gm_mounting_margin = $margins['gm_mounting_margin'];
            $dealer_mounting_margin = $margins['dealer_mounting_margin'];
            //Добавление маржи ГМ и дилера и округление
            for ($i = 0; $i < count($mounting_data); $i++) {
                $mounting_data[$i]['gm_salary_total'] = round($mounting_data[$i]['gm_salary_total'], 2); //Округление зарплаты монтажников ГМ
                $mounting_data[$i]['dealer_salary_total'] = round($mounting_data[$i]['dealer_salary_total'], 2); //Округление зарплаты монтажников дилера
                //Добавление маржи ГМ, если монтаж производит ГМ
                $mounting_data[$i]['price_with_gm_margin'] = margin($mounting_data[$i]['gm_salary'], $gm_mounting_margin);
                $mounting_data[$i]['total_with_gm_margin'] = round($mounting_data[$i]['quantity'] * $mounting_data[$i]['price_with_gm_margin'], 2);
                //Добавление маржи ГМ и дилера, если монтаж производит Дилер с помощью ГМ
                $mounting_data[$i]['price_with_gm_dealer_margin'] = double_margin($mounting_data[$i]['dealer_salary'], $gm_mounting_margin, $dealer_mounting_margin);
                $mounting_data[$i]['total_with_gm_dealer_margin'] = round($mounting_data[$i]['quantity'] * $mounting_data[$i]['price_with_gm_dealer_margin'], 2);
                //Добавление маржи дилера, если монтаж производит Дилер с помощью ГМ
                $mounting_data[$i]['price_with_dealer_margin'] = margin($mounting_data[$i]['dealer_salary'], $dealer_mounting_margin);
                $mounting_data[$i]['total_with_dealer_margin'] = round($mounting_data[$i]['quantity'] * $mounting_data[$i]['price_with_dealer_margin'], 2);
            }

            //...и монтаж дилера с помощью ГМ
            $total_gm_mounting = 0;
            $total_dealer_mounting = 0;
            $total_with_gm_margin = 0;
            $total_with_gm_dealer_margin = 0;
            $total_with_dealer_margin = 0;
            $stages = [];
            $calcMountData['id'] = $data['id'];
            foreach ($mounting_data as $mounting_item) {
                $mounting_item['gm_salary_total'] = $mounting_item['gm_salary_total'];
                $mounting_item['dealer_salary_total'] = $mounting_item['dealer_salary_total'];
                $total_gm_mounting += $mounting_item['gm_salary_total'];
                $total_dealer_mounting += $mounting_item['dealer_salary_total'];
                $total_with_gm_margin += $mounting_item['total_with_gm_margin'];
                $total_with_gm_dealer_margin += $mounting_item['total_with_gm_dealer_margin'];
                $total_with_dealer_margin += $mounting_item['total_with_dealer_margin'];
                $stages[$mounting_item['stage']] += $mounting_item['dealer_salary_total'];
            }

            $result['mounting_data'] = $mounting_data;
            $result['total_gm_mounting'] =  $total_gm_mounting + $transport;
            $result['total_dealer_mounting'] =  $total_dealer_mounting;
            $result['total_with_gm_margin'] = $total_with_gm_margin;
            $result['total_with_gm_dealer_margin'] = $total_with_gm_dealer_margin;
            $result['total_with_dealer_margin'] = $total_with_dealer_margin;
            $result['stages'] = $stages;
            if(empty($stages)){
                $stages[2] = 0;
                $stages[3] = 0;
                $stages[4] = 0;
            }
            $calcMountData['stages'] = $stages;
            $calcsMountModel = self::getModel('calcs_mount');
            $calcsMountModel->save($calcMountData);
            return $result;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }


    /* функция для расчета стоимости траноспорта
    $project_id - id проекта
    $transport_type тип транспорта 1 по городу, 2 за город, 0 отсутсвует
    $distance расстояние
    $distance_col - кол-во выездов
    */
    public static function calculate_transport($project_id,$service=null){
        try {
            $project_model = self::getModel('Project');
            $mount_model = self::getModel('mount');
            //$dealer_info_model = self::getModel('Dealer_info');
            if(!empty($project_id)){
                $project = $project_model->getData($project_id);
                $transport_type = $project->transport;
                $distance = $project->distance;
                $distance_col = $project->distance_col;
                if(!empty($project->dealer_id)){
                    $dealer = JFactory::getUser($project->dealer_id);
                    $dealer_id = $dealer->dealer_id;
                }
                else{
                    $dealer_id = 1;
                }
                $res = $mount_model->getDataAll($dealer_id);
                if(empty($res->user_id)) {
                    $res->user_id = 1;
                }

                if($service == "service"){
                    $res = $mount_model->getDataAll(1);
                    //$res->transport +=$res->transport*0.5;
                    //$res->distance +=$res->distance*0.5;
                }
                else{
                    $res = $mount_model->getDataAll(1);
                }
                //$margin = $dealer_info_model->getMargin('dealer_mounting_margin', $res->user_id);
                if($res) {
                    $res->distance = str_replace(',','.', $res->distance);
                    $res->transport = str_replace(',','.', $res->transport);
                    $distance = str_replace(',','.', $distance);
                    if($transport_type == 1) {
                        $transport_sum = round(double_margin($res->transport * $distance_col, $project->gm_mounting_margin, 30));
                        $transport_sum_1 = round(margin($res->transport * $distance_col, 0));
                        $result = array(
                            'transport' => 'Транспорт по городу',
                            'distance' => '-',
                            'distance_col'=> $distance_col,
                            'one_transport_price' =>$res->transport,
                            'client_sum' => $transport_sum,
                            'mounter_sum' => $transport_sum_1
                        );

                    }
                    elseif($transport_type == 2) {
                        if($distance<=50){
                            $transport_sum = 500 * $distance_col;
                            $transport_sum_1 = 500  * $distance_col;
                            $one_transport_price = 500;
                        }
                        else {
                            $transport_sum = ($res->distance * $distance) * $distance_col;
                            $transport_sum_1 = ($res->distance * $distance) * $distance_col;
                            $one_transport_price = ($res->distance * $distance);
                        }
                        $result = array(
                            'transport' => 'Выезд за город',
                            'distance' => $distance,
                            'distance_col'=> $distance_col,
                            'one_transport_price' =>$one_transport_price,
                            'client_sum' => $transport_sum,
                            'mounter_sum' => $transport_sum_1
                        );
                    }
                    else {
                        $transport_sum = 0;
                        $transport_sum_1 = 0;
                        $result = array(
                            'transport' => 'Без транспорта',
                            'distance' => '-',
                            'distance_col'=> '-',
                            'client_sum' => $transport_sum,
                            'mounter_sum' => $transport_sum_1
                        );
                    }
                }
               if($transport_type == 1) {
                   /*  $discount = $project_model->getDiscount($project_id);
                    $max = 0;
                    foreach ($discount as $d) if($d->discount > $max) $max = $d->discount;
                    $result['client_sum'] = $result['client_sum'] * ((100 - $max)/100); */
                }
                return $result;
            }
            else{
                throw new Exception("empty project_id!");
            }
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    /* функция генерации общего наряда на монтаж */
    public static function create_common_estimate_mounters($project_id,$calc_ids = null,$service=null){
        try {
            $sheets_dir = $_SERVER['DOCUMENT_ROOT'] . '/costsheets/';
            $project_model = self::getModel('project');
            $projects_mounts_model = self::getModel('projects_mounts');
            $project = $project_model->getData($project_id);
            $calculations_model = self::getModel('calculations');
            $calculations = $calculations_model->new_getProjectItems($project_id);
            $full = false;
            if(!empty($calc_ids)){
                foreach($calculations as $key => $calculation){
                    if(!in_array($calculation->id,$calc_ids)){
                        unset($calculations[$key]);
                    }
                }
            }
            if(!empty($project->mount_data)){
                $mount_data = json_decode(htmlspecialchars_decode($project->mount_data));
                $mount_types = $projects_mounts_model->get_mount_types();
                foreach ($mount_data as $value) {
                    $value->stage_name = $mount_types[$value->stage];
                }
            }
            $transport = self::calculate_transport($project_id,$service);
            $client_contacts_model = self::getModel('client_phones');
            $client_contacts = $client_contacts_model->getItemsByClientId($project->id_client);
            for($i=0;$i<count($client_contacts);$i++){
                $phones .= $client_contacts[$i]->phone . (($i < count($client_contacts) - 1) ? " , " : " ");
            }
            if((count($mount_data) == 1 && $mount_data[0]->stage == 1)||empty($mount_data)){
                $full = true;
            }
            if(empty($service) && !empty($project->calcs_mounting_sum)){
                $service = "service";
            }

            foreach($calculations as $calc){
                if(!empty($calc->n6)){
                    $stage_insert = (object)array(stage => 4,time => "","mounter" => "","stage_name" => "Вставка");
                    array_push($mount_data,$stage_insert);
                    break;
                }
            }

            foreach ($calculations as $calc) {
                $calc_mount = self::calculate_mount(0,$calc->id,null,$service);
                $stage_sum = [];$gm_stage_sum = [];
                if(!$full){
                    foreach ($mount_data as $stage) {
                        $s_sum = 0;$gm_s_sum = 0;
                        foreach ($calc_mount['mounting_data'] as $value) {
                           if($value['stage'] == $stage->stage){
                               $s_sum += $value['dealer_salary_total'];
                               $gm_s_sum += $value['gm_salary_total'];
                           }
                        }
                        $stage_sum[$stage->stage] = $s_sum;
                        $gm_stage_sum[$stage->stage] = $gm_s_sum;
                    }
                    $calc->mount_sum = $stage_sum;
                    $calc->gm_mount_sum = $gm_stage_sum;
                }
                else{
                    $calc->mount_sum[1] = $calc_mount['total_dealer_mounting'];
                    $calc->gm_mount_sum[1] = $calc_mount['total_gm_mounting'];
                }
            }
            $gm_html = self::createCommonSheet($project,$calculations,$mount_data,$transport,$full,"gm");
            $dealer_html = self::createCommonSheet($project,$calculations,$mount_data,$transport,$full,"dealer");
            $gm_array = [$gm_html];
            $dealer_array = [$dealer_html];
            foreach($calculations as $calc){
                if($full){
                    $gm_array[] = $_SERVER["DOCUMENT_ROOT"] . "/costsheets/" . md5($calc->id . "mount_single_gm") . ".pdf";
                    $dealer_array[] = $_SERVER["DOCUMENT_ROOT"] . "/costsheets/" . md5($calc->id . "mount_single_dealer") . ".pdf";
                }
                else{
                    foreach ($mount_data as $value) {
                        $gm_array[] = $_SERVER["DOCUMENT_ROOT"] . "/costsheets/" . md5($calc->id . "mount_stage_gm".$value->stage) . ".pdf";
                        $dealer_array[] = $_SERVER["DOCUMENT_ROOT"] . "/costsheets/" . md5($calc->id . "mount_stage_dealer".$value->stage) . ".pdf";
                    }
                }
            }
            //throw new Exception(print_r($array,true));
            $filename_gm = md5($project_id . "mount_common_gm") . ".pdf";
            $filename_dealer = md5($project_id . "mount_common_dealer") . ".pdf";

            self::save_pdf($gm_array, $sheets_dir . $filename_gm, "A4");
            self::save_pdf($dealer_array, $sheets_dir . $filename_dealer, "A4");
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    static function createCommonSheet($project,$calculations,$mount_data,$transport,$full,$type){
        try{
            $html = ' <h1>Номер договора: ' . $project->id . '</h1><br>';
            $html .= '<h2>Дата: ' . date("d.m.Y") . '</h2>';
            $calculations_model = self::getModel('calculations');
            $split_mount = [];$exist_calc_details = false;
            $html .= "<h2>Адрес: </h2>" . $project->project_info . "<br>";
            $html .= "<h2>Даты этапов и монтажные бригады </h2>";
            $html .= '<table border="0" cellspacing="0">
                            <tbody>
                                <tr>
                                    <th class="center">Этап</th>
                                    <th class="center">Дата</th>
                                    <th class="center">Бригада</th>
                                </tr>';

            //throw new Exception(print_r($mount_data,true));
            foreach ($mount_data as $value) {
                $split_mount[$value->time][] = $value;
                $brigade_names = "";
                $names = $calculations_model->FindAllMounters($value->mounter);
                for($i=0;$i<count($names);$i++){
                    $brigade_names .= $names[$i]->name . (($i < count($names) - 1) ? " , " : " ");
                }
                if(!empty($brigade_names)){
                    $mount_brigade = JFactory::getUser($value->mounter)->name."(".$brigade_names.")";
                }
                else{
                    $mount_brigade = JFactory::getUser($value->mounter)->name;
                }
                if(!empty($value->time)){
                    $stage_date = date('d.m.Y H:i',strtotime($value->time));
                }
                else{
                    $stage_date = "Дата отсутствует";
                }
                $html .="<tr>
                            <td>$value->stage_name</td>
                            <td class=\"center\"> ";
                                $html .= $stage_date;
                                $html .="</td>
                            <td class=\"center\">$mount_brigade</td>
                        </tr>";
            }
            $html .= "</tbody></table>";
            $html .= '<h2>Краткая информация: </h2>';
            $total_mount_sum = 0;
            foreach ($split_mount as $key=>$splitted){
                $sums = [];
                $sum = 0;
                if(!empty($key)){
                    $date = date('d.m.Y H:i',strtotime($key));
                }
                else{
                    $date = "Дата отсутствует";
                }
                $html.= '<b>'.$date.'</b><br>';
                $html .= '<table border="0" cellspacing="0" width="100%">
                        <tbody>
                            <tr>
                                <th>Название</th>
                                <th class="center">Площадь, м<sup>2</sup>.</th>
                                <th class="center">Периметр, м </th>';
                foreach ($splitted as $stage){
                    $html .= "<th class=\"center\">$stage->stage_name, руб.</th>";
                }
                $html .='<th class="center">Стоимость, руб.</th>';
                $html .= '</tr>';
                foreach ($calculations as $calc) {
                    //throw new Exception(print_r($calc,true));
                    if(!empty($calc->details)){
                        $exist_calc_details = true;
                    }
                    $html .= '<tr>';
                    $html .= '<td>' . $calc->calculation_title . '</td>';
                    $html .= '<td class="center">' . $calc->n4 . '</td>';
                    $html .= '<td class="center">' . $calc->n5 . '</td>';
                    $calc_sum = 0;
                    foreach ($splitted as $stage) {
                        $sum_value = ($type == "gm") ? $calc->gm_mount_sum[$stage->stage] : $calc->mount_sum[$stage->stage];
                        $html .= '<td class="center">' . $sum_value . '</td>';
                        $sums[$stage->stage] += $sum_value;
                        $calc_sum += $sum_value;
                    }
                    $html .= '<td class="center">' . $calc_sum . '</td>';
                    $html .= '</tr>';
                    $sum += $calc_sum;
                }
                $html .= '<tr><th colspan="3" class="right">Итого, руб:</th>';
                foreach ($sums as $item){
                    $html .= '<th class="center">' . $item . '</th>';
                }
                $html .= '<th class="center">' . $sum . '</th></tr>';
                $html .= '</tbody>';
                $html .= '</table>';
                $html .= '<h2>Транспортные расходы: </h2>';
                $html .= '<table border="0" cellspacing="0" width="100%">
                        <tbody>
                            <tr>
                                <th>Вид транспорта</th>
                                <th class="center">Кол-во км<sup>2</sup>.</th>
                                <th class="center">Кол-во выездов  </th><th class="center">Стоимость, руб.</th>
                            </tr>';

                if($transport['distance_col']>0) {
                    $html .= '<tr>';
                    $html .= '<td>' . $transport['transport'] . '</td>';
                    $html .= '<td class="center">' . $transport['distance'] . '</td>';
                    $html .= '<td class="center">' . 1 . '</td>';
                    $html .= '<td class="center">' . $transport['one_transport_price'] . '</td>';
                    $html .= '</tr>';



                }
                else{
                    $html .= '<tr>';
                    $html .= '<td colspan="4">Просчитано меньшее кол-во выездов</td>';
                    $html .= '</tr>';
                }
                if($transport['distance_col']>0) {
                    $one_transport = $transport['one_transport_price'];
                }
                else{
                    $one_transport = 0;
                }
                $transport['distance_col'] -= 1;
                $html .= '</tbody></table><p>&nbsp;</p>';
                $total_mount_sum+=round($one_transport + $sum);
                $html .= '<div style="text-align: right; font-weight: bold;"> Сумма: ' . round($one_transport + $sum, 2) . ' руб.</div>';
            }
            if($transport['distance_col']>0){
                $html .= '<h2>Транспортные расходы: </h2>';
                $html .= '<table border="0" cellspacing="0" width="100%">
                        <tbody>
                            <tr>
                                <th>Вид транспорта</th>
                                <th class="center">Кол-во км<sup>2</sup>.</th>
                                <th class="center">Кол-во выездов  </th><th class="center">Стоимость, руб.</th>
                            </tr>';
                $html .= '<tr>';
                $html .= '<td>' . $transport['transport']. '</td>';
                $html .= '<td class="center">' . $transport['distance'] . '</td>';
                $html .= '<td class="center">' . $transport['distance_col'] . '</td>';
                $html .= '<td class="center">' . $transport['distance_col']*$transport['one_transport_price'] . '</td>';
                $html .= '</tr>';
                $html .= '</tbody></table><p>&nbsp;</p>';
                $total_mount_sum += round( $transport['distance_col']*$transport['one_transport_price'],2);
            }

            $html .= '<br><div style="text-align: right; font-weight: bold;"> Итого: ' . $total_mount_sum . ' руб.</div>';
            if($exist_calc_details) {
                $html .= '<h2>Примечания: </h2>';
                foreach ($calculations as $calc) {
                    if (!empty($calc->details)) {
                        $html .= "$calc->calculation_title: $calc->details;<br>";
                    }
                }
            }
            return $html;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    static function create_mount_estimate_by_stage($calc_id,$mounter,$stage,$mount_date=null,$service=null,$need_mount = null){
        try{
            if(!empty($calc_id)){
                $calculation_model = self::getModel('calculation');
                $projects_mounts_model = Gm_ceilingHelpersGm_ceiling::getModel('projects_mounts');
                $calculations_model = self::getModel('calculations');
                $data = get_object_vars($calculation_model->getData($calc_id));
                $project_model = self::getModel('project');
                $project = $project_model->getData($data['project_id']);
                if(!empty($project->calcs_mounting_sum)){
                    $service = "service";
                }
                $data_mount = self::calculate_mount(0,$data['id'],null,$service);

                $names = $calculations_model->FindAllMounters($mounter);
                $mount_types = $projects_mounts_model->get_mount_types();
                for($i=0;$i<count($names);$i++){
                    $brigade_names .= $names[$i]->name . (($i < count($names) - 1) ? " , " : " ");
                }
                $client_contacts_model = self::getModel('client_phones');
                $client_contacts = $client_contacts_model->getItemsByClientId($project->id_client);
                for($i=0;$i<count($client_contacts);$i++){
                    $phones .= $client_contacts[$i]->phone . (($i < count($client_contacts) - 1) ? " , " : " ");
                }
                $full = false;
                if($stage == 1){
                    $full = true;
                }
                $html .= '<h1>Информация</h1>';
                $html .= "<b>Название: </b>" . $data['calculation_title'] . "<br>";
                if (isset($project->id)) {
                    if ($project->id) {
                        $html .= "<b>Номер договора: </b>" . $project->id . "<br>";
                    }
                }
                if (isset($project->client_id)) {
                    if ($project->client_id) {
                        $html .= "<b>Клиент: </b>" . $project->client_id . "<br>";
                    }
                }
                if (isset($phones)) {
                    $html .= "<b>Телефон: </b>".$phones . "<br>";

                }
                if (isset($project->project_info)) {
                    if ($project->project_info) {
                        $html .= "<b>Адрес: </b>" . $project->project_info . "<br>";
                    }
                }
                if (isset($mounter)) {
                    if ($mounter) {
                        $html .= "<b>Монтажная бригада: </b>" . JFactory::getUser($mounter)->name . "<br>";
                    }
                }
                if (isset($brigade_names)) {
                    $html .= "<b>Состав монтажной бригады: </b>".$brigade_names."<br>";
                }
                $html .= "<b>Этап</b>: $mount_types[$stage]<br>";
                if (isset($project->gm_calculator_note)) {
                    if ($project->gm_calculator_note) {
                        $html .= "<b>Примечание замерщика ГМ: </b>" . $project->gm_calculator_note . "<br>";
                    }
                }
                if (isset($project->dealer_calculator_note)) {
                    if ($project->dealer_calculator_note) {
                        $html .= "<b>Примечание замерщика дилера: </b>" . $project->dealer_calculator_note . "<br>";
                    }
                }
                if (isset($project->gm_chief_note)) {
                    if ($project->gm_chief_note) {
                        $html .= "<b>Примечание начальника МС ГМ: </b>" . $project->gm_chief_note . "<br>";
                    }
                }
                if (isset($project->dealer_chief_note)) {
                    if ($project->dealer_chief_note) {
                        $html .= "<b>Примечание начальника МС дилера: </b>" . $project->dealer_chief_note . "<br>";
                    }
                }
                if(isset($data['details'])){
                    $html .= "<b>Примечание : </b>" . $data['details'] . "<br>";
                }
                if (!empty($mount_date)) {
                    $jdate = new JDate($mount_date);
                    $html .= "<b>Дата монтажа: </b>" . $jdate->format('d.m.Y  H:i') . "<br>";
                }
                $mounting_data = $data_mount['mounting_data'];

                $html .= '<p>&nbsp;</p>
                        <h1>Наряд монтажной бригаде</h1>
                        <h2>Дата: ' . date("d.m.Y") . '</h2>';
                if (file_exists($_SERVER['DOCUMENT_ROOT'].'/calculation_images/' . md5("calculation_sketch" . $data['id']) . '.svg'))
                    $html .= '<img src="' . $_SERVER['DOCUMENT_ROOT'].'/calculation_images/' . md5("calculation_sketch" . $data['id']) . '.svg' . '" style="width: 100%; max-height: 800px;"/> ';
                $html .= '<table border="0" cellspacing="0" width="100%">
                        <tbody>
                            <tr>
                                <th>Наименование</th>
                                <th class="center">Цена, руб.</th>
                                <th class="center">Кол-во</th>
                                <th class="center">Стоимость, руб.</th>
                            </tr>';
                $html_gm .= $html;
                $html_dealer .= $html;
                foreach ($mounting_data as $item) {
                    if(!$full){
                        if($item["stage"] == $stage){
                            $html_gm .= '<tr>';
                            $html_gm .= '<td>' . $item['title'] . '</td>';
                            $html_gm .= '<td class="center">' . round($item['gm_salary'], 2) . '</td>';
                            $html_gm .= '<td class="center">' . $item['quantity'] . '</td>';
                            $html_gm .= '<td class="center">' . round($item['gm_salary_total'], 2) . '</td>';
                            $html_gm .= '</tr>';
                            $total_sum_gm += round($item['gm_salary_total'], 2);
                            //pdf dealer
                            $html_dealer .= '<tr>';
                            $html_dealer .= '<td>' . $item['title'] . '</td>';
                            $html_dealer .= '<td class="center">' . round($item['dealer_salary'], 2) . '</td>';
                            $html_dealer .= '<td class="center">' . $item['quantity'] . '</td>';
                            $html_dealer .= '<td class="center">' . round($item['dealer_salary_total'], 2) . '</td>';
                            $html_dealer .= '</tr>';
                            $total_sum_dealer += round($item['dealer_salary_total'], 2);
                        }
                    }
                    else{
                        $html_gm .= '<tr>';
                        $html_gm .= '<td>' . $item['title'] . '</td>';
                        $html_gm .= '<td class="center">' . round($item['gm_salary'], 2) . '</td>';
                        $html_gm .= '<td class="center">' . $item['quantity'] . '</td>';
                        $html_gm .= '<td class="center">' . round($item['gm_salary_total'], 2) . '</td>';
                        $html_gm .= '</tr>';
                        //pdf dealer
                        $html_dealer .= '<tr>';
                        $html_dealer .= '<td>' . $item['title'] . '</td>';
                        $html_dealer .= '<td class="center">' . round($item['dealer_salary'], 2) . '</td>';
                        $html_dealer .= '<td class="center">' . $item['quantity'] . '</td>';
                        $html_dealer .= '<td class="center">' . round($item['dealer_salary_total'], 2) . '</td>';
                        $html_dealer .= '</tr>';
                    }

                }
                if(!$full){
                    $html_gm .= '<tr><th colspan="3" class="right">Итого, руб:</th><th class="center">' . round($total_sum_gm, 2) . '</th></tr>';
                    $html_dealer .= '<tr><th colspan="3" class="right">Итого, руб:</th><th class="center">' . round($total_sum_dealer, 2) . '</th></tr>';
                }
                else{
                    $html_gm .= '<tr><th colspan="3" class="right">Итого, руб:</th><th class="center">' . round($data_mount['total_gm_mounting'], 2) . '</th></tr>';
                    $html_dealer .= '<tr><th colspan="3" class="right">Итого, руб:</th><th class="center">' . round($data_mount['total_dealer_mounting'], 2) . '</th></tr>';
                }

                $html_gm .= '</tbody></table><p>&nbsp;</p>';
                $html_dealer .= '</tbody></table><p>&nbsp;</p>';

                $sheets_dir = $_SERVER['DOCUMENT_ROOT'] . '/costsheets/';
                if(!$full){
                    $filename_gm = md5($calc_id."mount_stage_gm".$stage) . ".pdf";
                    $filename_dealer = md5($calc_id."mount_stage_dealer".$stage) . ".pdf";

                }
                else{
                    $filename_gm = md5($calc_id."mount_single_gm") . ".pdf";
                    $filename_dealer = md5($calc_id."mount_single_dealer") . ".pdf";
                }
                Gm_ceilingHelpersGm_ceiling::save_pdf($html_gm, $sheets_dir . $filename_gm, "A4");
                Gm_ceilingHelpersGm_ceiling::save_pdf($html_dealer, $sheets_dir . $filename_dealer, "A4");
                return 1;

            }
            else{
                throw new Exception("Empty calculation id");
            }
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /* функция для создания PDF документа с расходкой по проекту */
    public static function create_estimate_of_consumables($project_id,$need_price=1){
        try {
            $project_model = self::getModel('Project');
            $project = $project_model->getData($project_id);
            $client_model = self::getModel('Client');
            $client = $client_model->getClientById($project->id_client);
            $components_data = array();
            $calculations_model = self::getModel('calculations');
            $calculations = $calculations_model->new_getProjectItems($project_id);
            $dealer_info = JFactory::getUser($client->dealer_id);
            if($dealer_info->dealer_id == 1){
                $dealer_info_components = $dealer_info->getComponentsPrice($dealer_info->dealer_id);
            }
            else{
                $dealer_info_components = $dealer_info->getComponentsPrice();
            }
            foreach($calculations as $calc){
                $components_data [] = self::calculate_components($calc->id,null,0);
            }

            $components_model = Gm_ceilingHelpersGm_ceiling::getModel('components');
            $components_list = $components_model->getFilteredItems();
            foreach ($components_list as $i => $component) {
                $components[$component->id] = $component;
            }
            $print_data = array();


            foreach ($components_data as $component_array) {
                foreach ($component_array as $key1 => $component) {
                    if ($component['stack'] == 0) {
                        if(array_key_exists($component['id'], $print_data)){
                            $print_data[$component['id']]['self_total'] +=  $component['self_total'];
                            $print_data[$component['id']]['dealer_total'] += $component['self_dealer_total'];
                            $print_data[$component['id']]['quantity'] = self::rounding($print_data[$component['id']]['quantity'] + $component['quantity'], $components[$component['id']]->count_sale);
                        }
                        else{
                            $print_data[$component['id']] = $component;
                        }

                    }
                }
            }

            foreach ($components_data as $component_array) {
                foreach ($component_array as $key => $component) {
                    if ($component['stack'] == 1) {
                        $print_data[] = $component;
                    }
                }
            }
            $html = '<h1>Расходные материалы</h1>';
            if (isset($project_id)) {
                if ($project_id) {
                    $html .= "<b>Номер договора:  </b>" . $project_id . "<br>";
                }
            }
            $html .= '<p>&nbsp;</p>
            <h2>Дата: ' . date("d.m.Y") . '</h2>
            <table border="0" cellspacing="0" width="100%">
            <tbody><tr><th>Наименование</th><th class="center">Ед. изм.</th><th class="center">Кол-во</th>';
            if($need_price == 1){
                $html .='<th class="center">Общая стоимость</th></tr>';
            }
            $price_itog = 0;
            foreach ($print_data as $key => $item) {
                if ($item['quantity'] > 0 || $item['quantity'] > 0.0) {
                    $html .= '<tr>';
                    $html .= '<td>' . $item['title'] . '</td>';
                    $html .= '<td class="center">' . $item['unit'] . '</td>';
                    $html .= '<td class="center">' . $item['quantity'] . '</td>';
                    if($need_price == 1){
                        $html .= '<td class="center">' . round($item['self_total'], 2) . '</td>';
                    }
                    $html .= '</tr>';
                    $price_itog += $item['self_total'];
                }
            }
            if($need_price == 1){
                $html .= '<tr><th colspan="3" class="right">Итого, руб:</th><th class="center">' . round($price_itog, 2) . '</th></tr>';
            }
            $html .= '</tbody></table><p>&nbsp;</p>';
            $sheets_dir = $_SERVER['DOCUMENT_ROOT'] . '/costsheets/';
            if($need_price == 1){
                $filename = md5($project_id . "consumables") . ".pdf";
            }
            else{
                $filename = md5($project_id . "consumablesnone") . ".pdf";
            }
            Gm_ceilingHelpersGm_ceiling::save_pdf($html, $sheets_dir . $filename, "A4");
            return $html;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    /* функция генерации pdf раскроя */
    public static function create_cut_pdf($calc_id=null,$data = null){
        try{
            if(!empty($calc_id)){
                $calculation_model = self::getModel('calculation');
                $data = $calculation_model->getData($calc_id);
                $data = get_object_vars($data);
            }
            if(empty($calc_id)){
                $calc_id = $data['id'];
            }
            $project_model = self::getModel('project');
            $project = $project_model->getData($data['project_id']);
            $canvases_data = self::calculate_canvases($calc_id);
            $client_model = self::getModel('client');
            $client = $client_model->getClientById($project->id_client);
            $dealer_name = JFactory::getUser($client->dealer_id)->name;
            if(!empty($data['shrink_percent'])) {
                $cut_data = $data['cut_data'];
                $p_usadki = $data['shrink_percent'];
            }
            else{
                $array_cut = explode('||',$data['cut_data']);
                $cut_data = $array_cut[0];
                $p_usadki = $array_cut[1];
            }
            $usadka = (1-$p_usadki)*100;
            $array1 = array();
            $array2 = explode(';', $data['calc_data']);
            array_pop($array2);
            foreach($array2 as $str) {
                list($key, $value) = explode('=', $str);
                $array1[$key] = $value;
            }
            foreach($array1 as $key=>$value){
                $us_walls .= $key.'='.round($value*$p_usadki,1).';';
            }
            $mpdf = new mPDF('utf-8', 'A4','8', '', 10, 10, 7, 7, 10, 10); //'8', '', 1, 1, 5, 5, 1, 0);
            $mpdf->setAutoTopMargin='stretch';
            $mpdf->setAutoBottomMargin = 'stretch';
            //throw new Exception($data['calculation_title']);
            $header = '
                <table width="100%" style="vertical-align: middle;">
                    <tr>
                        <td width="33%"  style="text-align:left"><!--<img style="max-height:50px" src="/images/GM.png"/>--></td>
                        <td width="33%" style="text-align: center;"><h1 style="text-align:center;">'.$data['calculation_title'].'</h1></td>
                        <td width="33%" style="text-align: right;">{DATE j.m.Y}</td>
                    </tr>
                </table>';
            $mpdf->SetHTMLHeader($header);
            $html .= '<table>';
            $html .= '<tbody>';
            $html .= '<tr>';
            $html .= '<th>Договор №: </th> <td>' . $project->id . '</td>';
            $html .= '<th>Клиент:</th><td >' . $project->client_id . '</td>';
            $html .= '<th>Дилер:</th><td >' . $dealer_name . '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<th>Адрес : </th> <td colspan="5">' . $project->project_info . '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<th>Цвет: </th><td colspan="2" >' . $canvases_data["title"] . '</td>';
            $html .= '<th>Примечание:</th><td >' . $data['manager_note'] . '</td>';
            $html .= '</tr>';
            $html .= '</tbody>';
            $html .= '</table>';
            $html .= '<table>';
            $html .= '<tbody>';
            $html .= '<tr>';
            $html .= '<th>Стороны и диагонали: </th><td>' . str_replace(';', '; ', $data['calc_data']) . '</td>';
            $html .= '</tr>';
            $html .= '</tbody>';
            $html .= '</table>';
            $html .= '<table>';
            $html .= '<tbody>';
            $html .= '<tr>';
            $html .= '<th>Площадь:</th><td class="td-val">' . $data['n4'] . 'м<sup>2</sup></td>
                      <th>Обрезки:</th><td class="td-val">' . round($data['offcut_square'],2) . 'м<sup>2</sup></td>
                      <th>% усадки:</th><td class="td-val">'.$usadka.'</td> ';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<th>Периметр:</th><td class="td-val">' . $data['n5'] . 'м</td>
                      <th>Кол-во углов:</th><td class="td-val">' . $data['n9'] . '</td>
                      <th>Криволинейный участок:</th><td class="td-val">' . $data['n10'] . '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<th>Внутренний вырез:</th><td class="td-val">' . $data['n31'] . 'м</td>';
            $html .= '</tr>';
            $html .= '</tbody>';
            $html .= '</table>';
            $stylesheet = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/libraries/mpdf/gm_cut.css');
            $mpdf->WriteHTML($stylesheet, 1);
            $mpdf->WriteHTML($html, 2);
            $unusedSpaceH = $mpdf->h - $mpdf->y - $mpdf->bMargin;
            if($mpdf->y < $unusedSpaceH){
                $min_height = ($unusedSpaceH -5)*3.779528;
                $max_height_attr = " max-height:$min_height;";
            }
            else{
                $max_height_attr = "";
            }
            $html_image = '<div align="center" style="width: 100%;page-break-after:always;">';
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/calculation_images/" . md5("calculation_sketch" . $data['id']) . ".svg"))
                $html_image .= '<img src="' . $_SERVER['DOCUMENT_ROOT'] . "/calculation_images/" . md5("calculation_sketch" . $data['id']) . ".svg" . '" style="width: 100%;'.$max_height_attr.'"/>';
            $html_image .= '</div>';
            $html_image .= "<pagebreak/>";

            $mpdf->WriteHTML($stylesheet, 1);
            $mpdf->WriteHTML($html_image, 2);
            $html .= $html_image;
            $html .= '<pagebreak/>';
            $mpdf->WriteHTML($html, 2);
            $header = '
                <table width="100%" style="vertical-align: middle;">
                    <tr>
                        <td width="33%"  style="text-align:left"><!--<img style="max-height:50px" src="/images/GM.png"/>--></td>
                        <td width="33%" style="text-align: center;"><h1 style="text-align:center;">Раскрой:'.$data['calculation_title'].'</h1></td>
                        <td width="33%" style="text-align: right;">{DATE j.m.Y}</td>
                    </tr>
                </table>';
            $mpdf->SetHTMLHeader($header,'O',true);
            $html_cut .= '<table >';
            $html_cut .= '<tbody>';
            $html_cut .= '<tr>';
            $html_cut .= '<th>Договор №: </th> <td>' . $project->id . '</td>';
            $html_cut .= '<th class ="left">Клиент:</th><td >' . $project->client_id . '</td>';
            $html_cut .= '<th>Дилер:</th><td >' . $dealer_name . '</td>';
            $html_cut .= '</tr>';
            $html_cut .= '<tr>';
            $html_cut .= '<th>Адрес : </th> <td colspan="5">' . $project->project_info . '</td>';
            $html_cut .= '</tr>';
            $html_cut .= '<tr>';
            $html_cut .= '<th>Цвет: </th><td colspan="3" >' . $canvases_data["title"] . '</td>';
            $html_cut .= '<th>Примечание:</th><td >' . $data['manager_note'] . '</td>';
            $html_cut .= '</tr>';
            $html_cut .= '</tbody>';
            $html_cut .= '</table>';
            $html_cut .= '<table>';
            $html_cut .= '<tbody>';
            $html_cut .= '<tr>';
            $html_cut .= '<th>Полотна: </th><td style="font-size: 14pt">' . str_replace('|', ";<br>", $cut_data) . '</td>';
            $html_cut .= '</tr>';
            $html_cut .= '</tbody>';
            $html_cut .= '</table>';
            $html_cut .= '<table>';
            $html_cut .= '<tbody>';
            $html_cut .= '<tr>';
            $html_cut .= '<th>Стороны: </th><td>' . $us_walls. '</td>';
            $html_cut .= '</tr>';
            $html_cut .= '</tbody>';
            $html_cut .= '</table>';
            $html_cut .= '<table>';
            $html_cut .= '<tbody>';
            $html_cut .= '<tr>';
            $html_cut .=  '<th>Площадь:</th><td>' . $data['n4'] . 'м<sup>2</sup></td>
                      <th>Обрезки:</th><td style = "border-style:hidden">' . round($data['offcut_square'],2) . 'м<sup>2</sup></td>
                      <th>% усадки:</th> <td>'.$usadka.'</td>';
            $html_cut .= '</tr>';
            $html_cut .= '<tr>';
            $html_cut .= '<th>Периметр:</th><td>' . $data['n5'] . 'м</td><th>Кол-во углов:</th><td>' . $data['n9'] . '</td>
                      <th>Криволинейный участок:</th><td class="td-val">' . $data['n10'] . '</td>;';
            $html_cut .= '</tr>';
            $html_cut .= '<tr>';
            $html_cut .= '<th>Внутренний вырез:</th><td class="td-val">' . $data['n31'] . 'м</td>';
            $html_cut .= '</tr>';
            $html_cut .= '</tbody>';
            $html_cut .= '</table>';
            $mpdf->WriteHTML($stylesheet, 1);
            $mpdf->WriteHTML($html_cut, 2);
            $footer ='<hr style="color:#414099;size:4px">';
            $footer .= '<div class="left_f">';
            $footer .= "<span>Отгрузку разрешил:</span> _________ <span class='name'> ".JFactory::getUser()->name."</span><br>";
            $footer .= "<div class='podp'>Подпись</div><div class='rs'> Расшифровка</div><br>";
            $footer .= "<span>Отпустил:</span><div class='lines'> _______________  __________________</div>";
            $footer .= "<div class='podp'>Подпись</div><div class='rs'> Расшифровка</div><br>";
            $footer .= '</div>';
            $footer .= '<div class="right_f">';
            $footer .= "<span>Принял:</span><div class='lines'> _______________  __________________</div>";
            $footer .= "<div class='podp'>Подпись</div><div class='rs'> Расшифровка</div><br>";
            $footer .= '</div>';
            $footer .= '<div align="right" style="width: 100%;font-size: 12pt";font-style: italic;>';
            $footer .= '<b>Сумма:'. $data['canvases_sum'] .'р.</b>';
            $footer .= '</div>';
            $mpdf->SetHTMLFooter($footer);
            $unusedSpaceH = $mpdf->h - $mpdf->y - $mpdf->bMargin;
            if($mpdf->y < $unusedSpaceH){
                $min_height = ($unusedSpaceH -5)*3.779528;
                $max_height_attr = " max-height:$min_height;";
            }
            else{
                $max_height_attr = "";
            }
            $html = '<div align="center" style="width: 100%;">';
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/cut_images/" . md5("cut_sketch" . $data['id']) . ".svg"))
                $html .= '<img src="' . $_SERVER['DOCUMENT_ROOT'] . "/cut_images/" . md5("cut_sketch" . $data['id']) . ".svg" . '" style="width: 100%;'.$max_height_attr.'"/>';
            $html .= '</div>';

            $mpdf->WriteHTML($stylesheet, 1);
            $mpdf->WriteHTML($html, 2);
            $mpdf->WriteHTML('<pagebreak>', 2);
            $mpdf->WriteHTML($html_cut, 2);
            $mpdf->WriteHTML($html, 2);
            $filename = md5($calc_id . 'cutpdf') . '.pdf';
            $mpdf->Output($_SERVER['DOCUMENT_ROOT'] . '/costsheets/'.$filename, 'F');
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }

    }
    /*функция генерации pdf для менеджера*/
    public static function create_manager_estimate($need_price,$calc_id=null,$data = null,$canvases_data = null,$offcut_square_data = null,$guild_data = null){
        try {
            $sheets_dir = $_SERVER['DOCUMENT_ROOT'] . '/costsheets/';
            if(!empty($calc_id)){
                $calculation_model = self::getModel('calculation');
                $data = get_object_vars($calculation_model->getData($calc_id));
            }
            if(empty($calc_id)){
                $calc_id = $data['id'];
            }
            $project_model = self::getModel('project');
            $project = $project_model->getData($data['project_id']);
            if(!empty($data['n3'])) {
                if (empty($canvases_data)) {
                    $canvases_data = self::calculate_canvases($calc_id);
                }
                if (empty($offcut_square_data)) {
                    if($data['need_cuts']) {
                        $offcut_square_data = self::calculate_offcut($calc_id);
                    }
                }
            }
            if(empty($guild_data)){
                $guild_data = self::calculate_guild_jobs($calc_id);
            }
            $html = '<h1>Информация</h1>';
            $html .= "<b>Название: </b>" . $data['calculation_title'] . "<br>";
            if (isset($project->id)) {
                if ($project->id) {
                    $html .= "<b>Номер договора: </b>" . $project->id . "<br>";
                }
            }
            if (isset($project->client_id)) {
                if ($project->client_id) {
                    $html .= "<b>Клиент: </b>" . $project->client_id . "<br>";
                }
            }
            if (isset($project->project_info)) {
                if ($project->project_info) {
                    $html .= "<b>Адрес: </b>" . $project->project_info . "<br>";
                }
            }
            if (isset($project->project_mounter)&&$need_price == 1) {
                if ($project->project_mounter) {
                    $html .= "<b>Монтажная группа: </b>" . JFactory::getUser($project->project_mounter)->name . "<br>";
                }
            }
            if (isset($calculation_title)) {
                if ($calculation_title) {
                    $html .= "<b>Потолок: </b>" . $calculation_title . "<br>";
                }
            }
            $html .= '<p>&nbsp;</p>
                    <h1>Для менеджера</h1>
                    <table border="0" cellspacing="0" width="100%">
                    <tbody><tr><th>Наименование</th>';
            if($need_price == 1){
                $html .='<th class="center">Себестоимость</th>';
            }
            $html .='<th class="center">Кол-во</th>';
            if($need_price == 1){
                $html .=' <th>Итого</th>';
            }
            $html .='</tr>';
            if($canvases_data['min_self_dealer_total']){
                $canvases_data['self_dealer_total'] = $canvases_data['min_self_dealer_total'];
            }
            if ($data['n3']) {
                if ($data['color'] > 0) {
                    $color_model = Gm_ceilingHelpersGm_ceiling::getModel('color');
                    $color = $color_model->getData($data['color']);
                    $name = $canvases_data['title'] . ", цвет: " . $color->colors_title;
                } else {
                    $name = $canvases_data['title'];
                }
                $html .= '<tr>';
                $html .= '<td>' . $name . '</td>';
                if($need_price == 1){
                    $html .= '<td>' . round($canvases_data['self_dealer_price'], 2) . '</td>';
                }
                $html .= '<td class="center">' . $canvases_data['quantity'] . '</td>';
                if($need_price == 1){
                    $html .= '<td>' . $canvases_data['self_dealer_total'] . '</td>';
                }
                $html .= '</tr>';
            }
            if ($data['n3'] && $data['offcut_square'] > $data['n4']*0.5 && $data['need_cuts']) {
                $name = $offcut_square_data['title'];
                $html .= '<tr>';
                $html .= '<td>' . $name . '</td>';
                if($need_price == 1){
                    $html .= '<td >' . round($offcut_square_data['self_dealer_price'], 2) . '</td>';
                }
                $html .= '<td class="center">' . $offcut_square_data['quantity'] . '</td>';
                if($need_price == 1){
                    $html .= '<td>' . $offcut_square_data['self_dealer_total'] . '</td>';
                }
                $html .= '</tr>';
            }
            foreach ($guild_data["guild_data"] as $item) {
                $html .= '<tr>';
                $html .= '<td>' . $item['title'] . '</td>';
                if($need_price == 1){
                    $html .= '<td>' . round($item['gm_salary'], 2) . '</td>';
                }
                $html .= '<td class="center">' . $item['quantity'] . '</td>';
                if($need_price == 1){
                    $html .= '<td>' . $item['gm_salary_total'] . '</td>';
                }
                $html .= '</tr>';
            }
            if ($data['n9'] > 0) {
                $html .= '<tr>';
                $html .= '<td>Всего углов</td>';
                $html .= '<td></td>';
                $html .= '<td class="center">' . $data['n9'] . '</td>';
                $html .= '<td></td>';
                $html .= '</tr>';
            }
            if($canvases_data['min_self_dealer_total']) {
                $offcut_square_data['self_dealer_total']  = 0;
                $guild_data["total_gm_guild"] = 0;
            }
            if($need_price == 1){
                $price_itog = $canvases_data['self_dealer_total'] + $offcut_square_data['self_dealer_total'] + $guild_data["total_gm_guild"];
                $html .= '<tr><th colspan="3" class="right">Итого, руб:</th><th class="center">' . round($price_itog, 2) . '</th></tr>';
            }
            $html .= '</tbody></table><p>&nbsp;</p>';
            $html .= "<b>Длины сторон: </b>" . $data['calc_data'] . "<br>";
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/calculation_images/" . md5("calculation_sketch" . $data['id']) . ".svg"))
                $html .= '<img src="' . $_SERVER['DOCUMENT_ROOT'] . "/calculation_images/" . md5("calculation_sketch" . $data['id']) . ".svg" . '" style="width: 100%; max-height: 530px;"/> <br>';
            if($need_price == 1){
                $filename = md5($calc_id . "manager") . ".pdf";
            }
            else{
                $filename = md5($calc_id . "managernone") . ".pdf";
            }
            Gm_ceilingHelpersGm_ceiling::save_pdf($html, $sheets_dir . $filename, "A4");
            return 1;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public static function create_common_manager_estimate($project_id) {
        try {
            if (!empty($project_id))
            {
                $html = ' <h1>Номер договора: ' . $project_id . '</h1><br>';
                $calculations_model = self::getModel('calculations');
                $calculations = $calculations_model->new_getProjectItems($project_id);
                $html .= '<h2>Краткая информация по выбранным(-ому) потолкам(-у): </h2>';
                $html .= '<table border="0" cellspacing="0" width="100%">
                        <tbody>
                            <tr>
                                <th>Название</th>
                                <th class="center">Площадь, м<sup>2</sup>.</th>
                                <th class="center">Периметр, м </th>
                                <th class="center">Углы,шт </th>
                                <th class="center">Стоимость, руб.</th>';

                foreach ($calculations as $calc) {
                    if(!empty($calc->n3)) {
                        $canvases_data = self::calculate_canvases($calc->id);
                        if($calc->need_cuts) {
                            $offcut_square_data = self::calculate_offcut($calc->id);
                        }
                    }
                    if($canvases_data['min_self_dealer_total']){
                        $canvases_data['self_dealer_total'] = $canvases_data['min_self_dealer_total'];
                        $offcut_square_data['self_dealer_total'] = 0;
                    }
                    $guild_data = self::calculate_guild_jobs($calc->id);
                    $price_itog = $canvases_data['self_dealer_total'] + $offcut_square_data['self_dealer_total'] + $guild_data["total_gm_guild"];
                    $html .= '<tr>';
                    $html .= '<td>' . $calc->calculation_title . '</td>';
                    $html .= '<td class="center">' . $calc->n4 . '</td>';
                    $html .= '<td class="center">' . $calc->n5 . '</td>';
                    $html .= '<td class="center">' . $calc->n9 . '</td>';
                    $html .= '<td class="center">' . $price_itog . '</td>';
                    $html .= '</tr>';
                    $n4_sum += $calc->n4;
                    $n5_sum += $calc->n5;
                    $n9_sum += $calc->n9;
                    $sum += $price_itog;
                }
                $html .= '<tr><th class="right"><b>Итого, руб:</b></th>';
                $html .= '<th class="center"><b>' . $n4_sum . '</b></th></tr>';
                $html .= '<th class="center"><b>' . $n5_sum . '</b></th></tr>';
                $html .= '<th class="center"><b>' . $n9_sum . '</b></th></tr>';
                $html .= '<th class="center"><b>' . $sum . '</b></th></tr>';
                $html .= '</tbody></table><p>&nbsp;</p>';

                $html .= '<div style="text-align: right; font-weight: bold;"> ИТОГО: ' . round($sum, 2) . ' руб.</div>';
                $html .= '</tbody></table><p>&nbsp;</p><br>';
                $filename = $_SERVER['DOCUMENT_ROOT'] . '/costsheets/' . md5($project_id . "common_manager") . ".pdf";
                $model = self::getModel("project");
                $calculations_id = $model->getCalculationIdById($project_id);
                $array_files = [];
                array_push($array_files,$html);
                foreach ($calculations_id as $item)
                    $array_files[$item->id] = $_SERVER['DOCUMENT_ROOT'] . '/costsheets/' .md5($item->id . "manager") . ".pdf";
                self::save_pdf($array_files, $filename, "A4");
            }
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public static function create_common_cut_pdf($project_id) {
        try {
            if (!empty($project_id))
            {
                $filename = $_SERVER['DOCUMENT_ROOT'] . '/costsheets/' . md5($project_id . "common_cutpdf") . ".pdf";
                $model = self::getModel("project");
                $calculations_id = $model->getCalculationIdById($project_id);
                $array_files = [];
                foreach ($calculations_id as $item)
                    $array_files[$item->id] = $_SERVER['DOCUMENT_ROOT'] . '/costsheets/' .md5($item->id . "cutpdf") . ".pdf";
                self::save_pdf($array_files, $filename, "A4");
            }
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    //Эта функция предназначена для подготовки данных для печати PDF в момент отправки договора в монтаж
    public static function print_components($project_id, $components_data){
        try {
            $components_model = Gm_ceilingHelpersGm_ceiling::getModel('components');
            $components_list = $components_model->getFilteredItems();
            foreach ($components_list as $i => $component) {
                $components[$component->id] = $component;
            }
            $component_count = array();
            foreach ($components as $key => $value) $component_count[$key] = 0;
            $print_data = array();
            foreach ($component_count as $key => $cost) {
                $component_item = array();
                $component_item['title'] = $components[$key]->component_title;                                //Название комплектующего
                $component_item['unit'] = $components[$key]->component_unit;                                //В чем измеряется
                $component_item['self_total'] = 0;                                                            //В чем измеряется
                $component_item['id'] = $components[$key]->id;                                                //ID
                $component_item['quantity'] = 0;
                $print_data[] = $component_item;
            }
            foreach ($components_data as $component_array) {
                foreach ($component_array as $key => $component) {
                    if ($component['stack'] == 0) {
                        $new_component = $component;
                        $new_component['self_total'] = $print_data[$key]['self_total'] + $new_component['self_total'];
                        $new_component['quantity'] = $print_data[$key]['quantity'] + $new_component['quantity'];
                        $print_data[$key] = $new_component;
                    }
                }
            }
            foreach ($components_data as $component_array) {
                foreach ($component_array as $key => $component) {
                    if ($component['stack'] == 1) {
                        $print_data[] = $component;
                    }
                }
            }
            $i = 0;
            foreach ($print_data as $data) {
                if ($data['title'] == "Багет ПВХ (2,5 м)") $it_11 = $i;
                if ($data['title'] == "Багет потолочный аллюм") $it_236 = $i;
                if ($data['title'] == "Багет стеновой аллюм") $it_239 = $i;
                if ($data['title'] == "Провод ПВС 2 х 0,75 (20 м)") $it_4 = $i;
                if ($data['title'] == "Брус 40*50") $it_1 = $i;
                if ($data['title'] == "Багет для парящих пот аллюм") $it_559 = $i;
                if ($data['title'] == "Вставка для парящих потолков") $it_38 = $i;
                if ($data['title'] == "Профиль ПП 75") $it_650 = $i;
                if ($data['title'] == "Профиль ПЛ 75") $it_651 = $i;
                if ($data['title'] == "Профиль КП 2") $it_652 = $i;
                if ($data['title'] == "Профиль НП 5") $it_653 = $i;
                if ($data['title'] == "Профиль БП 40") $it_654 = $i;
                if ($data['title'] == "Профиль СП 1") $it_655 = $i;
                if ($data['title'] == "Профиль СП 2") $it_656 = $i;
                $i++;
            }
            //tyt
            //---------------------------------- ДЛЯ СКЛАДА РАСХОДКА --------------------------------------//
            $html = '<h1>Расходные материалы</h1>';
            if (isset($project_id)) {
                if ($project_id) {
                    $html .= "<b>Номер договора:  </b>" . $project_id . "<br>";
                }
            }
            $html .= '<p>&nbsp;</p>
            
            <h2>Дата: ' . date("d.m.Y") . '</h2>
            <table border="0" cellspacing="0" width="100%">
            <tbody><tr><th>Наименование</th><th class="center">Ед. изм.</th><th class="center">Кол-во</th><th class="center">Общая стоимость</th></tr>';
            //throw new Exception(implode("//", $items_11[0]->id) , 1);
            $print_data[$it_11]['quantity'] = self::rounding($print_data[$it_11]['quantity'], 2.5);
            $print_data[$it_236]['quantity'] = self::rounding($print_data[$it_236]['quantity'], 2.5);
            $print_data[$it_239]['quantity'] = self::rounding($print_data[$it_239]['quantity'], 2.5);
            $print_data[$it_559]['quantity'] = self::rounding($print_data[$it_559]['quantity'], 2.5);
            $print_data[$it_38]['quantity'] = self::rounding($print_data[$it_38]['quantity'], 0.5);
            $print_data[$it_1]['quantity'] = self::rounding($print_data[$it_1]['quantity'], 0.5);
            $print_data[$it_650]['quantity'] = self::rounding($print_data[$it_650]['quantity'], 2.5);
            $print_data[$it_651]['quantity'] = self::rounding($print_data[$it_651]['quantity'], 2.5);
            $print_data[$it_652]['quantity'] = self::rounding($print_data[$it_652]['quantity'], 2.5);
            $print_data[$it_653]['quantity'] = self::rounding($print_data[$it_653]['quantity'], 2.5);
            $print_data[$it_654]['quantity'] = self::rounding($print_data[$it_654]['quantity'], 2.5);
            $print_data[$it_655]['quantity'] = self::rounding($print_data[$it_655]['quantity'], 2.5);
            $print_data[$it_656]['quantity'] = self::rounding($print_data[$it_656]['quantity'], 2.5);
            $print_data[$it_11]['self_total'] = $print_data[$it_11]['self_price'] * $print_data[$it_11]['quantity'];
            $print_data[$it_236]['self_total'] = $print_data[$it_236]['self_price'] * $print_data[$it_236]['quantity'];
            $print_data[$it_239]['self_total'] = $print_data[$it_239]['self_price'] * $print_data[$it_239]['quantity'];
            $print_data[$it_559]['self_total'] = $print_data[$it_559]['self_price'] * $print_data[$it_559]['quantity'];
            $print_data[$it_38]['self_total'] = $print_data[$it_38]['self_price'] * $print_data[$it_38]['quantity'];
            $print_data[$it_1]['self_total'] = $print_data[$it_1]['self_price'] * $print_data[$it_1]['quantity'];
            $print_data[$it_650]['self_total'] = $print_data[$it_650]['self_price'] * $print_data[$it_650]['quantity'];
            $print_data[$it_651]['self_total'] = $print_data[$it_651]['self_price'] * $print_data[$it_651]['quantity'];
            $print_data[$it_652]['self_total'] = $print_data[$it_652]['self_price'] * $print_data[$it_652]['quantity'];
            $print_data[$it_653]['self_total'] = $print_data[$it_653]['self_price'] * $print_data[$it_653]['quantity'];
            $print_data[$it_654]['self_total'] = $print_data[$it_654]['self_price'] * $print_data[$it_654]['quantity'];
            $print_data[$it_655]['self_total'] = $print_data[$it_655]['self_price'] * $print_data[$it_655]['quantity'];
            $print_data[$it_656]['self_total'] = $print_data[$it_656]['self_price'] * $print_data[$it_656]['quantity'];
            //округляем провод
            $print_data[$it_4]['quantity'] = ceil($print_data[$it_4]['quantity']);
            $print_data[$it_4]['self_total'] = $print_data[$it_4]['self_price'] * $print_data[$it_4]['quantity'];
            $price_itog = 0;
            foreach ($print_data as $key => $item) {
                if ($item['quantity'] > 0 && $item['quantity'] > 0.0) {
                    $html .= '<tr>';
                    $html .= '<td>' . $item['title'] . '</td>';
                    $html .= '<td class="center">' . $item['unit'] . '</td>';
                    $html .= '<td class="center">' . $item['quantity'] . '</td>';
                    $html .= '<td class="center">' . round($item['self_total'], 2) . '</td>';
                    $html .= '</tr>';
                    $price_itog += $item['self_total'];
                }
            }
            //throw new Exception($item[4]['self_total'], 1);
            $html .= '<tr><th colspan="3" class="right">Итого, руб:</th><th class="center">' . round($price_itog, 2) . '</th></tr>';
            $html .= '</tbody></table><p>&nbsp;</p>';
            $sheets_dir = $_SERVER['DOCUMENT_ROOT'] . '/costsheets/';
            $filename = md5($project_id . "-8") . ".pdf";
            Gm_ceilingHelpersGm_ceiling::save_pdf($html, $sheets_dir . $filename, "A4");
            return 1;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public static function rounding($id, $value)
    {
        try {
            if (!empty($value)) {
                $count = intval($id / $value);
                if (floatval($id / $value) > $count) {
                    $count++;
                }
                $id = $count * $value;
            }
            else {
                $id = 0;
            }
            return $id;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    //Печатаем подготовленные данные в PDF
    public static function save_pdf($html, $filename, $mode, $type = null){
        try {
            if (is_file($_SERVER['DOCUMENT_ROOT'] . "/costsheets/" . $filename)) {
                unlink($_SERVER['DOCUMENT_ROOT'] . "/costsheets/" . $filename);
            }
            $mpdf = new mPDF('utf-8', $mode, '8', '', 10, 10, 7, 7, 10, 10);
            $mpdf->showImageErrors = true;
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->list_indent_first_level = 0;
            if ($type == "cut") {
                $stylesheet = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/libraries/mpdf/gm_cut.css');
            } else {
                $stylesheet = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/libraries/mpdf/gm_documents.css');
            }
            if (gettype($html) == "array") {
                $mpdf->SetImportUse();
                foreach ($html as $index => $value) {
                    if (substr($value, -4, 4) == ".pdf") {
                        if (!file_exists($value)) continue;
                        $page = $mpdf->SetSourceFile($value);
                        for ($i = 1; $i <= $page; $i++) {
                            $mpdf->AddPage("P");
                            $id = $mpdf->ImportPage($i);
                            $mpdf->UseTemplate($id);
                        }
                    } else {
                        $mpdf->AddPage("P");
                        $mpdf->WriteHTML($stylesheet, 1);
                        $mpdf->WriteHTML($value, 2);
                    }
                }
            } else {

                $mpdf->WriteHTML($stylesheet, 1);
                $mpdf->WriteHTML($html, 2);

            }

            $mpdf->Output($filename, 'F');
            return 1;
        }
        catch(Exception $e)
        {
             Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public static function getClassPDF($mode, $format, $default_font_size, $default_font, $margin_left, $margin_right, $margin_top, $margin_bottom, $margin_header, $margin_footer, $orientation)
    {
        try {
            return new mPDF($mode, $format, $default_font_size, $default_font, $margin_left, $margin_right, $margin_top, $margin_bottom, $margin_header, $margin_footer, $orientation);
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    //Уведомление разных структур
    public static function notify($data, $type){
        try {
            $mailer = JFactory::getMailer();
            $config = JFactory::getConfig();
            $sender = array(
                $config->get('mailfrom'),
                $config->get('fromname')
            );
            $mailer->setSender($sender);
            $client = Gm_ceilingHelpersGm_ceiling::getModel('clientform');
            $mounterModel = Gm_ceilingHelpersGm_ceiling::getModel('mounters');

            if ($type == 0) {
                //Уведомление о записи на замер
                $gauger = JFactory::getUser($data['project_calculator']);
                $mailer->addRecipient($gauger->email);
                $body = "Здравствуйте. На сайте произведена новая запись на замер\n\n";
                if (!empty($data['client_name'])) {
                    $body .= "Имя клиента: " . $data['client_name'] . "\n";
                }
                if (!empty($data['client_contacts'])) {
                    if (is_array($data['client_contacts'])) {
                        foreach ($data['client_contacts'] as $key => $value) {
                            $body .= "Телефон клиента: " . $value . "\n";
                        }
                    } else {
                        $body .= "Телефон клиента: " . $data['client_contacts'] . "\n";
                    }
                }
                $address = $data['project_info'];
                if (!empty($data['project_info_house'])) $address .= ", дом: " . $data['project_info_house'];
                if (!empty($data['project_info_bdq'])) $address .= ", корпус: " . $data['project_info_bdq'];
                if (!empty($data['project_apartment'])) $address .= ", квартира: " . $data['project_apartment'];
                if (!empty($data['project_info_porch'])) $address .= ", подъезд: " . $data['project_info_porch'];
                if (!empty($data['project_info_floor'])) $address .= ", этаж: " . $data['project_info_floor'];
                if (!empty($data['project_info_code'])) $address .= ", код: " . $data['project_info_code'];
                $body .= "Адрес: " . $address . "\n";
                $body .= "Дата замера: " . $data['project_calculation_date'] . "\n";

                $body .= "Чтобы перейти на сайт, щелкните здесь: http://calc.gm-vrn.ru/";
                $mailer->setSubject('Новая запись на замер');
                $mailer->setBody($body);
            } elseif ($type == 1) {
                //Уведомление о назначении договора на монтаж
                /*$user_model = self::getModel('users');
                if($data->dealer_id == 1){
                    //отправка начальнику МС ГМ
                    $users = $user_model->getUserByGroup(17);
                }
                else{
                    //отправка  начальнику МС дилера и самому дилеру
                    $users = $user_model->getUsersByGroupAndDealer(13,$data->dealer_id);
                    $dealer = JFactory::getUser($data->dealer_id);
                    array_push($users,$dealer);
                }
                foreach ($users as $user) {
                    if(!empty($user->email)) {
                        $mailer->addRecipient($user->email);
                    }
                }

                $dopinfo = $client->getInfo($data->client_id);
                $body = "Здравствуйте. Новый договор " . $data->id . " ожидает монтажа!\n\n";
                $body .= "Имя клиента: " . $dopinfo->client_name . "\n";
                $body .= "Телефон клиента: " . $dopinfo->phone . "\n";
                $body .= "Адрес: " . $data->project_info . "\n";
                if (!empty($data->project_mounting_date))
                    $body .= "Дата и время монтажа: " . $data->project_mounting_date . "\n";
                $body .= "Примечание клиента: " . $data->project_note . "\n";
                $body .= "Примечание замерщика ГМ: " . $data->gm_calculator_note . "\n";
                $body .= "Чтобы перейти на сайт, щелкните здесь: http://calc.gm-vrn.ru/";
                $mailer->setSubject('Новый договор назначен на монтаж');
                $mailer->setBody($body);*/
            } elseif ($type == 2) {
                //Уведомление об отправке договора в производство
                $user_model = self::getModel('users');
                $users = $user_model->getUserByGroup(16);
                foreach ($users as $user) {
                    if(!empty($user->email)) {
                        $mailer->addRecipient($user->email);
                    }
                }
                $dopinfo = $client->getInfo($data->id_client);
                $body = "Здравствуйте. Новый договор " . $data->id . " отправлен в производство\n\n";
                $body .= "Имя клиента: " . $data->client_id . "\n";
                $body .= "Телефон клиента: " . $dopinfo->phone . "\n";
                $body .= "Адрес: " . $data->project_info . "\n";

                if(!empty($data->dealer_calculator_note)){
                    $body .= "Примечание замерщика: " . $data->dealer_calculator_note . "\n";
                }
                if(!empty($data->gm_calculator_note)){
                    $body .= "Примечание замерщика ГМ: " . $data->gm_calculator_note . "\n";
                }
                if(!empty($data->dealer_chief_note)){
                    $body .= "Примечание начальника МС: " . $data->dealer_chief_note . "\n";
                }
                if(!empty($data->gm_chief_note)){
                    $body .= "Примечание начальника МС ГМ: " . $data->gm_chief_note . "\n";
                }
                $sum = (!empty((float)$data->new_project_sum)) ? $data->new_project_sum : $data->project_sum;
                $body .= "Сумма договора: " . $sum . "\n";
                $user = JFactory::getUser();
                $dealer = JFactory::getUser($user->dealer_id);
                $body .= "Дилер: " . $dealer->name . "\n";
                $body .= "Чтобы перейти на сайт, щелкните здесь: http://calc.gm-vrn.ru/";
                $mailer->setSubject('Новый договор в производстве');
                $mailer->setBody($body);
            } elseif ($type == 3) {
                //Уведомление о выполнении договора
                $user_model = self::getModel('users');
                if($data->dealer_id == 1){
                    //отправка менеджерам ГМ
                    $users = $user_model->getUserByGroup(16);
                }
                else{
                    //отправка менеджерам дилера и самому дилеру
                    $users = $user_model->getUsersByGroupAndDealer(13,$data->dealer_id);
                    $dealer = JFactory::getUser($data->dealer_id);
                    array_push($users,$dealer);
                }
                foreach ($users as $user) {
                    if(!empty($user->email)) {
                        $mailer->addRecipient($user->email);
                    }
                }
                $dopinfo = $client->getInfo($data->client_id);
                $body = "Здравствуйте.  Договор № " . $data->id . " выполнен \n\n";
                $sum = (!empty((float)$data->new_project_sum)) ? $data->new_project_sum : $data->projecct_sum;
                $body .= "Сумма договора: " . $sum . "\n";
                $body .= "Имя клиента: " . $dopinfo->client_name . "\n";
                $body .= "Телефон клиента: " . $dopinfo->phone . "\n";
                $body .= "Адрес: " . $data->project_info . "\n";
                if(!empty($data->dealer_calculator_note)){
                    $body .= "Примечание замерщика: " . $data->dealer_calculator_note . "\n";
                }
                if(!empty($data->gm_calculator_note)){
                    $body .= "Примечание замерщика ГМ: " . $data->gm_calculator_note . "\n";
                }
                if(!empty($data->dealer_chief_note)){
                    $body .= "Примечание начальника МС: " . $data->dealer_chief_note . "\n";
                }
                if(!empty($data->gm_chief_note)){
                    $body .= "Примечание начальника МС ГМ: " . $data->gm_chief_note . "\n";
                }
                if(!empty($data->mounter_note)){
                    $body .= "Примечание монтажной бригады: " . $data->mounter_note . "\n";
                }
                if(!empty($data->gm_mounter_note)){
                    $body .= "Примечание монтажной бригады ГМ: " . $data->gm_mounter_note . "\n";
                }
                $body .= "Чтобы перейти на сайт, щелкните здесь: http://calc.gm-vrn.ru/";
                $mailer->setSubject('Договор выполнен');
                $mailer->setBody($body);
            } elseif ($type == 4) {
                //Уведомление об отправке договора в отказы
                $user_model = self::getModel('users');
                if($data->dealer_id == 1){
                    //отправка менеджерам ГМ
                    $users = $user_model->getUserByGroup(16);
                }
                else{
                    //отправка менеджерам дилера и самому дилеру
                    $users = $user_model->getUsersByGroupAndDealer(13,$data->dealer_id);
                    $dealer = JFactory::getUser($data->dealer_id);
                    array_push($users,$dealer);
                }
                foreach ($users as $user) {
                    if(!empty($user->email)) {
                        $mailer->addRecipient($user->email);
                    }
                }
                $notes = self::getProjectNotes($data->id,3);
                foreach($notes as $note){
                    $noteText .= $note->description.$note->value.";\n\n";
                }
                $dopinfo = $client->getInfo($data->id_client);
                $body = "Здравствуйте. Проект " . $data->id . " перемещен в отказы.\n\n";
                $body .= "Имя клиента: " . $data->client_id . "\n";
                $body .= "Телефон клиента: " . $dopinfo->phone . "\n";
                $body .= "Адрес: " . $data->project_info . "\n";
                $body .= "Причина отказа: " . $noteText. "\n";
                $sum = (!empty((float)$data->new_project_sum)) ? $data->new_project_sum : $data->project_sum;
                $body .= "Сумма проекта:" .$sum ."\n";
                $body .= "Чтобы перейти на сайт, щелкните здесь: http://calc.gm-vrn.ru/";
                $mailer->setSubject('Договор перемещен в отказы');
                $mailer->setBody($body);
            } elseif ($type == 5) {
                $dopinfo = $client->getInfo($data->client_id);
                $body = "Здравствуйте. Необходимо перезвонить:\n\n";
                $body .= "Имя клиента: " . $dopinfo->client_name . "\n";
                $body .= "Дата и время: " . $data->date_time . "\n";
                $body .= "Комментарий: " . $data->comment . "\n";
                $body .= "Имя менеджера: " . $data->manager_name . "\n";
                $body .= "Чтобы перейти на сайт, щелкните здесь: http://calc.gm-vrn.ru/";
                $mailer->setSubject('Перезвонить клиенту!');
                $mailer->setBody($body);
                $mailer->addRecipient($data->email);
            } elseif ($type == 6) {
                //Уведомление об отправке договора в производство и отказы
                $user_model = self::getModel('users');
                $users = $user_model->getUserByGroup(16);
                foreach ($users as $user) {
                    if(!empty($user->email)) {
                        $mailer->addRecipient($user->email);
                    }
                }
                $dopinfo = $client->getInfo($data->client_id);
                $body = "Здравствуйте. Новый договор " . $data->id . " отправлен в производство, несколько потолков перемещены в отказы под номером " . $data->refuse_id . "!\n\n";
                $body .= "Имя клиента: " . $data->client_id . "\n";
                $body .= "Телефон клиента: " . $dopinfo->phone . "\n";
                $body .= "Адрес: " . $data->project_info . "\n";

                if(!empty($data->dealer_calculator_note)){
                    $body .= "Примечание замерщика: " . $data->dealer_calculator_note . "\n";
                }
                if(!empty($data->gm_calculator_note)){
                    $body .= "Примечание замерщика ГМ: " . $data->gm_calculator_note . "\n";
                }
                if(!empty($data->dealer_chief_note)){
                    $body .= "Примечание начальника МС: " . $data->dealer_chief_note . "\n";
                }
                if(!empty($data->gm_chief_note)){
                    $body .= "Примечание начальника МС ГМ: " . $data->gm_chief_note . "\n";
                }
                $sum = (!empty((float)$data->new_project_sum)) ? $data->new_project_sum : $data->project_sum;
                $body .= "Сумма договора: " . $sum . "\n";
                $user = JFactory::getUser();
                $dealer = JFactory::getUser($user->dealer_id);
                $body .= "Дилер: " . $dealer->name . "\n";
                $body .= "Чтобы перейти на сайт, щелкните здесь: http://calc.gm-vrn.ru/";
                $mailer->setSubject('Новый договор в производстве');
                $mailer->setBody($body);
            } elseif ($type == 7) {
                //Уведомление о назначении договора на монтаж нужной бригаде
                if ($data->project_mounter) $mounters = $mounterModel->getEmailMount($data->project_mounter);
                $dopinfo = $client->getInfo($data->id_client);
                $body = "Здравствуйте! Новый договор " . $data->id . " ожидает монтажа!\n\n";
                $body .= "Имя клиента: " . $dopinfo->client_name . "\n";
                $body .= "Телефон клиента: " . $dopinfo->phone . "\n";
                $body .= "Адрес: " . $data->project_info . "\n";
                $body .= "Периметр: " . $data->perimeter . "\n";
                $body .= "З/п: " . $data->salary . "\n";
                $jdate = new JDate(JFactory::getDate($data->project_mounting_date));
                if ($data->project_mounting_date != "0000-00-00 00:00:00")
                    $body .= "Дата и время монтажа: " . $jdate->format('d.m.Y H:i') . "\n";
                $body .= "Примечание замерщика ГМ: " . $data->gm_calculator_note . "\n";
                $body .= "Чтобы перейти на сайт, щелкните здесь: http://calc.gm-vrn.ru/";
                $mailer->setSubject('Новый договор назначен на монтаж');
                $mailer->setBody($body);
                if(!empty($data->mount_data)){
                    $mount_data = json_decode(htmlspecialchars_decode($data->mount_data));
                    foreach ($mount_data as $key => $value) {
                        $email = JFactory::getUser($value->mounter)->email;
                        $mailer->addRecipient($email);
                    }

                }
                else{
                    $mailer->addRecipient($mounters->email);
                }
            } elseif ($type == 8) {
                //Уведомление о изменении времени на монтаж нужной бригаде
                if ($data->project_mounter) $mounters = $mounterModel->getEmailMount($data->project_mounter);
                $dopinfo = $client->getInfo($data->id_client);
                $body = "Здравствуйте, " . $mounters->name . ". У договора " . $data->id . " изменилась дата(время) монтажа!\n\n";
                $body .= "Имя клиента: " . $dopinfo->client_name . "\n";
                $body .= "Телефон клиента: " . $dopinfo->phone . "\n";
                $body .= "Адрес: " . $data->project_info . "\n";
                $jdate1 = new JDate(JFactory::getDate($data->old_date));
                if ($data->old_date != "0000-00-00 00:00:00")
                    $body .= "Старая дата и время монтажа: " . $jdate1->format('d.m.Y H:i') . "\n";
                $jdate = new JDate(JFactory::getDate($data->project_mounting_date));
                if ($data->project_mounting_date != "0000-00-00 00:00:00")
                    $body .= "Новая дата и время монтажа: " . $jdate->format('d.m.Y H:i') . "\n";
                $body .= "Примечание замерщика ГМ: " . $data->gm_calculator_note . "\n";
                $body .= "Чтобы перейти на сайт, щелкните здесь: http://calc.gm-vrn.ru/";
                $mailer->setSubject('Изменена дата(время) монтажа');
                $mailer->setBody($body);
                $mailer->addRecipient($mounters->email);
            } elseif ($type == 9) {
                //Уведомление о изменении бригады
                if ($data->project_mounter) $mounters = $mounterModel->getEmailMount($data->old_mounter);
                $dopinfo = $client->getInfo($data->id_client);
                $jdate1 = new JDate(JFactory::getDate($data->old_date));
                $body = "Здравствуйте, " . $mounters->name . ". Монтаж договора " . $data->id . " на время:  " . $jdate1->format('d.m.Y H:i') . "  был отменен !\n\n";
                $body .= "Имя клиента: " . $dopinfo->client_name . "\n";
                $body .= "Телефон клиента: " . $dopinfo->phone . "\n";

                $body .= "Чтобы перейти на сайт, щелкните здесь: http://calc.gm-vrn.ru/";
                $mailer->setSubject('Монтаж отменен');
                $mailer->setBody($body);
                $mailer->addRecipient($mounters->email);
            } elseif ($type == 10) {
                // уведомление о изменении даты замера
                $user = JFactory::getUser($data->project_calculator);
                $dopinfo = $client->getInfo($data->id_client);
                $body = "Здравствуйте, " . $user->name . ". У договора " . $data->id . " изменилась дата(время) замера!\n\n";
                $body .= "Имя клиента: " . $dopinfo->client_name . "\n";
                $body .= "Телефон клиента: " . $dopinfo->phone . "\n";
                $body .= "Адрес: " . $data->project_info . "\n";
                $jdate1 = new JDate(JFactory::getDate($data->old_date_gauger));
                if ($data->old_date_gauger != "0000-00-00 00:00:00") {
                    $body .= "Старая дата и время замера: " . $jdate1->format('d.m.Y H:i') . "\n";
                }
                $jdate = new JDate(JFactory::getDate($data->project_calculation_date));
                if ($data->project_calculation_date != "0000-00-00 00:00:00") {
                    $body .= "Новая дата и время замера: " . $jdate->format('d.m.Y H:i') . "\n";
                }
                $body .= "Примечание менеджера ГМ: " . $data->gm_manager_note . "\n";
                $body .= "Чтобы перейти на сайт, щелкните здесь: http://calc.gm-vrn.ru/";
                $mailer->setSubject('Изменена дата(время) замера');
                $mailer->setBody($body);
                $mailer->addRecipient($user->email);
            } elseif ($type == 11) {
                // уведомление о изменнеии замерщика
                $user = JFactory::getUser($data->project_calculator);
                $dopinfo = $client->getInfo($data->id_client);
                $jdate1 = new JDate(JFactory::getDate($data->old_date_gauger));
                $body = "Здравствуйте, " . $user->name . ". замер договора " . $data->id . " на время:  " . $jdate1->format('d.m.Y H:i') . "  был отменен !\n\n";
                $body .= "Имя клиента: " . $dopinfo->client_name . "\n";
                $body .= "Телефон клиента: " . $dopinfo->phone . "\n";
                $body .= "Чтобы перейти на сайт, щелкните здесь: http://calc.gm-vrn.ru/";
                $mailer->setSubject('Замер отменен');
                $mailer->setBody($body);
                $mailer->addRecipient($user->email);
            } elseif ($type == 12) {
                // уведомление для менеджеров о изменении даты монтажа от дилеров
                $user = JFactory::getUser($data->read_by_manager);
                $dopinfo = $client->getInfo($data->id_client);

                $body = "Здравствуйте, " . $user->name . ". У договора (дилера) " . $data->id . " изменилась дата(время) монтажа!\n\n";
                $body .= "Имя клиента: " . $dopinfo->client_name . "\n";
                $body .= "Телефон клиента: " . $dopinfo->phone . "\n";
                $body .= "Адрес: " . $data->project_info . "\n";
                $jdate1 = new JDate(JFactory::getDate($data->old_date));
                if ($data->old_date != "0000-00-00 00:00:00") {
                $body .= "Старая дата и время монтажа: " . $jdate1->format('d.m.Y H:i') . "\n";
                }
                $jdate = new JDate(JFactory::getDate($data->project_calculation_date));
                if ($data->project_calculation_date != "0000-00-00 00:00:00") {
                    $body .= "Новая дата и время монтажа: " . $jdate->format('d.m.Y H:i') . "\n";
                }
                $body .= "Чтобы перейти на сайт, щелкните здесь: http://calc.gm-vrn.ru/";
                $mailer->setSubject('Изменена дата(время) монтажа дилера');
                $mailer->setBody($body);
                $mailer->addRecipient($user->email);
            } elseif ($type == 13) {
            // уведомление дилера, что потолок запушен в производство
            $dopinfo = $client->getInfo($data->client_id);
            $dealer = JFactory::getUser($dopinfo->dealer_id);
            $body = "Здравствуйте, " . $dealer->name . ". Договор № " . $data->id . " запущен в производство\n\n";
            $body .= "Имя клиента: " . $dopinfo->client_name . "\n";
            $body .= "Телефон клиента: " . $dopinfo->phone . "\n";
            $body .= "Адрес: " . $data->project_info . "\n";
            $body .= "Чтобы перейти на сайт, щелкните здесь: http://calc.gm-vrn.ru/";
            $mailer->setSubject('Договор запущен в производство');
            $mailer->setBody($body);
            $mailer->addRecipient($dealer->email);
            }
            elseif($type == 14){
                $user_model = self::getModel('users');
                $users = $user_model->getUserByGroup(17);
                $client_model = self::getModel('client');
                $client = $client_model->getClientById($data->client_id);
                $dealer = JFactory::getUser($client->dealer_id);
                foreach ($users as $user) {
                    $mailer->addRecipient($user->email);
                }
                $body = "Здравствуйте. Дилер выбрал монтажную службу ГМ в проекте №" . $data->project_id . "\n\n";
                $body .= "Имя дилера: " . $dealer->name . "\n";
                $body .= "Телефон дилера: " . $dealer->username . "\n";
                $body .= "Желаемые дата и время монтажа: \n";
                foreach ($data->mount as $value) {
                    $body .=$value->stage_name." : ". $value->time . "\n";
                }
                $body .= "Чтобы перейти на сайт, щелкните здесь: http://calc.gm-vrn.ru/";
                $mailer->setSubject('Дилер заказал монтаж');
                $mailer->setBody($body);
            }
            elseif($type == 15){
                $dealer = JFactory::getUser($data->dealer_id);
                $mailer->addRecipient($dealer->email);
                $body = "Здравствуйте. Начальник монтажной службы утвердил дату монтажа в проекте №" . $data->project_id . "\n\n";
                $body .= "Дата и время монтажа: \n";
                foreach ($data->mount as $value) {
                    $body .=$value->stage_name." : ". $value->time . "\n";
                }
                $body .= "Чтобы перейти на сайт, щелкните здесь: http://calc.gm-vrn.ru/";
                $mailer->setSubject('Начальник монтажной службы утвердил монтаж');
                $mailer->setBody($body);
            }
            elseif($type == 16){
                $user_model = self::getModel('users');
                $users = $user_model->getUserByGroup(16);
                foreach ($users as $user) {
                    if(!empty($user->email)) {
                        $mailer->addRecipient($user->email);
                    }
                }
                $body = "Здравствуйте. В проекте №" . $data['id']. " изменилась дата монтажа.\n";
                $body .= "Старая данные о монтаже: \n";
                foreach ($data['old_data'] as $stage) {
                    $body.= "Этап: $stage->name \n";
                    $time = strtotime($stage->time);
                    $formatted = date("d.m.Y H:i:s", $time);
                    $body.= "Дата: $formatted\n";
                }
                $body .= "Новые данные о монтаже: \n";
                foreach ($data['new_data'] as $stage) {
                    $body.= "Этап: $stage->name \n";
                    $time = strtotime($stage->time);
                    $formatted = date("d.m.Y H:i:s", $time);
                    $body.= "Дата: $formatted\n";
                }

                $body .= "Чтобы перейти на сайт, щелкните здесь: http://calc.gm-vrn.ru/";
                $mailer->setSubject('Изменена дата монтажа');
                $mailer->setBody($body);
            }
            $send = $mailer->Send();
            return 1;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public static function push($id, $status_id){
        try {
            $mailer = JFactory::getMailer();
            $em = '';
                //'popowa.alinochka@gmail.co';
                ////'kostikkuzmenko@mail.ru';
            $config = JFactory::getConfig();
            $sender = array(
                $config->get('mailfrom'),
                $config->get('fromname')
            );
            $projects_model = Gm_ceilingHelpersGm_ceiling::getModel('projects');
            $status_name = $projects_model->getFilteredStatus($status_id);
            $mailer->setSender($sender);
            $body = "Здравствуйте. Проекту № " . $id . " назначен новый статус: " . $status_name->title . " \n\n";
            $body .= "Чтобы перейти на сайт, щелкните здесь: http://calc.gm-vrn.ru/";
            $mailer->setSubject('Новый статус!');
            $mailer->setBody($body);
            $mailer->addRecipient($em);
            $send = $mailer->Send();
            return 1;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public static function saverclient($data, $id){
        try {
            $html = file_get_contents("http://" . $_SERVER['SERVER_NAME'] . "/components/com_gm_ceiling/views/mail/saverclient.html");
            $mailer = JFactory::getMailer();
            $em = $data['send_email'];
            $rek = $data['rek'];
            $config = JFactory::getConfig();
            $sender = array(
                $config->get('mailfrom'),
                $config->get('fromname')
            );
            $api = Gm_ceilingHelpersGm_ceiling::getModel('api_phones');
            $api_phone = $api->getDataById($rek);
            //$html = str_replace("#client_id",$clients, $html);
            $html = str_replace("#id", $id, $html);
            $html = str_replace("#phone", $api_phone->number, $html);
            $html = str_replace("#price", $data['new_total'], $html);
            $html = str_replace("#discount", $data['new_total_discount_dop'], $html);
            $html = str_replace("#link", $_SERVER['SERVER_NAME'], $html);
            $mailer->setSender($sender);
            $mailer->setSubject('Скидка может быть еще больше!');
            $mailer->isHtml(true);
            $mailer->Encoding = 'base64';
            $mailer->setBody($html);
            $mailer->addRecipient($em);
            $send = $mailer->Send();
            return 1;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    /*  функция декодирования дополнительных комплектующих, монтажных работ и пр. */
    public static function decode_extra($extra){
        try {
            $extra_array = json_decode($extra);
            $result_array = array();
            if (count($extra_array) > 0) {
                foreach ($extra_array as $item) {
                    if (isset($item->title) && isset($item->value)) {
                        $result_array[] = array(
                            'title' => $item->title,
                            'value' => $item->value
                        );
                    }
                }
            }
            return $result_array;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public static function decode_stock($components){
        try {
            $comp_stock_array = json_decode($components);
            $result_array = array();
            if (count($comp_stock_array) > 0) {
                $model = Gm_ceilingHelpersGm_ceiling::getModel('components');
                foreach ($comp_stock_array as $item) {
                    if (isset($item->title) && isset($item->value)) {
                        $title = $model->getComponents(["select"=>["FullName"=>"CONCAT(components.title, ' ', options.title)"], "where" => ["=" => ["options.id" => $item->title]]]);
                        $result_array[] = array(
                            'title' => $title[0]->FullName,
                            'value' => $item->value,
                            'id' => $item->title
                        );
                    }
                }
            }
            return json_encode($result_array);
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    // рисует календарь
    public static function DrawCalendarTar($id, $month, $year, $flag){
        try {
            // $id - айдишник бригады или менеджера или замерщика, $month - месяц, $year - год
            $ArMonths = ['', 'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
            $ArDays = ['ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ', 'ВС'];
            // начало таблицы
            $table = "";
            $table .= '<table class="calendar-table" cellspacing="0" colls="7">';
            // заголовок календаря
            $table .= '<tr>';
            $table .= '<td><button class="btn btn-primary btn-sm" id="prev"><i class="fa fa-arrow-left" aria-hidden="true"></i></button></td>';
            $table .= '<td colspan="5" class="caption-month"><p>' . $ArMonths[$month] . ' ' . $year . 'г.</p></td>';
            $table .='<td><button class="btn btn-primary btn-sm" id="next"><i class="fa fa-arrow-right" aria-hidden="true"></i></button></td>';
            $table .='</tr>';
            // создание и заполнение ячеек под дни
            $table .= '<tr>';
            for ($d = 0; $d < 7; $d++) {
                $table .= '<td class="weekday">' . $ArDays[$d] . '</td>';
            }
            $table .= '</tr>';
            // первый день недели текущего месяца
            $first_day_of_week = date("w", mktime(0, 0, 0, $month, 1, $year));
            // приводим к 1-7 = пн-вс
            if ($first_day_of_week == 0) {
                $first_day_of_week = 7;
            }
            // узнаем количество дней в текущем месяце
            $current_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            // узнаем количество дней дней в предыдущем месяце
            if ($month == 1) {
                $prev_month = 12;
            } else {
                $prev_month = $month - 1;
            }
            if ($prev_month == 11) {
                $prev_year = $year - 1;
            } else {
                $prev_year = $year;
            }
            $prev_days = cal_days_in_month(CAL_GREGORIAN, $prev_month, $prev_year);
            // заполнение дней недели
            for ($j = 1, $k = 0; $j <= 42; $j++) {
                // если первый день недели текущего месяца больше j, то заполняем предыдущим
                if ($j < $first_day_of_week) {
                    $table .= '<td class="other-month">' . ($prev_days - $first_day_of_week + $j + 1) . '</td>';
                } // числа после последнего дня текущего месяца
                else if ($j >= $first_day_of_week + $current_days) {
                    $k = $k + 1;
                    $table .= '<td class="other-month">' . $k . '</td>';
                } // вывод текущего месяца
                else {
                    // вывод свободных замерщиков
                    if ($flag[0] == 3) {
                        $model = self::getModel('calculations');
                        if ($flag[1] == 1) {
                            $AllGauger = $model->FindAllGauger($flag[1], 22);
                        } else {
                            $AllGauger = $model->FindAllGauger($flag[1], 21);
                            if ($AllGauger == null) {
                                $AllGauger = [1];
                            }
                        }
                        if (strlen($month) == 1) {
                            $monthfull = "0" . $month;
                        } else {
                            $monthfull = $month;
                        }
                        $date1 = $year . "-" . $monthfull . "-01";
                        $date2 = $year . "-" . $monthfull . "-" . $current_days;
                        $BusyGauger = $model->FindBusyGauger2($date1, $date2, $flag[1]);
                        $DayTimeGauger = [];
                        for ($w = 1; $w <= $current_days; $w++) {
                            if (strlen($w) == 1) {
                                $e = "0" . strval($w);
                            } else {
                                $e = $w;
                            }
                            foreach ($BusyGauger as $value) {
                                if (substr($value->project_calculation_date, 0, 10) == $year . "-" . $monthfull . "-" . $e) {
                                    $DayTimeGauger[$w] += 1;
                                }
                            }
                        }
                        if (count($DayTimeGauger[$j - $first_day_of_week + 1]) == 0) {
                            $table .= '<td class="current-month" id="current-monthD' . ($j - $first_day_of_week + 1) . 'DM' . $month . 'MY' . $year . 'YI' . $id . 'IC0C">' . ($j - $first_day_of_week + 1) . '</td>';
                        } else if (($DayTimeGauger[$j - $first_day_of_week + 1] * Count($AllGauger)) == (Count($AllGauger) * 12)) {
                            $table .= '<td class="full-day" id="current-monthD' . ($j - $first_day_of_week + 1) . 'DM' . $month . 'MY' . $year . 'YI' . $id . 'IC0C">' . ($j - $first_day_of_week + 1) . '</td>';
                        } else {
                            $table .= '<td class="not-full-day" id="current-monthD' . ($j - $first_day_of_week + 1) . 'DM' . $month . 'MY' . $year . 'YI' . $id . 'IC0C">' . ($j - $first_day_of_week + 1) . '</td>';
                        }
                    }
                    // вывод свободных замерщиков (по одному)
                    if ($flag[0] == 4) {
                        $model = self::getModel('gaugers');
                        //$gaugers_id = $model->getData($flag[1]);
                        if (strlen($month) == 1) {
                            $monthfull = "0" . $month;
                        } else {
                            $monthfull = $month;
                        }
                        //$masID = [];
                        //if (!empty($gaugers_id)) {
                            //foreach ($gaugers_id as $value) {
                                //array_push($masID, $value->id);
                            //}
                        //} else {
                            //$masID = [$id];
                        //}
                        $date1 = $year . "-" . $monthfull . "-01";
                        $date2 = $year . "-" . $monthfull . "-" . $current_days;
                        $AllGaugingOfGaugers = $model->GetAllGaugingOfGaugers($id, $date1, $date2);//masID
                        $Dates = [];
                        for ($y = 1; $y <= $current_days; $y++) {
                            if (strlen($y) == 1) {
                                $u = "0" . strval($y);
                            } else {
                                $u = $y;
                            }
                            foreach ($AllGaugingOfGaugers as $value) {
                                //if ($value->project_calculator == $id) {
                                    if (substr($value->project_calculation_date, 0, 10) == $year . "-" . $monthfull . "-" . $u) {
                                        $Dates[$y] += 1;
                                    }
                                //}
                            }
                        }
                        // выходные дни
                        $statusDayOff = "";
                        $AllDayOff = $model->GetAllDayOff($id, $date1, $date2);
                        if (!empty($AllDayOff)) {
                            foreach ($AllDayOff as $value) {
                                if (substr($value->date_from, 8, 1) == "0") {
                                    $perem1 = substr($value->date_from, 9, 1);
                                } else {
                                    $perem1 = substr($value->date_from, 8, 2);
                                }
                                $statusDayOff[$perem1] = "DayOff";
                            }
                        }
                        if (count($Dates[$j - $first_day_of_week + 1]) == 0) {
                            if (isset($statusDayOff[$j - $first_day_of_week + 1])) {
                                $table .= '<td class="day-off" id="current-monthD'.($j - $first_day_of_week + 1).'DM'.$month.'MY'.$year.'YI'.$id.'I">'.($j - $first_day_of_week + 1).'</td>';
                            } else {
                                $table .= '<td class="current-month" id="current-monthD'.($j - $first_day_of_week + 1).'DM'.$month.'MY'.$year.'YI'.$id.'I">'.($j - $first_day_of_week + 1).'</td>';
                            }
                        } else if (count($Dates[$j - $first_day_of_week + 1]) == 12) {
                            $table .= '<td class="full-day" id="current-monthD' . ($j - $first_day_of_week + 1) . 'DM' . $month . 'MY' . $year . 'YI' . $id . 'I">' . ($j - $first_day_of_week + 1) . '</td>';
                        } else {
                            $table .= '<td class="not-full-day" id="current-monthD' . ($j - $first_day_of_week + 1) . 'DM' . $month . 'MY' . $year . 'YI' . $id . 'I">' . ($j - $first_day_of_week + 1) . '</td>';
                        }
                    }
                    // вывод свободных монтажников (всех)
                    if ($flag[0] == 2) {
                        $model = self::getModel('calculations');
                        $Allbrigades = $model->FindAllbrigades($flag[1]);
                        if (strlen($month) == 1) {
                            $monthfull = "0" . $month;
                        } else {
                            $monthfull = $month;
                        }
                        $date1 = $year . "-" . $monthfull . "-01";
                        $date2 = $year . "-" . $monthfull . "-" . $current_days;
                        $BusyMounters = $model->FindBusyMounters2($date1, $date2, $flag[1]);
                        $DayMounters = [];
                        for ($i = 1; $i <= $current_days; $i++) {
                            if (strlen($i) == 1) {
                                $q = "0" . strval($i);
                            } else {
                                $q = $i;
                            }
                            foreach ($BusyMounters as $value) {
                                if (substr($value->project_mounting_date, 0, 10) == $year . "-" . $monthfull . "-" . $q) {
                                    $DayMounters[$i] += 1;
                                }
                            }
                        }
                        if (count($DayMounters[$j - $first_day_of_week + 1]) == 0) {
                            $table .= '<td class="current-month" id="current-monthD' . ($j - $first_day_of_week + 1) . 'DM' . $month . 'MY' . $year . 'YI' . $id . 'IC1C">' . ($j - $first_day_of_week + 1) . '</td>';
                        } /*else if ($DayMounters[$j - $first_day_of_week + 1] == Count($Allbrigades)) {
                            $table .= '<td class="full-day" id="current-monthD'.($j - $first_day_of_week + 1).'DM'.$month.'MY'.$year.'YI'.$id.'I">'.($j - $first_day_of_week + 1).'</td>';                            
                        }*/ else {
                            $table .= '<td class="not-full-day" id="current-monthD' . ($j - $first_day_of_week + 1) . 'DM' . $month . 'MY' . $year . 'YI' . $id . 'IC1C">' . ($j - $first_day_of_week + 1) . '</td>';
                        }
                    }
                    // для вывода монтажника (по одному)
                    if ($flag[0] == 1) {
                        $model = self::getModel('teams');
                        $brigade_id = $model->getDatas($flag[1]);
                        if (strlen($month) == 1) {
                            $monthfull = "0" . $month;
                        } else {
                            $monthfull = $month;
                        }
                        $masID = [];
                        if (!empty($brigade_id)) {
                            foreach ($brigade_id as $value) {
                                array_push($masID, $value->id);
                            }
                        } else {
                            $masID = [$id];
                        }
                        $date1 = $year . "-" . $monthfull . "-01";
                        $date2 = $year . "-" . $monthfull . "-" . $current_days;
                        $AllMountingOfBrigades = $model->GetAllMountingOfBrigades($masID, $date1, $date2);
                        $DateStatys = [];
                        foreach ($AllMountingOfBrigades as $value) {
                            if ($value->project_mounter == $id) {
                                if ($value->read_by_mounter == null) {
                                    $value->read_by_mounter = "0";
                                }
                                $arr = [substr($value->project_mounting_date, 0, 10), $value->read_by_mounter, $value->project_status];
                                array_push($DateStatys, $arr);
                            }
                        }

                        for ($r = 1; $r <= $current_days; $r++) {
                            if (strlen($r) == 1) {
                                $t = "0" . strval($r);
                            } else {
                                $t = $r;
                            }
                            foreach ($DateStatys as $value) {
                                if ($value[0] == $year . "-" . $monthfull . "-" . $t) {
                                    if ($value[1] == 0) {
                                        $DayMounters[$r] = "red";
                                    } else if ($value[1] == 1) {
                                        if ($value[2] == 5 || $value[2] == 6 || $value[2] == 7 || $value[2] == 8 || $value[2] == 10 || $value[2] == 19) {
                                            $DayMounters[$r] = "yellow";
                                        }
                                        if ($value[2] == 16 || $value[2] == 24 || $value[2] == 25 || $value[2] == 26|| $value[2] == 27 || $value[2] == 28 || $value[2] == 29 ) {
                                            $DayMounters[$r] = "navy";
                                        }
                                        if ($value[2] == 17) {
                                            $DayMounters[$r] = "brown";
                                        }
                                        if ($value[2] == 11 ) {
                                            $DayMounters[$r] = "green";
                                        }
                                        if ($value[2] == 12) {
                                            $DayMounters[$r] = "blue";
                                        }
                                    }
                                }
                            }
                        }//throw new Exception(print_r($DayMounters,true));
                        // выходные дни
                        $statusDayOff = "";
                        $AllDayOff = $model->GetAllDayOff($id, $date1, $date2);
                        if (!empty($AllDayOff)) {
                            foreach ($AllDayOff as $value) {
                                if (substr($value->date_from, 8, 1) == "0") {
                                    $perem1 = substr($value->date_from, 9, 1);
                                } else {
                                    $perem1 = substr($value->date_from, 8, 2);
                                }
                                $statusDayOff[$perem1] = "DayOff";
                            }
                        }
                        if ($DayMounters[$j - $first_day_of_week + 1] == "red") {
                            $table .= '<td class="day-not-read" id="current-monthD' . ($j - $first_day_of_week + 1) . 'DM' . $month . 'MY' . $year . 'YI' . $id . 'I">' . ($j - $first_day_of_week + 1) . '</td>';
                        } else if ($DayMounters[$j - $first_day_of_week + 1] == "yellow") {
                            $table .= '<td class="day-read" id="current-monthD' . ($j - $first_day_of_week + 1) . 'DM' . $month . 'MY' . $year . 'YI' . $id . 'I">' . ($j - $first_day_of_week + 1) . '</td>';
                        } else if ($DayMounters[$j - $first_day_of_week + 1] == "navy") {
                            $table .= '<td class="day-in-work" id="current-monthD' . ($j - $first_day_of_week + 1) . 'DM' . $month . 'MY' . $year . 'YI' . $id . 'I">' . ($j - $first_day_of_week + 1) . '</td>';
                        } else if ($DayMounters[$j - $first_day_of_week + 1] == "brown") {
                            $table .= '<td class="day-underfulfilled" id="current-monthD' . ($j - $first_day_of_week + 1) . 'DM' . $month . 'MY' . $year . 'YI' . $id . 'I">' . ($j - $first_day_of_week + 1) . '</td>';
                        } else if ($DayMounters[$j - $first_day_of_week + 1] == "green") {
                            $table .= '<td class="day-complite" id="current-monthD' . ($j - $first_day_of_week + 1) . 'DM' . $month . 'MY' . $year . 'YI' . $id . 'I">' . ($j - $first_day_of_week + 1) . '</td>';
                        } else if ($DayMounters[$j - $first_day_of_week + 1] == "blue") {
                            $table .= '<td class="old-project" id="current-monthD' . ($j - $first_day_of_week + 1) . 'DM' . $month . 'MY' . $year . 'YI' . $id . 'I">' . ($j - $first_day_of_week + 1) . '</td>';
                        } else {
                            if (isset($statusDayOff[$j - $first_day_of_week + 1])) {
                                $table .= '<td class="day-off" id="current-monthD'.($j - $first_day_of_week + 1).'DM'.$month.'MY'.$year.'YI'.$id.'I">'.($j - $first_day_of_week + 1).'</td>';
                            } else {
                                $table .= '<td class="current-month" id="current-monthD'.($j - $first_day_of_week + 1).'DM'.$month.'MY'.$year.'YI'.$id.'I">'.($j - $first_day_of_week + 1).'</td>';
                            }
                        }
                    }
                    //для вывода монтажной бригады в личном кабинете монтажной бригады
                    if ($flag[0] == 5) {
                        $model = self::getModel('mounterscalendar');
                        if (strlen($month) == 1) {
                            $monthfull = "0" . $month;
                        } else {
                            $monthfull = $month;
                        }
                        $date1 = $year . "-" . $monthfull . "-01";
                        $date2 = $year . "-" . $monthfull . "-" . $current_days;
                        $AllMountingOfBrigade = $model->GetAllMountingOfBrigade($id, $date1, $date2);
                        $DateStatys = [];$need_calc_projects = [];
                        foreach ($AllMountingOfBrigade as $value) {
                            if ($value->read_by_mounter == null) {
                                $value->read_by_mounter = "0";
                            }
                            $arr = array("mount_date" =>substr($value->project_mounting_date, 0, 10),
                                         "read_by_mounter" => $value->read_by_mounter,
                                         "project_status" => $value->project_status,
                                         "perimeter" => $value->n5,
                                         "id"=>$value->id);
                            array_push($DateStatys, $arr);


                        }
                        $DayMounter = [];
                        for ($r = 1; $r <= $current_days; $r++) {
                            if (strlen($r) == 1) {
                                $t = "0" . strval($r);
                            } else {
                                $t = $r;
                            }
                            foreach ($DateStatys as $value) {
                                if ($value["mount_date"] == $year . "-" . $monthfull . "-" . $t) {
                                    if(!in_array($value["id"],$need_calc_projects[$value["mount_date"]])){
                                        $perimeter += $value["perimeter"];
                                        $need_calc_projects[$value["mount_date"]][] = $value["id"];
                                    }
                                    if ($value["read_by_mounter"] == 0) {
                                        $DayMounter[$r] = ["red", $perimeter];
                                    } else if ($value['read_by_mounter'] == 1) {
                                        switch ($value["project_status"]) {
                                            case 5:
                                            case 6:
                                            case 7:
                                            case 8:
                                            case 10:
                                            case 19:
                                                $DayMounter[$r] = ["yellow", $perimeter];
                                                break;
                                            case 16:
                                                $DayMounter[$r] = ["navy", $perimeter];
                                                break;
                                            case 17:
                                                $DayMounter[$r] = ["brown", $perimeter];
                                                break;
                                            case 11:
                                                $DayMounter[$r] = ["green", $perimeter];
                                                break;
                                            case 12:
                                                $DayMounter[$r] = ["blue", $perimeter];
                                                break;
                                            default:
                                                $DayMounter[$r] = ["yellow", $perimeter];
                                                break;
                                        }
                                        /*if(empty($DayMounters[$r])){
                                            if ($value["project_status"] == 5 || $value["project_status"] == 6 || $value["project_status"] == 7 || $value["project_status"] == 8 || $value["project_status"] == 10 || $value["project_status"] == 19) {
                                                $DayMounter[$r] = ["yellow", $perimeter];
                                            }
                                            if ($value["project_status"] == 16) {
                                                $DayMounter[$r] = ["navy", $perimeter];
                                            }
                                            if ($value["project_status"] == 17) {
                                                $DayMounter[$r] = ["brown", $perimeter];
                                            }
                                            if ($value["project_status"] == 11) {
                                                $DayMounter[$r] = ["green", $perimeter];
                                            }
                                            if ($value["project_status"] == 12) {
                                                $DayMounter[$r] = ["blue", $perimeter];
                                            }
                                        }*/
                                    }
                                } else {
                                    $perimeter = 0;
                                }
                            }
                        }
                        // выходные дни
                        $statusDayOff = "";
                        $AllDayOff = $model->GetAllDayOff($id, $date1, $date2);
                        if (!empty($AllDayOff)) {
                            foreach ($AllDayOff as $value) {
                                $statusDayOff[substr($value->date_from, 8, 2)] = "DayOff";
                            }
                        }
                        // заполнение дней
                        if ($DayMounter[$j - $first_day_of_week + 1][0] == "red") {
                            $table .= '<td class="day-not-read" id="current-monthD' . ($j - $first_day_of_week + 1) . 'DM' . $month . 'MY' . $year . 'YI' . $id . 'I">' . ($j - $first_day_of_week + 1) . '</br><div class="perimeter">P=' . ($DayMounter[$j - $first_day_of_week + 1][1]) . 'м</div></td>';
                        } else if ($DayMounter[$j - $first_day_of_week + 1][0] == "yellow") {
                            $table .= '<td class="day-read" id="current-monthD' . ($j - $first_day_of_week + 1) . 'DM' . $month . 'MY' . $year . 'YI' . $id . 'I">' . ($j - $first_day_of_week + 1) . '</br><div class="perimeter">P=' . ($DayMounter[$j - $first_day_of_week + 1][1]) . 'м</div></td>';
                        } else if ($DayMounter[$j - $first_day_of_week + 1][0] == "navy") {
                            $table .= '<td class="day-in-work" id="current-monthD' . ($j - $first_day_of_week + 1) . 'DM' . $month . 'MY' . $year . 'YI' . $id . 'I">' . ($j - $first_day_of_week + 1) . '</br><div class="perimeter">P=' . ($DayMounter[$j - $first_day_of_week + 1][1]) . 'м</div></td>';
                        } else if ($DayMounter[$j - $first_day_of_week + 1][0] == "brown") {
                            $table .= '<td class="day-underfulfilled" id="current-monthD' . ($j - $first_day_of_week + 1) . 'DM' . $month . 'MY' . $year . 'YI' . $id . 'I">' . ($j - $first_day_of_week + 1) . '</br><div class="perimeter">P=' . ($DayMounter[$j - $first_day_of_week + 1][1]) . 'м</div></td>';
                        } else if ($DayMounter[$j - $first_day_of_week + 1][0] == "green") {
                            $table .= '<td class="day-complite" id="current-monthD' . ($j - $first_day_of_week + 1) . 'DM' . $month . 'MY' . $year . 'YI' . $id . 'I">' . ($j - $first_day_of_week + 1) . '</br><div class="perimeter">P=' . ($DayMounter[$j - $first_day_of_week + 1][1]) . 'м</div></td>';
                        } else if($DayMounter[$j - $first_day_of_week + 1][0] == "blue") {
                            $table .= '<td class="old-project" id="current-monthD' . ($j - $first_day_of_week + 1) . 'DM' . $month . 'MY' . $year . 'YI' . $id . 'I">' . ($j - $first_day_of_week + 1) . '</br><div class="perimeter">P=' . ($DayMounter[$j - $first_day_of_week + 1][1]) . 'м</div></td>';
                        } else {
                            if (isset($statusDayOff[$j - $first_day_of_week + 1])) {
                                $table .= '<td class="day-off2" id="current-monthD'.($j - $first_day_of_week + 1).'DM'.$month.'MY'.$year.'YI'.$id.'I">'.($j - $first_day_of_week + 1).'</td>';
                            } else {
                                $table .= '<td class="current-month" id="current-monthD'.($j - $first_day_of_week + 1).'DM'.$month.'MY'.$year.'YI'.$id.'I">'.($j - $first_day_of_week + 1).'</td>';
                            }
                        }
                    }
                }
                // обрезка строки по семь ячеек
                if ($j == 7 || $j == 14 || $j == 21 || $j == 28 || $j == 35 || $j == 42) {
                    $table .= '</tr>';
                }
            }
            // конец таблицы
            $table .= '</table>';
            return $table;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    /*
     * Функция создания html календаря
     *
     * @function    {LiteCalendar}
     * @param   number  $month  - От даты сейчас
     * @result  string  html
     */
    public function LiteCalendar($month = null, $year = null, $day = null){
        try {
            $month = ($month == null && $month != 0)?date("m"):intval($month);
            $monthNow = date("m");
            $year = ($year == null)?date("Y"):$year;
            $yearNow = date("Y");
            $day = ($day == null)?date("j"):$day;
            $DATA = (object)[
                "Month" => ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
                "Month2" => ['Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня', 'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря'],
                "Day" => ['ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ', 'ВС'],
                "DayFull" => ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье']
            ];
            $Calendar = '
                    <div class="Calendar Month" id="m%s" month="%s" year="%s" name="%s" modalname="%s">
                    <div class="Name">%s</div>
                    <div class="DaysOfTheWeek">%s</div>
                    <div class="Days">%s</div>
                    </div>
                    ';
            $DayOfTheWeek = '<div class="DayOfTheWeek" id="w%s">%s</div>';
            $Day = '<div class="Day %s" id="d%s" dotw="%s" day="%s">%s</div>';
            $DATE = (object)[
                "Year" => date("Y", mktime(0, 0, 0, $month, 1, $year)),
                "MonthNumber" => date("m", mktime(0, 0, 0, $month, 1, $year))
            ];
            $DATE->MonthName = $DATA->Month[$DATE->MonthNumber - 1];
            $DATE->MonthName2 = $DATA->Month2[$DATE->MonthNumber - 1];
            $DATE->CalDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $DATE->MonthNumber, $DATE->Year);
            $DATE->TopName = $DATE->MonthName . " " . $DATE->Year . " г.";
            $DATE->FirstDay = date("w", mktime(0, 0, 0, $DATE->MonthNumber, 1, $DATE->Year));
            if ($DATE->FirstDay == 0) $DATE->FirstDay = 7;
            $DaysOfTheWeek = "";
            foreach ($DATA->Day as $TKey => $TDay)
                $DaysOfTheWeek .= sprintf($DayOfTheWeek, $TKey + 1, $TDay);
            $Days = "";
            $SumDays = $DATE->CalDaysInMonth + $DATE->FirstDay;
            for ($i = 1; $i <= 42; $i++) {
                $TDayOfTheWeek = date("w", mktime(0, 0, 0, $DATE->MonthNumber, $i - $DATE->FirstDay, $DATE->Year));
                $dotw = "w" . ($TDayOfTheWeek + 1);
                $TDay = date("j", mktime(0, 0, 0, $DATE->MonthNumber, $i + 1 - $DATE->FirstDay, $DATE->Year));//$i + 1 - $DATE->FirstDay;
                $Now = ($TDay == $day && $DATE->MonthNumber == $monthNow && $DATE->Year == $yearNow) ? " Now" : "";
                $Days .= ($i < $DATE->FirstDay || $SumDays <= $i)
                    ? sprintf($Day, "EmptyDay", 0, $dotw, "none", $TDay)
                    : sprintf($Day, "IssetDay" . $Now, $TDay, $dotw, $TDay, $TDay);
            }
            $Calendar = sprintf($Calendar, $DATE->MonthNumber, $DATE->MonthNumber, $DATE->Year, $DATA->Month[intval($DATE->MonthNumber) - 1], $DATA->Month2[intval($DATE->MonthNumber) - 1], $DATE->TopName, $DaysOfTheWeek, $Days);
            return $Calendar;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public static function parse_price($price, $dealerPrice, $PriceDB = null)
    {
        try {
            if ($price == null || gettype($dealerPrice) != "object")
                return null;
            if ($PriceDB == null)
            {
                $Table = ($dealerPrice->component_id != null)?"Components":"Canvases";
                $IdPrice = ($dealerPrice->component_id != null)?$dealerPrice->component_id:$dealerPrice->canvas_id;
                $model = self::getModel($Table);
                $PriceDB = $model->getPrice($IdPrice)->price;
            }
            $data = (object) [];
            $data->dealerPrice = $dealerPrice;
            $price = (string) $price;
            $data->price = $price;
            $temp = str_replace("*", "", $price);
            $data->star = (strlen($temp) != strlen($price));
            $price = $temp;
            $temp = str_replace("#", "", $price);
            $data->sharp = (strlen($temp) != strlen($price));
            $price = $temp;
            $temp = str_replace("%", "", $price);
            $data->percent = (strlen($temp) != strlen($price));
            $price = $temp;
            $temp = str_replace(["+", "-"], "", $price);
            $data->switch = (strlen($temp) != strlen($price));
            $point = str_replace(".", "", $temp);
            $data->point = (strlen($point) != strlen($temp));
            $data->value = floatval($price);
            $data->valueEmpty = ($point == "");
            $data->type = 0;
            $data->switchValue = 0.0;
            if ($data->dealerPrice->type == 3)
                $data->switchValue += $PriceDB * ($data->dealerPrice->value / 100);
            else if ($data->dealerPrice->type == 5)
                $data->switchValue += $data->dealerPrice->price * ($data->dealerPrice->value / 100);
            else if ($data->dealerPrice->type == 2 || $data->dealerPrice->type == 4)
                $data->switchValue += $data->dealerPrice->value;
            $data->percentValue = 0.0;
            if ($data->dealerPrice->type == 2)
                $data->percentValue += ($PriceDB + $data->dealerPrice->value) * 100 / $PriceDB - 100;
            else if ($data->dealerPrice->type == 4)
                $data->percentValue += ($data->dealerPrice->price + $data->dealerPrice->value) * 100 / $data->dealerPrice->price - 100;
            else if ($data->dealerPrice->type == 3 || $data->dealerPrice->type == 5)
                $data->percentValue += $data->dealerPrice->value;

            //throw new Exception(print_r($data,true));
            if ($data->point && !($data->star || $data->sharp || $data->switch || $data->percent || !$data->valueEmpty))
            {
                $data->dealerPrice->type = 0;
                $data->dealerPrice->price = 0;
                $data->dealerPrice->value = 0;
            }
            else if ($data->star && $data->point && !($data->sharp || $data->switch || $data->percent || !$data->valueEmpty))
            {
                $data->dealerPrice->type = 1;
                $data->dealerPrice->price = $PriceDB;
                $data->dealerPrice->value = 0;
            }
            else if ($data->star && !($data->sharp || $data->switch || $data->percent || !$data->valueEmpty))
            {
                $data->dealerPrice->price = $PriceDB;
                $data->dealerPrice->value = 0;
            }
            else if ($data->sharp && $data->point && !($data->star || $data->switch || $data->percent || !$data->valueEmpty))
            {
                $data->dealerPrice->type = 1;
                $data->dealerPrice->value = 0;
            }
            else if ($data->sharp && !$data->valueEmpty && !($data->star || $data->switch || $data->percent))
            {
                $data->dealerPrice->price = $data->value;
            }
            else if (!$data->valueEmpty && !($data->sharp || $data->switch || $data->percent))
            {
                $data->dealerPrice->type = 1;
                $data->dealerPrice->price = $data->value;
                $data->dealerPrice->value = 0;
            }
            else if (!$data->valueEmpty && $data->switch && !($data->sharp || $data->star || $data->percent))
            {
                $data->dealerPrice->type = 4;
                $data->dealerPrice->price = $PriceDB;
                $data->dealerPrice->value = $data->value + $data->switchValue;
            }
            else if ($data->star && !$data->valueEmpty && $data->switch && !($data->sharp || $data->percent))
            {
                $data->dealerPrice->type = 4;
                $data->dealerPrice->price = $PriceDB;
                $data->dealerPrice->value = $data->value + $data->switchValue;
            }
            else if (!$data->valueEmpty && $data->switch && $data->percent && !($data->sharp || $data->star))
            {
                $data->dealerPrice->type = 5;
                $data->dealerPrice->value = $data->value + $data->percentValue;
            }
            else if ($data->star && !$data->valueEmpty && $data->switch && $data->percent && !$data->sharp)
            {
                $data->dealerPrice->type = 5;
                $data->dealerPrice->price = $PriceDB;
                $data->dealerPrice->value = $data->value + $data->percentValue;
            }
            else if ($data->sharp && !$data->valueEmpty && $data->switch && !($data->star || $data->percent))
            {
                $data->dealerPrice->type = 4;
                $data->dealerPrice->value = $data->value;
            }
            else if ($data->sharp && $data->star && !$data->valueEmpty && $data->switch && !$data->percent)
            {
                $data->dealerPrice->type = 4;
                $data->dealerPrice->price = $PriceDB;
                $data->dealerPrice->value = $data->value;
            }
            else if ($data->sharp && !$data->valueEmpty && $data->switch && $data->percent && !$data->star)
            {
                $data->dealerPrice->type = 5;
                $data->dealerPrice->value = $data->value;
            }
            else if ($data->sharp && $data->star && !$data->valueEmpty && $data->switch && $data->percent)
            {
                $data->dealerPrice->type = 5;
                $data->dealerPrice->price = $PriceDB;
                $data->dealerPrice->value = $data->value;
            }

            $data->updatePrice = self::update_price($data->dealerPrice, $PriceDB);
            return $data;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public static function update_price($objectDealerPrice, $Price)
    {
        try {
            $percent = ($objectDealerPrice->type == 3 || $objectDealerPrice->type == 5);
            $value = abs($objectDealerPrice->value);
            $valueSTR = (($objectDealerPrice->value == abs($objectDealerPrice->value))?" + ":" - ") . $value;
            $updatePrice = "";
            if ($objectDealerPrice->price != $Price) $updatePrice .= $objectDealerPrice->price;
            if ($value != 0) $updatePrice .= $valueSTR . (($percent)?"%":"");
            return $updatePrice;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public static function generatePassword($length) {
        try {
            $pass = '';
            for ($i = $length; $i--;) {
                $temp = random_int(1, 3);
                switch ($temp) {
                    case 1:
                        $symbol = chr(random_int(48, 57));
                        break;
                    case 2:
                        $symbol = chr(random_int(65, 90));
                        break;
                    case 3:
                        $symbol = chr(random_int(97, 122));
                        break;
                    default:
                        $symbol = chr(random_int(97, 122));
                        break;
                }
                $pass .= $symbol;
            }
            return $pass;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    static function rus2translit($string) {
        try {
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
                'ь' => '\'',  'ы' => 'y',   'ъ' => '\'',
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
                'Ь' => '\'',  'Ы' => 'Y',   'Ъ' => '\'',
                'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
            );
            return strtr($string, $converter);
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public static function groupMapNoteTitle($groups){
        $result = (object)[];
        if (in_array("13", $groups)) { //Менеджер дилера
            $result->title = "d_manager";
            $result->description = "Менеджер ";
        }
        if (in_array("21", $groups)) {//Замерщик дилера
            $result->title = "d_calculator";
            $result->description = "Замерщик ";
        }
        if (in_array("12", $groups)) { //Начальник МС дилера
            $result->title = "d_chief";
            $result->description = "Начальник МС ";
        }
        if (in_array("14", $groups)) { //Дилер
            $result->title = "dealer";
            $result->description = "Дилер ";
        }
        if (in_array("16", $groups)) { //Менеджер ГМ
            $result->title = "gm_manager";
            $result->description = "Менеджер ГМ ";
        }
        if (in_array("17", $groups)) { //Начальник МС ГМ
            $result->title = "gm_chief";
            $result->description = "Начальник МС ГМ ";

        }
        if (in_array("22", $groups)) { //Замерщик ГМ
            $result->title = "gm_calculator";
            $result->description = "Замерщик ГМ ";
        }
        if (in_array("19", $groups)) { //Кладовщик ГМ
            $result->title = "gm_stockman";
            $result->description = "Кладовщик ГМ ";
        }
        if (in_array("11", $groups)) { //монтажная бригада
            $result->title = "mounter";
            $result->description = "Монтажная бригада ";
        }
        return $result;
    }
    public static function getProjectNotes($project_id,$type = null) {
        try {
            $model_project = self::getModel('project');
            $notes = $model_project->getProjectNotes($project_id,$type);
            $result = [];

            foreach ($notes as $key => $value) {
                $user = JFactory::getUser($value->user_id);
                $groups = $user->get('groups');
                $groupTitle = self::groupMapNoteTitle($groups);
                $noteObject = (object)array("value"=>"","description"=>"");

                switch($value->type){
                    case 1://общее примечание
                        $noteObject->description = 'Общее примечание по проекту: ';
                        $noteObject->value = $value->note;
                        break;
                    case 2://примечание к замеру
                        $noteObject->value =$value->note;
                        $noteObject->description = $groupTitle->description."к замеру: ";
                        $result[$groupTitle->title."_measure"] = $noteObject;
                        break;
                    case 3://примечание отказа
                        $noteObject->value =$value->note;
                        $noteObject->description = $groupTitle->description." к отказу: ";
                        $result[$groupTitle->title."_refuse"] = $noteObject;
                        break;
                    case 4://в производство
                        $noteObject->value =$value->note;
                        $noteObject->description = $groupTitle->description." к производству: ";
                        $result[$groupTitle->title."_production"] = $noteObject;
                        break;
                    case 5://к монтажу
                        $noteObject->value =$value->note;
                        $noteObject->description = $groupTitle->description." к монтажу: ";
                        $result[$groupTitle->title."_mount"] = $noteObject;
                        break;
                    case 6://после монтажа
                        $noteObject->value =$value->note;
                        $result[$groupTitle->title."_after_mount"] = $noteObject;
                        break;
                }

            }
            return (object)$result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public static function forceLogin($userId) {
        $app = JFactory::getApplication();
        $logoutStatus = $app->logout();
        if ($logoutStatus) {
            $user = JFactory::getUser($userId);
            if ($user->guest) {
                return 'guest';
            } else {
                //Will authorize you as this user.
                JPluginHelper::importPlugin('user');
                $options = array();
                $options['action'] = 'core.login.site';
                $response = new stdClass();
                $response->username = $user->username;
                $response->language = '';
                $response->email = $user->email;
                $response->password_clear = '';
                $response->fullname = '';
                $result = $app->triggerEvent('onUserLogin', array((array)$response, $options));
                return $result;
            }
        } else {
            return false;
        }
    }

}
