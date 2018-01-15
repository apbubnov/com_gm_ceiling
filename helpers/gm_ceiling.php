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

/* функция для применения маржи */
function margin($value, $margin)
{

    $return = ($value * 100 / (100 - $margin));
    return $return;
}

/* функция для применения сразу двойной маржи */
function double_margin($value, $margin1, $margin2)
{

    $return = ($value * 100 / (100 - $margin1)) * 100 / (100 - $margin2);
    return $return;
}

class Gm_ceilingHelpersGm_ceiling
{


    /**
     * Get an instance of the named modelt
     *
     * @param   string $name Model name
     *
     * @return null|object
     */
    public static function getModel($name)
    {
        $model = null;

        // If the file exists, let's
        if (file_exists(JPATH_SITE . '/components/com_gm_ceiling/models/' . strtolower($name) . '.php')) {
            require_once JPATH_SITE . '/components/com_gm_ceiling/models/' . strtolower($name) . '.php';
            $model = JModelLegacy::getInstance($name, 'Gm_ceilingModel');
        }

        return $model;
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
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select($field)
            ->from($table)
            ->where('id = ' . (int)$pk);

        $db->setQuery($query);

        return explode(',', $db->loadResult());
    }

    public static function registerUser($FIO, $phone, $email, $client_id)
    {

        jimport('joomla.user.helper');

        $data = array(
            "name" => $FIO,
            "username" => $phone,
            "password" => $phone,
            "password2" => $phone,
            "email" => $email,
            "groups" => array(2, 14),
            "phone" => $phone,
            "block" => 0,
            "dealer_type" => 0
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
        $post['dealer_id'] = $userID;
        $post['associated_client'] = $client_id;
        if (!$user->bind($post)) return false;
        if (!$user->save()) return false;
        JFactory::getApplication()->enqueueMessage("Добавлен новый дилер!");
        return $userID;
        //header('location: /index.php?option=com_gm_ceiling&view=mainpage&type=gmmanagermainpage');
    }

    /*функция для получения всех монтажных бригад и проверки кому принадлежит та или иная бригада*/
    public static function whoseTeam($teamName)
    {
        $teamsModel = Gm_ceilingHelpersGm_ceiling::getModel('teams');
        $teamsList = $teamsModel->getAllItems();
        for ($i = 0; $i < count($teamsList); $i++) {
            if ($teamsList[$i]['name'] == $teamName) {
                return $teamsList[$i]['dealer_id'];
            }
        }
    }

    /* 	основная функция для расчета стоимости потолка
		$from_db - 0,1 флаг, брать ли данные калькуляции из БД
		$calculation_id - id калькуляции в БД
		$save - 0,1 флаг, чтобы сохранить калькуляцию в БД
		$ajax - 0,1 флаг AJAX-запроса
		$pdf - 0,1 флаг формирования PDF
		$print_components - 0,1 флаг возвращения расчета при вызове в переменную
	*/
    public static function calculate($from_db, $calculation_id, $save, $ajax, $pdf, $print_components, $del_flag, $need_mount)
    {
        $jinput = JFactory::getApplication()->input;


        //Получаем прайс-лист комплектующих
        $components_model = Gm_ceilingHelpersGm_ceiling::getModel('components');

        $components_list = $components_model->getFilteredItems();

        foreach ($components_list as $i => $component) {
            $components[$component->id] = $component;
        }
        //	print_r($components); exit;
        //throw new components($del_flag, 1);

        //Получаем прайс-лист полотен
        $canvases_model = Gm_ceilingHelpersGm_ceiling::getModel('canvases');
        $canvases_list = $canvases_model->getFilteredItemsCanvas();
        foreach ($canvases_list as $i => $canvas) {
            $canvases[$canvas->id] = $canvas;
        }

        //Получаем данные
        $send_client_cost = $jinput->get('send_client_cost', '0', 'INT');
        $new_client = $jinput->get('new_client', '0', 'INT');
        $flag = $jinput->get('flag', '0', 'INT');
        if ($from_db == 1) {
            //Загружаем из БД
            $calculation_model = Gm_ceilingHelpersGm_ceiling::getModel('calculation');


            $calculation_data = $calculation_model->getData($calculation_id);
            foreach ($calculation_data as $key => $item) {
                $data[$key] = $item;
            }
            //throw  new Exception("Test", 3);
            $data['n1'] = $calculation_data->n1_id;
            $data['n2'] = $calculation_data->n2_id;
            $data['n3'] = $calculation_data->n3_id;


            //JFactory::getApplication()->enqueueMessage(print_r($data, true), 'error');
        } else {
            //Получаем из запроса
            $data = $jinput->getArray(array(
                'jform' => array(
                    'id' => 'int', //id потолка
                    'dealer_id' => 'int', //владелец
                    'n1' => 'int', //тип потолка
                    'n2' => 'int', //тип фактуры
                    'n3' => 'int', //Производитель и ширина
                    'n4' => 'float', //Площадь
                    'n5' => 'float', //Периметр
                    'n6' => 'int', //Со вставкой
                    'n7' => 'float', //Крепление в плитку
                    'n8' => 'float', //Крепление в керамогранит
                    'n9' => 'int', //Углы
                    'n10' => 'float', //Криволинейный участок
                    'n11' => 'float', //Внутренний вырез
                    'n12' => 'int', //Упрощенные люстры
                    'n13' => 'int', //Упрощенные светильники
                    'n14' => 'int', //Упрощенные обводы труб
                    'n15' => 'int', //Шторный карниз
                    'n16' => 'int', //Скрытый карниз
                    'n17' => 'float', //Закладная брусом
                    'n18' => 'float', //Укрепление стены
                    'n19' => 'float', //Провод
                    'n20' => 'float', //Разделитель
                    'n21' => 'float', //Пожарная сигнализация
                    'n22' => 'int',
                    'n23' => 'int',
                    'n24' => 'float', //Сложность к месту доступа
                    'n25' => 'int',
                    'n26' => 'int',
                    'n27' => 'float',
                    'n28' => 'int',
                    'n29' => 'float',
                    'n30' => 'float',
                    'n31' => 'float',
                    'n32' => 'int',
                    'height' => 'int',
                    'distance' => 'float',
                    'distance_col' => 'int',
                    'dop_krepezh' => 'float', //Доп. крепеж
                    'transport' => 'int', //Транспортные расходы
                    'calculation_title' => 'string',
                    'project_id' => 'int',
                    'send_email' => 'string', //адрес почты клиента
                    'sketch_name' => 'string', //имя чертежа
                    'cut_name' => 'string',//имя раскроя
                    'color' => 'string', //цвет
                    'details' => 'string', //цвет
                    'offcut_square' => 'float',
                    'discount' => 'int',
                    'original_name' => 'string',
                    'rek' => 'int'
                )
            ));
            //


            $data = $data['jform'];

            $data['n3'] = ($_SESSION['n3']) ? ($_SESSION['n3']) : $data['n3'];
            //print_r($data); exit;
            if ($data['n2'] == 0) {
                $data['n3'] = 0;
                $data['n4'] = 0;
                $data['n5'] = 0;
                $data['n9'] = 0;
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
            $data['extra_components'] = json_encode($extra_components, JSON_FORCE_OBJECT);

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
            $data['extra_mounting'] = json_encode($extra_mounting, JSON_FORCE_OBJECT);
            //Транспортные расходы
            /*if($data['project_id'] <= 0) {
			//Если калькуляция еще не привязана к договору, то добавляем транспортные, если не указано обратного
				if($data['transport'] < 1) {
					$data['transport'] = 1;
				}
			} else {
			//Если калькуляция привязана к договору, то назначаем транспортные только если калькуляция первая.
			//Если не первая, то автоматически не добавляем
			$db= JFactory::getDBO();
			$query = 'SELECT `id` FROM `#__gm_ceiling_calculations` WHERE `project_id` = ' . (int) $data['project_id'];
			$db->setQuery($query);
			$calculations = $db->loadObjectList();
			if(count($calculations) > 0) {
				if($calculations[0]->id == $data['id']){
					if($data['transport'] < 1) {
						$data['transport'] = 1;
					}
				}
			} else {
				if($data['transport'] < 1) {
					$data['transport'] = 1;
				}
			}
		}*/

        }

        /*
        if ($data['project_id'] <= 0) {
            //Если калькуляция еще не привязана к договору, то добавляем транспортные, если не указано обратного
            if ($data['transport'] < 1) {
                $data['transport'] = 1;
            }
        } else {
            //Если калькуляция привязана к договору, то назначаем транспортные только если калькуляция первая.
            //Если не первая, то автоматически не добавляем
            $db = JFactory::getDBO();
            $query = 'SELECT `id` FROM `#__gm_ceiling_calculations` WHERE `project_id` = ' . (int)$data['project_id'];
            $db->setQuery($query);
            $calculations = $db->loadObjectList();
            if (count($calculations) > 0) {
                if ($calculations[0]->id == $data['id']) {
                    if ($data['transport'] < 1) {
                        $data['transport'] = 1;
                    }
                }
            } else {
                if ($data['transport'] < 1) {
                    $data['transport'] = 1;
                }
            }
        }

        if($data['transport'] == 1) {
            if (!empty($data['distance']) && !empty($data['distance_col']))
             $data['transport'] = 0;
        }
        else {
            if (!empty($data['distance']) && !empty($data['distance_col'])) {
                $data['distance'] = 0;
                $data['distance_col'] = 0;
            }
             
        }*/
        //if (!empty($data['distance']) && !empty($data['distance_col']) && $data['transport'] == 1)
        // $data['transport'] = 0;
        //print_r($data['n15']); exit;
        //print_r($data); exit;

        if ($data['n2'] == 29) $data['n1'] = 29;

        //Получаем объект дилера
        if (empty($data['dealer_id'])) {
            $dealer = JFactory::getUser(2);
        } else {
            $dealer = JFactory::getUser($data['dealer_id']);
        }

        $dealer_info = Gm_ceilingHelpersGm_ceiling::getModel('dealer_info');
        $dealer_marg = $dealer_info->getData();
        //throw new Exception("Error Processing Request", 1);
        //Получаем объект ГМ
        $gm = JFactory::getUser(1);

        //Получаем объект договора, если он уже создан
        if ($data['project_id']) {
            $project_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
            $project = $project_model->getData($data['project_id']);

            $gm_canvases_margin = $project->gm_canvases_margin;            //Маржа ГМ на полотно
            $gm_components_margin = $project->gm_components_margin;            //Маржа ГМ на комплектующие
            $gm_mounting_margin = $project->gm_mounting_margin;            //Маржа ГМ на монтажные работы

            $dealer_canvases_margin = $project->dealer_canvases_margin;    //Маржа дилера на полотно
            $dealer_components_margin = $project->dealer_components_margin;    //Маржа дилера на комплектующие
            $dealer_mounting_margin = $project->dealer_mounting_margin;    //Маржа дилера на монтажные работы

        } else {

            //Или назначаем маржи из настроек дилера
            $gm_canvases_margin = $dealer_marg->gm_canvases_margin;            //Маржа ГМ на полотно
            $gm_components_margin = $dealer_marg->gm_components_margin;            //Маржа ГМ на комплектующие
            $gm_mounting_margin = $dealer_marg->gm_mounting_margin;            //Маржа ГМ на монтажные работы

            $dealer_canvases_margin = $dealer_marg->dealer_canvases_margin;    //Маржа дилера на полотно
            $dealer_components_margin = $dealer_marg->dealer_components_margin;    //Маржа дилера на комплектующие
            $dealer_mounting_margin = $dealer_marg->dealer_mounting_margin;

            //Маржа дилера на монтажные работы
        }

//print_r( $dealer_canvases_margin);exit;
        //------------------- РАСЧЕТ СТОИМОСТИ КОМПЛЕКТУЮЩИХ -------------------------------------//

        //print_r($data['n13']);
        //Количество комплектующих обнуляем

        $component_count = array();
        foreach ($components as $key => $value) $component_count[$key] = 0;
        //throw new Exception($count, 1);
        //print_r($comonents[636]); exit;
        //периметр ТОЛЬКО ДЛЯ ПВХ

        $filter = "a.title  LIKE('%3,5 * 51%') AND component.title LIKE('%Саморез%') ";
        $items_9 = $components_model->getFilteredItems($filter);

        $filter = "a.title  LIKE('%6 * 51%') AND component.title LIKE('%Дюбель%') ";
        $items_5 = $components_model->getFilteredItems($filter);

        $filter = "a.title  LIKE('%ПВХ (2,5 м)%') AND component.title LIKE('%Багет%') ";
        $items_11 = $components_model->getFilteredItems($filter);

        $filter = "a.title  LIKE('%потолочный аллюм%') AND component.title LIKE('%Багет%') ";
        $items_236 = $components_model->getFilteredItems($filter);

        $filter = "a.title  LIKE('%стеновой аллюм%') AND component.title LIKE('%Багет%') ";
        $items_239 = $components_model->getFilteredItems($filter);


        $filter = "a.title  LIKE('%для парящих пот аллюм%') AND component.title LIKE('%Багет%') ";
        $items_559 = $components_model->getFilteredItems($filter);

        $filter = "a.title  LIKE('%303 белая%') AND component.title LIKE('%Вставка%') ";
        $items_vstavka_bel = $components_model->getFilteredItems($filter);

        if ($data['color'] > 0) {
            $color_model1 = Gm_ceilingHelpersGm_ceiling::getModel('colors');
            $color1 = $color_model1->getColorTitle($data['color']);
            $name1 = $color1->title;
            $filter = "a.title  LIKE('%" . $name1 . "%') AND component.title LIKE('%Вставка%') ";
            $items_vstavka = $components_model->getFilteredItems($filter);
            if (empty($items_vstavka[0])) $items_vstavka = $items_vstavka_bel;
        }

        $filter = "a.title  LIKE('%п/сф 3,5*9,5%') AND component.title LIKE('%Саморез%') ";
        $items_10 = $components_model->getFilteredItems($filter);

        $filter = "a.title  LIKE('%тарелка%') AND component.title LIKE('%Платформа под люстру%') ";
        $items_16 = $components_model->getFilteredItems($filter);

        $filter = "a.title  LIKE('%6*40%') AND component.title LIKE('%Шуруп-полукольцо%') ";
        $items_556 = $components_model->getFilteredItems($filter);

        $filter = "a.title  LIKE('%ПВС 2 х 0,75%') AND component.title LIKE('%Провод%') ";
        $items_4 = $components_model->getFilteredItems($filter);

        $filter = "a.title  LIKE('50') AND component.title LIKE('%Круглое кольцо%') ";
        $items_58 = $components_model->getFilteredItems($filter);

        $filter = "a.title  LIKE('%П 60%') AND component.title LIKE('%Подвес прямой %') ";
        $items_3 = $components_model->getFilteredItems($filter);

        $filter = "a.title  LIKE('%2,5 мм%') AND component.title LIKE('%Клеммная колодка%') ";
        $items_2 = $components_model->getFilteredItems($filter);

        $filter = "a.title  LIKE('%40*50%') AND component.title LIKE('%Брус%') ";
        $items_1 = $components_model->getFilteredItems($filter);

        $filter = "a.title  LIKE('%3,5 * 41%') AND component.title LIKE('%Саморез%') ";
        $items_8 = $components_model->getFilteredItems($filter);

        $filter = "a.title  LIKE('%4,2 * 102%') AND component.title LIKE('%Саморез%') ";
        $items_6 = $components_model->getFilteredItems($filter);

        $filter = "a.title  LIKE('%в разд 303 гриб%') AND component.title LIKE('%Вставка%') ";
        $items_14 = $components_model->getFilteredItems($filter);

        $filter = "a.title  LIKE('%для парящих потолков%') AND component.title LIKE('%Вставка%') ";
        $items_38 = $components_model->getFilteredItems($filter);

        $filter = "a.title  LIKE('%15 * 12,5 см.%') AND component.title LIKE('%Кронштейн%') ";
        $items_430 = $components_model->getFilteredItems($filter);

        $filter = "a.title  LIKE('%разделительный аллюм%') AND component.title LIKE('%Багет%') ";
        $items_35 = $components_model->getFilteredItems($filter);

        $filter = "component.title LIKE('%Гарпун%') ";
        $items_360 = $components_model->getFilteredItems($filter);

        $filter = "a.title  LIKE('%70*100 мм%') AND component.title LIKE('%Платформа для карнизов%') ";
        $items_495 = $components_model->getFilteredItems($filter);

        $filter = "a.title  LIKE('%Декскор 2,5%') AND component.title LIKE('%Багет%') ";
        $items_233 = $components_model->getFilteredItems($filter);

        $filter = "component.title LIKE('%Переход уровня%') ";
        $items_659 = $components_model->getFilteredItems($filter);

        $filter = "component.title LIKE('%Переход уровня с нишей%') ";
        $items_660 = $components_model->getFilteredItems($filter);

        if ($data['n1'] == 28) {
            $component_count[$items_9[0]->id] += $data['n5'] * 10;
            $component_count[$items_5[0]->id] += $data['n5'] * 10;
            if ($data['n28'] == 0) $component_count[$items_11[0]->id] += $data['n5'];
            elseif ($data['n28'] == 1) $component_count[$items_236[0]->id] += $data['n5'];
            elseif ($data['n28'] == 2) $component_count[$items_239[0]->id] += $data['n5'];
        }

        if ($data['n1'] == 29) {
            $component_count[$items_9[0]->id] += $data['n5'] * 10;
            $component_count[$items_5[0]->id] += $data['n5'] * 10;
            $component_count[$items_233[0]->id] += $data['n11'];
        }
        // внутренний вырез
        if ($data['n11'] > 0) {
            $component_count[$items_1[0]->id] += $data['n11'];
            if ($data['n1'] == 29) $component_count[$items_233[0]->id] += $data['n11'];
            else if ($data['n28'] == 0) $component_count[$items_11[0]->id] += $data['n11'];
            elseif ($data['n28'] == 1) $component_count[$items_236[0]->id] += $data['n11'];
            elseif ($data['n28'] == 2) $component_count[$items_239[0]->id] += $data['n11'];
            $component_count[$items_430[0]->id] += $data['n11'] * 3;
            $component_count[$items_8[0]->id] += $data['n11'] * 22;
            $component_count[$items_5[0]->id] += $data['n11'] * 16;
            $component_count[$items_360[0]->id] += $data['n11'];
        }
        //для Димы
        if ($data['n31'] > 0) {
            $component_count[$items_9[0]->id] += $data['n31'] * 10;
            $component_count[$items_5[0]->id] += $data['n31'] * 10;
            $n31_count = ceil($data['n31']);
            if (!empty($data['n6'])) {
                $component_count[$data['n6']] += $n31_count;
            } else $component_count[$items_vstavka_bel[0]->id] += $n31_count;
            if ($data['n28'] == 0) $component_count[$items_11[0]->id] += $data['n31'];
            elseif ($data['n28'] == 1) $component_count[$items_236[0]->id] += $data['n31'];
            elseif ($data['n28'] == 2) $component_count[$items_239[0]->id] += $data['n31'];

        }

        //со вставкой
        /*if($data['n1'] == 28 && $data['n6']) {
			$n5_count = ceil($data['n5'] + 0.5);

			if($data['n2'] == 1 || $data['n2'] == 3 || $data['n2'] == 5) { 											 //Белый
				$component_count[$items_vstavka_bel[0]->id] += $n5_count + 0.5;
			} elseif($data['n2'] == 2 || $data['n2'] == 4 || $data['n2'] == 6 || $data['n2'] == 7 || $data['n2'] == 8 || $data['n2'] == 9) { //Цветной
				$component_count[$items_vstavka[0]->id] += $n5_count + 0.5;
			}
		}*/
        if ($data['n1'] == 28 && $data['n6']) {
            $n5_count = ceil($data['n5']);
            $component_count[$data['n6']] += $n5_count;
        }


        //люстры
        $component_count[$items_5[0]->id] += $data['n12'] * 3;
        $component_count[$items_9[0]->id] += $data['n12'] * 3;
        $component_count[$items_10[0]->id] += $data['n12'] * 8;
        $component_count[$items_16[0]->id] += $data['n12'];
        $component_count[$items_556[0]->id] += $data['n12'];
        $component_count[$items_4[0]->id] += $data['n12'] * 0.5;
        $component_count[$items_58[0]->id] += $data['n12'];
        $component_count[$items_3[0]->id] += $data['n12'] * 4;
        if ($data['n12'] > 0) {
            $component_count[$items_2[0]->id] += 2;
        }

//        $kolKP = 0.0;//переменная для просчета, является ли профиль из серии КП
//        $kolNP = 0.0; //переменная для просчета, является ли профиль из серии НП
//        $kolSP = 0.0;//переменная для просчета, является ли профиль из серии СП


        if ($del_flag == 1) {
            //светильники
            $calcform_model = Gm_ceilingHelpersGm_ceiling::getModel('calculationform');
            $n13 = json_decode($data['n13']);
            $n26 = json_decode($data['n26']);
            $n22 = json_decode($data['n22']);
            $n14 = json_decode($data['n14']);
            $n23 = json_decode($data['n23']);
            $n15 = json_decode($data['n15']);
            $n29 = json_decode($data['n29']);


            if (count($n29) > 0) {
                foreach ($n29 as $profil) {
                    if ($profil[0] > 0) {
//                        $component_count[$profil[2]] += $profil[0];
//                        if($profil[2] == 652) $kolKP = $profil[0];
//                        if($profil[2] == 653) $kolNP = $profil[0];
//                        if($profil[2] == 655) $kolSP = $profil[0];
                        if ($profil[1] == 12 || $profil[1] == 13) {
                            $component_count[$items_659[0]->id] += $profil[0];
                        } elseif ($profil[1] == 15 || $profil[1] == 16) {
                            $component_count[$items_660[0]->id] += $profil[0];
                        }
                    }
                }
            }

            $k = 0;
            if (count($n26) > 0) {
                foreach ($n26 as $ecola) {
                    if ($ecola[0] > 0) {
                        $component_count[$ecola[1]] += $ecola[0];
                        $component_count[$ecola[2]] += $ecola[0];
                        $k += $ecola[0];
                    }

                }
            }
            //print_r($n13); exit;
            if (count($n13) > 0 && $n13 != 0) {
                foreach ($n13 as $lamp) {
                    $fix_components = $calcform_model->components_list_n13_n22($lamp[1], $lamp[2]);
                    foreach ($fix_components as $comp) $component_count[$comp['id']] += ($comp['count'] * $lamp[0]);

                    if ($lamp[1] == 2 && $lamp[2] == 66) {
                        $component_count[66] -= $k;
                        if ($component_count[66] < 0) $component_count[66] = 0;
                    }

                }
                $component_count[$items_2[0]->id]++;
            }


            //вентиляция

            if (count($n22) > 0) {
                foreach ($n22 as $ventilation) {
                    $ventilation_components = $calcform_model->components_list_n13_n22($ventilation[1], $ventilation[2]);

                    foreach ($ventilation_components as $comp) $component_count[$comp['id']] += ($comp['count'] * $ventilation[0]);
                }
            }


            if (count($n14) > 0) {
                foreach ($n14 as $truba) {
                    if ($truba[0] > 0) $component_count[$truba[1]] += $truba[0];
                }
            }


            if (count($n23) > 0) {
                foreach ($n23 as $diffuzor) {
                    if ($diffuzor[0] > 0) $component_count[$diffuzor[1]] += $diffuzor[0];
                }
            }

            if (count($n15) > 0) {
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
                foreach ($n13 as $lamp) {
                    $fix_components = $calcform_model->components_list_n13_n22($lamp->n13_type, $lamp->n13_size);
                    foreach ($fix_components as $comp) $component_count[$comp['id']] += ($comp['count'] * $lamp->n13_count);

                    if ($lamp->n13_type == 2 && $lamp->n13_size == 66) {
                        $component_count[66] -= $k;
                    }
                    if ($component_count[66] < 0) $component_count[66] = 0;
                }
                $component_count[$items_2[0]->id]++;

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
                        if ($profil->n29_type == 12 || $profil->n29_type == 13) {
                            $component_count[$items_659[0]->id] += $profil->n29_count;
                        } elseif ($profil->n29_type == 15 || $profil->n29_type == 16) {
                            $component_count[$items_660[0]->id] += $profil->n29_count;
                        }

                    }
                }
            }


        }


        //парящий потолок
        $component_count[$items_559[0]->id] += $data['n30'];
        $component_count[$items_38[0]->id] += $data['n30'];

        //карниз
        $component_count[$items_1[0]->id] += $data['n27'];
        $component_count[$items_3[0]->id] += $data['n27'] * 3;
        $component_count[$items_5[0]->id] += $data['n27'] * 6;
        $component_count[$items_8[0]->id] += $data['n27'] * 9;
        $component_count[$items_9[0]->id] += $data['n27'] * 6;

        //скрытый карниз
        if ($data['n16']) {
            $component_count[$items_430[0]->id] += $data['n27'] * 2;
            $component_count[$items_8[0]->id] += $data['n27'] * 4;
        }

        //закладная брусом
        $component_count[$items_1[0]->id] += $data['n17'];
        $component_count[$items_3[0]->id] += $data['n17'] * 3;
        $component_count[$items_5[0]->id] += $data['n17'] * 6;
        $component_count[$items_9[0]->id] += $data['n17'] * 6;
        $component_count[$items_8[0]->id] += $data['n17'] * 6;

        //укрепление стен
        $component_count[$items_1[0]->id] += $data['n18'];
        $component_count[$items_6[0]->id] += $data['n18'] * 3;
        $component_count[$items_5[0]->id] += $data['n18'] * 3;
        $component_count[$items_430[0]->id] += $data['n18'] * 3;

        //провод
        $component_count[$items_4[0]->id] += $data['n19'];
        $component_count[$items_9[0]->id] += $data['n19'] * 2;
        $component_count[$items_5[0]->id] += $data['n19'] * 2;

        //разделитель ТОЛЬКО ДЛЯ ПВХ
        if ($data['n1'] == 28) {
            $component_count[$items_1[0]->id] += $data['n20'];
            $component_count[$items_6[0]->id] += $data['n20'] * 3;
            $component_count[$items_9[0]->id] += $data['n20'] * 20;
            $component_count[$items_5[0]->id] += $data['n20'] * 3;
            $component_count[$items_14[0]->id] += $data['n20'];

            $n20_count = intval($data['n20'] / 2.5);
            if ((double)($data['n20'] * 10 % 25) > 0) {
                $n20_count++;
            }
            $component_count[$items_35[0]->id] += $n20_count * 2.5;
        }

        //Дополнительный крепеж
        $component_count[$items_9[0]->id] += $data['dop_krepezh'] * 10;
        if ($data['n1'] == 29) $component_count[$items_233[0]->id] += $data['dop_krepezh'] / 2;
        else if ($data['n28'] == 0) $component_count[$items_11[0]->id] += $data['dop_krepezh'] / 2;
        elseif ($data['n28'] == 1) $component_count[$items_236[0]->id] += $data['dop_krepezh'] / 2;
        elseif ($data['n28'] == 2) $component_count[$items_239[0]->id] += $data['dop_krepezh'] / 2;

        //пожарная сигнализация
        $component_count[$items_9[0]->id] += $data['n21'] * 3;
        $component_count[$items_10[0]->id] += $data['n21'] * 6;
        $component_count[$items_495[0]->id] += $data['n21'];
        $component_count[$items_58[0]->id] += $data['n21'];
        $component_count[$items_3[0]->id] += $data['n21'] * 3;
        $component_count[$items_5[0]->id] += $data['n21'] * 3;
        if ($data['n21'] > 0) {
            $component_count[$items_2[0]->id] += $data['n21'] * 2;
        }


        //Брус до 0,5 и багет 2.5м считается кусками, которые потребуются выложить весь периметр

//tyt
        if ($print_components == 0) {

            if ($data['n28'] == 0) $component_count[$items_11[0]->id] = self::rounding($component_count[$items_11[0]->id], 2.5);
            elseif ($data['n28'] == 1) $component_count[$items_236[0]->id] = self::rounding($component_count[$items_236[0]->id], 2.5);
            elseif ($data['n28'] == 2) $component_count[$items_239[0]->id] = self::rounding($component_count[$items_239[0]->id], 2.5);
            $component_count[$items_559[0]->id] = self::rounding($component_count[$items_559[0]->id], 2.5);
            $component_count[$items_233[0]->id] = self::rounding($component_count[$items_233[0]->id], 2.5);
            $component_count[$items_38[0]->id] = self::rounding($component_count[$items_38[0]->id], 0.5);
            $component_count[$items_1[0]->id] = self::rounding($component_count[$items_1[0]->id], 0.5);
            $component_count[650] = self::rounding($component_count[650], 2.5);
            $component_count[651] = self::rounding($component_count[651], 2.5);
            $component_count[652] = self::rounding($component_count[652], 2.5);
            $component_count[653] = self::rounding($component_count[653], 2.5);
            $component_count[654] = self::rounding($component_count[654], 2.5);
            $component_count[655] = self::rounding($component_count[655], 2.5);
            $component_count[656] = self::rounding($component_count[656], 2.5);
            $component_count[$items_4[0]->id] = ceil($component_count[$items_4[0]->id]);
            /*
                        if ($data['n28'] == 0) $baget_type = $component_count[$items_11[0]->id];
                        elseif ($data['n28'] == 1) $baget_type = $component_count[$items_236[0]->id];
                        elseif ($data['n28'] == 2) $baget_type = $component_count[$items_239[0]->id];
                        $baget_count = intval($baget_type / 2.5);
                        if (floatval($baget_type / 2.5) > $baget_count) {
                            $baget_count++;
                        }
                        $baget_type = $baget_count * 2.5;
                        if ($data['n28'] == 0) $component_count[$items_11[0]->id] = $baget_type;
                        elseif ($data['n28'] == 1) $component_count[$items_236[0]->id] = $baget_type;
                        elseif ($data['n28'] == 2) $component_count[$items_239[0]->id] = $baget_type;

                        $par_type = $component_count[$items_559[0]->id];
                        $baget_count = intval($par_type / 2.5);
                        if (floatval($par_type / 2.5) > $baget_count) {
                            $baget_count++;
                        }
                        $par_type = $baget_count * 2.5;
                        $component_count[$items_559[0]->id] = $par_type;


                        $brus_count = intval($component_count[$items_1[0]->id] / 0.5);
                        if (floatval($component_count[$items_1[0]->id] / 0.5) > $brus_count) {
                            $brus_count++;
                        }
                        $component_count[$items_1[0]->id] = $brus_count * 0.5;

                        $brus_count = intval($component_count[$items_38[0]->id] / 0.5);
                        if (floatval($component_count[$items_38[0]->id] / 0.5) > $brus_count) {
                            $brus_count++;
                        }
                        $component_count[$items_38[0]->id] = $brus_count * 0.5;


                        */
        }

        //print_r($component_count); exit;
        //Клемы считаются блоками по 12 шт.
        /*$klems_count = intval( $component_count[$items_2[0]->id] / 12 );
        if ($component_count[$items_2[0]->id] % 6 > 0) {
            $klems_count++;
        }
        $component_count[$items_2[0]->id] = $klems_count;*/

        //---------------------------------- ВОЗВРАЩАЕМ СТОИМОСТЬ КОМПЛЕКТУЮЩИХ --------------------------------------//

        //Сюда считаем итоговую сумму полотна
        $canvases_data = array();
        if ($data['n1'] && $data['n2'] && $data['n3']) {


            $canvases_data['title'] = $canvases[$data['n3']]->texture_title . ", " . $canvases[$data['n3']]->name . " " . $canvases[$data['n3']]->width; //Название фактуры и полотна
            $canvases_data['quantity'] = $data['n4'];                                                                        //Кол-во
            $canvases_data['self_price'] = round($canvases[$data['n3']]->price, 2);                                    //Себестоимость
            $canvases_data['self_total'] = round($data['n4'] * $canvases_data['self_price'], 2);                            //Кол-во * Себестоимость

            //Стоимость с маржой ГМ (для дилера)
            $canvases_data['gm_price'] = margin($canvases[$data['n3']]->price, $gm_canvases_margin);
            //Кол-во * Стоимость с маржой ГМ (для дилера)
            $canvases_data['gm_total'] = round($data['n4'] * $canvases_data['gm_price'], 2);

            //Стоимость с маржой ГМ и дилера (для клиента)
            $canvases_data['dealer_price'] = double_margin($canvases[$data['n3']]->price, $gm_canvases_margin, $dealer_canvases_margin);
            //Кол-во * Стоимость с маржой ГМ и дилера (для клиента)
            $canvases_data['dealer_total'] = round($data['n4'] * $canvases_data['dealer_price'], 2);

        }
        $offcut_square_data = array();
        if ($data['n1'] && $data['n2'] && $data['n3'] && $data['offcut_square'] != 0) {


            $offcut_square_data['title'] = "Количество обрезков"; //Название фактуры и полотна
            $offcut_square_data['quantity'] = $data['offcut_square'];                                                                        //Кол-во
            $offcut_square_data['self_price'] = round($canvases[$data['n3']]->price / 2.5, 2);                                    //Себестоимость
            $offcut_square_data['self_total'] = round($data['offcut_square'] * $offcut_square_data['self_price'], 2);                            //Кол-во * Себестоимость

            //Стоимость с маржой ГМ (для дилера)
            $offcut_square_data['gm_price'] = round(margin($canvases[$data['n3']]->price, $gm_canvases_margin) / 2.5, 2);
            //Кол-во * Стоимость с маржой ГМ (для дилера)
            $offcut_square_data['gm_total'] = round($data['offcut_square'] * $offcut_square_data['gm_price'], 2);

            //Стоимость с маржой ГМ и дилера (для клиента)
            $offcut_square_data['dealer_price'] = round(double_margin($canvases[$data['n3']]->price, $gm_canvases_margin, 50) / 2.5, 2);
            //Кол-во * Стоимость с маржой ГМ и дилера (для клиента)
            $offcut_square_data['dealer_total'] = round($data['offcut_square'] * $offcut_square_data['dealer_price'], 2);

        }

        //print_r($offcut_square_data); exit;
        //Сюда считаем итоговую сумму компонентов
        $components_data = array();

        foreach ($component_count as $key => $cost) {
            $component_item = array();

            $component_item['title'] = $components[$key]->full_name;//. " ".$components[$key]->title ; 								//Название комплектующего
            $component_item['unit'] = $components[$key]->component_unit;                                //В чем измеряется
            $component_item['id'] = $components[$key]->id;                                                //ID
            if ($component_item['unit'] == "шт.") {
                $component_item['quantity'] = ceil($cost);                                                //Кол-во
            } else {
                $component_item['quantity'] = $component_count[$key];
            }
            $component_item['stack'] = 0;                                                                //Флаг, складывать ли этот компонент при сложении калькуляций

            $component_item['self_price'] = $components[$key]->price;                            //Себестоимость
            $component_item['self_total'] = round($component_item['self_price'] * $component_item['quantity'], 2);//Кол-во * Себестоимость

            //Стоимость с маржой ГМ (для дилера)
            $component_item['gm_price'] = margin($components[$key]->price, $gm_components_margin);
            //Кол-во * Стоимость с маржой ГМ (для дилера)
            $component_item['gm_total'] = round($component_item['quantity'] * $component_item['gm_price'], 2);

            //Стоимость с маржой ГМ и дилера (для клиента)
            $component_item['dealer_price'] = double_margin($components[$key]->price, $gm_components_margin, $dealer_components_margin);
            //Кол-во * Стоимость с маржой ГМ и дилера (для клиента)
            $component_item['dealer_total'] = round($component_item['quantity'] * $component_item['dealer_price'], 2);

            $components_data[] = $component_item;

        }
        //throw new Exception(implode("|||", $components_data[247]->title), 1);
        //добавляем щепотку дополнительных комплектующих
        $extra_components = json_decode($data['extra_components']);
        foreach ($extra_components as $extra_component) {
            $component_item = array();

            $component_item['title'] = $extra_component->title;                                        //Название комплектующего
            $component_item['unit'] = "шт.";                                                            //В чем измеряется
            $component_item['id'] = 0;                                                                    //ID
            $component_item['quantity'] = 1;
            $component_item['stack'] = 1;

            $component_item['self_price'] = $extra_component->value;                                    //Себестоимость
            $component_item['self_total'] = round($component_item['self_price'] * $component_item['quantity'], 2);//Кол-во * Себестоимость

            //Стоимость с маржой ГМ (для дилера)
            $component_item['gm_price'] = margin($component_item['self_price'], $gm_components_margin);
            //Кол-во * Стоимость с маржой ГМ (для дилера)
            $component_item['gm_total'] = round($component_item['quantity'] * $component_item['gm_price'], 2);

            //Стоимость с маржой ГМ и дилера (для клиента)
            $component_item['dealer_price'] = double_margin($component_item['self_price'], $gm_components_margin, $dealer_components_margin);
            //Кол-во * Стоимость с маржой ГМ и дилера (для клиента)
            $component_item['dealer_total'] = round($component_item['quantity'] * $component_item['dealer_price'], 2);

            $components_data[] = $component_item;
        }


        //header('Content-Type: text/html; charset=cp1251_general_ci');

        //---------------------------------- РАСЧЕТ СТОИМОСТИ МОНТАЖА --------------------------------------//
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select('a.*')
            ->from('#__gm_ceiling_mount AS a')
            ->where('a.user_id = ' . $dealer->id);
        $db->setQuery($query);
        $results = $db->loadObject();

        //Сюда мы складываем данные и стоимость монтажа ГМ и дилера
        $mounting_data = array();
        $guild_data = array();

        $count_svet = 0;
        $count_ventilation = 0;
        $count_ventilation_1 = 0;
        $count_diffuzor = 0;
        $count_truba = 0;
        $count_profil_1 = 0;
        $count_profil_2 = 0;
        $count_profil_3 = 0;
        $count_profil_4 = 0;

        if ($data['n1'] == 28 && $data['n9'] > 4) {
            //Обработка 1 угла
            if ($data['n9']) {
                $guild_data[] = array(
                    "title" => "Обработка 1 угла",                                                                    //Название
                    "quantity" => $data['n9'] - 4,                                                                //Кол-во
                    "gm_salary" => $results->mp20,                                                                //Себестоимость монтажа ГМ (зарплата монтажников)
                    "gm_salary_total" => ($data['n9'] - 4) * $results->mp20,                                            //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                    "dealer_salary" => $results->mp20,                                                        //Себестоимость монтажа дилера (зарплата монтажников)
                    "dealer_salary_total" => ($data['n9'] - 4) * $results->mp20                                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                );
                // $tmp += ($data['n9'] - 4) * $results->mp20;
            }
        }
        /*        if ($data['n1'] == 28 && $data['n10'] > 0) {
                    //криволинейный участок ТОЛЬКО ДЛЯ ПВХ
                    $guild_data[] = array(
                        "title" => "Криволинейный участок для ПВХ",                                                                    //Название
                        "quantity" => $data['n10'],                                                                //Кол-во
                        "gm_salary" => $results->mp21,                                                                //Себестоимость монтажа ГМ (зарплата монтажников)
                        "gm_salary_total" => $data['n10'] * $results->mp21,                                            //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                        "dealer_salary" => $results->mp21,                                                        //Себестоимость монтажа дилера (зарплата монтажников)
                        "dealer_salary_total" => $data['n10'] * $results->mp21                                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                    );
                    //$tmp += $data['n10'] * $results->mp21;

                }*/
        if ($data['n1'] == 28 && $data['n11'] > 0) {
            //внутренний вырез ТОЛЬКО ДЛЯ ПВХ
            $mounting_data[] = array(
                "title" => "Внутренний вырез для ПВХ",                                                                    //Название
                "quantity" => $data['n11'],                                                                //Кол-во
                "gm_salary" => $results->mp22,                                                                //Себестоимость монтажа ГМ (зарплата монтажников)
                "gm_salary_total" => $data['n11'] * $results->mp22,                                            //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                "dealer_salary" => $results->mp22,                                                        //Себестоимость монтажа дилера (зарплата монтажников)
                "dealer_salary_total" => $data['n11'] * $results->mp22                                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
            );
            //$tmp += $data['n11'] * $results->mp22;
        }

        //только для ПВХ
        if ($data['n1'] == 28) {
            //периметр
            if ($data['n5'] > 0 && $data['n28'] == 0) {
                $mounting_data[] = array(
                    "title" => "Периметр",                                                                    //Название
                    "quantity" => $data['n5'],                                                                //Кол-во
                    "gm_salary" => ($data['height'] == 1) ? ($results->mp1 + 10) : $results->mp1,                                                                //Себестоимость монтажа ГМ (зарплата монтажников)
                    "gm_salary_total" => $data['n5'] * (($data['height'] == 1) ? ($results->mp1 + 10) : $results->mp1),                                            //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                    "dealer_salary" => ($data['height'] == 1) ? ($results->mp1 + 10) : $results->mp1,                                                        //Себестоимость монтажа дилера (зарплата монтажников)
                    "dealer_salary_total" => $data['n5'] * (($data['height'] == 1) ? ($results->mp1 + 10) : $results->mp1)                                       //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                );
            }
            //периметр
            if ($data['n5'] > 0 && $data['n28'] == 1) {
                $mounting_data[] = array(
                    "title" => "Периметр",                                                                    //Название
                    "quantity" => $data['n5'],                                                                //Кол-во
                    "gm_salary" => ($data['height'] == 1) ? ($results->mp31 + 10) : $results->mp31,                                                                //Себестоимость монтажа ГМ (зарплата монтажников)
                    "gm_salary_total" => $data['n5'] * (($data['height'] == 1) ? ($results->mp31 + 10) : $results->mp31),                                            //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                    "dealer_salary" => ($data['height'] == 1) ? ($results->mp31 + 10) : $results->mp31,                                                        //Себестоимость монтажа дилера (зарплата монтажников)
                    "dealer_salary_total" => $data['n5'] * (($data['height'] == 1) ? ($results->mp31 + 10) : $results->mp31)                                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                );

            }
            //периметр
            if ($data['n5'] > 0 && $data['n28'] == 2) {
                $mounting_data[] = array(
                    "title" => "Периметр",                                                                    //Название
                    "quantity" => $data['n5'],                                                                //Кол-во
                    "gm_salary" => ($data['height'] == 1) ? ($results->mp32 + 10) : $results->mp32,                                                                //Себестоимость монтажа ГМ (зарплата монтажников)
                    "gm_salary_total" => $data['n5'] * (($data['height'] == 1) ? ($results->mp32 + 10) : $results->mp32),                                            //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                    "dealer_salary" => ($data['height'] == 1) ? ($results->mp32 + 10) : $results->mp32,                                                        //Себестоимость монтажа дилера (зарплата монтажников)
                    "dealer_salary_total" => $data['n5'] * (($data['height'] == 1) ? ($results->mp32 + 10) : $results->mp32)                                      //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                );

            }
//?????????????????????????????????????????????????????????????????? здесь тоже + 10рублей????
            if ($data['n31'] > 0) {
                $mounting_data[] = array(
                    "title" => "Периметр (внутренний вырез)",                                                                    //Название
                    "quantity" => $data['n31'],                                                                //Кол-во
                    "gm_salary" => $results->mp1,                                                                //Себестоимость монтажа ГМ (зарплата монтажников)
                    "gm_salary_total" => $data['n31'] * $results->mp1,                                            //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                    "dealer_salary" => $results->mp1,                                                        //Себестоимость монтажа дилера (зарплата монтажников)
                    "dealer_salary_total" => $data['n31'] * $results->mp1                                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                );

            }
            if ($data['n31'] > 0) {
                if ($data['color'] > 0) {
                    $color_model = Gm_ceilingHelpersGm_ceiling::getModel('color');
                    $color = $color_model->getData($data['color']);
                    $name = "Вставка (внутренний вырез), цвет: " . $color->colors_title;
                } else {
                    $name = "Вставка (внутренний вырез)";
                }
                $mounting_data[] = array(
                    "title" => $name,                                                                        //Название
                    "quantity" => $data['n31'],                                                                //Кол-во
                    "gm_salary" => $results->mp10,                                                                //Себестоимость монтажа ГМ (зарплата монтажников)
                    "gm_salary_total" => $data['n31'] * $results->mp10,                                            //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                    "dealer_salary" => $results->mp10,                                                        //Себестоимость монтажа дилера (зарплата монтажников)
                    "dealer_salary_total" => $data['n31'] * $results->mp10                                    //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                );
            }

            if ($data['n31'] > 0) {
                //внутренний вырез ТОЛЬКО ДЛЯ ПВХ
                $guild_data[] = array(
                    "title" => "Внутренний вырез(в цеху)",                                                                    //Название
                    "quantity" => $data['n31'],                                                                //Кол-во
                    "gm_salary" => $results->mp22,                                                                //Себестоимость монтажа ГМ (зарплата монтажников)
                    "gm_salary_total" => $data['n31'] * $results->mp22,                                            //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                    "dealer_salary" => $results->mp22,                                                        //Себестоимость монтажа дилера (зарплата монтажников)
                    "dealer_salary_total" => $data['n31'] * $results->mp22                                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                );
                //$tmp += $data['n11'] * $results->mp22;
            }

            //со вставкой
            if ($data['n6'] > 0) {
                if ($data['color'] > 0) {
                    $color_model = Gm_ceilingHelpersGm_ceiling::getModel('color');
                    $color = $color_model->getData($data['color']);
                    $name = "Вставка, цвет: " . $color->colors_title;
                } else {
                    $name = "Вставка";
                }
                $mounting_data[] = array(
                    "title" => $name,                                                                        //Название
                    "quantity" => $data['n5'],                                                                //Кол-во
                    "gm_salary" => $results->mp10,                                                                //Себестоимость монтажа ГМ (зарплата монтажников)
                    "gm_salary_total" => $data['n5'] * $results->mp10,                                            //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                    "dealer_salary" => $results->mp10,                                                        //Себестоимость монтажа дилера (зарплата монтажников)
                    "dealer_salary_total" => $data['n5'] * $results->mp10                                    //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                );
            }

            //установка люстры
            if ($data['n12'] > 0) {
                $mounting_data[] = array(
                    "title" => "Установка люстр",                                                                       //Название
                    "quantity" => $data['n12'],//$count_lust,															//Кол-во
                    "gm_salary" => $results->mp2,//max($gm->mp4, $gm->mp5),												//Себестоимость монтажа ГМ (зарплата монтажников)
                    "gm_salary_total" => $results->mp2 * $data['n12'],//$count_lust * max($gm->mp4, $gm->mp5),			//Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                    "dealer_salary" => $results->mp2,//max($dealer->mp4, $dealer->mp5),									//Себестоимость монтажа дилера (зарплата монтажников)
                    "dealer_salary_total" => $data['n12'] * $results->mp2//max($dealer->mp4, $dealer->mp5)				//Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                );
                //}
            }

            if ($del_flag == 0) {
                //Установка светильников

                if (count($n13) > 0) {

                    foreach ($n13 as $svet) {
                        if ($svet->n13_count > 0) {

                            $count_svet += $svet->n13_count;

                        }
                    }

                    if ($count_svet > 0) {
                        $mounting_data[] = array(
                            "title" => "Установка светильников",                                                //Название
                            "quantity" => $count_svet,                                                          //Кол-во
                            "gm_salary" => max($results->mp4, $results->mp5),                                   //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_svet * max($results->mp4, $results->mp5),               //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => max($results->mp4, $results->mp5),                               //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_svet * max($results->mp4, $results->mp5)            //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }
                }


                if (count($n22) > 0) {
                    foreach ($n22 as $ventilation) {
                        if ($ventilation->n22_count > 0 && ($ventilation->n22_type == 5 || $ventilation->n22_type == 6)) {
                            $count_ventilation += $ventilation->n22_count;
                        }
                        if ($ventilation->n22_count > 0 && ($ventilation->n22_type == 7 || $ventilation->n22_type == 8)) {
                            $count_ventilation_1 += $ventilation->n22_count;

                        }
                    }
                    if ($count_ventilation > 0) {
                        $mounting_data[] = array(
                            "title" => "Установка вентиляции",                                                    //Название
                            "quantity" => $count_ventilation,                                                    //Кол-во
                            "gm_salary" => $results->mp12,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_ventilation * $results->mp12,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp12,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_ventilation * $results->mp12                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }
                    if ($count_ventilation_1 > 0) {
                        $mounting_data[] = array(
                            "title" => "Установка электровытяжки",                                                    //Название
                            "quantity" => $count_ventilation_1,                                                    //Кол-во
                            "gm_salary" => $results->mp16,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_ventilation_1 * $results->mp16,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp16,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_ventilation_1 * $results->mp16                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }

                }

                if (count($n29) > 0) {
                    foreach ($n29 as $profil) {
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
                    }
                    if ($count_profil_1 > 0) {
                        $mounting_data[] = array(
                            "title" => "Переход уровня по прямой",                                                    //Название
                            "quantity" => $count_profil_1,                                                    //Кол-во
                            "gm_salary" => $results->mp23,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_profil_1 * $results->mp23,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp23,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_profil_1 * $results->mp23                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }
                    if ($count_profil_2 > 0) {
                        $mounting_data[] = array(
                            "title" => "Переход уровня по кривой",                                                    //Название
                            "quantity" => $count_profil_2,                                                    //Кол-во
                            "gm_salary" => $results->mp24,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_profil_2 * $results->mp24,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp24,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_profil_2 * $results->mp24                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }
                    if ($count_profil_3 > 0) {
                        $mounting_data[] = array(
                            "title" => "Переход уровня по прямой с нишей",                                                    //Название
                            "quantity" => $count_profil_3,                                                    //Кол-во
                            "gm_salary" => $results->mp25,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_profil_3 * $results->mp25,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp25,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_profil_3 * $results->mp25                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }
                    if ($count_profil_4 > 0) {
                        $mounting_data[] = array(
                            "title" => "Переход уровня по кривой с нишей",                                                    //Название
                            "quantity" => $count_profil_4,                                                    //Кол-во
                            "gm_salary" => $results->mp26,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_profil_4 * $results->mp26,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp26,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_profil_4 * $results->mp26                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }

                }
                // установка диффузора
                if (count($n23) > 0) {
                    foreach ($n23 as $diffuzor) {
                        if ($diffuzor->n23_count > 0) {
                            $count_diffuzor += $diffuzor->n23_count;

                        }
                    }
                    if ($count_diffuzor > 0) {
                        $mounting_data[] = array(
                            "title" => "Установка диффузора",                                                    //Название
                            "quantity" => $count_diffuzor,                                                    //Кол-во
                            "gm_salary" => $results->mp19,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_diffuzor * $results->mp19,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp19,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_diffuzor * $results->mp19                    //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }
                }
                //обвод трубы
                if (count($n14) > 0) {
                    foreach ($n14 as $truba) {
                        if ($truba->n14_count > 0) {
                            $count_truba += $truba->n14_count;

                        }
                    }
                    if ($count_truba > 0) {
                        $mounting_data[] = array(
                            "title" => "Обвод трубы",                                                    //Название
                            "quantity" => $count_truba,                                                    //Кол-во
                            "gm_salary" => $results->mp8,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_truba * $results->mp8,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp8,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_truba * $results->mp8                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }
                }

//коммент ниже не удалять!!!!!!!!!!!!!!!!!!!!!!!!!!1
                /*
                                if (count($n29) > 0) {
                                    if ($kolNP > 0 && $kolKP > 0) {
                                        if ($profil->n29_type == 12)
                                            $mounting_data[] = array(
                                                "title" => "Установка профиля КП + НП по прямой",                                                    //Название
                                                "quantity" => $kolNP,                                                    //Кол-во
                                                "gm_salary" => $results->mp27,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                                                "gm_salary_total" => $kolNP * $results->mp27,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                                                "dealer_salary" => $results->mp27,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                                                "dealer_salary_total" => $kolNP * $results->mp27                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                                            );
                                        else $mounting_data[] = array(
                                            "title" => "Установка профиля КП + НП по кривой",                                                    //Название
                                            "quantity" => $kolNP,                                                    //Кол-во
                                            "gm_salary" => $results->mp28,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                                            "gm_salary_total" => $kolNP * $results->mp28,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                                            "dealer_salary" => $results->mp28,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                                            "dealer_salary_total" => $kolNP * $results->mp278                       //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                                        );
                                    }
                                    if ($kolSP > 0 && $kolKP > 0) {
                                        $mounting_data[] = array(
                                            "title" => "Установка профиля КП + СП",                                                    //Название
                                            "quantity" => $kolSP,                                                    //Кол-во
                                            "gm_salary" => $results->mp29,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                                            "gm_salary_total" => $kolSP * $results->mp29,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                                            "dealer_salary" => $results->mp29,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                                            "dealer_salary_total" => $kolSP * $results->mp29                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                                        );

                                    }

                                    foreach ($n29 as $profil) {
                                        if ($profil->n29_count > 0) {
                                            if ($profil->n29_profil == 650 && $profil->n29_type == 12)
                                                $mounting_data[] = array(
                                                    "title" => "Установка профиля ПП по прямой",                                                    //Название
                                                    "quantity" => $profil->n29_count,                                                    //Кол-во
                                                    "gm_salary" => $results->mp23,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                                                    "gm_salary_total" => $profil->n29_count * $results->mp23,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                                                    "dealer_salary" => $results->mp23,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                                                    "dealer_salary_total" => $profil->n29_count * $results->mp23                       //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                                                );
                                            if ($profil->n29_profil == 650 && $profil->n29_type == 13)
                                                $mounting_data[] = array(
                                                    "title" => "Установка профиля ПП по кривой",                                                    //Название
                                                    "quantity" => $profil->n29_count,                                                    //Кол-во
                                                    "gm_salary" => $results->mp24,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                                                    "gm_salary_total" => $profil->n29_count * $results->mp24,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                                                    "dealer_salary" => $results->mp24,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                                                    "dealer_salary_total" => $profil->n29_count * $results->mp24                       //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                                                );
                                            if ($profil->n29_profil == 651 && $profil->n29_type == 12)
                                                $mounting_data[] = array(
                                                    "title" => "Установка профиля ПЛ по прямой",                                                    //Название
                                                    "quantity" => $profil->n29_count,                                                    //Кол-во
                                                    "gm_salary" => $results->mp25,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                                                    "gm_salary_total" => $profil->n29_count * $results->mp25,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                                                    "dealer_salary" => $results->mp25,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                                                    "dealer_salary_total" => $profil->n29_count * $results->mp25                       //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                                                );
                                            if ($profil->n29_profil == 651 && $profil->n29_type == 13)
                                                $mounting_data[] = array(
                                                    "title" => "Установка профиля ПЛ по кривой",                                                    //Название
                                                    "quantity" => $profil->n29_count,                                                    //Кол-во
                                                    "gm_salary" => $results->mp26,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                                                    "gm_salary_total" => $profil->n29_count * $results->mp26,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                                                    "dealer_salary" => $results->mp26,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                                                    "dealer_salary_total" => $profil->n29_count * $results->mp26                      //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                                                );
                                        }
                                    }
                                }*/
            } else {
                //Установка светильников
                if (count($n13) > 0) {

                    foreach ($n13 as $svet) {
                        if ($svet[0] > 0) {

                            $count_svet += $svet[0];

                        }
                    }
                    //throw new Exception($count_svet, 1);

                    //print_r($svet->n13_count); exit;
                    if ($count_svet > 0) {
                        $mounting_data[] = array(
                            "title" => "Установка светильников",                                                //Название
                            "quantity" => $count_svet,                                                            //Кол-во
                            "gm_salary" => max($results->mp4, $results->mp5),                                                //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_svet * max($results->mp4, $results->mp5),                            //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => max($results->mp4, $results->mp5),                                    //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_svet * max($results->mp4, $results->mp5)                //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }
                }


                if (count($n22) > 0) {
                    foreach ($n22 as $ventilation) {
                        if ($ventilation[0] > 0 && ($ventilation[1] == 5 || $ventilation[1] == 6)) {
                            $count_ventilation += $ventilation[0];
                        }
                        if ($ventilation[0] > 0 && ($ventilation[1] == 7 || $ventilation[1] == 8)) {
                            $count_ventilation_1 += $ventilation[0];

                        }
                    }
                    if ($count_ventilation > 0) {
                        $mounting_data[] = array(
                            "title" => "Установка вентиляции",                                                    //Название
                            "quantity" => $count_ventilation,                                                    //Кол-во
                            "gm_salary" => $results->mp12,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_ventilation * $results->mp12,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp12,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_ventilation * $results->mp12                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }
                    if ($count_ventilation_1 > 0) {
                        $mounting_data[] = array(
                            "title" => "Установка электровытяжки",                                                    //Название
                            "quantity" => $count_ventilation_1,                                                    //Кол-во
                            "gm_salary" => $results->mp16,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_ventilation_1 * $results->mp16,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp16,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_ventilation_1 * $results->mp16                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }

                }

                // установка диффузора
                if (count($n23) > 0) {
                    foreach ($n23 as $diffuzor) {
                        if ($diffuzor[0] > 0) {
                            $count_diffuzor += $diffuzor[0];

                        }
                    }
                    if ($count_diffuzor > 0) {
                        $mounting_data[] = array(
                            "title" => "Установка диффузора",                                                    //Название
                            "quantity" => $count_diffuzor,                                                    //Кол-во
                            "gm_salary" => $results->mp19,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_diffuzor * $results->mp19,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp19,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_diffuzor * $results->mp19                    //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }
                }
                //обвод трубы
                if (count($n14) > 0) {
                    foreach ($n14 as $truba) {
                        if ($truba[0] > 0) {
                            $count_truba += $truba[0];

                        }
                    }
                    if ($count_truba > 0) {
                        $mounting_data[] = array(
                            "title" => "Обвод трубы",                                                    //Название
                            "quantity" => $count_truba,                                                    //Кол-во
                            "gm_salary" => $results->mp8,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_truba * $results->mp8,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp8,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_truba * $results->mp8                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }
                }

                if (count($n29) > 0) {
                    foreach ($n29 as $profil) {
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
                    }
                    if ($count_profil_1 > 0) {
                        $mounting_data[] = array(
                            "title" => "Переход уровня по прямой",                                                    //Название
                            "quantity" => $count_profil_1,                                                    //Кол-во
                            "gm_salary" => $results->mp23,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_profil_1 * $results->mp23,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp23,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_profil_1 * $results->mp23                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }
                    if ($count_profil_2 > 0) {
                        $mounting_data[] = array(
                            "title" => "Переход уровня по кривой",                                                    //Название
                            "quantity" => $count_profil_2,                                                    //Кол-во
                            "gm_salary" => $results->mp24,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_profil_2 * $results->mp24,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp24,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_profil_2 * $results->mp24                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }
                    if ($count_profil_3 > 0) {
                        $mounting_data[] = array(
                            "title" => "Переход уровня по прямой с нишей",                                                    //Название
                            "quantity" => $count_profil_3,                                                    //Кол-во
                            "gm_salary" => $results->mp25,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_profil_3 * $results->mp25,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp25,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_profil_3 * $results->mp25                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }
                    if ($count_profil_4 > 0) {
                        $mounting_data[] = array(
                            "title" => "Переход уровня по кривой с нишей",                                                    //Название
                            "quantity" => $count_profil_4,                                                    //Кол-во
                            "gm_salary" => $results->mp26,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_profil_4 * $results->mp26,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp26,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_profil_4 * $results->mp26                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }

                }
                //не удалять!!!!!!!!!!!!!!!!!!!!!!!!!!1
                /*
                                if (count($n29) > 0) {
                                    if ($kolNP > 0 && $kolKP > 0) {
                                        if ($profil[1] == 12)
                                            $mounting_data[] = array(
                                                "title" => "Установка профиля КП + НП по прямой",                                                    //Название
                                                "quantity" => $kolNP,                                                    //Кол-во
                                                "gm_salary" => $results->mp27,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                                                "gm_salary_total" => $kolNP * $results->mp27,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                                                "dealer_salary" => $results->mp27,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                                                "dealer_salary_total" => $kolNP * $results->mp27                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                                            );
                                        else $mounting_data[] = array(
                                            "title" => "Установка профиля КП + НП по кривой",                                                    //Название
                                            "quantity" => $kolNP,                                                    //Кол-во
                                            "gm_salary" => $results->mp28,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                                            "gm_salary_total" => $kolNP * $results->mp28,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                                            "dealer_salary" => $results->mp28,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                                            "dealer_salary_total" => $kolNP * $results->mp278                       //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                                        );
                                    }
                                    if ($kolSP > 0 && $kolKP > 0) {
                                        $mounting_data[] = array(
                                            "title" => "Установка профиля КП + СП",                                                    //Название
                                            "quantity" => $kolSP,                                                    //Кол-во
                                            "gm_salary" => $results->mp29,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                                            "gm_salary_total" => $kolSP * $results->mp29,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                                            "dealer_salary" => $results->mp29,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                                            "dealer_salary_total" => $kolSP * $results->mp29                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                                        );

                                    }

                                    foreach ($n29 as $profil) {
                                        if ($profil[0] > 0) {
                                            if ($profil[2] == 650 && $profil[1] == 12)
                                                $mounting_data[] = array(
                                                    "title" => "Установка профиля ПП по прямой",                                                    //Название
                                                    "quantity" => $profil[0],                                                    //Кол-во
                                                    "gm_salary" => $results->mp23,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                                                    "gm_salary_total" => $profil[0] * $results->mp23,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                                                    "dealer_salary" => $results->mp23,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                                                    "dealer_salary_total" => $profil[0] * $results->mp23                       //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                                                );
                                            if ($profil[2] == 650 && $profil[1] == 13)
                                                $mounting_data[] = array(
                                                    "title" => "Установка профиля ПП по кривой",                                                    //Название
                                                    "quantity" => $profil[0],                                                    //Кол-во
                                                    "gm_salary" => $results->mp24,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                                                    "gm_salary_total" => $profil[0] * $results->mp24,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                                                    "dealer_salary" => $results->mp24,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                                                    "dealer_salary_total" => $profil[0] * $results->mp24                       //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                                                );
                                            if ($profil[2] == 651 && $profil[1] == 12)
                                                $mounting_data[] = array(
                                                    "title" => "Установка профиля ПЛ по прямой",                                                    //Название
                                                    "quantity" => $profil[0],                                                    //Кол-во
                                                    "gm_salary" => $results->mp25,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                                                    "gm_salary_total" => $profil[0] * $results->mp25,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                                                    "dealer_salary" => $results->mp25,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                                                    "dealer_salary_total" => $profil[0] * $results->mp25                       //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                                                );
                                            if ($profil[2] == 651 && $profil[1] == 13)
                                                $mounting_data[] = array(
                                                    "title" => "Установка профиля ПЛ по кривой",                                                    //Название
                                                    "quantity" => $profil[0],                                                    //Кол-во
                                                    "gm_salary" => $results->mp26,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                                                    "gm_salary_total" => $profil[0] * $results->mp26,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                                                    "dealer_salary" => $results->mp26,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                                                    "dealer_salary_total" => $profil[0] * $results->mp26                      //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                                                );
                                        }
                                    }
                                }
                            */
            }
            //шторный карниз
            if ($data['n27'] > 0) {
                $mounting_data[] = array(
                    "title" => "Шторный карниз",                                                    //Название
                    "quantity" => $data['n27'],                                                        //Кол-во
                    "gm_salary" => $results->mp11,                                                        //Себестоимость монтажа ГМ (зарплата монтажников)
                    "gm_salary_total" => $data['n27'] * $results->mp11,                                    //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                    "dealer_salary" => $results->mp11,                                                //Себестоимость монтажа дилера (зарплата монтажников)
                    "dealer_salary_total" => $data['n27'] * $results->mp11                            //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                );
            }

            //закладная брусом
            if ($data['n17'] > 0) {
                $mounting_data[] = array(
                    "title" => "Закладная брусом",                                                    //Название
                    "quantity" => $data['n17'],                                                        //Кол-во
                    "gm_salary" => $results->mp11,                                                        //Себестоимость монтажа ГМ (зарплата монтажников)
                    "gm_salary_total" => $data['n17'] * $results->mp11,                                    //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                    "dealer_salary" => $results->mp11,                                                //Себестоимость монтажа дилера (зарплата монтажников)
                    "dealer_salary_total" => $data['n17'] * $results->mp11                            //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                );
            }


            //пожарная сигнализация
            if ($data['n21'] > 0) {
                $mounting_data[] = array(
                    "title" => "Пожарная сигнализация",                                                //Название
                    "quantity" => $data['n21'],                                                        //Кол-во
                    "gm_salary" => $results->mp6,                                                        //Себестоимость монтажа ГМ (зарплата монтажников)
                    "gm_salary_total" => $data['n21'] * $results->mp6,                                    //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                    "dealer_salary" => $results->mp6,                                                //Себестоимость монтажа дилера (зарплата монтажников)
                    "dealer_salary_total" => $data['n21'] * $results->mp6                            //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                );
            }
            //парящий потолок
            if ($data['n30'] > 0) {
                $mounting_data[] = array(
                    "title" => "Парящий потолок",                                                //Название
                    "quantity" => $data['n30'],                                                        //Кол-во
                    "gm_salary" => $results->mp30,                                                        //Себестоимость монтажа ГМ (зарплата монтажников)
                    "gm_salary_total" => $data['n30'] * $results->mp30,                                    //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                    "dealer_salary" => $results->mp30,                                                //Себестоимость монтажа дилера (зарплата монтажников)
                    "dealer_salary_total" => $data['n30'] * $results->mp30                            //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                );
            }
            //разделитель
            if ($data['n20'] > 0) {
                $mounting_data[] = array(
                    "title" => "Разделитель",                                                        //Название
                    "quantity" => $data['n20'],                                                        //Кол-во
                    "gm_salary" => $results->mp9,                                                        //Себестоимость монтажа ГМ (зарплата монтажников)
                    "gm_salary_total" => $data['n20'] * $results->mp9,                                    //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                    "dealer_salary" => $results->mp9,                                                //Себестоимость монтажа дилера (зарплата монтажников)
                    "dealer_salary_total" => $data['n20'] * $results->mp9                            //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                );
            }


            if ($data['n32'] > 0) {
                $mounting_data[] = array(
                    "title" => "Слив воды",                                                        //Название
                    "quantity" => $data['n32'],                                                        //Кол-во
                    "gm_salary" => $results->mp27,                                                        //Себестоимость монтажа ГМ (зарплата монтажников)
                    "gm_salary_total" => $data['n32'] * $results->mp27,                                    //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                    "dealer_salary" => $results->mp27,                                                //Себестоимость монтажа дилера (зарплата монтажников)
                    "dealer_salary_total" => $data['n32'] * $results->mp27                            //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                );
            }

            /*   //транспортные расходы
               if ($data['transport']) {
                   $mounting_data[] = array(
                       "title" => "Транспортные расходы",                                                //Название
                       "quantity" => $data['transport'],                                                //Кол-во
                       "gm_salary" => $results->transport,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                       "gm_salary_total" => $data['transport'] * $results->transport,                        //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                       "dealer_salary" => $results->transport,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                       "dealer_salary_total" => $data['transport'] * $results->transport                //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                   );
               }
           }
           if ($data['distance'] && $data['distance_col']) {
               $mounting_data[] = array(
                   "title" => "Выезд за город ( км * кол-во раз)",                                                    //Название
                   "quantity" => $data['distance'] *  $data['distance_col'],                                                        //Кол-во
                   "gm_salary" => $results->distance,                                                        //Себестоимость монтажа ГМ (зарплата монтажников)
                   "gm_salary_total" => $data['distance'] * $data['distance_col'] * $results->distance ,                                    //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                   "dealer_salary" => $results->distance,                                                //Себестоимость монтажа дилера (зарплата монтажников)
                   "dealer_salary_total" => $data['distance']* $data['distance_col'] * $results->distance                             //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
               );
               //print_r($mounting_data);exit;
           }*/


        }


        //--------------------------------------- ПРОСЧЕТ МОНТАЖА ДЛЯ ТКАНИ -----------------------------

        if ($data['n1'] == 29) {
            //периметр
            if ($data['n5'] > 0) {
                $mounting_data[] = array(
                    "title" => "Периметр",                                                                    //Название
                    "quantity" => $data['n5'],                                                                //Кол-во
                    "gm_salary" => ($data['height'] == 1) ? ($results->mp33 + 10) : $results->mp33,                                                                //Себестоимость монтажа ГМ (зарплата монтажников)
                    "gm_salary_total" => $data['n5'] * (($data['height'] == 1) ? ($results->mp33 + 10) : $results->mp33),                                            //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                    "dealer_salary" => ($data['height'] == 1) ? ($results->mp33 + 10) : $results->mp33,                                                        //Себестоимость монтажа дилера (зарплата монтажников)
                    "dealer_salary_total" => $data['n5'] * (($data['height'] == 1) ? ($results->mp33 + 10) : $results->mp33)                                       //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                );

            }
            if ($data['n11'] > 0) {
                //внутренний вырез
                $mounting_data[] = array(
                    "title" => "Внутренний вырез",                                                                    //Название
                    "quantity" => $data['n11'],                                                                //Кол-во
                    "gm_salary" => $results->mp33,                                                                //Себестоимость монтажа ГМ (зарплата монтажников)
                    "gm_salary_total" => $data['n11'] * $results->mp33,                                            //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                    "dealer_salary" => $results->mp33,                                                        //Себестоимость монтажа дилера (зарплата монтажников)
                    "dealer_salary_total" => $data['n11'] * $results->mp33                                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                );
                //$tmp += $data['n11'] * $results->mp22;
            }

            if ($data['n9']) {
                $mounting_data[] = array(
                    "title" => "Обработка 1 угла",                                                                    //Название
                    "quantity" => $data['n9'],                                                                //Кол-во
                    "gm_salary" => $results->mp43,                                                                //Себестоимость монтажа ГМ (зарплата монтажников)
                    "gm_salary_total" => ($data['n9']) * $results->mp43,                                            //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                    "dealer_salary" => $results->mp43,                                                        //Себестоимость монтажа дилера (зарплата монтажников)
                    "dealer_salary_total" => ($data['n9']) * $results->mp43                                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                );
            }

            //установка люстры
            if ($data['n12'] > 0) {
                $mounting_data[] = array(
                    "title" => "Установка люстр",
                    "quantity" => $data['n12'],
                    "gm_salary" => $results->mp34,
                    "gm_salary_total" => $results->mp34 * $data['n12'],
                    "dealer_salary" => $results->mp34,
                    "dealer_salary_total" => $data['n12'] * $results->mp34
                );
                //}
            }

            //шторный карниз
            if ($data['n27'] > 0) {
                $mounting_data[] = array(
                    "title" => "Шторный карниз",                                                    //Название
                    "quantity" => $data['n27'],                                                        //Кол-во
                    "gm_salary" => $results->mp41,                                                        //Себестоимость монтажа ГМ (зарплата монтажников)
                    "gm_salary_total" => $data['n27'] * $results->mp41,                                    //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                    "dealer_salary" => $results->mp41,                                                //Себестоимость монтажа дилера (зарплата монтажников)
                    "dealer_salary_total" => $data['n27'] * $results->mp41                            //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                );
            }

            //закладная брусом 
            if ($data['n17'] > 0) {
                $mounting_data[] = array(
                    "title" => "Закладная брусом",                                                    //Название
                    "quantity" => $data['n17'],                                                        //Кол-во
                    "gm_salary" => $results->mp41,                                                        //Себестоимость монтажа ГМ (зарплата монтажников)
                    "gm_salary_total" => $data['n17'] * $results->mp41,                                    //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                    "dealer_salary" => $results->mp41,                                                //Себестоимость монтажа дилера (зарплата монтажников)
                    "dealer_salary_total" => $data['n17'] * $results->mp41                            //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                );
            }

            //пожарная сигнализация
            if ($data['n21'] > 0) {
                $mounting_data[] = array(
                    "title" => "Пожарная сигнализация",                                                //Название
                    "quantity" => $data['n21'],                                                        //Кол-во
                    "gm_salary" => $results->mp38,                                                        //Себестоимость монтажа ГМ (зарплата монтажников)
                    "gm_salary_total" => $data['n21'] * $results->mp38,                                    //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                    "dealer_salary" => $results->mp38,                                                //Себестоимость монтажа дилера (зарплата монтажников)
                    "dealer_salary_total" => $data['n21'] * $results->mp38                            //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                );
            }
            if ($del_flag == 0) {
                //Установка светильников
                if (count($n13) > 0) {

                    foreach ($n13 as $svet) {
                        if ($svet->n13_count > 0) {

                            $count_svet += $svet->n13_count;

                        }
                    }

                    if ($count_svet > 0) {
                        $mounting_data[] = array(
                            "title" => "Установка светильников",                                                //Название
                            "quantity" => $count_svet,                                                          //Кол-во
                            "gm_salary" => max($results->mp36, $results->mp37),                                   //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_svet * max($results->mp36, $results->mp37),               //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => max($results->mp36, $results->mp37),                               //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_svet * max($results->mp36, $results->mp37)            //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }
                }


                if (count($n22) > 0) {
                    foreach ($n22 as $ventilation) {
                        if ($ventilation->n22_count > 0 && ($ventilation->n22_type == 5 || $ventilation->n22_type == 6)) {
                            $count_ventilation += $ventilation->n22_count;
                        }
                        if ($ventilation->n22_count > 0 && ($ventilation->n22_type == 7 || $ventilation->n22_type == 8)) {
                            $count_ventilation_1 += $ventilation->n22_count;

                        }
                    }
                    if ($count_ventilation > 0) {
                        $mounting_data[] = array(
                            "title" => "Установка вентиляции",                                                    //Название
                            "quantity" => $count_ventilation,                                                    //Кол-во
                            "gm_salary" => $results->mp42,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_ventilation * $results->mp42,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp42,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_ventilation * $results->mp42                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }
                    if ($count_ventilation_1 > 0) {
                        $mounting_data[] = array(
                            "title" => "Установка электровытяжки",                                                    //Название
                            "quantity" => $count_ventilation_1,                                                    //Кол-во
                            "gm_salary" => $results->mp16,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_ventilation_1 * $results->mp16,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp16,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_ventilation_1 * $results->mp16                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }

                }

                if (count($n29) > 0) {
                    foreach ($n29 as $profil) {
                        if ($profil->n29_count > 0 && $profil->n29_type == 12) {
                            $count_profil_1 += $profil->n29_count;
                        }
                        if ($profil->n29_count > 0 && $profil->n29_type == 15) {
                            $count_profil_3 += $profil->n29_count;
                        }

                    }
                    if ($count_profil_1 > 0) {
                        $mounting_data[] = array(
                            "title" => "Переход уровня по прямой",                                                    //Название
                            "quantity" => $count_profil_1,                                                    //Кол-во
                            "gm_salary" => $results->mp23,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_profil_1 * $results->mp23,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp23,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_profil_1 * $results->mp23                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }

                    if ($count_profil_3 > 0) {
                        $mounting_data[] = array(
                            "title" => "Переход уровня по прямой с нишей",                                                    //Название
                            "quantity" => $count_profil_3,                                                    //Кол-во
                            "gm_salary" => $results->mp25,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_profil_3 * $results->mp25,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp25,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_profil_3 * $results->mp25                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }


                }
                // установка диффузора
                if (count($n23) > 0) {
                    foreach ($n23 as $diffuzor) {
                        if ($diffuzor->n23_count > 0) {
                            $count_diffuzor += $diffuzor->n23_count;

                        }
                    }
                    if ($count_diffuzor > 0) {
                        $mounting_data[] = array(
                            "title" => "Установка диффузора",                                                    //Название
                            "quantity" => $count_diffuzor,                                                    //Кол-во
                            "gm_salary" => $results->mp19,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_diffuzor * $results->mp19,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp19,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_diffuzor * $results->mp19                    //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }
                }
                //обвод трубы
                if (count($n14) > 0) {
                    foreach ($n14 as $truba) {
                        if ($truba->n14_count > 0) {
                            $count_truba += $truba->n14_count;

                        }
                    }
                    if ($count_truba > 0) {
                        $mounting_data[] = array(
                            "title" => "Обвод трубы",                                                    //Название
                            "quantity" => $count_truba,                                                    //Кол-во
                            "gm_salary" => $results->mp40,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_truba * $results->mp40,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp40,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_truba * $results->mp40                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }
                }

            } else {
                //Установка светильников
                if (count($n13) > 0) {

                    foreach ($n13 as $svet) {
                        if ($svet[0] > 0) {

                            $count_svet += $svet[0];

                        }
                    }
                    //throw new Exception($count_svet, 1);

                    //print_r($svet->n13_count); exit;
                    if ($count_svet > 0) {
                        $mounting_data[] = array(
                            "title" => "Установка светильников",                                                //Название
                            "quantity" => $count_svet,                                                            //Кол-во
                            "gm_salary" => max($results->mp36, $results->mp37),                                                //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_svet * max($results->mp36, $results->mp37),                            //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => max($results->mp36, $results->mp37),                                    //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_svet * max($results->mp36, $results->mp37)                //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }
                }


                if (count($n22) > 0) {
                    foreach ($n22 as $ventilation) {
                        if ($ventilation[0] > 0 && ($ventilation[1] == 5 || $ventilation[1] == 6)) {
                            $count_ventilation += $ventilation[0];
                        }
                        if ($ventilation[0] > 0 && ($ventilation[1] == 7 || $ventilation[1] == 8)) {
                            $count_ventilation_1 += $ventilation[0];

                        }
                    }
                    if ($count_ventilation > 0) {
                        $mounting_data[] = array(
                            "title" => "Установка вентиляции",                                                    //Название
                            "quantity" => $count_ventilation,                                                    //Кол-во
                            "gm_salary" => $results->mp42,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_ventilation * $results->mp42,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp42,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_ventilation * $results->mp42                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }
                    if ($count_ventilation_1 > 0) {
                        $mounting_data[] = array(
                            "title" => "Установка электровытяжки",                                                    //Название
                            "quantity" => $count_ventilation_1,                                                    //Кол-во
                            "gm_salary" => $results->mp16,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_ventilation_1 * $results->mp16,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp16,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_ventilation_1 * $results->mp16                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }

                }

                // установка диффузора
                if (count($n23) > 0) {
                    foreach ($n23 as $diffuzor) {
                        if ($diffuzor[0] > 0) {
                            $count_diffuzor += $diffuzor[0];

                        }
                    }
                    if ($count_diffuzor > 0) {
                        $mounting_data[] = array(
                            "title" => "Установка диффузора",                                                    //Название
                            "quantity" => $count_diffuzor,                                                    //Кол-во
                            "gm_salary" => $results->mp19,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_diffuzor * $results->mp19,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp19,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_diffuzor * $results->mp19                    //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }
                }
                //обвод трубы
                if (count($n14) > 0) {
                    foreach ($n14 as $truba) {
                        if ($truba[0] > 0) {
                            $count_truba += $truba[0];

                        }
                    }
                    if ($count_truba > 0) {
                        $mounting_data[] = array(
                            "title" => "Обвод трубы",                                                    //Название
                            "quantity" => $count_truba,                                                    //Кол-во
                            "gm_salary" => $results->mp40,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_truba * $results->mp40,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp40,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_truba * $results->mp40                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }
                }

                if (count($n29) > 0) {
                    foreach ($n29 as $profil) {
                        if ($profil[0] > 0 && $profil[1] == 12) {
                            $count_profil_1 += $profil[0];
                        }

                        if ($profil[0] > 0 && $profil[1] == 15) {
                            $count_profil_3 += $profil[0];
                        }

                    }
                    if ($count_profil_1 > 0) {
                        $mounting_data[] = array(
                            "title" => "Переход уровня по прямой",                                                    //Название
                            "quantity" => $count_profil_1,                                                    //Кол-во
                            "gm_salary" => $results->mp23,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_profil_1 * $results->mp23,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp23,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_profil_1 * $results->mp23                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }

                    if ($count_profil_3 > 0) {
                        $mounting_data[] = array(
                            "title" => "Переход уровня по прямой с нишей",                                                    //Название
                            "quantity" => $count_profil_3,                                                    //Кол-во
                            "gm_salary" => $results->mp25,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                            "gm_salary_total" => $count_profil_3 * $results->mp25,                                //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                            "dealer_salary" => $results->mp25,                                            //Себестоимость монтажа дилера (зарплата монтажников)
                            "dealer_salary_total" => $count_profil_3 * $results->mp25                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
                        );
                    }


                }

            }


        }
        //----------------------------- Общие просчеты и для ПВХ и для ткани ----------------------------------------


        //крепление в плитку
        if ($data['n7'] > 0) {
            $mounting_data[] = array(
                "title" => "Крепление в плитку",                                                        //Название
                "quantity" => $data['n7'],                                                                //Кол-во
                "gm_salary" => $results->mp13,                                                                //Себестоимость монтажа ГМ (зарплата монтажников)
                "gm_salary_total" => $data['n7'] * $results->mp13,                                            //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                "dealer_salary" => $results->mp13,                                                        //Себестоимость монтажа дилера (зарплата монтажников)
                "dealer_salary_total" => $data['n7'] * $results->mp13                                    //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
            );
        }
        //крепление в керамогранит
        if ($data['n8'] > 0) {
            $mounting_data[] = array(
                "title" => "Крепление в керамогранит",                                                    //Название
                "quantity" => $data['n8'],                                                                //Кол-во
                "gm_salary" => $results->mp14,                                                                //Себестоимость монтажа ГМ (зарплата монтажников)
                "gm_salary_total" => $data['n8'] * $results->mp14,                                            //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                "dealer_salary" => $results->mp14,                                                        //Себестоимость монтажа дилера (зарплата монтажников)
                "dealer_salary_total" => $data['n8'] * $results->mp14                                    //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
            );
        }
        //укрепление стены
        if ($data['n18'] > 0) {
            $mounting_data[] = array(
                "title" => "Укрепление стены",                                                    //Название
                "quantity" => $data['n18'],                                                        //Кол-во
                "gm_salary" => $results->mp15,                                                        //Себестоимость монтажа ГМ (зарплата монтажников)
                "gm_salary_total" => $data['n18'] * $results->mp15,                                    //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                "dealer_salary" => $results->mp15,                                                //Себестоимость монтажа дилера (зарплата монтажников)
                "dealer_salary_total" => $data['n18'] * $results->mp15                            //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
            );
        }
        //сложность доступа к месту установки
        if ($data['n24'] > 0) {
            $mounting_data[] = array(
                "title" => "Сложность доступа",                                                    //Название
                "quantity" => $data['n24'],                                                        //Кол-во
                "gm_salary" => $results->mp17,                                                        //Себестоимость монтажа ГМ (зарплата монтажников)
                "gm_salary_total" => $data['n24'] * $results->mp17,                                    //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                "dealer_salary" => $results->mp17,                                                //Себестоимость монтажа дилера (зарплата монтажников)
                "dealer_salary_total" => $data['n24'] * $results->mp17                            //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
            );
        }

        //сложность доступа к месту установки
        if ($data['dop_krepezh'] > 0) {
            $mounting_data[] = array(
                "title" => "Дополнительный крепеж",                                                //Название
                "quantity" => $data['dop_krepezh'],                                                //Кол-во
                "gm_salary" => $results->mp18,                                                        //Себестоимость монтажа ГМ (зарплата монтажников)
                "gm_salary_total" => $data['dop_krepezh'] * $results->mp18,                            //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                "dealer_salary" => $results->mp18,                                                //Себестоимость монтажа дилера (зарплата монтажников)
                "dealer_salary_total" => $data['dop_krepezh'] * $results->mp18                    //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
            );
        }
        //Дополнительный монтаж
        $extra_mounting = json_decode($data['extra_mounting']);
        foreach ($extra_mounting as $extra_mount) {
            $mounting_data[] = array(
                "title" => $extra_mount->title,                                                        //Название
                "quantity" => 1,                                                                    //Кол-во
                "gm_salary" => $extra_mount->value,                                                    //Себестоимость монтажа ГМ (зарплата монтажников)
                "gm_salary_total" => $extra_mount->value,                                            //Кол-во * себестоимость монтажа ГМ (зарплата монтажников)
                "dealer_salary" => $extra_mount->value,                                                //Себестоимость монтажа дилера (зарплата монтажников)
                "dealer_salary_total" => $extra_mount->value                                        //Кол-во * себестоимость монтажа дилера (зарплата монтажников)
            );
        }
//print_r($mounting_data);exit;
        //print_r($mounting_data); exit;
        //Добавление маржи ГМ и дилера и округление
        for ($i = 0; $i < count($mounting_data); $i++) {
            $mounting_data[$i]['gm_salary_total'] = round($mounting_data[$i]['gm_salary_total'], 2); //Округление зарплаты монтажников ГМ
            $mounting_data[$i]['dealer_salary_total'] = round($mounting_data[$i]['dealer_salary_total'], 2); //Округление зарплаты монтажников дилера

            //Добавление маржи ГМ, если монтаж производит ГМ
            $mounting_data[$i]['price_with_gm_margin'] = margin($mounting_data[$i]['gm_salary'], $gm_mounting_margin);
            $mounting_data[$i]['total_with_gm_margin'] = round($mounting_data[$i]['quantity'] * $mounting_data[$i]['price_with_gm_margin'], 2);

            //Добавление маржи ГМ и дилера, если монтаж производит Дилер с помощью ГМ
            $mounting_data[$i]['price_with_gm_dealer_margin'] = double_margin($mounting_data[$i]['gm_salary'], $gm_mounting_margin, $dealer_mounting_margin);
            $mounting_data[$i]['total_with_gm_dealer_margin'] = round($mounting_data[$i]['quantity'] * $mounting_data[$i]['price_with_gm_dealer_margin'], 2);

            //Добавление маржи дилера, если монтаж производит Дилер с помощью ГМ
            $mounting_data[$i]['price_with_dealer_margin'] = margin($mounting_data[$i]['dealer_salary'], $dealer_mounting_margin);
            $mounting_data[$i]['total_with_dealer_margin'] = round($mounting_data[$i]['quantity'] * $mounting_data[$i]['price_with_dealer_margin'], 2);
        }

        for ($i = 0; $i < count($guild_data); $i++) {
            $guild_data[$i]['gm_salary_total'] = round($guild_data[$i]['gm_salary_total'], 2); //Округление зарплаты монтажников ГМ
            $guild_data[$i]['dealer_salary_total'] = round($guild_data[$i]['dealer_salary_total'], 2); //Округление зарплаты монтажников дилера

            //Добавление маржи ГМ, если монтаж производит ГМ
            $guild_data[$i]['price_with_gm_margin'] = margin($guild_data[$i]['gm_salary'], $gm_canvases_margin);
            $guild_data[$i]['total_with_gm_margin'] = round($guild_data[$i]['quantity'] * $guild_data[$i]['price_with_gm_margin'], 2);

            //Добавление маржи ГМ и дилера, если монтаж производит Дилер с помощью ГМ
            $guild_data[$i]['price_with_gm_dealer_margin'] = double_margin($guild_data[$i]['gm_salary'], $gm_canvases_margin, $dealer_canvases_margin);
            $guild_data[$i]['total_with_gm_dealer_margin'] = round($guild_data[$i]['quantity'] * $guild_data[$i]['price_with_gm_dealer_margin'], 2);

            //Добавление маржи дилера, если монтаж производит Дилер с помощью ГМ
            $guild_data[$i]['price_with_dealer_margin'] = margin($guild_data[$i]['dealer_salary'], $dealer_canvases_margin);
            $guild_data[$i]['total_with_dealer_margin'] = round($guild_data[$i]['quantity'] * $guild_data[$i]['price_with_dealer_margin'], 2);
        }
        //Итоговая сумма компонентов
        $total_sum = 0;
//print_r($data); exit;
        //Прибавляем к подсчету комплектующие
        $components_sum = 0;
        $gm_components_sum = 0;
        $dealer_components_sum = 0;
        //print_r($components_data); exit;
        foreach ($components_data as $component_item) {
            $components_sum += $component_item['self_total'];
            $gm_components_sum += $component_item['gm_total'];
            $dealer_components_sum += $component_item['dealer_total'];
        }
        //...и монтаж дилера с помощью ГМ
        $total_gm_mounting = 0;
        $total_dealer_mounting = 0;
        $total_with_gm_margin = 0;
        $total_with_gm_dealer_margin = 0;
        $total_with_dealer_margin = 0;
        $total_gm_guild = 0;
        $total_dealer_guild = 0;
        $total_with_gm_dealer_margin_guild = 0;
        $total_with_gm_margin_guild = 0;
        $total_with_dealer_margin_guild = 0;

        if ($need_mount) {

            foreach ($mounting_data as $mounting_item) {
                $total_gm_mounting += $mounting_item['gm_salary_total'];
                $total_dealer_mounting += $mounting_item['dealer_salary_total'];
                $total_with_gm_margin += $mounting_item['total_with_gm_margin'];
                $total_with_gm_dealer_margin += $mounting_item['total_with_gm_dealer_margin'];
                $total_with_dealer_margin += $mounting_item['total_with_dealer_margin'];
            }
        }
        foreach ($guild_data as $guild) {
            $total_gm_guild += $guild['gm_salary_total'];
            $total_dealer_guild += $guild['dealer_salary_total'];
            $total_with_gm_margin_guild += $guild['total_with_gm_margin'];
            $total_with_gm_dealer_margin_guild += $guild['total_with_gm_dealer_margin'];
            $total_with_dealer_margin_guild += $guild['total_with_dealer_margin'];
        }


        //Дебаг он и Африке дебаг
        /*$debug = $jinput->get('debug', '0', 'INT');
        if($debug == 1) {

        }*/
        //throw new Exception($need_mount);

        //throw  new  Exception($components_sum);
        //Получаем скидку
        // $new_discount = $data['project_discount'];
        $new_discount = $data['discount'];
        //Сюда забиваем ответ в JSON
        $ajax_return = array();
        $ajax_return['total_sum'] = round($canvases_data['dealer_total'] + $offcut_square_data['dealer_total'] + $dealer_components_sum + $total_with_gm_dealer_margin + $total_with_gm_dealer_margin_guild, 2);
        //throw new Exception($total_with_gm_dealer_margin , 1);
        $ajax_return['project_discount'] = $new_discount;
        /* if ($data['project_id']) {

             $ajax_return['project_discount'] = $new_discount; //($new_discount != -1)?$new_discount:0;///* Данные до 18.08 *///$project->project_discount;
        //} else {
        // $ajax_return['project_discount'] = $new_discount; //($new_discount != -1)?$new_discount:0;///* Данные до 18.08 *//$dealer->discount;
        // }

        $ajax_return['canvases_sum'] = $canvases_data['self_total'] + $offcut_square_data['self_total'];
        $ajax_return['components_sum'] = $components_sum;
        $ajax_return['mounting_sum'] = $total_gm_mounting;
        //$ajax_return['mounting_sum'] = $total_dealer_mounting;

        $data['canvases_sum'] = $canvases_data['self_total'] + $offcut_square_data['self_total'] + $total_gm_guild;
        $data['components_sum'] = $components_sum;
        $data['mounting_sum'] = $total_gm_mounting;
        //$data['mounting_sum'] = $total_dealer_mounting;

        $data['project_discount'] = $dealer->discount;

        $data["state"] = 1;
        $data["checked_out"] = 0;
        $data["checked_out_time"] = "00.00.0000 00:00";
        $data["created_by"] = $user->id;
        $data["modified_by"] = $user->id;

        if (empty($data['dealer_id'])) {
            //$data["dealer_id"] = $dealer_id;
        }

        $db = JFactory::getDBO();
        $query = 'SELECT `id` FROM `#__gm_ceiling_calculations` WHERE `project_id` = ' . (int)$data['project_id'] . ' AND `calculation_title` LIKE  \'%Потолок%\'';
        $db->setQuery($query);
        $calculations = $db->loadObjectList();
        $k = count($calculations);
        if ($k > 0) {

            if (empty($data['calculation_title']))
                $data['calculation_title'] = "Потолок " . $k;
        } else if (empty($data['calculation_title']))
            $data['calculation_title'] = "Потолок 1";
        /*
                //Транспортные расходы
                if ($data['project_id'] <= 0) {
                    //Если калькуляция еще не привязана к договору, то добавляем транспортные, если не указано обратного
                    if ($data['transport'] < 1) {
                        $data['transport'] = 1;
                    }
                } else {
                    //Если калькуляция привязана к договору, то назначаем транспортные только если калькуляция первая.
                    //Если не первая, то автоматически не добавляем
                    $db = JFactory::getDBO();
                    $query = 'SELECT `id` FROM `#__gm_ceiling_calculations` WHERE `project_id` = ' . (int)$data['project_id'];
                    $db->setQuery($query);
                    $calculations = $db->loadObjectList();
                    if (count($calculations) > 0) {
                        if ($calculations[0]->id == $data['id']) {
                            if ($data['transport'] < 1) {
                                $data['transport'] = 1;
                            }
                        }
                    } else {
                        if ($data['transport'] < 1) {
                            $data['transport'] = 1;
                        }
                    }
                }
                 if($data['transport'] == 1) {
                    if (!empty($data['distance']) && !empty($data['distance_col']))
                     $data['transport'] = 0;
                }
                else {
                    if (!empty($data['distance']) && !empty($data['distance_col'])) {
                        $data['distance'] = 0;
                        $data['distance_col'] = 0;
                    }

                }*/
        //throw new Exception(($components_data[636]), 1);

        //Сохранение калькуляции
        $calculation_model = Gm_ceilingHelpersGm_ceiling::getModel('CalculationForm', 'Gm_ceilingModel');

        if ($save == 1) {
            $tmp_filename = $data['sketch_name'];
            $tmp_cut_filename = $data['cut_name'];
            $tmp_original_filename = $data['original_name'];
            if (is_file($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $tmp_filename . ".png")) {
                // $data['calc_image'] = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $tmp_filename . ".png");
            }
            if (is_file($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $tmp_filename . ".txt")) {
                $data['calc_data'] = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $tmp_filename . ".txt");
            }
            if (is_file($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $tmp_filename . ".png")) {
                // $data['cut_image'] = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $tmp_cut_filename . ".png");
            }
            if (is_file($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $tmp_filename . ".txt")) {
                $data['cut_data'] = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $tmp_cut_filename . ".txt");
            }
            if (is_file($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $tmp_original_filename . ".txt")) {
                $data['original_sketch'] = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $tmp_original_filename . ".txt");
            }
            if ($new_client != 1) {
                $ajax_return['id'] = $calculation_model->save($data, $del_flag);
            }

            $filename = md5("calculation_sketch" . $ajax_return['id']);
            $cut_filename = md5("cut_sketch" . $ajax_return['id']);
            if (is_file($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $tmp_filename . ".png")) {
                rename($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $tmp_filename . ".png", $_SERVER['DOCUMENT_ROOT'] . "/calculation_images/" . $filename . ".png");
            }
            if (is_file($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $tmp_cut_filename . ".png")) {
                rename($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $tmp_cut_filename . ".png", $_SERVER['DOCUMENT_ROOT'] . "/cut_images/" . $cut_filename . ".png");

            }
        }

        if ($new_client == 1) {
            $clients = $calculation_model->add_client($data);
            if (!empty($clients)) {
                $project_client = $calculation_model->add_project($data, $clients);

                $data['project_id'] = $project_client;
                //$data['project_discount'] = 30;
                $data['discount'] = 30;
                $tmp_filename = $data['sketch_name'];
                $tmp_cut_filename = $data['cut_name'];
                $tmp_original_filename = $data['original_name'];
                if (is_file($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $tmp_filename . ".png")) {
                    $data['calc_image'] = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $tmp_filename . ".png");
                }
                if (is_file($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $tmp_filename . ".txt")) {
                    $data['calc_data'] = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $tmp_filename . ".txt");
                }
                if (is_file($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $tmp_filename . ".png")) {
                    $data['cut_image'] = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $tmp_cut_filename . ".png");
                }
                if (is_file($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $tmp_filename . ".txt")) {
                    $data['cut_data'] = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $tmp_cut_filename . ".txt");
                }
                if (is_file($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $tmp_filename . ".txt")) {

                    $data['original_sketch'] = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $tmp_original_filename . ".txt");
                }
              
                if ($new_client != 1) {
                    
                    $ajax_return['id'] = $calculation_model->save($data, $del_flag);
                }

                $filename = md5("calculation_sketch" . $ajax_return['id']);
                $cut_filename = md5("cut_sketch" . $ajax_return['id']);
              
                if (is_file($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $tmp_filename . ".png")) {
                    rename($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $tmp_filename . ".png", $_SERVER['DOCUMENT_ROOT'] . "/calculation_images/" . $filename . ".png");

                }

            }
          
            
            //throw new Exception($ajax_return['id']);
            //throw new Exception($pdf);
            $project_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
            $phone = $project_model->getClientPhones($project->id_client);
            $mount = $project_model->getMount($project->id);
            if(!empty($mount->id)) $mount_name = $project_model->getMounterBrigade($mount->id);

            //Пошла печать PDF
            if ($pdf == 1) {
                $sheets_dir = $_SERVER['DOCUMENT_ROOT'] . '/costsheets/';
              

        }
        //throw new Exception($ajax_return['id']);
        //throw new Exception($pdf);
        $project_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
        $phone = $project_model->getClientPhones($project->id_client);
        $mount = $project_model->getMount($project->id);
        if (!empty($mount->id)) $mount_name = $project_model->getMounterBrigade($mount->id);

        //Пошла печать PDF
        if ($pdf == 1) {
            $sheets_dir = $_SERVER['DOCUMENT_ROOT'] . '/costsheets/';

            //---------------------------------- ПОДГОТАВЛИВАЕМ ПЕЧАТЬ НАРЯДА МОНТАЖНОЙ БРИГАДЕ ГМ --------------------------------------//
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


            if (isset($phone)) {
                $html .= "<b>Телефон: </b>";
                foreach ($phone AS $k => $contact) {
                    $html .= $contact->phone . (($k < count($phone) - 1) ? " , " : "<br>");
                }
            }
            if (isset($project->project_info)) {
                if ($project->project_info) {
                    $html .= "<b>Адрес: </b>" . $project->project_info . "<br>";
                }
            }
            if (isset($calculation_title)) {
                if ($calculation_title) {
                    $html .= "<b>Потолок: </b>" . $calculation_title . "<br>";
                }
            }

            if (isset($mount->name)) {
                if ($mount->name) {
                    $html .= "<b>Монтажная бригада: </b>" . $mount->name . "<br>";
                }
            }
            if (isset($mount_name)) {
                $html .= "<b>Состав монтажной бригады: </b>";
                foreach ($mount_name AS $k => $value) {
                    $html .= $value->name . (($k < count($mount_name) - 1) ? " , " : " ");
                }
                $html .= "<br>";
                // foreach($mount_name as $value) $html .= $value->name." ,";


            }
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
            } /*if(isset($project->project_mounting_from)&&isset($project->project_mounting_to)){
				if(isset($project->project_mounting_from)) {
					if($project->project_mounting_from != "0000-00-00 00:00:00") {
						//$jdate =  $project->project_mounting_from;
						//if($jdate != "0000-00-00 00:00:00"){
						$jdate = new JDate(JFactory::getDate($project->project_mounting_from));
						$html .= "<b>Дата начала монтажа: </b>" .$jdate->format('d.m.Y  H:i') . "<br>";	}
						else 	$html .= "<b>Дата начала монтажа: </b>  не указано <br>";
				}
				if(isset($project->project_mounting_to)) {
					if($project->project_mounting_to != "0000-00-00 00:00:00") {
						//$jdate = $project->project_mounting_to;
						//if($jdate != "0000-00-00 00:00:00")
						$jdate = new JDate(JFactory::getDate($project->project_mounting_to));
						$html .= "<b>Дата окончания монтажа: </b>" .$jdate->format('d.m.Y  H:i') . "<br>";
					} else 	$html .= "<b>Дата окончания монтажа: </b>  не указано <br>";
				}
			}*/
            if ($project->project_mounting_date != '0000-00-00 00:00:00') {
                //$jdate = $project->project_mounting_date;
                $jdate = new JDate(JFactory::getDate($project->project_mounting_date));
                $html .= "<b>Дата монтажа: </b>" . $jdate->format('d.m.Y  H:i') . "<br>";
                //$html .= "<b>Дата монтажа: </b>" . $project->project_mounting_date . "<br>";
            }


            if ($need_mount) {
                $html .= '<p>&nbsp;</p>
			<h1>Наряд монтажной бригаде</h1>
			<h2>Дата: ' . date("d.m.Y") . '</h2>
			<img src="' . $_SERVER['DOCUMENT_ROOT'] . "/calculation_images/" . md5("calculation_sketch" . $ajax_return['id']) . ".png" . '" style="width: 100%; max-height: 800px;"/>
			<table border="0" cellspacing="0" width="100%">
			<tbody>
				<tr>
					<th>Наименование</th>
					<th class="center">Цена, руб.</th>
					<th class="center">Кол-во</th>
					<th class="center">Стоимость, руб.</th>
				</tr>';

                if ($project->who_mounting == 1) {
                    foreach ($mounting_data as $item) {
                        //if ($item['title'] != "Обработка 1 угла" && $item['title'] != "Криволинейный участок для ПВХ" && $item['title'] != "Внутренний вырез для ПВХ") {
                        $html .= '<tr>';
                        $html .= '<td>' . $item['title'] . '</td>';
                        $html .= '<td class="center">' . round($item['gm_salary'], 2) . '</td>';
                        $html .= '<td class="center">' . $item['quantity'] . '</td>';
                        $html .= '<td class="center">' . round($item['gm_salary_total'], 2) . '</td>';
                        $html .= '</tr>';
                        //}
                    }
                    $html .= '<tr><th colspan="3" class="right">Итого, руб:</th><th class="center">' . round($total_gm_mounting, 2) . '</th></tr>';
                } else {
                    foreach ($mounting_data as $item) {
                        //if ($item['title'] != "Обработка 1 угла" && $item['title'] != "Криволинейный участок для ПВХ" && $item['title'] != "Внутренний вырез для ПВХ") {
                        $html .= '<tr>';
                        $html .= '<td>' . $item['title'] . '</td>';
                        $html .= '<td class="center">' . round($item['dealer_salary'], 2) . '</td>';
                        $html .= '<td class="center">' . $item['quantity'] . '</td>';
                        $html .= '<td class="center">' . $item['dealer_salary_total'] . '</td>';
                        $html .= '</tr>';
                        //}
                    }
                    $html .= '<tr><th colspan="3" class="right">Итого, руб:</th><th class="center">' . round($total_dealer_mounting, 2) . '</th></tr>';
                }

                $html .= '</tbody></table><p>&nbsp;</p>';
            } else {
                $html .= '<p>&nbsp;</p>
			<h1>Наряд монтажной бригаде</h1>
			<h2>Дата: ' . date("d.m.Y") . '</h2>
			<img src="' . $_SERVER['DOCUMENT_ROOT'] . "/calculation_images/" . md5("calculation_sketch" . $ajax_return['id']) . ".png" . '" style="width: 100%; max-height: 800px;"/>
			<table border="0" cellspacing="0" width="100%">
			<tbody>
				<tr>
					<th>Наименование</th>
					<th class="center">Кол-во</th>
				</tr>';

                if ($project->who_mounting == 1) {
                    foreach ($mounting_data as $item) {
                        // if ($item['title'] != "Обработка 1 угла" && $item['title'] != "Криволинейный участок для ПВХ" && $item['title'] != "Внутренний вырез для ПВХ") {
                        $html .= '<tr>';
                        $html .= '<td>' . $item['title'] . '</td>';
                        $html .= '<td class="center">' . $item['quantity'] . '</td>';
                        $html .= '</tr>';
                        // }
                    }
                } else {
                    foreach ($mounting_data as $item) {
                        // if ($item['title'] != "Обработка 1 угла" && $item['title'] != "Криволинейный участок для ПВХ" && $item['title'] != "Внутренний вырез для ПВХ") {
                        $html .= '<tr>';
                        $html .= '<td>' . $item['title'] . '</td>';
                        $html .= '<td class="center">' . $item['quantity'] . '</td>';
                        $html .= '</tr>';
                        //}
                    }
                }

                $html .= '</tbody></table><p>&nbsp;</p>';
            }

            $filename = md5($data['id'] . "-2") . ".pdf";
            Gm_ceilingHelpersGm_ceiling::save_pdf($html, $sheets_dir . $filename, "A4");

            //----------------------------------------------ДЛЯ СКЛАДА------------------------------------------------------//
            $html = '<h1>Информация для склада</h1>';

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
            if (isset($calculation_title)) {
                if ($calculation_title) {
                    $html .= "<b>Потолок: </b>" . $calculation_title . "<br>";
                }
            }
            if (isset($project->project_mounter)) {
                if ($project->project_mounter) {
                    $html .= "<b>Монтажная группа: </b>" . $project->project_mounter . "<br>";
                }
            }/*
			if(isset($project->project_mounting_from)) {
				if($project->project_mounting_from != "0000-00-00 00:00:00") {
						//$jdate =  $project->project_mounting_from;
						//if($jdate != "0000-00-00 00:00:00"){
						$jdate = new JDate(JFactory::getDate($project->project_mounting_from));
						$html .= "<b>Дата начала монтажа: </b>" .$jdate->format('d.m.Y  H:i') . "<br>";	}
						else 	$html .= "<b>Дата начала монтажа: </b>  не указано <br>";

			}
			if(isset($project->project_mounting_to)) {
				if($project->project_mounting_to != "0000-00-00 00:00:00") {
						//$jdate =  $project->project_mounting_from;
						//if($jdate != "0000-00-00 00:00:00"){
						$jdate = new JDate(JFactory::getDate($project->project_mounting_to));
						$html .= "<b>Дата окончания монтажа: </b>" .$jdate->format('d.m.Y  H:i') . "<br>";	}
						else 	$html .= "<b>Дата окончания монтажа: </b>  не указано <br>";
			}*/

            $html .= '<p>&nbsp;</p>
			<h1>Смета по материалам и комплектующим</h1>
			<h2>Дата: ' . date("d.m.Y") . '</h2>
			<table border="0" cellspacing="0" width="100%">
			<tbody><tr><th>Наименование</th><th class="center">Кол-во</th></tr>';

            if ($data['n1'] && $data['n2'] && $data['n3']) {
                if ($data['color'] > 0) {
                    $color_model = Gm_ceilingHelpersGm_ceiling::getModel('color');
                    $color = $color_model->getData($data['color']);
                    $name = $canvases_data['title'] . ", цвет: " . $color->colors_title;
                } else {
                    $name = $canvases_data['title'];
                }

                $html .= '<tr>';
                $html .= '<td>' . $name . '</td>';
                $html .= '<td class="center">' . $canvases_data['quantity'] . '</td>';
                $html .= '</tr>';
            }

            if ($data['n1'] && $data['n2'] && $data['n3'] && $data['offcut_square'] > 0) {
                $name = $offcut_square_data['title'];
                $html .= '<tr>';
                $html .= '<td>' . $name . '</td>';
                $html .= '<td class="center">' . $offcut_square_data['quantity'] . '</td>';
                $html .= '</tr>';
            }


            foreach ($components_data as $key => $item) {
                if ($item['quantity'] > 0 && $item['quantity'] > 0.0) {
                    $html .= '<tr>';
                    $html .= '<td>' . $item['title'] . '</td>';
                    $html .= '<td class="center">' . $item['quantity'] . '</td>';
                    $html .= '</tr>';
                }
            }
            $html .= '</tbody></table><p>&nbsp;</p>';

            $filename = md5($data['id'] . "-3") . ".pdf";
            Gm_ceilingHelpersGm_ceiling::save_pdf($html, $sheets_dir . $filename, "A4");
            //---------------------------------- РАСКРОЙ------------------------------------------\\
            $html = '<img class= "image" src="/images/GM.png"/><h1 style="text-align:center;">Потолок № _________</h1>';
            $html .= '<table>';
            $html .= '<tbody>';
            $html .= '<tr>';
            $html .= '<th>Договор №: </th> <td>' . $project->id . '</td>';
            $html .= '<th>Клиент:</th><td >' . $project->client_id . '</td>';
            $html .= '<th>Дата:</th><td >' . date("d.m.y") . '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<th>Адрес : </th> <td colspan="5">' . $project->project_info . '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            if ($data['color'] > 0) {
                $color_model = Gm_ceilingHelpersGm_ceiling::getModel('color');
                $color = $color_model->getData($data['color']);
                $name = $canvases_data['title'] . ", цвет: " . $color->colors_title;
            } else {
                $name = $canvases_data['title'];
            }
            $html .= '<th>Цвет: </th><td colspan="3" >' . $name . '</td>';
            $html .= '</tr>';
            $html .= '</tbody>';
            $html .= '</table>';
            $html .= '<table>';
            $html .= '<tbody>';
            $html .= '<tr>';
            $html .= '<th >Стороны и диагонали: </th><td>' . str_replace(';', '; ', $data['calc_data']) . '</td>';
            $html .= '</tr>';
            $html .= ' </tbody>';
            $html .= '</table>';
            $html .= '<table>';
            $html .= '<tbody>';
            $html .= '<tr>';
            $html .= '<th>Площадь:</th><td >' . $data['n4'] . 'м<sup>2</sup></td><th>Обрезки(>50%):</th><td  style = "border-style:hidden">' . $data['offcut_square'] . 'м<sup>2</sup></td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<th>Периметр:</th><td >' . $data['n5'] . 'м</td> <th>Кол-во углов:</th><td>' . $data['n9'] . '</td>';
            $html .= '</tr>';
            $html .= ' </tbody>';
            $html .= '</table>';
            $html .= '<img src="' . $_SERVER['DOCUMENT_ROOT'] . "/calculation_images/" . md5("calculation_sketch" . $ajax_return['id']) . ".png" . '" style="width: 100%;"/>';
            $html .= "<pagebreak />";
            $html .= $html;
            $html .= '<img class= "image" src="/images/GM.png"/><h1 style="text-align:center;">Раскрой № _________</h1>';
            $html .= '<table>';
            $html .= '<tbody>';
            $html .= '<tr>';
            $html .= '<th>Договор №: </th> <td>' . $project->id . '</td>';
            $html .= '<th class ="left">Клиент:</th><td >' . $project->client_id . '</td>';
            $html .= '<th>Дата:</th><td >' . date("d.m.y") . '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<th>Адрес : </th> <td colspan="5">' . $project->project_info . '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<th>Цвет: </th><td colspan="3" >' . $name . '</td>';
            $html .= '</tr>';
            $html .= '</tbody>';
            $html .= '</table>';
            $html .= '<table>';
            $html .= '<tbody>';
            $html .= '<tr>';
            $html .= '<th>Полотна: </th><td>' . str_replace(';', ";<br>", $data['cut_data']) . '</td>';
            $html .= '</tr>';
            $html .= '</tbody>';
            $html .= '</table>';
            $html .= '<table>';
            $html .= '<tbody>';
            $html .= '<tr>';
            $html .= '<th>Площадь:</th><td>' . $data['n4'] . 'м<sup>2</sup></td><th>Обрезки(>50%):</th><td>' . $data['offcut_square'] . 'м<sup>2</sup></td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<th>Периметр:</th><td>' . $data['n5'] . 'м</td><th>Кол-во углов:</th><td>' . $data['n9'] . '</td>';
            $html .= '</tr>';
            $html .= '</tbody>';
            $html .= '</table>';
            $html .= '<center><img src="' . $_SERVER['DOCUMENT_ROOT'] . "/cut_images/" . md5("cut_sketch" . $ajax_return['id']) . ".png" . '" style="width: 100%;"/></center>';
            $filename = md5($data['id'] . 'cutpdf' . -2) . '.pdf';
            Gm_ceilingHelpersGm_ceiling::save_pdf($html, $sheets_dir . $filename, "A4", "cut");
            //---------------------------------- ДЛЯ МЕНЕДЖЕРА --------------------------------------//
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
            if (isset($mount->name)) {
                if ($mount->name) {
                    $html .= "<b>Монтажная группа: </b>" . $mount->name . "<br>";
                }
            }/*
			if(isset($project->project_mounting_from)) {
				if($project->project_mounting_from != "0000-00-00 00:00:00") {
						//$jdate =  $project->project_mounting_from;
						//if($jdate != "0000-00-00 00:00:00"){
						$jdate = new JDate(JFactory::getDate($project->project_mounting_from));
						$html .= "<b>Дата начала монтажа: </b>" .$jdate->format('d.m.Y  H:i') . "<br>";	}
						else 	$html .= "<b>Дата начала монтажа: </b>  не указано <br>";
			}
			if(isset($project->project_mounting_to)) {
				if($project->project_mounting_to != "0000-00-00 00:00:00") {
						//$jdate =  $project->project_mounting_from;
						//if($jdate != "0000-00-00 00:00:00"){
						$jdate = new JDate(JFactory::getDate($project->project_mounting_to));
						$html .= "<b>Дата окончания монтажа: </b>" .$jdate->format('d.m.Y  H:i') . "<br>";	}
						else 	$html .= "<b>Дата окончания монтажа: </b>  не указано <br>";
			}*/
            if (isset($calculation_title)) {
                if ($calculation_title) {
                    $html .= "<b>Потолок: </b>" . $calculation_title . "<br>";
                }
            }

            $html .= '<p>&nbsp;</p>
			<h1>Для менеджера</h1>
			<table border="0" cellspacing="0" width="100%">
			<tbody><tr><th>Наименование</th><th class="center">Себестоимость</th><th class="center">Кол-во</th><th>Итого</th></tr>';

            if ($data['n1'] && $data['n2'] && $data['n3']) {
                if ($data['color'] > 0) {
                    $color_model = Gm_ceilingHelpersGm_ceiling::getModel('color');
                    $color = $color_model->getData($data['color']);
                    $name = $canvases_data['title'] . ", цвет: " . $color->colors_title;
                } else {
                    $name = $canvases_data['title'];
                }

                $html .= '<tr>';
                $html .= '<td>' . $name . '</td>';
                $html .= '<td>' . round($canvases_data['self_price'], 2) . '</td>';
                $html .= '<td class="center">' . $canvases_data['quantity'] . '</td>';
                $html .= '<td>' . $canvases_data['self_total'] . '</td>';
                $html .= '</tr>';
            }
            if ($data['n1'] && $data['n2'] && $data['n3'] && $data['offcut_square'] > 0) {
                $name = $offcut_square_data['title'];
                $html .= '<tr>';
                $html .= '<td>' . $name . '</td>';
                $html .= '<td >' . round($offcut_square_data['self_price'], 2) . '</td>';
                $html .= '<td class="center">' . $offcut_square_data['quantity'] . '</td>';
                $html .= '<td>' . $offcut_square_data['self_total'] . '</td>';
                $html .= '</tr>';
            }
            $price = 0;
            //if ($data['n9'] > 4) {
            foreach ($guild_data as $item) {
                $html .= '<tr>';
                $html .= '<td>' . $item['title'] . '</td>';
                $html .= '<td>' . round($item['gm_salary'], 2) . '</td>';
                $html .= '<td class="center">' . $item['quantity'] . '</td>';
                $html .= '<td>' . $item['gm_salary_total'] . '</td>';
                $html .= '</tr>';
                $price += $item['gm_salary_total'];
            }
            //}
            /* if ($data['n1'] == 28 && $data['n10'] > 0) {
                 foreach ($mounting_data as $item) {
                     if ($item['title'] == "Криволинейный участок для ПВХ") {
                         $html .= '<tr>';
                         $html .= '<td>' . $item['title'] . '</td>';
                         $html .= '<td>' . round($item['gm_salary'], 2) . '</td>';
                         $html .= '<td class="center">' . $item['quantity'] . '</td>';
                         $html .= '<td>' . $item['gm_salary_total'] . '</td>';
                         $html .= '</tr>';
                         $price += $item['gm_salary_total'];
                     }
                 }

             }
             if ($data['n1'] == 28 && $data['n11'] > 0) {
                 foreach ($mounting_data as $item) {
                     if ($item['title'] == "Внутренний вырез для ПВХ") {
                         $html .= '<tr>';
                         $html .= '<td>' . $item['title'] . '</td>';
                         $html .= '<td>' . round($item['gm_salary'], 2) . '</td>';
                         $html .= '<td class="center">' . $item['quantity'] . '</td>';
                         $html .= '<td>' . $item['gm_salary_total'] . '</td>';
                         $html .= '</tr>';
                         $price += $item['gm_salary_total'];
                     }
                 }
             }

         */
            if ($data['n9'] > 0) {
                $html .= '<tr>';
                $html .= '<td>Всего углов</td>';
                $html .= '<td></td>';
                $html .= '<td class="center">' . $data['n9'] . '</td>';
                $html .= '<td></td>';
                $html .= '</tr>';
            }

            $price_itog = $canvases_data['self_total'] + $offcut_square_data['self_total'] + $total_gm_guild;
            $html .= '<tr><th colspan="3" class="right">Итого, руб:</th><th class="center">' . round($price_itog, 2) . '</th></tr>';
            $html .= '</tbody></table><p>&nbsp;</p>';
            $html .= '<img src="' . $_SERVER['DOCUMENT_ROOT'] . "/calculation_images/" . md5("calculation_sketch" . $ajax_return['id']) . ".png" . '" style="width: 100%; max-height: 530px;"/> <br>';
            $html .= "<b>Длины сторон: </b>" . $data['calc_data'] . "<br>";
            //echo "<pre>";
            //header("Content-type: image/* charset=utf-8");
            //print_r('<img src="'.Gm_ceilingHelpersGm_ceiling::get_image_from_db( $calculation_id).'" width="100%" height="530px" />');
            //echo "</pre>";
            //$html .= '<img src="'.Gm_ceilingHelpersGm_ceiling::get_image_from_db( $calculation_id).'" width="100%" height="530px" />';
            $filename = md5($data['id'] . "-4") . ".pdf";
            Gm_ceilingHelpersGm_ceiling::save_pdf($html, $sheets_dir . $filename, "A4");

            //___________________________СМЕТА ДЛЯ КЛИЕНТА С ДИЛЕРСКИМИ ЦЕНАМИ___________________________________________//

            $user = JFactory::getUser();
            $dealer = JFactory::getUser($user->dealer_id);

            $new_total = round($canvases_data['dealer_total'] + $offcut_square_data['dealer_total'] + $dealer_components_sum + $total_with_gm_dealer_margin + $total_with_gm_dealer_margin_guild, 2);
            $new_total_discount = round($new_total * (1 - ($data['discount'] / 100)), 2);

            $html = '
			<h1>Смета по материалам и комплектующим</h1>';
            $html .= "<h1>Название: " . $data['calculation_title'] . "</h1>";
            $html .= "<b>Дилер: </b>" . $dealer->name . "<br>";
            $html .= '<h2>Дата: ' . date("d.m.Y") . '</h2>
			<h2>Общее: ' . $new_total . ' руб.</h2>';

            if ($data['discount'] != 0)
                $html .= '<h2>Общее (со скидкой): <strong>' . $new_total_discount . ' руб.</strong></h2>';
            $html .= '<table border="0" cellspacing="0" width="100%">
			<tbody><tr><th>Наименование</th><th class="center">Цена, руб.</th><th class="center">Кол-во</th><th class="center">Стоимость, руб.</th></tr>';
            if ($data['n1'] && $data['n2'] && $data['n3']) {
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
            if ($data['n1'] && $data['n2'] && $data['n3'] && $data['offcut_square'] > 0) {
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
            // if ($data['n9'] > 4) {
            foreach ($guild_data as $item) {
                // if ($item['title'] == "Обработка 1 угла") {
                $html .= '<tr>';
                $html .= '<td>' . $item['title'] . '</td>';
                $html .= '<td class="center">' . round($item['gm_salary'] * 2, 2) . '</td>';
                $html .= '<td class="center">' . $item['quantity'] . '</td>';
                $html .= '<td class="center">' . $item['gm_salary_total'] * 2 . '</td>';
                $html .= '</tr>';
                //  }
            }
            //}
            /*
            if ($data['n1'] == 28 && $data['n10'] > 0) {
                foreach ($mounting_data as $item) {
                    if ($item['title'] == "Криволинейный участок для ПВХ") {
                        $html .= '<tr>';
                        $html .= '<td>' . $item['title'] . '</td>';
                        $html .= '<td class="center">' . round($item['gm_salary'] * 2, 2) . '</td>';
                        $html .= '<td class="center">' . $item['quantity'] . '</td>';
                        $html .= '<td class="center">' . $item['gm_salary_total'] * 2 . '</td>';
                        $html .= '</tr>';
                    }
                }

            }
            if ($data['n1'] == 28 && $data['n11'] > 0) {
                foreach ($mounting_data as $item) {
                    if ($item['title'] == "Внутренний вырез для ПВХ") {
                        $html .= '<tr>';
                        $html .= '<td >' . $item['title'] . '</td>';
                        $html .= '<td class="center">' . round($item['gm_salary'] * 2, 2) . '</td>';
                        $html .= '<td class="center">' . $item['quantity'] . '</td>';
                        $html .= '<td class="center">' . $item['gm_salary_total'] * 2 . '</td>';
                        $html .= '</tr>';
                    }
                }
            }
        */
            $html .= '<tr><th colspan="3" class="right">Итого, руб:</th><th class="center">' . round($canvases_data['dealer_total'] + $offcut_square_data['dealer_total'] + $dealer_components_sum + $total_with_gm_dealer_margin_guild, 2) . '</th></tr>';
            $html .= '</tbody></table><p>&nbsp;</p>';
            if ($need_mount) {
                $html .= '<h1>Смета по монтажным работам</h1>
				<h2>Дата: ' . date("d.m.Y") . '</h2>
				<table border="0" cellspacing="0" width="100%">
				<tbody><tr><th>Наименование</th><th class="center">Цена, руб.</th><th class="center">Кол-во</th><th class="center">Стоимость, руб.</th></tr>';

                foreach ($mounting_data as $item) {
                    // if ($item['title'] != "Обработка 1 угла" && $item['title'] != "Криволинейный участок для ПВХ" && $item['title'] != "Внутренний вырез для ПВХ") {
                    $html .= '<tr>';
                    $html .= '<td>' . $item['title'] . '</td>';
                    $html .= '<td class="center">' . round($item['price_with_gm_dealer_margin'], 2) . '</td>';
                    $html .= '<td class="center">' . $item['quantity'] . '</td>';
                    $html .= '<td class="center">' . $item['total_with_gm_dealer_margin'] . '</td>';
                    $html .= '</tr>';
                    //  }
                }

                $html .= '<tr><th colspan="3" class="right">Итого, руб:</th><th class="center">' . round($total_with_gm_dealer_margin, 2) . '</th></tr>';
                $html .= '</tbody></table><p>&nbsp;</p>';
            }
            if ($data['discount'] != 0)
                $html .= '<div style="text-align: right; font-weight: bold;">ИТОГО: ' . $new_total . ' руб. - ' . $data['discount'] . '% = <span style="background: #14D100; color: #fff;">' . $new_total_discount . ' руб.</span></div>';
            else $html .= '<div style="text-align: right; font-weight: bold;">ИТОГО: ' . $new_total . ' руб.</div>';

            $filename = md5($data['id'] . "-0-0") . ".pdf";
            //$filename = $data['calculation_title'].".pdf";
            Gm_ceilingHelpersGm_ceiling::save_pdf($html, $sheets_dir . $filename, "A4");

        }

        //throw new Exception($components_data, 1);

        if ($print_components == 1) {
            return $components_data;
        }
        //print_r($components_data); exit;

        if ($send_client_cost == 1) {
            //print_r($components_data); exit;
            //___________________________СМЕТА ДЛЯ КЛИЕНТА С ДИЛЕРСКИМИ ЦЕНАМИ___НА ПОЧТУ________________________________________//
            $user = JFactory::getUser();
            $dealer = JFactory::getUser($user->dealer_id);
            $html .= "<b>Название: </b>" . $data['calculation_title'] . "<br>";
            //throw new Exception($dealer->name, 1);

            $html = '<b>Дилер: </b>' . $dealer->name . '<br>';
            $html = '
			
			<h1>Смета по материалам и комплектующим</h1>
			<h2> Дилер: ' . $dealer->name . '</h2>
			<h2>Дата: ' . date("d.m.Y") . '</h2>

			<table border="0" cellspacing="0" width="100%">
			<tbody><tr><th>Наименование</th><th class="center">Цена, руб.</th><th class="center">Кол-во</th><th class="center">Стоимость, руб.</th></tr>';

            if ($data['n1'] && $data['n2'] && $data['n3']) {
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
            if ($data['n1'] && $data['n2'] && $data['n3'] && $data['offcut_square'] > 0) {
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
            // if ($data['n9'] > 4) {
            foreach ($guild_data as $item) {
                //  if ($item['title'] == "Обработка 1 угла") {
                $html .= '<tr>';
                $html .= '<td>' . $item['title'] . '</td>';
                $html .= '<td class="center">' . round($item['gm_salary'] * 2, 2) . '</td>';
                $html .= '<td class="center">' . $item['quantity'] . '</td>';
                $html .= '<td class="center">' . $item['gm_salary_total'] * 2 . '</td>';
                $html .= '</tr>';
                // }
            }
            //  }
            /*
            if ($data['n1'] == 28 && $data['n10'] > 0) {
                foreach ($mounting_data as $item) {
                    if ($item['title'] == "Криволинейный участок для ПВХ") {
                        $html .= '<tr>';
                        $html .= '<td>' . $item['title'] . '</td>';
                        $html .= '<td class="center">' . round($item['gm_salary'] * 2, 2) . '</td>';
                        $html .= '<td class="center">' . $item['quantity'] . '</td>';
                        $html .= '<td class="center">' . $item['gm_salary_total'] * 2 . '</td>';
                        $html .= '</tr>';
                    }
                }

            }
            if ($data['n1'] == 28 && $data['n11'] > 0) {
                foreach ($mounting_data as $item) {
                    if ($item['title'] == "Внутренний вырез для ПВХ") {
                        $html .= '<tr>';
                        $html .= '<td >' . $item['title'] . '</td>';
                        $html .= '<td class="center">' . round($item['gm_salary'] * 2, 2) . '</td>';
                        $html .= '<td class="center">' . $item['quantity'] . '</td>';
                        $html .= '<td class="center">' . $item['gm_salary_total'] * 2 . '</td>';
                        $html .= '</tr>';
                    }
                }
            }
        */
            $html .= '<tr><th colspan="3" class="right">Итого, руб:</th><th class="center">' . round($canvases_data['dealer_total'] + $offcut_square_data['dealer_total'] + $dealer_components_sum + $total_with_gm_dealer_margin_guild, 2) . '</th></tr>';
            $html .= '</tbody></table><p>&nbsp;</p>';
            if ($need_mount) {
                $html .= '<h1>Смета по монтажным работам</h1>
				<h2>Дата: ' . date("d.m.Y") . '</h2>
				<table border="0" cellspacing="0" width="100%">
				<tbody><tr><th>Наименование</th><th class="center">Цена, руб.</th><th class="center">Кол-во</th><th class="center">Стоимость, руб.</th></tr>';

                foreach ($mounting_data as $item) {
                    //if ($item['title'] != "Обработка 1 угла" && $item['title'] != "Криволинейный участок для ПВХ" && $item['title'] != "Внутренний вырез для ПВХ") {
                    $html .= '<tr>';
                    $html .= '<td>' . $item['title'] . '</td>';
                    $html .= '<td class="center">' . round($item['price_with_gm_dealer_margin'], 2) . '</td>';
                    $html .= '<td class="center">' . $item['quantity'] . '</td>';
                    $html .= '<td class="center">' . $item['total_with_gm_dealer_margin'] . '</td>';
                    $html .= '</tr>';
                    // }
                }
                $html .= '<tr><th colspan="3" class="right">Итого, руб:</th><th class="center">' . round($total_with_gm_dealer_margin, 2) . '</th></tr>';
                $html .= '</tbody></table><p>&nbsp;</p>';
            }
            $new_total = round($canvases_data['dealer_total'] + $offcut_square_data['dealer_total'] + $dealer_components_sum + $total_with_gm_dealer_margin + $total_with_gm_dealer_margin_guild, 2);

            if ($new_client == 1 || $flag == 1) {

                $new_total_discount = round($new_total * (1 - (30 / 100)), 2);
                $new_total_discount_dop = round($new_total * (1 - (30 / 100)), 2);
                $data['new_total'] = $new_total;
                $data['new_total_discount_dop'] = $new_total_discount_dop;
            } else {
                $new_total_discount = round($new_total * (1 - ($data['discount'] / 100)), 2);

            }
            if ($flag == 1) $html .= '<div style="text-align: right; font-weight: bold;">ИТОГО: ' . $new_total . ' руб. Итого со скидкой 30% = <span style="background: #14D100; color: #fff;">' . $new_total_discount . ' руб.</span></div>';
            elseif (/*$project->project_discount*/
                $new_discount != 0 || $new_client == 1)
                $html .= '<div style="text-align: right; font-weight: bold;">ИТОГО: ' . $new_total . ' руб. Итого со скидкой = <span style="background: #14D100; color: #fff;">' . $new_total_discount . ' руб.</span></div>';

            else $html .= '<div style="text-align: right; font-weight: bold;">ИТОГО: ' . $new_total . ' руб. </div>';

            if ($flag == 1) $filename = "Ваш потолок со скидкой 30%" . ".pdf";
            else $filename = $data['calculation_title'] . ".pdf";
            Gm_ceilingHelpersGm_ceiling::save_pdf($html, $_SERVER['DOCUMENT_ROOT'] . '/tmp/' . $filename, "P");


            if ($flag == 1) {
                $user = JFactory::getUser();
                $dealer = JFactory::getUser($user->dealer_id);
                $html .= "<b>Название: </b>" . $data['calculation_title'] . "<br>";
                //throw new Exception($dealer->name, 1);

                $html = '<b>Дилер: </b>' . $dealer->name . '<br>';
                $html = '
			
			<h1>Смета по материалам и комплектующим</h1>
			<h2> Дилер: ' . $dealer->name . '</h2>
			<h2>Дата: ' . date("d.m.Y") . '</h2>

			<table border="0" cellspacing="0" width="100%">
			<tbody><tr><th>Наименование</th><th class="center">Цена, руб.</th><th class="center">Кол-во</th><th class="center">Стоимость, руб.</th></tr>';

                if ($data['n1'] && $data['n2'] && $data['n3']) {
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
                if ($data['n1'] && $data['n2'] && $data['n3'] && $data['offcut_square'] > 0) {
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
                    $html .= '<td class="center">' . round($item['gm_salary'] * 2, 2) . '</td>';
                    $html .= '<td class="center">' . $item['quantity'] . '</td>';
                    $html .= '<td class="center">' . $item['gm_salary_total'] * 2 . '</td>';
                    $html .= '</tr>';
                }

                $html .= '<tr><th colspan="3" class="right">Итого, руб:</th><th class="center">' . round($canvases_data['dealer_total'] + $offcut_square_data['dealer_total'] + $dealer_components_sum + $total_with_gm_dealer_margin_guild, 2) . '</th></tr>';
                $html .= '</tbody></table><p>&nbsp;</p>';
                if ($need_mount) {
                    $html .= '<h1>Смета по монтажным работам</h1>
				<h2>Дата: ' . date("d.m.Y") . '</h2>
				<table border="0" cellspacing="0" width="100%">
				<tbody><tr><th>Наименование</th><th class="center">Цена, руб.</th><th class="center">Кол-во</th><th class="center">Стоимость, руб.</th></tr>';

                    foreach ($mounting_data as $item) {
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
                $new_total = round($canvases_data['dealer_total'] + $offcut_square_data['dealer_total'] + $dealer_components_sum + $total_with_gm_dealer_margin + $total_with_gm_dealer_margin_guild, 2);


                $new_total_discount = round($new_total * (1 - (50 / 100)), 2);
                $new_total_discount_dop = round($new_total * (1 - (50 / 100)), 2);
                $data['new_total'] = $new_total;
                $data['new_total_discount_dop'] = $new_total_discount_dop;

                $html .= '<div style="text-align: right; font-weight: bold;">ИТОГО: ' . $new_total . ' руб. Итого со скидкой  50% = <span style="background: #14D100; color: #fff;">' . $new_total_discount . ' руб.</span></div>';


                $filename1 = "Ваш потолок со скидкой 50%" . ".pdf";
                Gm_ceilingHelpersGm_ceiling::save_pdf($html, $_SERVER['DOCUMENT_ROOT'] . '/tmp/' . $filename1, "P");

            }
            //throw new Exception("bhbrfbhr", 0);
            $mailer = JFactory::getMailer();

            $config = JFactory::getConfig();
            $sender = array(
                $config->get('mailfrom'),
                $config->get('fromname')
            );

            $mailer->setSender($sender);

            $mailer->addRecipient($data['send_email']);

            if ($flag == 1) {
                $new_project_id = $jinput->get('new_project_id', '0', 'INT');
                $password = $jinput->get('password', '', 'STRING');

                $html = file_get_contents("http://" . $_SERVER['SERVER_NAME'] . "/components/com_gm_ceiling/views/mail/mail_discount.html");
                $html = str_replace("#id", $id, $html);
                $html = str_replace("#login", $login, $html);
                $html = str_replace("#password", $password, $html);
                $html = str_replace("#link", $_SERVER['SERVER_NAME'], $html);
                $mailer->isHtml(true);
                $mailer->Encoding = 'base64';
                $body = $html;
            } else $body = "Здравствуйте. Вы запросили подробную смету потолка. Смета во вложении";

            $mailer->setSubject('Подробная смета');
            $mailer->setBody($body);

            $mailer->addAttachment($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $filename);
            $mailer->addAttachment($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $filename1);

            $send = $mailer->Send();

            $mailer = JFactory::getMailer();

            $config = JFactory::getConfig();
            $sender = array(
                $config->get('mailfrom'),
                $config->get('fromname')
            );

            $mailer->setSender($sender);
            /*
                if ($new_client == 1) {
                    $client_model = Gm_ceilingHelpersGm_ceiling::getModel('client');
                    if (!empty($clients)) {

                        $client_timer_spec = $client_model->add_client_timer_spec($clients, $data['rek']);
                        if (!empty($client_timer_spec)) {
                            self::saverclient($data, $client_timer_spec->id);
                        }
                    }
                }*/
            $body = "Здравствуйте. Клиент запросил подробную смету на адрес: " . $data['send_email'];

            $mailer->setSubject('Подробная смета');
            $mailer->setBody($body);

            $mailer->addAttachment($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $filename);

            $send = $mailer->Send();

            unlink($_SERVER['DOCUMENT_ROOT'] . "/tmp/" . $filename);
        }

        if ($ajax == 1) {
            $return = json_encode($ajax_return);
        }

        return $return;
    }
    }

    //Эта функция предназначена для подготовки данных для печати PDF в момент отправки договора в монтаж
    public static function print_components($project_id, $components_data)
    {
        //throw new Exception($project_id, 1);

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
        //print_r($print_data[10], 1); exit;
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

        /*
                //Снова округляем багет до 2.5 палок
                $baget_count = intval($print_data[$it_11]['quantity'] / 2.5);
                if (floatval($print_data[$it_11]['quantity'] / 2.5) > $baget_count) {
                    $baget_count++;
                }
                $print_data[$it_11]['quantity'] = $baget_count * 2.5;
                $print_data[$it_11]['self_total'] = $print_data[$it_11]['self_price'] * $print_data[$it_11]['quantity'];

                $baget_count_1 = intval($print_data[$it_236]['quantity'] / 2.5);
                if (floatval($print_data[$it_236]['quantity'] / 2.5) > $baget_count_1) {
                    $baget_count_1++;
                }
                $print_data[$it_236]['quantity'] = $baget_count_1 * 2.5;
                $print_data[$it_236]['self_total'] = $print_data[$it_236]['self_price'] * $print_data[$it_236]['quantity'];

                $baget_count_2 = intval($print_data[$it_239]['quantity'] / 2.5);
                if (floatval($print_data[$it_239]['quantity'] / 2.5) > $baget_count_2) {
                    $baget_count_2++;
                }
                $print_data[$it_239]['quantity'] = $baget_count_2 * 2.5;
                $print_data[$it_239]['self_total'] = $print_data[$it_239]['self_price'] * $print_data[$it_239]['quantity'];

                //округляем брус
                $brus_count = intval($print_data[$it_1]['quantity'] / 0.5);
                if (floatval($print_data[$it_1]['quantity'] / 0.5) > $brus_count) {
                    $brus_count++;
                }
                $print_data[$it_1]['quantity'] = $brus_count * 0.5;
                $print_data[$it_1]['self_total'] = $print_data[$it_1]['self_price'] * $print_data[$it_1]['quantity'];
        */

        //округляем провод
        $print_data[$it_4]['quantity'] = ceil($print_data[$it_4]['quantity']);
        $print_data[$it_4]['self_total'] = $print_data[$it_4]['self_price'] * $print_data[$it_4]['quantity'];
        //print_r($print_data); exit;
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

    public static function rounding($id, $value)
    {
        $count = intval($id / $value);
        if (floatval($id / $value) > $count) {
            $count++;
        }
        $id = $count * $value;

        return $id;
    }


    public static function get_image_from_db($calc_id)
    {
        $db = JFactory::getDBO();
        $q = 'SELECT `calc_image` FROM `#__gm_ceiling_calculations` WHERE `id` = ' . $calc_id;
        $db->setQuery($q);
        $image = $db->loadResult();
        header('Content-type: image/png');
        return $image;
    }

    //Печатаем подготовленные данные в PDF
    public static function save_pdf($html, $filename, $mode, $type = null)
    {

        $mpdf = new mPDF('utf-8', $mode, '8', '', 10, 10, 7, 7, 10, 10);
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->list_indent_first_level = 0;
        if ($type == "cut") {
            $stylesheet = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/libraries/mpdf/gm_cut.css');
        } else {
            $stylesheet = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/libraries/mpdf/gm_documents.css');
        }
        //$stylesheet = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/libraries/mpdf/gm_documents.css');

        if (gettype($html) == "array") {
            $mpdf->SetImportUse();
            foreach ($html as $index => $value) {
                if (substr($value, -4, 4) == ".pdf") {
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
                // if ($index < count($html) - 1) $mpdf->AddPage("P");
            }
        } else {
            $mpdf->WriteHTML($stylesheet, 1);
            $mpdf->WriteHTML($html, 2);
        }

        $mpdf->Output($filename, 'F');
        return 1;
    }

    public static function getClassPDF($mode, $format, $default_font_size, $default_font, $margin_left, $margin_right, $margin_top, $margin_bottom, $margin_header, $margin_footer, $orientation)
    {
        return new mPDF($mode, $format, $default_font_size, $default_font, $margin_left, $margin_right, $margin_top, $margin_bottom, $margin_header, $margin_footer, $orientation);
    }

    //Уведомление разных структур
    public static function notify($data, $type)
    {

        $mailer = JFactory::getMailer();
        $em = /*'kostikkuzmenko@mail.ru';*/
            'popowa.alinochka@gmail.com';//'al.p.bubnov@gmail.com';
        $em1 = 'g';//'gmvrn1510@gmail.com';
        $config = JFactory::getConfig();
        $sender = array(
            $config->get('mailfrom'),
            $config->get('fromname')
        );

        $mailer->setSender($sender);
        // throw new Exception("fdf");
        $client = Gm_ceilingHelpersGm_ceiling::getModel('clientform');
        $mounterModel = Gm_ceilingHelpersGm_ceiling::getModel('mounters');
        //throw new Exception("fdf");
        if ($type == 0) {


            //Уведомление о записи на замер
            $db = JFactory::getDBO();
            $q = 'SELECT t1.`id`, t1.`email`, t2.`group_id` FROM `#__users` as t1
				  LEFT JOIN `#__user_usergroup_map` as t2 ON t1.`id` = t2.`user_id` WHERE t1.`block` = 0 AND t2.`group_id` = 22';
            $db->setQuery($q);
            $users = $db->loadObjectList();
            //print_r($users); exit;


            foreach ($users as $user) {
                $mailer->addRecipient($user->email);
            }
            $body = "Здравствуйте. На сайте произведена новая запись на замер\n\n";
            if (!empty($data['client_name']))
                $body .= "Имя клиента: " . $data['client_name'] . "\n";
            elseif (!empty($data['client_name-top']))
                $body .= "Имя клиента: " . $data['client_name-top'] . "\n";
            if (!empty($data['client_contacts']))
                if (is_array($data['client_contacts'])) foreach ($data['client_contacts'] as $key => $value) {
                    $body .= "Телефон клиента: " . $value . "\n";
                }
                else {
                    $body .= "Телефон клиента: " . $data['client_contacts'] . "\n";
                }
            elseif (!empty($data['client_contacts-top']))
                $body .= "Телефон клиента: " . $data['client_contacts-top'] . "\n";
            if (!empty($data['project_info']))
                $body .= "Адрес: " . $data['project_info'] . "\n";
            elseif (!empty($data['project_info-top']))
                $body .= "Адрес: " . $data['project_info-top'] . "\n";

            if (($data['project_calculation_date'] != "0000-00-00")) {
                $jdate = new JDate(JFactory::getDate($data['project_calculation_date']));
                $body .= "Удобная дата замера: " . $jdate->format('d.m.Y') . "\n";
            } elseif ($data['project_calculation_date'] == "0000-00-00") {
                $body .= "Удобная дата замера: не указано \n";
            }
            if (($data['project_calculation_daypart'] != "00:00")) {
                $body .= "Удобное время замера: " . $data['project_calculation_daypart'] . "\n";
            } elseif ($data['project_calculation_daypart'] == "00:00") {
                $body .= "Удобное время замера: не указано \n";
            }
            if (!empty($data['project_note']))
                $body .= "Примечание клиента: " . $data['project_note'] . "\n";
            elseif (!empty($data['project_note-top']))
                $body .= "Примечание клиента: " . $data['project_note-top'] . "\n";
            if ($em || $em1) {
                $user = JFactory::getUser();
                $dealer = JFactory::getUser($user->dealer_id);
                $body .= "Дилер: " . $dealer->name . "\n";
            }
            $body .= "Чтобы перейти на сайт, щелкните здесь: http://calc.gm-vrn.ru/";
            $mailer->setSubject('Новая запись на замер');
            $mailer->setBody($body);
        } elseif ($type == 1) {
            //Уведомление о назначении договора на монтаж
            $db = JFactory::getDBO();
            $q = 'SELECT t1.`id`, t1.`email`, t2.`group_id` FROM `#__users` as t1
				  LEFT JOIN `#__user_usergroup_map` as t2 ON t1.`id` = t2.`user_id` WHERE t1.`block` = 0 AND t2.`group_id` = 17';

            $db->setQuery($q);
            $users = $db->loadObjectList();
            //print_r($users); exit;

            foreach ($users as $user) {

                $mailer->addRecipient($user->email);
            }
            $dopinfo = $client->getInfo($data->client_id);

            $body = "Здравствуйте. Новый договор " . $data->id . " ожидает монтажа!\n\n";
            $body .= "Имя клиента: " . $dopinfo->client_name . "\n";
            $body .= "Телефон клиента: " . $dopinfo->phone . "\n";
            $body .= "Адрес: " . $data->project_info . "\n";
            $jdate = new JDate(JFactory::getDate($data->project_mounting_date));
            if ($data->project_mounting_date != "0000-00-00 00:00:00")
                $body .= "Дата и время монтажа: " . $jdate->format('d.m.Y H:i') . "\n";
            $body .= "Примечание клиента: " . $data->project_note . "\n";
            $body .= "Примечание замерщика ГМ: " . $data->gm_calculator_note . "\n";
            if ($em || $em1) {
                $user = JFactory::getUser();
                $dealer = JFactory::getUser($user->dealer_id);
                $body .= "Дилер: " . $dealer->name . "\n";
            }
            $body .= "Чтобы перейти на сайт, щелкните здесь: http://calc.gm-vrn.ru/";
            $mailer->setSubject('Новый договор назначен на монтаж');
            $mailer->setBody($body);
        } elseif ($type == 2) {
            //Уведомление об отправке договора в производство
            $db = JFactory::getDBO();
            $q = 'SELECT t1.`id`, t1.`email`, t2.`group_id` FROM `#__users` as t1
				  LEFT JOIN `#__user_usergroup_map` as t2 ON t1.`id` = t2.`user_id` WHERE t1.`block` = 0 AND t2.`group_id` = 16';
            $db->setQuery($q);
            $users = $db->loadObjectList();
            //print_r($users); exit;

            foreach ($users as $user) {
                $mailer->addRecipient($user->email);
            }

            $dopinfo = $client->getInfo($data->id_client);

            $body = "Здравствуйте. Новый договор " . $data->id . " отправлен в производство\n\n";
            $body .= "Имя клиента: " . $data->client_id . "\n";
            $body .= "Телефон клиента: " . $dopinfo->phone . "\n";
            $body .= "Адрес: " . $data->project_info . "\n";
            $body .= "Примечание клиента: " . $data->project_note . "\n";
            $body .= "Примечание замерщика ГМ: " . $data->gm_calculator_note . "\n";
            $body .= "Примечание начальника МС ГМ: " . $data->gm_chief_note . "\n";
            if ($data->new_project_sum) $body .= "Сумма договора: " . $data->new_project_sum . "\n";
            else $body .= "Сумма договора: " . $data->project_sum . "\n";
            if ($em || $em1) {
                $user = JFactory::getUser();
                $dealer = JFactory::getUser($user->dealer_id);
                $body .= "Дилер: " . $dealer->name . "\n";
            }
            $body .= "Чтобы перейти на сайт, щелкните здесь: http://calc.gm-vrn.ru/";
            $mailer->setSubject('Новый договор в производстве');
            $mailer->setBody($body);
        } elseif ($type == 3) {
            //Уведомление о выполнении договора
            $db = JFactory::getDBO();
            $q = 'SELECT t1.`id`, t1.`email`, t2.`group_id` FROM `#__users` as t1
				  LEFT JOIN `#__user_usergroup_map` as t2 ON t1.`id` = t2.`user_id` WHERE t1.`block` = 0 AND t2.`group_id` = 16';

            $db->setQuery($q);
            $users = $db->loadObjectList();
            //print_r($users); exit;

            foreach ($users as $user) {
                $mailer->addRecipient($user->email);
            }
            $dopinfo = $client->getInfo($data->client_id);
            $q = 'SELECT * FROM  `rgzbn_gm_ceiling_clients` as t1 where t1.`id`=' . $data->client_id;
            $db->setQuery($q);
            $client = $db->loadObject();
            $body = "Здравствуйте.  Договор № " . $data->id . " выполнен \n\n";
            $body .= "Номер договора: " . $data->id . "\n";
            if ($data->new_project_sum) $body .= "Сумма договора: " . $data->new_project_sum . "\n";
            else $body .= "Сумма договора: " . $data->project_sum . "\n";
            $body .= "Имя клиента: " . $dopinfo->client_name . "\n";
            $body .= "Телефон клиента: " . $dopinfo->phone . "\n";
            $body .= "Адрес: " . $data->project_info . "\n";
            if (!empty($data->project_note))
                $body .= "Примечание клиента: " . $data->project_note . "\n";
            if (!empty($data->gm_calculator_note))
                $body .= "Примечание замерщика ГМ: " . $data->gm_calculator_note . "\n";
            if (!empty($data->gm_chief_note))
                $body .= "Примечание начальника МС ГМ: " . $data->gm_chief_note . "\n";

            if ($em || $em1) {
                $user = JFactory::getUser();
                $dealer = JFactory::getUser($user->dealer_id);
                $body .= "Дилер: " . $dealer->name . "\n";
            }
            $body .= "Чтобы перейти на сайт, щелкните здесь: http://calc.gm-vrn.ru/";
            $mailer->setSubject('Договор выполнен');
            $mailer->setBody($body);
        } elseif ($type == 4) {
            //Уведомление об отправке договора в отказы
            $db = JFactory::getDBO();
            $q = 'SELECT t1.`id`, t1.`email`, t2.`group_id` FROM `#__users` as t1
				  LEFT JOIN `#__user_usergroup_map` as t2 ON t1.`id` = t2.`user_id` WHERE t1.`block` = 0 AND t2.`group_id` = 16';
            $db->setQuery($q);
            $users = $db->loadObjectList();
            //print_r($users); exit;

            foreach ($users as $user) {
                $mailer->addRecipient($user->email);
            }
            $dopinfo = $client->getInfo($data->id_client);
            $body = "Здравствуйте. Договор " . $data->id . " перемещен в отказы.\n\n";
            $body .= "Имя клиента: " . $data->client_id . "\n";
            $body .= "Телефон клиента: " . $dopinfo->phone . "\n";
            $body .= "Адрес: " . $data->project_info . "\n";
            $body .= "Причина отказа: " . $data->dealer_calculator_note . "\n";
            if ($em || $em1) {
                $user = JFactory::getUser();
                $dealer = JFactory::getUser($user->dealer_id);
                $body .= "Дилер: " . $dealer->name . "\n";
            }

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
            $db = JFactory::getDBO();
            $q = 'SELECT t1.`id`, t1.`email`, t2.`group_id` FROM `#__users` as t1
				  LEFT JOIN `#__user_usergroup_map` as t2 ON t1.`id` = t2.`user_id` WHERE t1.`block` = 0 AND t2.`group_id` = 16';
            $db->setQuery($q);
            $users = $db->loadObjectList();
            //print_r($users); exit;

            foreach ($users as $user) {
                $mailer->addRecipient($user->email);
            }
            //print_r($data); exit;
            $dopinfo = $client->getInfo($data->client_id);
            $body = "Здравствуйте. Новый договор " . $data->id . " отправлен в производство, а неотмеченные Вами потолки перемещены в отказы под номером " . $data->refuse_id . "!\n\n";
            $body .= "Имя клиента: " . $dopinfo->client_name . "\n";
            $body .= "Телефон клиента: " . $dopinfo->phone . "\n";
            $body .= "Адрес: " . $data->project_info . "\n";
            $body .= "Примечание клиента: " . $data->project_note . "\n";
            $body .= "Примечание замерщика ГМ: " . $data->gm_calculator_note . "\n";
            $body .= "Примечание начальника МС ГМ: " . $data->gm_chief_note . "\n";
            if ($data->new_project_sum) $body .= "Сумма договора: " . $data->new_project_sum . "\n";
            else $body .= "Сумма договора: " . $data->project_sum . "\n";

            $body .= "Чтобы перейти на сайт, щелкните здесь: http://calc.gm-vrn.ru/";
            $mailer->setSubject('Новый договор в производстве');
            $mailer->setBody($body);
        } elseif ($type == 7) {
            // print_r($data); exit;
            //Уведомление о назначении договора на монтаж нужной бригаде
            $db = JFactory::getDBO();
            if ($data->project_mounter) $mounters = $mounterModel->getEmailMount($data->project_mounter);
            $dopinfo = $client->getInfo($data->id_client);

            $body = "Здравствуйте, " . $mounters->name . ". Новый договор " . $data->id . " ожидает монтажа!\n\n";
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
            $mailer->addRecipient($mounters->email);
        } elseif ($type == 8) {
            //Уведомление о изменении времени на монтаж нужной бригаде
            $db = JFactory::getDBO();
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
            $body .= "Примечание клиента: " . $data->project_note . "\n";
            $body .= "Примечание замерщика ГМ: " . $data->gm_calculator_note . "\n";
            if ($em) {
                $user = JFactory::getUser();
                $dealer = JFactory::getUser($user->dealer_id);
                $body .= "Дилер: " . $dealer->name . "\n";
            }
            $body .= "Чтобы перейти на сайт, щелкните здесь: http://calc.gm-vrn.ru/";
            $mailer->setSubject('Изменена дата(время) монтажа');
            $mailer->setBody($body);
            $mailer->addRecipient($mounters->email);
        } elseif ($type == 9) {
            //Уведомление о изменении бригады
            $db = JFactory::getDBO();
            if ($data->project_mounter) $mounters = $mounterModel->getEmailMount($data->old_mounter);
            $dopinfo = $client->getInfo($data->id_client);
            $jdate1 = new JDate(JFactory::getDate($data->old_date));
            $body = "Здравствуйте, " . $mounters->name . ". Монтаж договора " . $data->id . " на время:  " . $jdate1->format('d.m.Y H:i') . "  был отменен !\n\n";
            $body .= "Имя клиента: " . $dopinfo->client_name . "\n";
            $body .= "Телефон клиента: " . $dopinfo->phone . "\n";

            /* $jdate = new JDate(JFactory::getDate($data->project_mounting_date));
             if ($data->project_mounting_date != "0000-00-00 00:00:00")
                 $body .= "Новая дата и время монтажа: " . $jdate->format('d.m.Y H:i') . "\n";
             $body .= "Примечание клиента: " . $data->project_note . "\n";
             $body .= "Примечание замерщика ГМ: " . $data->gm_calculator_note . "\n";*/
            if ($em) {
                $user = JFactory::getUser();
                $dealer = JFactory::getUser($user->dealer_id);
                $body .= "Дилер: " . $dealer->name . "\n";
            }
            $body .= "Чтобы перейти на сайт, щелкните здесь: http://calc.gm-vrn.ru/";
            $mailer->setSubject('Монтаж отменен');
            $mailer->setBody($body);
            $mailer->addRecipient($mounters->email);
        }
        if ($type != 5) {
            $mailer->addRecipient($em);
        }

        if ($type < 4 && $type != 1) {
            $mailer->addRecipient($em1);
        }
        $send = $mailer->Send();

        return 1;
    }

    public static function push($id, $status_id)
    {

        $mailer = JFactory::getMailer();
        $em = 'popowa.alinochka@gmail.com';//'kostikkuzmenko@mail.ru';
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

    public static function saverclient($data, $id)
    {

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

    /* 	функция декодирования дополнительных комплектующих, монтажных работ и пр. */
    public static function decode_extra($extra)
    {

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

    /*
		отрисовка календаря замеров
		$project_id - id договора
		$project_mounter - id назначенной монтажной бригады
		$month - месяц
		$year - год
		$current_from - фильтр даты от
		$current_to - фильтр даты до
	*/
    public static function draw_calendar($project_id, $project_mounter, $month, $year, $current_from, $current_to)
    {

        if ($project_mounter > 0) {

        } else {
            $project_mounter = 0;
        }
        //throw new Exception($current_from, 3);
        //проверка, если он уже назначен, чтобы выделить его
        if ($current_from == '0000-00-00 00:00:00') {
            $current_is_set = 0;
        } else {
            $current_is_set = 1;
        }

        /*$jdate = new JDate($current_from);
		$current_from = strtotime($jdate->format('d.m.Y H:i'));
        throw new Exception($current_from);*/

        //$jdate = new JDate($current_to);
        //$current_to = strtotime($jdate->format('d.m.Y H:i'));

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select("`id`")
            ->select("`project_mounting_date`")
            //->select("`project_mounting_to`")
            ->from("#__gm_ceiling_projects")
            ->where("`project_mounter` = " . $project_mounter)
            ->where("`project_mounting_date` <> '0000-00-00 00:00:00'");
        //->where("`project_mounting_to` <> '0000-00-00 00:00:00'");

        $db->setQuery($query);

        $projects = $db->loadObjectList();

        $months = Array(
            0 => '',
            1 => 'Январь',
            2 => 'Февраль',
            3 => 'Март',
            4 => 'Апрель',
            5 => 'Май',
            6 => 'Июнь',
            7 => 'Июль',
            8 => 'Август',
            9 => 'Сентябрь',
            10 => 'Октябрь',
            11 => 'Ноябрь',
            12 => 'Декабрь'
        );

        $calendar = '<div class="b-calendar"><div class="b-calendar__title"><span class="b-calendar__month">' . $months[$month] . '</span> <span class="b-calendar__year">' . $year . '</span></div>';
        $calendar .= '<table cellpadding="0" cellspacing="0" class="b-calendar__tb">';

        // вывод дней недели
        $headings = array('Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс');
        $calendar .= '<tr class="b-calendar__row">';
        for ($head_day = 0; $head_day <= 6; $head_day++) {
            $calendar .= '<th class="b-calendar__head';
            // выделяем выходные дни
            if ($head_day != 0) {
                if (($head_day % 5 == 0) || ($head_day % 6 == 0)) {
                    $calendar .= ' b-calendar__weekend';
                }
            }
            $calendar .= '">';
            $calendar .= '<div class="b-calendar__number">' . $headings[$head_day] . '</div>';
            $calendar .= '</th>';
        }
        $calendar .= '</tr>';

        // выставляем начало недели на понедельник
        $running_day = date('w', mktime(0, 0, 0, $month, 1, $year));
        $running_day = $running_day - 1;
        if ($running_day == -1) {
            $running_day = 6;
        }

        $days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));
        $day_counter = 0;
        $days_in_this_week = 1;
        $dates_array = array();

        // первая строка календаря
        $calendar .= '<tr class="b-calendar__row">';

        // вывод пустых ячеек
        for ($x = 0; $x < $running_day; $x++) {
            $calendar .= '<td class="b-calendar__np"></td>';
            $days_in_this_week++;
        }

        $current_from_date = DateTime::createFromFormat('d.m.Y', $current_from);
        $current_from_d = date_format($current_from_date, 'd');
        $current_from_m = date_format($current_from_date, 'm');
        $current_from_y = date_format($current_from_date, 'Y');

        // дошли до чисел, будем их писать в первую строку
        for ($list_day = 1; $list_day <= $days_in_month; $list_day++) {

            //Пишем дату в ячейку для обработки JS
            if ($list_day < 10) {
                $list_day_text = "0" . $list_day;
            } else {
                $list_day_text = $list_day;
            }
            if ($month < 10) {
                $month_text = "0" . $month;
            } else {
                $month_text = $month;
            }
            $date = $year . "-" . $month_text . "-" . $list_day_text;

            $current_day = strtotime($year . "-" . $month_text . "-" . $list_day_text . " 12:00:00");

            $class = "";
            foreach ($projects as $each) {
                $jdate = new JDate($each->project_mounting_date);
                $from = strtotime($jdate->format('d.m.Y H:i'));

                //$jdate2 = new JDate($each->project_mounting_to);
                //$to = strtotime($jdate2->format('d.m.Y H:i'));

                //$from = strtotime($each->project_mounting_from);

                //$to = strtotime($each->project_mounting_to);
                if ($current_day = $from /*&& $current_day <= $to*/) {
                    $class = " busy";
                }
            }
            if ($current_is_set == 1) {
                if ($current_day >= $current_from && $current_day <= $current_to) {
                    $class = " b-calendar__day";
                }
            }

            //$calendar.= '<td class="b-calendar__day'.$class;
            $mount = Gm_ceilingController:: get_calendar(1);
            // print_r($mount); exit;
            $today1 = date("d");
            $today2 = date("m");
            $today3 = date("Y");

            if ($current_from != '0000-00-00 00:00:00' && $list_day == $current_from_d && $month == $current_from_m && $year == $current_from_y) {
                $calendar .= '<td class="b-calendar__day current_project' . $class;
            } else if ($today1 == $list_day && $today2 == $month && $today3 == $year && count($mount) < 1) {
                $calendar .= '<td style="background:rgba(65, 64, 153,.3); color:white; border: 1px solid black;" class="b-calendar__day' . $class;
            } else if ($today1 == $list_day && $today2 == $month && $today3 == $year && count($mount) > 1) {
                $calendar .= '<td style="background:rgba(65, 64, 153,.1); color:black; border: 1px solid black;" class="b-calendar__day' . $class;
            } else if (count($mount) < 1) {
                $calendar .= '<td style="background:rgba(65, 64, 153,.3); color:white;" class="b-calendar__day' . $class;
            } else if (count($mount) > 1) {
                $calendar .= '<td style="background:rgba(65, 64, 153,.1); color:black;" class="b-calendar__day' . $class;
            } else $calendar .= '<td class="b-calendar__day' . $class;


            // выделяем выходные дни
            if ($running_day != 0) {
                if (($running_day % 5 == 0) || ($running_day % 6 == 0)) {
                    $calendar .= ' b-calendar__weekend';
                }
            }
            $calendar .= '" data-date="' . $date . '">';

            // пишем номер в ячейку
            $calendar .= '<div class="b-calendar__number">' . $list_day . '</div>';
            $calendar .= '</td>';

            // дошли до последнего дня недели
            if ($running_day == 6) {
                // закрываем строку
                $calendar .= '</tr>';
                // если день не последний в месяце, начинаем следующую строку
                if (($day_counter + 1) != $days_in_month) {
                    $calendar .= '<tr class="b-calendar__row">';
                }
                // сбрасываем счетчики
                $running_day = -1;
                $days_in_this_week = 0;
            }

            $days_in_this_week++;
            $running_day++;
            $day_counter++;
        }

        // выводим пустые ячейки в конце последней недели
        if ($days_in_this_week < 8) {
            for ($x = 1; $x <= (8 - $days_in_this_week); $x++) {
                $calendar .= '<td class="b-calendar__np"> </td>';
            }
        }
        $calendar .= '</tr>';
        $calendar .= '</table></div>';

        return $calendar;
    }

    /*
		отрисовка календаря монтажей
		$calculated_by - назначенный замерщик
		$month - месяц
		$year - год
	*/
    public static function draw_calendar2($calculated_by, $month, $year)
    {

        if ($calculated_by > 0) {

        } else {
            $calculated_by = 0;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select("`id`")
            ->select("`project_calculation_date`")
            //->select("`project_calculation_to`")
            ->from("#__gm_ceiling_projects")
            ->where("`calculated_by` = " . $calculated_by)
            ->where("`project_calculation_date` <> '0000-00-00 00:00:00'");
        //->where("`project_calculation_to` <> '00.00.0000 00:00'");

        $db->setQuery($query);

        $projects = $db->loadObjectList();

        $months = Array(
            0 => '',
            1 => 'Январь',
            2 => 'Февраль',
            3 => 'Март',
            4 => 'Апрель',
            5 => 'Май',
            6 => 'Июнь',
            7 => 'Июль',
            8 => 'Август',
            9 => 'Сентябрь',
            10 => 'Октябрь',
            11 => 'Ноябрь',
            12 => 'Декабрь'
        );

        $calendar = '<div class="b-calendar"><div class="b-calendar__title"><span class="b-calendar__month">' . $months[$month] . '</span> <span class="b-calendar__year">' . $year . '</span></div>';
        $calendar .= '<table cellpadding="0" cellspacing="0" class="b-calendar__tb">';

        // вывод дней недели
        $headings = array('Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс');
        $calendar .= '<tr class="b-calendar__row">';
        for ($head_day = 0; $head_day <= 6; $head_day++) {
            $calendar .= '<th class="b-calendar__head';
            // выделяем выходные дни
            if ($head_day != 0) {
                if (($head_day % 5 == 0) || ($head_day % 6 == 0)) {
                    $calendar .= ' b-calendar__weekend';
                }
            }
            $calendar .= '">';
            $calendar .= '<div class="b-calendar__number">' . $headings[$head_day] . '</div>';
            $calendar .= '</th>';
        }
        $calendar .= '</tr>';

        // выставляем начало недели на понедельник
        $running_day = date('w', mktime(0, 0, 0, $month, 1, $year));
        $running_day = $running_day - 1;
        if ($running_day == -1) {
            $running_day = 6;
        }

        $days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));
        $day_counter = 0;
        $days_in_this_week = 1;
        $dates_array = array();

        // первая строка календаря
        $calendar .= '<tr class="b-calendar__row">';

        // вывод пустых ячеек
        for ($x = 0; $x < $running_day; $x++) {
            $calendar .= '<td class="b-calendar__np"></td>';
            $days_in_this_week++;
        }

        // дошли до чисел, будем их писать в первую строку
        for ($list_day = 1; $list_day <= $days_in_month; $list_day++) {

            //Пишем дату в ячейку для обработки JS
            if ($list_day < 10) {
                $list_day_text = "0" . $list_day;
            } else {
                $list_day_text = $list_day;
            }
            if ($month < 10) {
                $month_text = "0" . $month;
            } else {
                $month_text = $month;
            }
            $date = $year . "-" . $month_text . "-" . $list_day_text;

            $current_day = strtotime($year . "-" . $month_text . "-" . $list_day_text . " 12:00:00");

            $class = "";
            foreach ($projects as $each) {
                $jdate = new JDate($each->project_calculation_date);
                $from = strtotime($jdate->format('d.m.Y H:i'));

                $jdate = new JDate($each->project_calculation_to);
                $to = strtotime($jdate->format('d.m.Y H:i'));

                if ($current_day >= $from && $current_day <= $to) {
                    $class = " busy";
                }
            }

            //$calendar.= '<td class="b-calendar__day'.$class.'</td>';

            //            $current_from_date = DateTime::createFromFormat('d.m.Y', $from);
            //            $current_from_d = date_format($current_from_date,'d');
            //            $current_from_m = date_format($current_from_date,'m');
            //            $current_from_y = date_format($current_from_date,'Y');

            $zamer = Gm_ceilingController:: get_calendar2(1);
            // print_r($mount); exit;
            $today1 = date("d");
            $today2 = date("m");
            $today3 = date("Y");
            //            if ( $list_day == $current_from_d && $month == $current_from_m && $year == $current_from_y)
            //            { $calendar.= '<td class="b-calendar__day current_project'.$class;}
            //            else
            if ($today1 == $list_day && $today2 == $month && $today3 == $year && count($zamer) < 1) {
                $calendar .= '<td style="background:rgba(65, 64, 153,.3); color:white; border: 1px solid black;" class="b-calendar__day' . $class;
            } else if ($today1 == $list_day && $today2 == $month && $today3 == $year && count($zamer) > 1) {
                $calendar .= '<td style="background:rgba(65, 64, 153,.1); color:black; border: 1px solid black;" class="b-calendar__day' . $class;
            } else if (count($zamer) < 1) {
                $calendar .= '<td style="background:rgba(65, 64, 153,.5); color:white;" class="b-calendar__day' . $class;
            } else if (count($zamer) > 1) {
                $calendar .= '<td style="background:rgba(65, 64, 153,.1); color:black;" class="b-calendar__day' . $class;
            } else if (count($zamer) == 1) {
                $calendar .= '<td style="background:rgba(65, 64, 153,.3); color:black;" class="b-calendar__day' . $class;
            } else $calendar .= '<td class="b-calendar__day' . $class;


            // выделяем выходные дни
            if ($running_day != 0) {
                if (($running_day % 5 == 0) || ($running_day % 6 == 0)) {
                    $calendar .= ' b-calendar__weekend';
                }
            }
            $calendar .= '" data-date="' . $date . '">';

            // пишем номер в ячейку
            $calendar .= '<div class="b-calendar__number">' . $list_day . '</div>';
            $calendar .= '</td>';

            // дошли до последнего дня недели
            if ($running_day == 6) {
                // закрываем строку
                $calendar .= '</tr>';
                // если день не последний в месяце, начинаем следующую строку
                if (($day_counter + 1) != $days_in_month) {
                    $calendar .= '<tr class="b-calendar__row">';
                }
                // сбрасываем счетчики
                $running_day = -1;
                $days_in_this_week = 0;
            }

            $days_in_this_week++;
            $running_day++;
            $day_counter++;
        }

        // выводим пустые ячейки в конце последней недели
        if ($days_in_this_week < 8) {
            for ($x = 1; $x <= (8 - $days_in_this_week); $x++) {
                $calendar .= '<td class="b-calendar__np"> </td>';
            }
        }
        $calendar .= '</tr>';
        $calendar .= '</table></div>';

        return $calendar;
    }

    // рисует календарь
    public static function DrawCalendarTar($id, $month, $year, $flag)
    {
        // $id - айдишник бригады или менеджера или замерщика, $month - месяц, $year - год
        $ArMonths = ['', 'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
        $ArDays = ['ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ', 'ВС'];
        // начало таблицы
        $table = "";
        $table .= '<table class="calendar-table" cellspacing="0" colls="7">';
        // заголовок календаря
        $table .= '<tr><td colspan="7" class="caption-month"><p>' . $ArMonths[$month] . ' ' . $year . 'г.</p></td></tr>';
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
                    $BusyGauger = $model->FindBusyGauger($date1, $date2, $flag[1]);
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
                        $table .= '<td class="current-month" id="current-monthD' . ($j - $first_day_of_week + 1) . 'DM' . $month . 'MY' . $year . 'YI' . $id . 'I">' . ($j - $first_day_of_week + 1) . '</td>';
                    } else if (($DayTimeGauger[$j - $first_day_of_week + 1] * Count($AllGauger)) == (Count($AllGauger) * 12)) {
                        $table .= '<td class="full-day" id="current-monthD' . ($j - $first_day_of_week + 1) . 'DM' . $month . 'MY' . $year . 'YI' . $id . 'I">' . ($j - $first_day_of_week + 1) . '</td>';
                    } else {
                        $table .= '<td class="not-full-day" id="current-monthD' . ($j - $first_day_of_week + 1) . 'DM' . $month . 'MY' . $year . 'YI' . $id . 'I">' . ($j - $first_day_of_week + 1) . '</td>';
                    }
                }
                // вывод свободных замерщиков (по одному)
                if ($flag[0] == 4) {
                    $model = self::getModel('gaugers');
                    $gaugers_id = $model->getData($flag[1]);
                    if (strlen($month) == 1) {
                        $monthfull = "0" . $month;
                    } else {
                        $monthfull = $month;
                    }
                    if (!empty($gaugers_id)) {
                        $masID = [];
                        foreach ($gaugers_id as $value) {
                            array_push($masID, $value->id);
                        }
                        $date1 = $year . "-" . $monthfull . "-01";
                        $date2 = $year . "-" . $monthfull . "-" . $current_days;
                        $AllGaugingOfGaugers = $model->GetAllGaugingOfGaugers($masID, $date1, $date2);
                        $Dates = [];
                        for ($y = 1; $y <= $current_days; $y++) {
                            if (strlen($y) == 1) {
                                $u = "0" . strval($y);
                            } else {
                                $u = $y;
                            }
                            foreach ($AllGaugingOfGaugers as $value) {
                                if ($value->project_calculator == $id) {
                                    if (substr($value->project_calculation_date, 0, 10) == $year . "-" . $monthfull . "-" . $u) {
                                        $Dates[$y] += 1;
                                    }
                                }
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
                    $BusyMounters = $model->FindBusyMounters($date1, $date2, $flag[1]);
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
                        $table .= '<td class="current-month" id="current-monthD' . ($j - $first_day_of_week + 1) . 'DM' . $month . 'MY' . $year . 'YI' . $id . 'I">' . ($j - $first_day_of_week + 1) . '</td>';
                    } /*else if ($DayMounters[$j - $first_day_of_week + 1] == Count($Allbrigades)) {
                        $table .= '<td class="full-day" id="current-monthD'.($j - $first_day_of_week + 1).'DM'.$month.'MY'.$year.'YI'.$id.'I">'.($j - $first_day_of_week + 1).'</td>';                            
                    }*/ else {
                        $table .= '<td class="not-full-day" id="current-monthD' . ($j - $first_day_of_week + 1) . 'DM' . $month . 'MY' . $year . 'YI' . $id . 'I">' . ($j - $first_day_of_week + 1) . '</td>';
                    }
                }
                // для вывода монтажника (по одному)
                if ($flag[0] == 1) {
                    $model = self::getModel('teams');
                    $brigade_id = $model->getData($flag[1]);
                    if (strlen($month) == 1) {
                        $monthfull = "0" . $month;
                    } else {
                        $monthfull = $month;
                    }
                    if (!empty($brigade_id)) {
                        $masID = [];
                        foreach ($brigade_id as $value) {
                            array_push($masID, $value->id);
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
                                        if ($value[2] == 5 || $value[2] == 6 || $value[2] == 7 || $value[2] == 8 || $value[2] == 10) {
                                            $DayMounters[$r] = "yellow";
                                        }
                                        if ($value[2] == 16) {
                                            $DayMounters[$r] = "navy";
                                        }
                                        if ($value[2] == 17) {
                                            $DayMounters[$r] = "brown";
                                        }
                                        if ($value[2] == 11) {
                                            $DayMounters[$r] = "green";
                                        }
                                    }
                                }
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
                        } else {
                          
                            if (isset($statusDayOff[$j - $first_day_of_week + 1])) {
                                $table .= '<td class="day-off" id="current-monthD'.($j - $first_day_of_week + 1).'DM'.$month.'MY'.$year.'YI'.$id.'I">'.($j - $first_day_of_week + 1).'</td>';
                            } else {
                                $table .= '<td class="current-month" id="current-monthD'.($j - $first_day_of_week + 1).'DM'.$month.'MY'.$year.'YI'.$id.'I">'.($j - $first_day_of_week + 1).'</td>';                        
                            }
                          
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
                    $DateStatys = [];
                    foreach ($AllMountingOfBrigade as $value) {
                        if ($value->read_by_mounter == null) {
                            $value->read_by_mounter = "0";
                        }
                        $arr = [substr($value->project_mounting_date, 0, 10), $value->read_by_mounter, $value->project_status, $value->n5];
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
                            if ($value[0] == $year . "-" . $monthfull . "-" . $t) {
                                $perimeter += $value[3];
                                if ($value[1] == 0) {
                                    $DayMounter[$r] = ["red", $perimeter];
                                } else if ($value[1] == 1) {
                                    if ($value[2] == 5 || $value[2] == 6 || $value[2] == 7 || $value[2] == 8 || $value[2] == 10) {
                                        $DayMounter[$r] = ["yellow", $perimeter];
                                    }
                                    if ($value[2] == 16) {
                                        $DayMounter[$r] = ["navy", $perimeter];
                                    }
                                    if ($value[2] == 17) {
                                        $DayMounter[$r] = ["brown", $perimeter];
                                    }
                                    if ($value[2] == 11) {
                                        $DayMounter[$r] = ["green", $perimeter];
                                    }
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
                        $table .= '<td class="day-not-read" id="current-monthD' . ($j - $first_day_of_week + 1) . 'DM' . $month . 'MY' . $year . 'YI' . $id . 'I">' . ($j - $first_day_of_week + 1) . '</br><div class="perimeter">P = ' . ($DayMounter[$j - $first_day_of_week + 1][1]) . 'м</div></td>';
                    } else if ($DayMounter[$j - $first_day_of_week + 1][0] == "yellow") {
                        $table .= '<td class="day-read" id="current-monthD' . ($j - $first_day_of_week + 1) . 'DM' . $month . 'MY' . $year . 'YI' . $id . 'I">' . ($j - $first_day_of_week + 1) . '</br><div class="perimeter">P = ' . ($DayMounter[$j - $first_day_of_week + 1][1]) . 'м</div></td>';
                    } else if ($DayMounter[$j - $first_day_of_week + 1][0] == "navy") {
                        $table .= '<td class="day-in-work" id="current-monthD' . ($j - $first_day_of_week + 1) . 'DM' . $month . 'MY' . $year . 'YI' . $id . 'I">' . ($j - $first_day_of_week + 1) . '</br><div class="perimeter">P = ' . ($DayMounter[$j - $first_day_of_week + 1][1]) . 'м</div></td>';
                    } else if ($DayMounter[$j - $first_day_of_week + 1][0] == "brown") {
                        $table .= '<td class="day-underfulfilled" id="current-monthD' . ($j - $first_day_of_week + 1) . 'DM' . $month . 'MY' . $year . 'YI' . $id . 'I">' . ($j - $first_day_of_week + 1) . '</br><div class="perimeter">P = ' . ($DayMounter[$j - $first_day_of_week + 1][1]) . 'м</div></td>';
                    } else if ($DayMounter[$j - $first_day_of_week + 1][0] == "green") {
                        $table .= '<td class="day-complite" id="current-monthD' . ($j - $first_day_of_week + 1) . 'DM' . $month . 'MY' . $year . 'YI' . $id . 'I">' . ($j - $first_day_of_week + 1) . '</br><div class="perimeter">P = ' . ($DayMounter[$j - $first_day_of_week + 1][1]) . 'м</div></td>';
                    } else {
                      
                        if (isset($statusDayOff[$j - $first_day_of_week + 1])) {
                            $table .= '<td class="day-off" id="current-monthD'.($j - $first_day_of_week + 1).'DM'.$month.'MY'.$year.'YI'.$id.'I">'.($j - $first_day_of_week + 1).'</td>';
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

    /*
     * Функция создания html календаря
     *
     * @function    {LiteCalendar}
     * @param   number  $month  - От даты сейчас
     * @result  string  html
     */

    public function LiteCalendar($month = null, $year = null, $day = null)
    {
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
                <div class="Calendar Month" id="m%s" month="%s" year="%s">
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

        $Calendar = sprintf($Calendar, $DATE->MonthNumber, $DATE->MonthNumber, $DATE->Year, $DATE->TopName, $DaysOfTheWeek, $Days);

        return $Calendar;
    }

}
