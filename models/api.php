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

    public function generateKeypair() {
        try {
            $db = $this->getDbo();

            // Create the keypair
            $res = openssl_pkey_new();
            // Get private key
            openssl_pkey_export($res, $privatekey);
            // Get public key
            $publickey = openssl_pkey_get_details($res);
            $publickey = $publickey['key'];

            $delTime = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s').' -10 minutes'));

            $query = $db->getQuery(true);
            $query->delete('`#__keypairs`');
            $query->where("`change_time` < '$delTime'");
            $db->setQuery($query);
            $db->execute();

            $query = $db->getQuery(true);
            $query->insert("`#__keypairs`");
            $query->columns('`public_key`, `private_key`');
            $query->values("'$publickey', '$privatekey'");
            $db->setQuery($query);
            $db->execute();

            $result = (object) array('key_number' => $db->insertid(), 'public_key' => $publickey);
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getKeypair($key_number) {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->select('`public_key`, `private_key`');
            $query->from('`#__keypairs`');
            $query->where("`id` = $key_number");
            $db->setQuery($query);
            $result = $db->loadObject();
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getProjectByStatus($statuses) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("COUNT(DISTINCT d.project_id)")
            ->from("`rgzbn_analytic_detailed` AS d")
            ->where("d.client_id = a.client_id AND d.new_status in $statuses");
        return $query;
    }
    
    public function getProjectsAnalytic($date1,$date2,$managers) {
        try {
            /*SELECT a.client_id,c.manager_id,
            (SELECT COUNT(DISTINCT d.project_id) FROM `rgzbn_analytic_detailed` AS d WHERE d.client_id = a.client_id AND d.new_status = 1)  AS measures,
            (SELECT COUNT(DISTINCT d.project_id) FROM `rgzbn_analytic_detailed` AS d WHERE d.client_id = a.client_id AND d.new_status = 4) AS deals,
            CONCAT('[',GROUP_CONCAT(DISTINCT CONCAT('{"project_id":"',a.project_id,'","sum":"',a.sum,'","status":"',a.new_status,'"}') SEPARATOR ','),']') AS projects
            FROM `rgzbn_analytic_detailed` AS a
            INNER JOIN `rgzbn_gm_ceiling_clients` AS c ON a.client_id = c.id
            WHERE a.date_of_change BETWEEN '2018-01-01' AND '2019-01-04' AND a.new_status IN(1,4)
            GROUP BY a.client_id
            */

            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $measuresCountQuery = $this->getProjectByStatus("(1)");
            $dealsCountQuery = $this->getProjectByStatus("(4,5)");
            $query
                ->select("a.client_id,c.manager_id")
                ->select("($measuresCountQuery) as measures")
                ->select("($dealsCountQuery) as deals")
                ->select("GROUP_CONCAT(DISTINCT CONCAT('{\"project_id\":\"',a.project_id,'\",\"sum\":\"',a.sum,'\",\"status\":\"',a.new_status,'\"}') SEPARATOR ';') AS projects")
                ->from("`rgzbn_analytic_detailed` AS a")
                ->innerJoin("`rgzbn_gm_ceiling_clients` AS c ON a.client_id = c.id")
                ->where("a.date_of_change BETWEEN '$date1' AND '$date2' AND a.new_status IN(1,4,5) and manager_id IN ($managers)")
                ->group("a.client_id");
            $db->setQuery($query);
            $items = $db->loadObjectList();
            $result = [];

            foreach ($items as $item){
                $result[$item->manager_id]['clients'][] = $item->client_id;

                $projects = explode(';',$item->projects);
                $client_projects = [];

                foreach($projects as $project){
                    $client_projects[$item->client_id][] = json_decode($project);
                    //$result[$item->manager_id]['projects']['client_id'] = $item->client_id;

                }
                $result[$item->manager_id]['projects'][]=$client_projects;
                $result[$item->manager_id]['measures'] += $item->measures;
                $result[$item->manager_id]['deals'] += $item->deals;

            }

            return $result;
        } catch(Exception $e) {
            die($e->getMessage());
            /*$date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);*/
        }
    }
    public function save_or_update_data_from_android($table, $data) {
        try {
            $db = $this->getDbo();
            $arr_ids = [];

            if (!empty($data)) foreach ($data as $key => $value)
            {
                try {
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
                        foreach ($value as $column => $column_value)
                        {
                            $column_value = mb_ereg_replace("[^a-zA-Zа-яёА-ЯЁ\s\d\,\.\_\/\(\)\-\:\@]", "", $column_value);
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
                } catch(Exception $e) {
                    continue;
                }
            }
            return $arr_ids;
        } catch(Exception $e) {
            /*$date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);*/
            die($e->getMessage());
        }
    }

    public function register_from_android($data) {
        try {
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
            
            if ($client_id == 'client_found') {
                $model_users = Gm_ceilingHelpersGm_ceiling::getModel('users');
                $model_client_phones = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
                $client = $model_client_phones->getItemsByPhoneNumber($phone, 1);
                $usr = $model_users->getUserByAssociatedClient($client->id);
                if (!is_null($usr)) {
                    throw new Exception('Этот аккаунт уже зарегистрирован. Авторизуйтесь.');
                }
                else
                {
                    throw new Exception('Данный номер уже используется.');
                }
            }
			//создание user'а
            $dealer_id = Gm_ceilingHelpersGm_ceiling::registerUser($name, $phone, $email, $client_id, 1, $android_id);

            $client_model = Gm_ceilingHelpersGm_ceiling::getModel('Client', 'Gm_ceilingModel');
            $client_model->updateClient($client_id, null, $dealer_id);

            $dop_contacts_model = Gm_ceilingHelpersGm_ceiling::getModel('Clients_dop_contacts', 'Gm_ceilingModel');
            $dop_contacts_model->save($client_id, 1, $email);

            $dealer_info_model = Gm_ceilingHelpersGm_ceiling::getModel('Dealer_info', 'Gm_ceilingModel');
            $dealer_info_model->update_city($dealer_id, $city);

            return (object)array("old_id" => $android_id, "new_id" => $dealer_id);
		    
        } catch(Exception $e) {
            die($e->getMessage());
            /*$date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);*/
        }
    }

    public function update_android_ids_from_android($table, $data) {
        try {
            $db = $this->getDbo();
            $arr_ids = [];
            $ids = '';
            if (empty($data)) {
                return $arr_ids;
            }
            foreach ($data as $key => $value) {
                if (empty($data[$key]->id)) {
                    continue;
                }
                $id = $data[$key]->id;
                if (!empty($ids)) {
                    $ids .= ','.$id;
                } else {
                    $ids = $id;
                }
                $arr_ids[$key] = (object)array("new_android_id" => $id);
            }
            $query = $db->getQuery(true);
            $query->update("`$table`");
            $query->set("`android_id` = '$id'");
            $query->where("`id` IN ($ids)");
            $db->setQuery($query);
            $db->execute();
            return $arr_ids;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function delete_from_android($table, $data) {
        try {
            $arr_ids = (object)array('table' => $table, 'ids' => '');
            foreach ($data as $key => $value) {
                $id = $data[$key]->id;
                if (empty($id)) {
                    $arr_ids->ids = 'empty id!';
                    return $arr_ids;
                }
                if (mb_ereg('[^\d]', $id)) {
                    $arr_ids->ids = 'invalid id!';
                    return $arr_ids;
                }
                if (empty($arr_ids->ids)) {
                    $arr_ids->ids .= $id;
                } else {
                    $arr_ids->ids .= ','.$id;
                }
            }
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->delete("`$table`");
            $query->where("`id` IN ($arr_ids->ids)");
            $db->setQuery($query);
            $db->execute();
            return $arr_ids;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
                $query->from("`#__gm_ceiling_clients`");
                $query->where("`dealer_id` = $dealer_id AND `deleted_by_user` = 0");
                $db->setQuery($query);
                $list_clients = $db->loadObjectList();

                if (count($list_clients) > 0)
                {
                    //проекты
                    $where = "(";
                    foreach ($list_clients as $key => $value)
                    {
                        $id = $value->id;
                        if ($key == count($list_clients) - 1)
                        {
                            $where .= "`client_id`=$id) AND `deleted_by_user` = 0";
                        }
                        else
                        {
                            $where .= "`client_id`=$id OR ";
                        }
                    }
                    
                    $query = $db->getQuery(true);
                    $query->select("p.*");
                    $query->select("s.title as status_name");
                    $query->from("`#__gm_ceiling_projects` as p");
                    $query->innerJoin("`#__gm_ceiling_status` as s on p.project_status = s.id");
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
                $query->from("`#__gm_ceiling_projects` as p");
                $query->where("`p`.`project_calculator` = $project_calculator");
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
                    $query->from("`#__gm_ceiling_clients`");
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
                $query->from("`#__gm_ceiling_clients_contacts`");
                $query->where($where);
                $db->setQuery($query);
                $list_contacts = $db->loadObjectList();

                $query = $db->getQuery(true);
                $query->select("*");
                $query->from("`#__gm_ceiling_client_history`");
                $query->where($where);
                $db->setQuery($query);
                $list_client_history = $db->loadObjectList();

                $query = $db->getQuery(true);
                $query->select("*");
                $query->from("`#__gm_ceiling_callback`");
                $query->where($where);
                $db->setQuery($query);
                $list_calls = $db->loadObjectList();
            }
            else
            {
                $list_contacts = array();
                $list_client_history = array();
                $list_calls = array();
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
                $query->from("`#__gm_ceiling_calculations`");
                $query->where($where);
                $db->setQuery($query);
                $list_calculations = $db->loadObjectList();

                $query = $db->getQuery(true);
                $query->select("*");
                $query->from("`#__gm_ceiling_projects_mounts`");
                $query->where($where);
                $db->setQuery($query);
                $list_mounts = $db->loadObjectList();
            }
            else
            {
                $list_calculations = array();
                $list_mounts = array();
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
                $query->from("`#__gm_ceiling_pipes`");
                $query->where($where);
                $db->setQuery($query);
                $list_pipes = $db->loadObjectList();

                //экола
                $query = $db->getQuery(true);
                $query->select("*");
                $query->from("`#__gm_ceiling_ecola`");
                $query->where($where);
                $db->setQuery($query);
                $list_ecola = $db->loadObjectList();

                //светильники
                $query = $db->getQuery(true);
                $query->select("*");
                $query->from("`#__gm_ceiling_fixtures`");
                $query->where($where);
                $db->setQuery($query);
                $list_fixtures = $db->loadObjectList();

                //вентиляции
                $query = $db->getQuery(true);
                $query->select("*");
                $query->from("`#__gm_ceiling_hoods`");
                $query->where($where);
                $db->setQuery($query);
                $list_hoods = $db->loadObjectList();

                //дифузоры
                $query = $db->getQuery(true);
                $query->select("*");
                $query->from("`#__gm_ceiling_diffusers`");
                $query->where($where);
                $db->setQuery($query);
                $list_diffusers = $db->loadObjectList();

                //корнизы
                $query = $db->getQuery(true);
                $query->select("*");
                $query->from("`#__gm_ceiling_cornice`");
                $query->where($where);
                $db->setQuery($query);
                $list_cornice = $db->loadObjectList();

                //профиль
                $query = $db->getQuery(true);
                $query->select("*");
                $query->from("`#__gm_ceiling_profil`");
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
            $result['rgzbn_gm_ceiling_client_history'] = $list_client_history;
            $result['rgzbn_gm_ceiling_callback'] = $list_calls;
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
            $result['rgzbn_gm_ceiling_projects_mounts'] = $list_mounts;

            $change_time = strtotime($change_time);

            foreach ($result as $key1 => $value1)
            {
                if (!empty($value1)) {
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            $query->from("`#__gm_ceiling_canvases_manufacturers`");
            $query->where("change_time >= '$change_time'");
            $db->setQuery($query);
            $list_manufacturers = $db->loadObjectList();
            //текстуры
            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("`#__gm_ceiling_textures`");
            $query->where("change_time >= '$change_time'");
            $db->setQuery($query);
            $list_textures = $db->loadObjectList();

            //цвета
            $query = $db->getQuery(true);
            $query->select("id,title,hex");
            $query->from("`#__gm_ceiling_colors`");
            $query->where("change_time >= '$change_time'");
            $db->setQuery($query);
            $list_colors = $db->loadObjectList();
            
            //полотна
            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("`#__gm_ceiling_canvases`");
            $query->where("change_time >= '$change_time'");
            $db->setQuery($query);
            $list_canvases = $db->loadObjectList();

            //компоненты
            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("`#__gm_ceiling_components`");
            $query->where("change_time >= '$change_time'");
            $db->setQuery($query);
            $list_components = $db->loadObjectList();

            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("`#__gm_ceiling_components_option`");
            $query->where("change_time >= '$change_time'");
            $db->setQuery($query);
            $list_components_option = $db->loadObjectList();

            //type && type_option
            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("`#__gm_ceiling_type`");
            $query->where("change_time >= '$change_time'");
            $db->setQuery($query);
            $list_type = $db->loadObjectList();

            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("`#__gm_ceiling_type_option`");
            $query->where("change_time >= '$change_time'");
            $db->setQuery($query);
            $list_type_option = $db->loadObjectList();

            //статус
            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("`#__gm_ceiling_status`");
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public function get_mounters_android($data){
        try{
            $db = $this->getDbo();
            $change_time = $db->escape($data->change_time, false);
            $dealer_id = $db->escape($data->dealer_id, false);
            //Дилер
            $query = $db->getQuery(true);
            $query->select("u.id as id,u.name as `name`,u.username as `username`,u.email as `email`,um.group_id as `group_id`,um.id as `map_id`,u.associated_client");
            $query->from("`#__users` as u");
            $query->leftJoin('`#__user_usergroup_map`AS um ON um.user_id = u.id');
            $query->where("u.change_time >= '$change_time' and  u.id = $dealer_id AND um.group_id = 14");
            $db->setQuery($query);
            $list_dealer = $db->loadObjectList();
            //Бригады
            $query = $db->getQuery(true);
            $query->select("u.id as id,u.name as `name`,u.username as `username`,u.email as `email`,um.group_id as `group_id`,um.id as `map_id`,u.associated_client");
            $query->from("`#__users` as u");
            $query->leftJoin('`#__user_usergroup_map`AS um ON um.user_id = u.id');
            $query->where("u.change_time >= '$change_time' and  u.dealer_id = $dealer_id AND um.group_id = 11");
            $db->setQuery($query);
            $list_brigades = $db->loadObjectList();

            //замерщики
            $query = $db->getQuery(true);
            $query->select("u.id as id,u.name as `name`,u.username as `username`,u.email as `email`,um.group_id as `group_id`,um.id as `map_id`,u.associated_client");
            $query->from("`#__users` as u");
            $query->leftJoin('`#__user_usergroup_map`AS um ON um.user_id = u.id');
            $query->where("u.change_time >= '$change_time' and  u.dealer_id = $dealer_id AND (um.group_id = 21 OR um.group_id = 22 )");
            $db->setQuery($query);
            $list_gaugers = $db->loadObjectList();
            //mounters & mounters_map
            $query = $db->getQuery(true);
            $query->select("id,name,phone");
            $query->from("`#__gm_ceiling_mounters`");
            $query->where("change_time >= '$change_time'");
            $db->setQuery($query);
            $list_mounters = $db->loadObjectList();


            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("`#__gm_ceiling_mounters_map`");
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function get_dealerInfo_android($data){
        try{
           
            $db = $this->getDbo();
            $change_time = $db->escape($data->change_time, false);
            $dealer_id = $db->escape($data->dealer_id, false);
            
            
            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("`#__gm_ceiling_dealer_info`");
            $query->where("change_time >= '$change_time' and dealer_id = $dealer_id");
            $db->setQuery($query);
            $list_info = $db->loadObjectList();
      
            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("`#__gm_ceiling_mount`");
            $query->where("change_time >= '$change_time' and user_id = $dealer_id");
            $db->setQuery($query);
            $list_mount = $db->loadObjectList();

            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("`#__gm_ceiling_canvases_dealer_price`");
            $query->where("change_time >= '$change_time' and user_id = $dealer_id");
            $db->setQuery($query);
            $list_canvases_dealer_price = $db->loadObjectList();

            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("`#__gm_ceiling_components_dealer_price`");
            $query->where("change_time >= '$change_time' and user_id = $dealer_id");
            $db->setQuery($query);
            $list_components_dealer_price = $db->loadObjectList();

            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("`#__gm_ceiling_api_phones`");
            $query->where("change_time >= '$change_time' and dealer_id = $dealer_id");
            $db->setQuery($query);
            $list_api_phones = $db->loadObjectList();

            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("`#__gm_ceiling_recoil_map_project`");
            $query->where("change_time >= '$change_time' and recoil_id = $dealer_id");
            $db->setQuery($query);
            $list_recoil_map_project = $db->loadObjectList();

            $result = [];
            $result['rgzbn_gm_ceiling_dealer_info'] = $list_info;
            $result['rgzbn_gm_ceiling_mount'] =  $list_mount;
            $result['rgzbn_gm_ceiling_canvases_dealer_price'] =  $list_canvases_dealer_price;
            $result['rgzbn_gm_ceiling_components_dealer_price'] =  $list_components_dealer_price;
            $result['rgzbn_gm_ceiling_api_phones'] =  $list_api_phones;
            $result['rgzbn_gm_ceiling_recoil_map_project'] =  $list_recoil_map_project;
            return $result;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function get_dealerInfo_androidCallGlider($data) {
        try {
            $db = $this->getDbo();
            $change_time = $db->escape($data->change_time, false);
            $dealer_id = $db->escape($data->dealer_id, false);

            $query = 'SET SESSION group_concat_max_len  = 32768';
            $db->setQuery($query);
            $db->execute();

            $query = $db->getQuery(true);
            $query->select('count(`id`) AS `count`,
                            GROUP_CONCAT(`id` SEPARATOR \',\') AS `ids`');
            $query->from('`#__gm_ceiling_clients`');
            $query->where("`dealer_id` = $dealer_id");
            $db->setQuery($query);
            $list_clients = $db->loadObject();

            if ($list_clients->count > 0) {
                $where = "`change_time` > '$change_time' AND `client_id` IN ($list_clients->ids)";

                $query = $db->getQuery(true);
                $query->select('*');
                $query->from('`#__gm_ceiling_clients_contacts`');
                $query->where($where);
                $db->setQuery($query);
                $list_contacts = $db->loadObjectList();
                
                $query = $db->getQuery(true);
                $query->select('*');
                $query->from('`#__gm_ceiling_client_history`');
                $query->where($where);
                $db->setQuery($query);
                $list_client_history = $db->loadObjectList();

                $query = $db->getQuery(true);
                $query->select('*');
                $query->from('`#__gm_ceiling_callback`');
                $query->where($where);
                $db->setQuery($query);
                $list_calls = $db->loadObjectList();

                $query = $db->getQuery(true);
                $query->select('*');
                $query->from('`#__gm_ceiling_clients_dop_contacts`');
                $query->where($where);
                $db->setQuery($query);
                $list_contacts_dop = $db->loadObjectList();

                $query = $db->getQuery(true);
                $query->select('*');
                $query->from('`#__gm_ceiling_calls_status_history`');
                $query->where($where);
                $db->setQuery($query);
                $list_calls_status_history = $db->loadObjectList();

                $query = $db->getQuery(true);
                $query->select('*');
                $query->from('`#__gm_ceiling_callback`');
                $query->where($where);
                $db->setQuery($query);
                $list_callback = $db->loadObjectList();

                $query = $db->getQuery(true);
                $query->select('*');
                $query->from('`#__gm_ceiling_clients_statuses_map`');
                $query->where($where);
                $db->setQuery($query);
                $list_clients_statuses_map = $db->loadObjectList();

                $query = $db->getQuery(true);
                $query->select('*');
                $query->from('`#__gm_ceiling_clients_labels_history`');
                $query->where($where);
                $db->setQuery($query);
                $list_clients_labels_history = $db->loadObjectList();
            }

            $query = $db->getQuery(true);
            $query->select('*');
            $query->from('`#__gm_ceiling_clients`');
            $query->where("`change_time` > '$change_time' AND `dealer_id` = $dealer_id");
            $db->setQuery($query);
            $list_clients = $db->loadObjectList();

            $query = $db->getQuery(true);
            $query->select('*');
            $query->from('`#__gm_ceiling_calls_status`');
            $query->where("`change_time` > '$change_time'");
            $db->setQuery($query);
            $list_calls_status = $db->loadObjectList();

            $query = $db->getQuery(true);
            $query->select('*');
            $query->from('`#__gm_ceiling_clients_statuses`');
            $query->where("`change_time` > '$change_time' and (`dealer_id` = $dealer_id or `id` = 1)");
            $db->setQuery($query);
            $list_clients_statuses = $db->loadObjectList();

            $query = $db->getQuery(true);
            $query->select('*');
            $query->from('`#__gm_ceiling_api_phones`');
            $query->where("`change_time` > '$change_time' and `dealer_id` = $dealer_id");
            $db->setQuery($query);
            $list_api_phones = $db->loadObjectList();

            $query = $db->getQuery(true);
            $query->select('*');
            $query->from('`#__gm_ceiling_messenger_types`');
            $query->where("`change_time` > '$change_time'");
            $db->setQuery($query);
            $list_messenger_types = $db->loadObjectList();

            $query = $db->getQuery(true);
            $query->select('*');
            $query->from('`#__gm_ceiling_clients_labels`');
            $query->where("`change_time` > '$change_time and `dealer_id` = $dealer_id'");
            $db->setQuery($query);
            $list_clients_labels = $db->loadObjectList();

            $query = $db->getQuery(true);
            $query->select('`u`.`id`,
                            `u`.`name`,
                            `u`.`username`,
                            `u`.`email`,
                            `u`.`dealer_id`,
                            `u`.`settings`,
                            `u`.`change_time`');
            $query->from('`rgzbn_users` as `u`');
            $query->innerJoin('`rgzbn_user_usergroup_map` as `um` on
                `u`.`id` = `um`.`user_id`');
            $query->where("`u`.`change_time` > '$change_time' and `u`.`dealer_id` = $dealer_id and (`um`.`group_id` = 13 or `u`.`id` = $dealer_id)");
            $db->setQuery($query);
            $list_users = $db->loadObjectList();

            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("`rgzbn_gm_ceiling_messenger_types`");
            $db->setQuery($query);
            $list_mesengers = $db->loadObjectList();

			$result = [];
   			$result['rgzbn_gm_ceiling_clients'] = $list_clients;
   			$result['rgzbn_gm_ceiling_clients_contacts'] = $list_contacts;
   			$result['rgzbn_gm_ceiling_clients_dop_contacts'] = $list_contacts_dop;
   			$result['rgzbn_gm_ceiling_callback'] = $list_callback;
   			$result['rgzbn_gm_ceiling_client_history'] = $list_client_history;
   			$result['rgzbn_gm_ceiling_calls_status_history'] = $list_calls_status_history;
   			$result['rgzbn_gm_ceiling_calls_status'] = $list_calls_status;
   			$result['rgzbn_gm_ceiling_clients_statuses'] = $list_clients_statuses;
   			$result['rgzbn_gm_ceiling_api_phones'] = $list_api_phones;
   			$result['rgzbn_gm_ceiling_clients_statuses_map'] = $list_clients_statuses_map;
            $result['rgzbn_gm_ceiling_messenger_types'] = $list_messenger_types;
            $result['rgzbn_users'] = $list_users;
            $result['rgzbn_gm_ceiling_clients_labels'] = $list_clients_labels;
            $result['rgzbn_gm_ceiling_clients_labels_history'] = $list_clients_labels_history;

            $result_is_empty = true;
            foreach ($result as $value) {
                if (!empty($value)) {
                    $result_is_empty = false;
                    break;
                }
            }
            if ($result_is_empty) {
                return null;
            }
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function get_measure_time($date) {
        try {
            $gauger_model = Gm_ceilingHelpersGm_ceiling::getModel('gaugers');
            return $gauger_model->getFreeGaugingTimes($date);
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function rec_to_measure($data) {
        try {
            if (!empty($data->user_id)) {

                $client_id = JFactory::getUser($data->user_id)->associated_client;
                $dealer_id = $data->user_id;
            } else {
                if (!empty($data->name)){
                     $name = delete_string_characters($data->name);
                } else {
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
                if ($client_id == 'client_found') {
                    return 'client_found';
                }
                if($name == "Клиент") {
                    $name.=$client_id;
                }
                //создание user'а
                $dealer_id = Gm_ceilingHelpersGm_ceiling::registerUser($name, $phone, "$client_id@$client_id", $client_id,2);

                $client_model = Gm_ceilingHelpersGm_ceiling::getModel('Client', 'Gm_ceilingModel');
                $client_model->updateClient($client_id, null, $dealer_id);

                $dealer_info_model = Gm_ceilingHelpersGm_ceiling::getModel('Dealer_info', 'Gm_ceilingModel');
                $dealer_info_model->update_city($dealer_id, $city);
            }
            if (empty($data->advt)) {
                die('empty advt!');
            }
            $status = 1;
            if (!empty($data->status)) {
                $status = $data->status;
            }
            $address = $data->address;
            $date_time = $data->date_time;
            $gauger_model = Gm_ceilingHelpersGm_ceiling::getModel('gaugers');
            $gaug_id = $gauger_model->getFreeGaugers($date_time)[0];
            if (empty($gaug_id)) {
                $gaug_id = null;
            }
            $project_data = [
                        "client_id" => $client_id,
                        "project_info" => $address,
                        "project_calculation_date" => $date_time,
                        "project_status"=>$status,
                        "api_phone_id"=>$data->advt,
                        "project_calculator"=>$gaug_id
                    ];

            $projectform_model = Gm_ceilingHelpersGm_ceiling::getModel('projectform', 'Gm_ceilingModel');
            $project = $projectform_model->save($project_data);
            if (!empty($data->calc_id)) {
                $calculationModel = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
                $calculationModel->changeProjectId($data->calc_id, $project);
            }
            $callback_model = Gm_ceilingHelpersGm_ceiling::getModel('callback');
            if ($status == 1) {
                $callback_model->save(date("Y-m-d H:i:s"), "Клиент заказал замер через гмпотолки. Уточнить данные", $client_id, 1);    
            } elseif ($status == 5) {
                $callback_model->save(date("Y-m-d H:i:s"), "Клиент запустил договор в производство через гмпотолки. Уточнить данные", $client_id, 1);
            }
            $result = [
                        "user_id" => $dealer_id,
                        "username" => JFactory::getUser($dealer_id)->username,
                        "project_id" => $project
                        ];
            return (object)$result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function change_password($data) {
        try {
            $user = JFactory::getUser($data->user_id);
            if (!empty($data->old_password)) {
                $verifyPass = JUserHelper::verifyPassword($data->old_password, $user->password, $user->id);
                if ($verifyPass) {
                    return $this->change_pass($user->id,$data->password);
                } else return false;
            } else {
                return false;
            }
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function change_pass($user_id,$pass) {
        try {
            $user_model = Gm_ceilingHelpersGm_ceiling::getModel('Users');
            return $user_model->change_user_pass($user_id,$pass);
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}