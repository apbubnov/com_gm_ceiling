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
	function delete($project_id){
		try{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->delete('`#__gm_ceiling_projects_mounts` as mp');
			$query->where("mp.project_id = $project_id");
			$db->setQuery($query);
			$result = $db->execute();
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
		
	}
	function save($project_id,$mounts){
		try{
			$db = JFactory::getDbo();
			if(!empty($project_id) && !empty($mounts)){
				$this->delete($project_id);
				foreach ($mounts as $value) {
					$query = $db->getQuery(true);
					$query->insert('`#__gm_ceiling_projects_mounts` as mp');
					$query->columns("`project_id`,`mounter_id`,`date_time`,`type`");
					$query->values("$project_id,$value->mounter,'$value->time',$value->stage");
					$db->setQuery($query);
					$result = $db->execute();
					return true;
				}
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
}