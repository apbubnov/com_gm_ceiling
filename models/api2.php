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
class Gm_ceilingModelApi2 extends JModelList
{
   
    public function get_material_android($data)
    {
        try
        {
            $db = $this->getDbo();
            $change_time = $db->escape($data->change_time, false);
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
            $query->select("u.id as id,u.name as `name`,um.group_id as `group_id`");
            $query->from("`rgzbn_users` as u");
            $query->leftJoin('`rgzbn_user_usergroup_map`AS um ON um.user_id = u.id');
            $query->where("u.change_time >= '$change_time' and  u.id = $dealer_id AND um.group_id = 14");
            $db->setQuery($query);
            $list_dealer = $db->loadObjectList();
            //Бригады
            $query = $db->getQuery(true);
            $query->select("u.id as id,u.name as `name`,um.group_id as `group_id`");
            $query->from("`rgzbn_users` as u");
            $query->leftJoin('`rgzbn_user_usergroup_map`AS um ON um.user_id = u.id');
            $query->where("u.change_time >= '$change_time' and  u.dealer_id = $dealer_id AND um.group_id = 11");
            $db->setQuery($query);
            $list_brigades = $db->loadObjectList();

            //замерщики
            $query = $db->getQuery(true);
            $query->select("u.id as id,u.name as `name`,um.group_id as `group_id`");
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

            $result = [];
            $result['rgzbn_gm_ceiling_dealer_info'] = $list_info;
            $result['rgzbn_gm_ceiling_mount'] =  $list_mount;

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

}