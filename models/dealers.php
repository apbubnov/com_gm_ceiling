<?php

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');
jimport('joomla.event.dispatcher');

use Joomla\Utilities\ArrayHelper;
/**
 * Gm_ceiling model.
 *
 * @since  1.6
 */
class Gm_ceilingModelDealers extends JModelItem
{
	function run_update(){
		try{
			$dealers = $this->select_dealers();
			$this->check_and_update_status($dealers);
			return true;
		}
		catch(Exception $e){
			Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
		}
	}
	function select_dealers(){
		/*SELECT u.id,u.name,pp.*
			FROM `rgzbn_users` AS u
			INNER JOIN `rgzbn_user_usergroup_map` AS um ON um.user_id = u.id
			LEFT JOIN (
			SELECT GROUP_CONCAT(DISTINCT p.id SEPARATOR ',') AS projects,MAX(ph.date_of_change ) AS date_change,c.dealer_id
			FROM `rgzbn_gm_ceiling_projects` AS p
			LEFT JOIN `rgzbn_gm_ceiling_projects_history` AS ph ON ph.project_id = p.id
			LEFT JOIN `rgzbn_gm_ceiling_clients` AS c ON p.client_id = c.id
			WHERE ph.new_status = 5
			GROUP BY c.dealer_id) AS pp ON pp.dealer_id = u.id
			WHERE u.dealer_type IN (0,1,6) AND um.group_id IN (14,27,28,29,30) AND u.associated_client IS NOT NULL */
		try{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$subquery = $db->getQuery(true);
			$subquery
				->select("GROUP_CONCAT(DISTINCT p.id SEPARATOR ',') AS projects,MAX(ph.date_of_change ) AS date_change,c.dealer_id")
				->from("`#__gm_ceiling_projects` AS p")
				->leftJoin("`#__gm_ceiling_projects_history` AS ph ON ph.project_id = p.id")
				->leftJoin("`#__gm_ceiling_clients` AS c ON p.client_id = c.id")
				->where("ph.new_status = 5")
				->group("c.dealer_id");
			$query
				->select("u.id,u.name,info.*")
				->from("`#__users` AS u")
				->innerJoin("`#__user_usergroup_map` AS um ON um.user_id = u.id")
				->leftJoin("($subquery) AS info ON info.dealer_id = u.id")
				->where("u.dealer_type IN (0,1,6) AND um.group_id IN (14,27,28,29,30) AND u.associated_client IS NOT NULL");
			$db->setQuery($query);
			$items = $db->loadObjectList();
			return count($items);
		}
		catch(Exception $e){
			Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
		}
	}

	function check_and_update_status($dealers){
		try{
			$groups_arr = [27,28,29];
			$today = date("Y-m-d");
			foreach ($dealers as $key => $value) {
				$dealer_groups = explode(',',$value->groups);
				if($value->projects == 0 && empty($value->date_change)){
					foreach ($groups as $group) {
						if(!in_array('30',$dealer_groups)){
							//вставить
						}
					}
				}
				else{
					$diff = date('d', strtotime($today)) - date('d', strtotime($value->date_change));
					switch(true){
						case $diff>=0 && $diff<=14:
							//заказывает
							$exist_group = $this->search_group($groups_arr,$dealer_groups);
							if($exist_group != 27){
								$this->update_group($value->id,$exist_group,'27');
							}
							break;
						case $diff>14 && $diff<=21:
							//перестал
							$exist_group = $this->search_group($groups_arr,$dealer_groups);
							if($exist_group != 28){
								$this->update_group($value->id,$exist_group,'28');
							}
							break;
						case $diff>21 && $diff<=28:
							//не заказывает
							$exist_group = $this->search_group($groups_arr,$dealer_groups);
							if($exist_group != 29){
								$this->update_group($value->id,$exist_group,'29');
							}
							break;
					}
				}
			}
		}
		catch(Exception $e){
			Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
		}
	}

	

	function search_group($groups,$dealer_groups){
		try{
			foreach ($groups as $group) {
				if(in_array($group,$dealer_groups)){
					return $group;
				}
			}
		}
		catch(Exception $e){
			Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
		}
	}	

	function update_group($dealer_id,$old_group,$new_group){
		try{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			if (!empty($old_group)) {
				$query
					->delete('`#__user_usergroup_map`')
					->where("user_id = $dealer_id and group_id = $old_group");
				$db->setQuery($query);
				$db->execute();
				$query = $db->getQuery(true);
			}
			$query
				->insert('`#__user_usergroup_map`')
				->columns('user_id,group_id')
				->values("$dealer_id,$new_group");
			$db->setQuery($query);
			$db->execute();
		}
		catch(Exception $e){
			Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
		}
	}	
}
