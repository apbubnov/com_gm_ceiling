<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 13.11.2018
 * Time: 10:32
 */
class Gm_ceilingModelCanvases_manufacturers extends JModelList
{
    function getData(){
        try{
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select("*")
                ->from('`#__gm_ceiling_canvases_manufacturers`');
            $db->setQuery($query);
            $result = $db->loadAssocList('id');
            return $result;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }
}