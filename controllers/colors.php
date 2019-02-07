<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail  <vms@itctl.ru>
 * @copyright  2016 Mikhail 
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Colors list controller class.
 *
 * @since  1.6
 */
class Gm_ceilingControllerColors extends Gm_ceilingController
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional
	 * @param   array   $config  Configuration array for model. Optional
	 *
	 * @return object	The model
	 *
	 * @since	1.6
	 */
	public function &getModel($name = 'Colors', $prefix = 'Gm_ceilingModel', $config = array())
	{
		try
		{
			$model = parent::getModel($name, $prefix, array('ignore_request' => true));

			return $model;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}
	function getColors(){
	    try{
	        $jinput = JFactory::getApplication()->input;
	        $type = $jinput->get('type','','STRING');
	        $model = $this->getModel();
	        $result = $model->getData($type);
	        die(json_encode($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }
    function createColorImage($name = null,$textures=null,$color_code=null)
    {
        try
        {
            $result = [];
            $width = 150;
            $height = 110;
            $ajax = false;
            if(empty($name)&&empty($textures)&&empty($color_code)) {
                $jinput = JFactory::getApplication()->input;
                $color_code = $jinput->get('hexCode', '', 'STRING');
                $textures = $jinput->get('textures', [], 'ARRAY');
                $name = $jinput->get('name', '', 'STRING');
                $ajax=true;
            }

            if (substr($color_code, 0, 1) != '#') {
                $color_code = '#'.$color_code;
            }
            $red = hexdec(substr($color_code, 1, 2));
            $green = hexdec(substr($color_code, 3, 2));
            $blue = hexdec(substr($color_code, 5, 2));


            $img = imagecreatetruecolor($width, $height) or die("Ошибка");
            $color = imagecolorallocate($img, $red, $green, $blue);

            imagefill($img, 0, 0, $color);
            foreach ($textures as $value){
                $filename = $name.$value.".png";
                if($value == "gly"){
                    $gl = imagecreatefrompng($_SERVER['DOCUMENT_ROOT'] . '/images/glyanec.png');
                    imagecopy($img, $gl, 0, 0, 0, 0, $width, $height);
                }
                if($value == "sat"){
                    $sat = imagecreatefrompng($_SERVER['DOCUMENT_ROOT'] . '/images/sat.png');
                    imagecopy($img, $sat, 0, 0, 0, 0, $width, $height);
                }
                imagepng($img, $_SERVER['DOCUMENT_ROOT'] . '/images/canvases/' . $filename);
                array_push($result ,'images/canvases/' . $filename. '?' . rand());
            }
            imagedestroy($img);
            if($ajax) {
                die(json_encode($result));
            }
            else{
                return $result;
            }
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function save()
    {
        try {
            $jinput = JFactory::getApplication()->input;
            $color_code = $jinput->get('hexCode', '', 'STRING');
            $files = $jinput->get('files', [], 'ARRAY');
            $name = $jinput->get('name', '', 'STRING');
            $id = $jinput->get('idColor', '', 'STRING');
            $model = $this->getModel();
            $result = $model->save($id, $name, $color_code, $files);
            die(json_encode($result));
        } catch (Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function update(){
        try{
            $jinput = JFactory::getApplication()->input;
            $color_code = $jinput->get('hexCode', '', 'STRING');
            $name = $jinput->get('name', '', 'STRING');
            $id = $jinput->get('idColor', '', 'STRING');
            $textures = $jinput->get('textures',[],'ARRAY');
            $this->createColorImage($name,$textures,$color_code);
            $model = $this->getModel();
            $result = $model->save($id,$name,$color_code);
            die(json_encode($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
