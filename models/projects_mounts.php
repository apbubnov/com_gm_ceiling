<?php

/**
 * @package    Com_Gm_ceiling
 * @author     Alexandr <al.p.bubnov@gmail.com>
 * @copyright  GM
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
class Gm_ceilingModelProjects_mounts extends JModelList
{

	function getData($project_id){
		try{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select("m.type as stage,m.date_time as time,m.mounter_id as mounter")
				->from('`#__gm_ceiling_projects_mounts`  as m')
				->where("project_id = $project_id");
			$db->setQuery($query);
			$result = $db->loadObjectList();
			return $result;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function delete($project_id){
		try{

            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'mount_stage_history.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.print_r($this->getData($project_id),true)."\n----------\n", FILE_APPEND);


            $db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->delete('`#__gm_ceiling_projects_mounts`');
			$query->where("project_id = $project_id");
			$db->setQuery($query);
			$result = $db->execute();

            return $result;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

    function deleteByStage($project_id,$stages){
        try{

            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'mount_stage_history.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.print_r($this->getData($project_id),true)."\n----------\n", FILE_APPEND);


            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->delete('`#__gm_ceiling_projects_mounts`');
            $query->where("project_id = $project_id and type in ($stages)");
            $db->setQuery($query);
            $result = $db->execute();

            return $result;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
	function save($project_id,$mounts){
		try{

			if(!empty($project_id) && !empty($mounts)){
				$this->delete($project_id);
                $db = JFactory::getDbo();
                $values = [];
				foreach ($mounts as $value) {
				    $values[]="$project_id,$value->mounter,'$value->time',$value->stage";
                }
                $query = $db->getQuery(true);
                $query->insert('`#__gm_ceiling_projects_mounts`');
                $query->columns("`project_id`,`mounter_id`,`date_time`,`type`");
                $query->values($values);
                $db->setQuery($query);
                $result = $db->execute();
                $date = date("d.m.Y H:i:s");
                $files = "components/com_gm_ceiling/";
                file_put_contents($files.'mount_stage_history.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.print_r($mounts,true)."\n----------\n", FILE_APPEND);

				return true;
			}
			else{
				throw new Exception("Empty project_id or mounts_array");
			}
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function get_mount_types(){
		try{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('`#__gm_ceiling_mounts_types`');
			$db->setQuery($query);
			$result = $db->loadAssocList('id', 'title');
			return $result;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function saveOrUpdateStage($projectId,$data){
	    try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select("m.type as stage,m.date_time as time,m.mounter_id as mounter")
                ->from('`#__gm_ceiling_projects_mounts`  as m')
                ->where("m.project_id = $projectId and m.type =". $data[0]->stage." and m.mounter_id = ".$data[0]->mounter);
            $db->setQuery($query);
            $items = $db->loadObjectList();
            if(empty($items)){
                foreach ($data as $value) {
                    $query = $db->getQuery(true);
                    $query->insert('`#__gm_ceiling_projects_mounts`');
                    $query->columns("`project_id`,`mounter_id`,`date_time`,`type`");
                    $query->values("$projectId,$value->mounter,'$value->time',$value->stage");
                    $db->setQuery($query);
                    $db->execute();
                }
            }
            else{
                foreach ($data as $value) {
                    $query = $db->getQuery(true);
                    $query->update('`#__gm_ceiling_projects_mounts`');
                    $query->set("`mounter_id`= $value->mounter");
                    $query->where("project_id = $projectId and `type` = $value->stage");
                    $db->setQuery($query);
                    $db->execute();
                }
            }
            return true;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function removeProjectMountByBrigade($projectId,$brigadeId){
	    try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->delete('`#__gm_ceiling_projects_mounts`');
            $query->where("project_id = $projectId and mounter_id = $brigadeId");
            $db->setQuery($query);
            $db->execute();

            return true;
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}