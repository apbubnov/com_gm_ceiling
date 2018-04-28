<?php

/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     SpectralEye <Xander@spectraleye.ru>
 * @copyright  2016 SpectralEye
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Gm_ceiling records.
 *
 * @since  1.6
 */
class Gm_ceilingModelApi extends JModelList
{
    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    public function save_or_update_data_from_android($table, $data)
    {
        try
        {
            $db = $this->getDbo();
            $arr_ids = [];
            throw new Exception("$table");
            foreach ($data as $key => $value)
            {
                if (empty($data[$key]->android_id))
                {
                    return false;
                    throw new Exception('empty id!');
                }
                
                $android_id = $data[$key]->android_id;
                $query = $db->getQuery(true);
                $query->from("`$table`")
                    ->select("count(`id`) as `count`")
                    ->where("`android_id` = $android_id OR `id` = $android_id");
                $db->setQuery($query);
                $count = $db->loadObject()->count;
                $columns = '';
                $columns_values = '';
                if ($count == 0)
                {
                    throw new Exception("$table");
                    foreach ($value as $column => $column_value)
                    {
                        if($column != "image" && $column != "cut_image"){
                            $columns .= '`'.$column.'`,';
                            $columns_values .= '\''.$column_value.'\',';
                        }
                    }
                    $columns = substr($columns, 0, -1);
                    $columns_values = substr($columns_values, 0, -1);
                    
                    $query = $db->getQuery(true);
                    $query->insert("`$table`")
                        ->columns($columns)
                        ->values($columns_values);
                    $db->setQuery($query);
                    $db->execute();
                    $id = $db->insertid();
                    $arr_ids[$key] = (object)array("old_id" => $android_id, "new_id" => $id);
                }
                else
                {
                    
                    $query = $db->getQuery(true);
                    $query->update("`$table`");
                    foreach ($value as $column => $column_value)
                    {
                        if($column!="image"&&$column!="cut_image"){
                            $query->set("`$column` = '$column_value'");
                        }
                    }
                    $query->where("`android_id` = $android_id OR `id` = $android_id");
                    $db->setQuery($query);
                    $db->execute();

                    $query = $db->getQuery(true);
                    $query->select("`id`");
                    $query->from("`$table`");
                    $query->where("`android_id` = $android_id OR `id` = $android_id");
                    $db->setQuery($query);
                    $object_table = $db->loadObject();
                    
                    if (isset($object_table->id))
                    {
                        $id = $object_table->id;
                    }
                    else
                    {
                        $id = null;
                    }

                    $arr_ids[$key] = (object)array("old_id" => $android_id, "new_id" => $id);
                }
                if($table == 'rgzbn_gm_ceiling_calculations'){
                    if(!empty($data[$key]->image)){
                        $filename = md5("calculation_sketch".$id);
                        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/calculation_images/' . $filename . ".svg", $data[$key]->image);
                    }
                    if(!empty($data[$key]->cut_image)){
                        $filename = md5("cut_sketch".$id);
                        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/cut_images/' . $filename . ".svg", $data[$key]->cut_image);
                    }
                }
                if($table == 'rgzbn_gm_ceiling_calculations'){

                }
            }
            return $arr_ids;
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function register_from_android($data){
        try{
            $data = $data;
            $android_id = $data->android_id;
            $name = delete_string_characters($data->name);
			$phone = $data->phone;
            $city  = delete_string_characters($data->city);
            $email = delete_string_characters($data->email);
			//Создание клиента
			$clientform_model = Gm_ceilingHelpersGm_ceiling::getModel('ClientForm', 'Gm_ceilingModel');
			$client_data['client_name'] = $name;
			$client_data['client_contacts'] = $phone;
			$client_id = $clientform_model->save($client_data);
			//создание user'а
            $dealer_id = Gm_ceilingHelpersGm_ceiling::registerUser($name, $phone, $email, $client_id);

            $client_model = Gm_ceilingHelpersGm_ceiling::getModel('Client', 'Gm_ceilingModel');
            $client_model->updateClient($client_id, null, $dealer_id);

            $dop_contacts_model = Gm_ceilingHelpersGm_ceiling::getModel('Clients_dop_contacts', 'Gm_ceilingModel');
            $dop_contacts_model->save($client_id, 1, $email);

            $dealer_info_model = Gm_ceilingHelpersGm_ceiling::getModel('Dealer_info', 'Gm_ceilingModel');
            $dealer_info_model->update_city($dealer_id, $city);

            return (object)array("old_id" => $android_id, "new_id" => $dealer_id);
		    
        }
        catch(Exception $e)
        {
            return $e->getMessage();
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }
    public function update_android_ids_from_android($table, $data)
    {
        try
        {
            $db = $this->getDbo();
            $arr_ids = [];
            foreach ($data as $key => $value)
            {
                if (empty($data[$key]->id))
                {
                    return false;
                    throw new Exception('empty id!');
                }
                $id = $data[$key]->id;
                $query = $db->getQuery(true);
                $query->update("`$table`");
                $query->set("`android_id` = '$id'");
                $query->where("`id` = $id");
                $db->setQuery($query);
                $db->execute();
                $arr_ids[$key] = (object)array("new_android_id" => $id);
            }
            return $arr_ids;
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function delete_from_android($table, $data)
    {
        try
        {
            $db = $this->getDbo();
            $arr_ids = [];
            foreach ($data as $key => $value)
            {
                if (empty($data[$key]->id))
                {
                    throw new Exception('empty id!');
                }
                $id = $data[$key]->id;
                $query = $db->getQuery(true);
                $query->delete("`$table`");
                $query->where("`id` = $id");
                $db->setQuery($query);
                $db->execute();
                $arr_ids[$key] = (object)array("delete_id" => $id);
            }
            return $arr_ids;
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    
    public function get_data_android($data)
    {
        try
        {
            $db = $this->getDbo();

            $change_time = $db->escape($data->change_time, false);

            if (!empty($data->dealer_id))
            {
                $dealer_id = $db->escape($data->dealer_id, false);
                //клиенты
                $query = $db->getQuery(true);
                $query->select("*");
                $query->from("`rgzbn_gm_ceiling_clients`");
                $query->where("`dealer_id` = $dealer_id");
                $db->setQuery($query);
                $list_clients = $db->loadObjectList();

                if (count($list_clients) > 0)
                {
                    //проекты
                    $where = "";
                    foreach ($list_clients as $key => $value)
                    {
                        $id = $value->id;
                        if ($key == count($list_clients) - 1)
                        {
                            $where .= "`client_id`=$id";
                        }
                        else
                        {
                            $where .= "`client_id`=$id OR ";
                        }
                    }
                    
                    $query = $db->getQuery(true);
                    $query->select("p.*");
                    $query->select("s.title as status_name");
                    $query->from("`rgzbn_gm_ceiling_projects` as p");
                    $query->innerJoin("`rgzbn_gm_ceiling_status` as s on p.project_status = s.id");
                    $query->where($where);
                    $db->setQuery($query);
                    $list_projects = $db->loadObjectList();
                }
                else
                {
                    $list_projects = array();
                }
            }
            elseif (!empty($data->project_calculator))
            {
                $project_calculator = $db->escape($data->project_calculator, false);
                //проекты
                $query = $db->getQuery(true);
                $query->select("*");
                $query->from("`rgzbn_gm_ceiling_projects` as p");
                $query->where("`p.project_calculator` = $project_calculator");
                $db->setQuery($query);
                $list_projects = $db->loadObjectList();

                if (count($list_projects) > 0)
                {
                    //клиенты
                    $where = "";
                    foreach ($list_projects as $key => $value)
                    {
                        $client_id = $value->client_id;
                        if ($key == count($list_projects) - 1)
                        {
                            $where .= "`id`=$client_id";
                        }
                        else
                        {
                            $where .= "`id`=$client_id OR ";
                        }
                    }
                    
                    $query = $db->getQuery(true);
                    $query->select("*");
                    $query->from("`rgzbn_gm_ceiling_clients`");
                    $query->where($where);
                    $db->setQuery($query);
                    $list_clients = $db->loadObjectList();
                }
                else
                {
                    $list_clients = array();
                }
            }

            if (count($list_clients) > 0)
            {
                //контакты
                $where = "";
                foreach ($list_clients as $key => $value)
                {
                    $id = $value->id;
                    if ($key == count($list_clients) - 1)
                    {
                        $where .= "`client_id`=$id";
                    }
                    else
                    {
                        $where .= "`client_id`=$id OR ";
                    }
                }

                $query = $db->getQuery(true);
                $query->select("*");
                $query->from("`rgzbn_gm_ceiling_clients_contacts`");
                $query->where($where);
                $db->setQuery($query);
                $list_contacts = $db->loadObjectList();
            }
            else
            {
                $list_contacts = array();
            }

            if (count($list_projects) > 0)
            {
                //калькуляции
                $where = "";
                foreach ($list_projects as $key => $value)
                {
                    $id = $value->id;
                    if ($key == count($list_projects) - 1)
                    {
                        $where .= "`project_id`=$id";
                    }
                    else
                    {
                        $where .= "`project_id`=$id OR ";
                    }
                }

                $query = $db->getQuery(true);
                $query->select("*");
                $query->from("`rgzbn_gm_ceiling_calculations`");
                $query->where($where);
                $db->setQuery($query);
                $list_calculations = $db->loadObjectList();
            }
            else
            {
                $list_calculations = array();
            }

            if (count($list_calculations) > 0)
            {
                //остальное
                $where = "";
                foreach ($list_calculations as $key => $value)
                {
                    $id = $value->id;
                    if ($key == count($list_calculations) - 1)
                    {
                        $where .= "`calculation_id`=$id";
                    }
                    else
                    {
                        $where .= "`calculation_id`=$id OR ";
                    }
                }

                //трубы
                $query = $db->getQuery(true);
                $query->select("*");
                $query->from("`rgzbn_gm_ceiling_pipes`");
                $query->where($where);
                $db->setQuery($query);
                $list_pipes = $db->loadObjectList();

                //экола
                $query = $db->getQuery(true);
                $query->select("*");
                $query->from("`rgzbn_gm_ceiling_ecola`");
                $query->where($where);
                $db->setQuery($query);
                $list_ecola = $db->loadObjectList();

                //светильники
                $query = $db->getQuery(true);
                $query->select("*");
                $query->from("`rgzbn_gm_ceiling_fixtures`");
                $query->where($where);
                $db->setQuery($query);
                $list_fixtures = $db->loadObjectList();

                //вентиляции
                $query = $db->getQuery(true);
                $query->select("*");
                $query->from("`rgzbn_gm_ceiling_hoods`");
                $query->where($where);
                $db->setQuery($query);
                $list_hoods = $db->loadObjectList();

                //дифузоры
                $query = $db->getQuery(true);
                $query->select("*");
                $query->from("`rgzbn_gm_ceiling_diffusers`");
                $query->where($where);
                $db->setQuery($query);
                $list_diffusers = $db->loadObjectList();

                //корнизы
                $query = $db->getQuery(true);
                $query->select("*");
                $query->from("`rgzbn_gm_ceiling_cornice`");
                $query->where($where);
                $db->setQuery($query);
                $list_cornice = $db->loadObjectList();

                //профиль
                $query = $db->getQuery(true);
                $query->select("*");
                $query->from("`rgzbn_gm_ceiling_profil`");
                $query->where($where);
                $db->setQuery($query);
                $list_profil = $db->loadObjectList();
            }
            else
            {
                $list_pipes = array();
                $list_ecola = array();
                $list_fixtures = array();
                $list_hoods = array();
                $list_diffusers = array();
                $list_cornice = array();
                $list_profil = array();
            }
            //добавление картинок к калькуляции           
            foreach($list_calculations as $calc){
                $calc->image = "";
                if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/calculation_images/" . md5("calculation_sketch" . $calc->id) . ".svg")){
                    $calc->image = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/calculation_images/' .md5("calculation_sketch" . $calc->id) . ".svg");
                }
            }
            $result = [];
            $result['rgzbn_gm_ceiling_clients'] = $list_clients;
            $result['rgzbn_gm_ceiling_clients_contacts'] = $list_contacts;
            $result['rgzbn_gm_ceiling_projects'] = $list_projects;
            $result['rgzbn_gm_ceiling_calculations'] = $list_calculations;
            $result['rgzbn_gm_ceiling_pipes'] = $list_pipes;
            $result['rgzbn_gm_ceiling_ecola'] = $list_ecola;
            $result['rgzbn_gm_ceiling_fixtures'] = $list_fixtures;
            $result['rgzbn_gm_ceiling_hoods'] = $list_hoods;
            $result['rgzbn_gm_ceiling_diffusers'] = $list_diffusers;
            $result['rgzbn_gm_ceiling_cornice'] = $list_cornice;
            $result['rgzbn_gm_ceiling_profil'] = $list_profil;

            $change_time = strtotime($change_time);

            foreach ($result as $key1 => $value1)
            {
                foreach ($value1 as $key2 => $value2)
                {
                    $time_from_db = strtotime($value2->change_time);
                    if ($time_from_db <= $change_time)
                    {
                        unset($result[$key1][$key2]);
                    }
                }
                $result[$key1] = array_values($result[$key1]);
            }

            $bool = false;
            foreach ($result as $key => $value)
            {
                if (count($result[$key]) != 0)
                {
                    $bool = true;
                    break;
                }
            }

            if ($bool == true)
            {
                return $result;
            }
            else
            {
                return null;
            }
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function getUserId($name)
    {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query->from("`#__users`")
                ->select("`id`")
                ->where("`username` LIKE '$name' OR `email` LIKE '$name'");

            $db->setQuery($query);
            $user = $db->loadObject();

            return ((empty($user))?null:$user->id);
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function get_material_android($data)
    {
        try
        {
            $db = $this->getDbo();
            $change_time = $db->escape($data->change_time, false);
            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("`rgzbn_gm_ceiling_canvases_manufacturers`");
            $query->where("change_time >= '$change_time'");
            $db->setQuery($query);
            $list_manufacturers = $db->loadObjectList();
            //текстуры
            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("`rgzbn_gm_ceiling_textures`");
            $query->where("change_time >= '$change_time'");
            $db->setQuery($query);
            $list_textures = $db->loadObjectList();

            //цвета
            $query = $db->getQuery(true);
            $query->select("id,title,hex");
            $query->from("`rgzbn_gm_ceiling_colors`");
            $query->where("change_time >= '$change_time'");
            $db->setQuery($query);
            $list_colors = $db->loadObjectList();
            
            //полотна
            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("`rgzbn_gm_ceiling_canvases`");
            $query->where("change_time >= '$change_time'");
            $db->setQuery($query);
            $list_canvases = $db->loadObjectList();

            //компоненты
            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("`rgzbn_gm_ceiling_components`");
            $query->where("change_time >= '$change_time'");
            $db->setQuery($query);
            $list_components = $db->loadObjectList();

            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("`rgzbn_gm_ceiling_components_option`");
            $query->where("change_time >= '$change_time'");
            $db->setQuery($query);
            $list_components_option = $db->loadObjectList();

            //type && type_option
            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("`rgzbn_gm_ceiling_type`");
            $query->where("change_time >= '$change_time'");
            $db->setQuery($query);
            $list_type = $db->loadObjectList();

            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("`rgzbn_gm_ceiling_type_option`");
            $query->where("change_time >= '$change_time'");
            $db->setQuery($query);
            $list_type_option = $db->loadObjectList();

            //статус
            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("`rgzbn_gm_ceiling_status`");
            $query->where("change_time >= '$change_time'");
            $db->setQuery($query);
            $list_status = $db->loadObjectList();

            $result = [];
            $result['rgzbn_gm_ceiling_textures'] = $list_textures;
            $result['rgzbn_gm_ceiling_colors'] = $list_colors;
            $result['rgzbn_gm_ceiling_canvases'] = $list_canvases;
            $result['rgzbn_gm_ceiling_components'] = $list_components;
            $result['rgzbn_gm_ceiling_components_option'] = $list_components_option;
            $result['rgzbn_gm_ceiling_type'] = $list_type;
            $result['rgzbn_gm_ceiling_type_option'] = $list_type_option;
            $result['rgzbn_gm_ceiling_status'] = $list_status;
            $result['rgzbn_gm_ceiling_canvases_manufacturers'] = $list_manufacturers;
           // $result['rgzbn_gm_ceiling_mounters'] = $list_mounters;
           // $result['rgzbn_gm_ceiling_mounters_map'] = $list_mounters_map; 
            return $result;

        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }
    public function get_mounters_android($data){
        try{
            $db = $this->getDbo();
            $change_time = $db->escape($data->change_time, false);
            $dealer_id = $db->escape($data->dealer_id, false);
            //Дилер
            $query = $db->getQuery(true);
            $query->select("u.id as id,u.name as `name`,u.username as `username`,u.email as `email`,um.group_id as `group_id`");
            $query->from("`rgzbn_users` as u");
            $query->leftJoin('`rgzbn_user_usergroup_map`AS um ON um.user_id = u.id');
            $query->where("u.change_time >= '$change_time' and  u.id = $dealer_id AND um.group_id = 14");
            $db->setQuery($query);
            $list_dealer = $db->loadObjectList();
            //Бригады
            $query = $db->getQuery(true);
            $query->select("u.id as id,u.name as `name`,u.username as `username`,u.email as `email`,um.group_id as `group_id`");
            $query->from("`rgzbn_users` as u");
            $query->leftJoin('`rgzbn_user_usergroup_map`AS um ON um.user_id = u.id');
            $query->where("u.change_time >= '$change_time' and  u.dealer_id = $dealer_id AND um.group_id = 11");
            $db->setQuery($query);
            $list_brigades = $db->loadObjectList();

            //замерщики
            $query = $db->getQuery(true);
            $query->select("u.id as id,u.name as `name`,u.username as `username`,u.email as `email`,um.group_id as `group_id`");
            $query->from("`rgzbn_users` as u");
            $query->leftJoin('`rgzbn_user_usergroup_map`AS um ON um.user_id = u.id');
            $query->where("u.change_time >= '$change_time' and  u.dealer_id = $dealer_id AND (um.group_id = 21 OR um.group_id = 22 )");
            $db->setQuery($query);
            $list_gaugers = $db->loadObjectList();
            //mounters & mounters_map
            $query = $db->getQuery(true);
            $query->select("id,name,phone");
            $query->from("`rgzbn_gm_ceiling_mounters`");
            $query->where("change_time >= '$change_time'");
            $db->setQuery($query);
            $list_mounters = $db->loadObjectList();


            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("`rgzbn_gm_ceiling_mounters_map`");
            $query->where("change_time >= '$change_time'");
            $db->setQuery($query);
            $list_mounters_map = $db->loadObjectList();

            $result = [];
            $list1 = array_merge($list_brigades,$list_gaugers);
            $result['rgzbn_users'] = array_merge($list1,$list_dealer);
            $result['rgzbn_gm_ceiling_mounters'] = $list_mounters;
            $result['rgzbn_gm_ceiling_mounters_map'] = $list_mounters_map; 
            
            return $result;
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function get_dealerInfo_android($data){
        try{
           
            $db = $this->getDbo();
            $change_time = $db->escape($data->change_time, false);
            $dealer_id = $db->escape($data->dealer_id, false);
            
            
            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("`rgzbn_gm_ceiling_dealer_info`");
            $query->where("change_time >= '$change_time' and dealer_id = $dealer_id");
            $db->setQuery($query);
            $list_info = $db->loadObjectList();
      
            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("`rgzbn_gm_ceiling_mount`");
            $query->where("change_time >= '$change_time' and user_id = $dealer_id");
            $db->setQuery($query);
            $list_mount = $db->loadObjectList();

            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("`rgzbn_gm_ceiling_canvases_dealer_price`");
            $query->where("change_time >= '$change_time' and user_id = $dealer_id");
            $db->setQuery($query);
            $list_canvases_dealer_price = $db->loadObjectList();

            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("`rgzbn_gm_ceiling_components_dealer_price`");
            $query->where("change_time >= '$change_time' and user_id = $dealer_id");
            $db->setQuery($query);
            $list_components_dealer_price = $db->loadObjectList();

            $result = [];
            $result['rgzbn_gm_ceiling_dealer_info'] = $list_info;
            $result['rgzbn_gm_ceiling_mount'] =  $list_mount;
            $result['rgzbn_gm_ceiling_canvases_dealer_price'] =  $list_canvases_dealer_price;
            $result['rgzbn_gm_ceiling_components_dealer_price'] =  $list_components_dealer_price;
            return $result;
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function get_measure_time($date){
        try{
            $gauger_model = Gm_ceilingHelpersGm_ceiling::getModel('gaugers');
            return $gauger_model->getFreeGaugingTimes($date);
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function rec_to_measure($data){
        try{
            if(!empty($data->user_id)){

                $client_id = JFactory::getUser($data->user_id)->associated_client;
                $dealer_id = $data->user_id;
            }
            else{
                if(!empty($data->name)){
                     $name = delete_string_characters($data->name);
                }
                else {
                    $name = "Клиент";
                }
                foreach ($data as $key => $value) {
                    $str.="$key;";
                }
                $phone = $data->phone;
                $city  = 'Воронеж';//пока по дефолту
                //Создание клиента
                $clientform_model = Gm_ceilingHelpersGm_ceiling::getModel('ClientForm', 'Gm_ceilingModel');
                $client_data['client_name'] = $name;
                $client_data['client_contacts'] = $phone;
                $client_id = $clientform_model->save($client_data);
                if($name == "Клиент"){
                    $name.=$client_id;
                }
                //создание user'а
                $dealer_id = Gm_ceilingHelpersGm_ceiling::registerUser($name, $phone, "$client_id@$client_id", $client_id,2);

                $client_model = Gm_ceilingHelpersGm_ceiling::getModel('Client', 'Gm_ceilingModel');
                $client_model->updateClient($client_id, null, $dealer_id);

                $dealer_info_model = Gm_ceilingHelpersGm_ceiling::getModel('Dealer_info', 'Gm_ceilingModel');
                $dealer_info_model->update_city($dealer_id, $city);
            }
           
            $address = $data->address;
            $date_time = $data->date_time;
            $gauger_model = Gm_ceilingHelpersGm_ceiling::getModel('gaugers');
            $gaug_id = $gauger_model->getFreeGaugers($date_time)[0];
            if(empty($gaug_id)){
                $gaug_id = null;
            }
            $project_data = [
                        "client_id" => $client_id,
                        "project_info" => $address,
                        "project_calculation_date" => $date_time,
                        "project_status"=>1,
                        "api_phone_id"=>$data->advt,
                        "project_calculator"=>$gaug_id
                    ];

            $projectform_model = Gm_ceilingHelpersGm_ceiling::getModel('projectform', 'Gm_ceilingModel');
            $project = $projectform_model->save($project_data);
            if(!empty($data->calc_id)){
                $calculationModel = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
                $calculationModel->changeProjectId($data->calc_id, $project);
            }
            $callback_model = Gm_ceilingHelpersGm_ceiling::getModel('callback');
            $callback_model->save(date("Y-m-d H:i:s"), "Клиент заказал замер через гмпотолки. Уточнить данные", $client_id, 1);
            $result = [
                        "user_id" => $dealer_id,
                        "username" => JFactory::getUser($dealer_id)->username,
                        "project_id" => $project
                        ];
            return (object)$result;
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function change_password($data){
        try{
            $user = JFactory::getUser($data->user_id);
            if(!empty($data->old_password)){
                $verifyPass = JUserHelper::verifyPassword($data->old_password, $user->password, $user->id);
                if($verifyPass){
                    return $this->change_pass($user->id,$data->password);
                }
                else return false;
            }
            else{
                return $this->change_pass($user->id,$data->password);
            }
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function change_pass($user_id,$pass){
        try{
            $user_model = Gm_ceilingHelpersGm_ceiling::getModel('Users');
            return $user_model->change_user_pass($user_id,$pass);
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }
}