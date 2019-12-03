<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 21.11.2019
 * Time: 16:04
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');
class Gm_ceilingModelBuildersDopCosts extends JModelList
{
    function save($builderid,$costSum,$costComment,$check){
        try{
            $user = JFactory::getUser();
            $userGroups = $user->groups;
            if(in_array('33',$userGroups)){
                $columns = '`builder_id`,`sum`,`from_user`,`accepted_by`,`check`,`description`';
                $values = "$builderid,$costSum,$user->id,$user->id,'$check','$costComment'";
            }
            else{
                $columns = '`builder_id`,`sum`,`from_user`,`check`,`description`';
                $values = "$builderid,$costSum,$user->id,'$check','$costComment'";
            }
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->insert('`rgzbn_gm_ceiling_builders_dop_costs`')
                ->columns($columns)
                ->values($values);
            $db->setQuery($query);
            $db->execute();
            return true;

        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getData($builderid){
        try{
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('c.sum,c.from_user,u.name as from_name,c.accepted_by, u1.name as accepted_name,c.check,c.description')
                ->from('`rgzbn_gm_ceiling_builders_dop_costs` as c')
                ->leftJoin('`rgzbn_users` as u on u.id = c.from_user')
                ->leftJoin('`rgzbn_users` as u1 on u1.id = c.accepted_by')
                ->where("builder_id = $builderid");
            $db->setQuery($query);
            $items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}