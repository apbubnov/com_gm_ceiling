<?php
// No direct access
defined('_JEXEC') or die;

class Gm_ceilingControllerUsers extends JControllerForm
{

	public function deleteUser() {
		try
		{
			$dealer = JFactory::getUser();
			$jinput = JFactory::getApplication()->input;
            $user_id = $jinput->get('user_id', null, 'INT');

			$model = Gm_ceilingHelpersGm_ceiling::getModel('users');
			$result = $model->delete($user_id, $dealer->dealer_id);
			
			die(json_encode($result));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	function getUserByGroup(){
		try
		{
			$dealer = JFactory::getUser();
			$jinput = JFactory::getApplication()->input;
			$model = Gm_ceilingHelpersGm_ceiling::getModel('users');
			$group_id = $jinput->get('group','',"STRING");
			$result = $model->getUserByGroup($group_id);
			die(json_encode($result));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}
/*    function rus2translit($string) {
        $converter = array(
            'а' => 'a',   'б' => 'b',   'в' => 'v',
            'г' => 'g',   'д' => 'd',   'е' => 'e',
            'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
            'и' => 'i',   'й' => 'y',   'к' => 'k',
            'л' => 'l',   'м' => 'm',   'н' => 'n',
            'о' => 'o',   'п' => 'p',   'р' => 'r',
            'с' => 's',   'т' => 't',   'у' => 'u',
            'ф' => 'f',   'х' => 'h',   'ц' => 'c',
            'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
            'ь' => '\'',  'ы' => 'y',   'ъ' => '\'',
            'э' => 'e',   'ю' => 'yu',  'я' => 'ya',

            'А' => 'A',   'Б' => 'B',   'В' => 'V',
            'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
            'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
            'И' => 'I',   'Й' => 'Y',   'К' => 'K',
            'Л' => 'L',   'М' => 'M',   'Н' => 'N',
            'О' => 'O',   'П' => 'P',   'Р' => 'R',
            'С' => 'S',   'Т' => 'T',   'У' => 'U',
            'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
            'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
            'Ь' => '\'',  'Ы' => 'Y',   'Ъ' => '\'',
            'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
        );
        return strtr($string, $converter);
    }*/

	function registerMounterForBuilding(){
	    try{
	        $jinput = JFactory::getApplication()->input;
	        $brigadeName = $jinput->get('name','','STRING');
	        $brigadePhone = $jinput->get('phone','','STRING');
	        if(!empty($brigadePhone)){
                $brigadePhone = mb_ereg_replace('[^\d]', '', $brigadePhone);
                if (mb_substr($brigadePhone, 0, 1) == '9' && strlen($brigadePhone) == 10)
                {
                    $brigadePhone = '7'.$brigadePhone;
                }
                if (mb_substr($brigadePhone, 0, 1) != '7')
                {
                    $brigadePhone = substr_replace($brigadePhone, '7', 0, 1);
                }
            }
	        else{
                $str = Gm_ceilingHelpersGm_ceiling::rus2translit($brigadeName);
                // в нижний регистр
                $str = strtolower($str);
                // заменям все ненужное нам на "-"
                $str = preg_replace('~[^-a-z0-9_]+~u', '-', $str);
                // удаляем начальные и конечные '-'
                $brigadePhone = trim($str, "-");
            }
            $data = array(
                "name" => $brigadeName,
                "username" => $brigadePhone,
                "password" => $brigadePhone,
                "password2" => $brigadePhone,
                "email" => $brigadePhone."@none",
                "groups" => array(2, 34),
                "dealer_type" => 0,
                "dealer_id" => 1
            );
            $user = new JUser;
            if (!$user->bind($data)) {
                throw new Exception($user->getError());
            }
            if (!$user->save()) {
                throw new Exception($user->getError());
            }
            die(json_encode(true));
	    }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function closeBuilderObject(){
	    try{
            $jinput = JFactory::getApplication()->input;
            $builderid = $jinput->getInt('builderId');
            $userModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
            $result = $userModel->addGroup($builderid,36);
            die(json_encode($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function saveMounterDebt(){
	    try{
	        $jinput = JFactory::getApplication()->input;
	        $mounterId = $jinput->getInt('mounterId');
	        $type = $jinput->getInt('type');
	        $sum = $jinput->get('sum','','STRING');
	        $model = Gm_ceilingHelpersGm_ceiling::getModel('mountersDebt');
	        $result = $model->save($mounterId,$sum,$type);
	        die(json_encode($result));

        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }

    }

    function getMounterDebtData(){
	    try{
            $jinput = JFactory::getApplication()->input;
            $mounterId = $jinput->getInt('mounterId');
            $model = Gm_ceilingHelpersGm_ceiling::getModel('mountersDebt');
            $result = $model->getData($mounterId);
            die(json_encode($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
?>