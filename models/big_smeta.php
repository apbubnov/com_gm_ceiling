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
class Gm_ceilingModelBig_smeta extends JModelList
{
    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    protected function getListQuery()
    {
        try {
            // Создаем запрос
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            return $query;
        } catch (Exception $e) {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files . 'error_log.txt', (string)$date . ' | ' . __FILE__ . ' | ' . __FUNCTION__ . ' | ' . $e->getMessage() . "\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function calculation($id_calc)
    {
        try {
            if (count($id_calc) > 0) {
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query->select("*")
                    ->from('`#__gm_ceiling_calculations`');
                $query->where('id = ' . implode(" OR id = ", $id_calc));
                $db->setQuery($query);
                return $db->loadObjectList();
            } else return array();
        } catch (Exception $e) {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files . 'error_log.txt', (string)$date . ' | ' . __FILE__ . ' | ' . __FUNCTION__ . ' | ' . $e->getMessage() . "\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }

    }
}