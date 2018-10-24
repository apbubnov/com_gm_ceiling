<?php
class Gm_ceilingModelProjects_materials extends JModelItem
{
	function getData($project_id){
		try{
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('*')
                ->from('`#__gm_ceiling_projects_materials`')
                ->where("project_id=$project_id");
            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }   
	}

	function save($project_id,$data){
		try{
			$db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
            	->insert('`#__gm_ceiling_projects_materials`')
            	->columns('`project_id`,`data`')
            	->values("$project_id,'$data'");
            $db->setQuery($query);
            $db->execute();
            return true;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }  
	}
	
}
?>
