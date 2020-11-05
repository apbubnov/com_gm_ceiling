<?php
class Gm_ceilingModelJobs extends JModelList{
    public function get($id = null){
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('`j`.`id`,  
                    `j`.`name`, 
                    `j`.`price`, 
                    `j`.`guild_only`,
                    `j`.`is_factory_work`,
                    `j`.`mount_type_id`')
                ->from('`rgzbn_gm_ceiling_jobs` as `j`');
            if (!empty($id)) {
                $query->where("`j`.`id`= $id");
            }
            $db->setQuery($query);
            if(!empty($id)){
                $items = $db->loadObject();
            }
            else{
                $items = $db->loadObjectList();

            }
            return $items;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}