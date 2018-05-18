<?php

/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     SpectralEye <Xander@spectraleye.ru>
 * @copyright  2016 SpectralEye
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
 // No direct access.
 defined('_JEXEC') or die;
 
 jimport('joomla.application.component.modelitem');
 jimport('joomla.event.dispatcher');

/**
 * Methods supporting a list of Gm_ceiling records.
 *
 * @since  1.6
 */
class Gm_ceilingModelRepeatRequest extends JModelList
{
	function getData(){
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('*')
                ->from('#__gm_ceiling_repeat_request');
            $db->setQuery($query);
            $items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function save($project_id,$advt_id){
        try
        {
            if(empty($advt_id)){
                $advt_id = "NULL";
            }
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->insert('#__gm_ceiling_repeat_request')
                ->columns('`project_id`,`advt_id`')
                ->values("$project_id,$advt_id");
            $db->setQuery($query);
            $db->execute();
            $last_id = $db->insertid();
            
            return $last_id;
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function update($proj_id,$advt_id)
    {
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->update('#__gm_ceiling_repeat_request')
                ->set("`advt_id` = $advt_id")
                ->where("project_id = $proj_id");
            $db->setQuery($query);
            $db->execute();
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function getDataByProjectId($id){
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('*')
                ->from('#__gm_ceiling_repeat_request')
                ->where("project_id = $id");
            $db->setQuery($query);
            $item = $db->loadObject();
            return $item;
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
?>