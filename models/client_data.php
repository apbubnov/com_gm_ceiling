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
class Gm_ceilingModelClient_data extends JModelList
{
    function getData($clientId){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('*')
                ->from('rgzbn_gm_ceiling_clients_data')
                ->where("client_id = $clientId");
            $db->setQuery($query);
            $items = $db->loadObject();
            return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function saveDocument($clientId,$document){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $data = $this->getData($clientId);
            if(!empty($data)){
                $query
                    ->update('rgzbn_gm_ceiling_clients_data')
                    ->set("document = '$document'")
                    ->where("client_id = $clientId");
            }
            else{
                $query
                    ->insert('rgzbn_gm_ceiling_clients_data')
                    ->columns('`client_id`,`document`')
                    ->values("$clientId,'$document'");
            }
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