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
		catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function getUsersByFilter(){
	    try{
            $jinput = JFactory::getApplication()->input;
            $filter_city = $jinput->get('filter_city',"","STRING");
            $filter_manager = $jinput->get('filter_manager',"","STRING");
            $filter_status = $jinput->get('filter_status',"","STRING");
            $limit = $jinput->get("limit",null,"INT");
            $select_size = $jinput->get("select_size",null,"STRING");
            $client_name = $jinput->get("client","","STRING");
            $label_id = $jinput->get("label_id",null,"int");
            $coop = $jinput->get('coop',0,'INT');
            $model =  Gm_ceilingHelpersGm_ceiling::getModel('users');
            $result = $model->getUsersByFilter($filter_manager,$filter_city,$filter_status,$client_name,$limit,$select_size,$coop, $label_id);
            die(json_encode($result));
        }
        catch(Exception $e) {
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
	        $email = $jinput->get('email','','STRING');
	        $groups = $jinput->get('groups',[],'ARRAY');

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

	        $usersModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
	        $user = $usersModel->getUserByUsername($brigadePhone);

	        if(empty($user)){
                if(empty($email)){
                    $email = $brigadePhone."@none";
                }
                $data = [
                    "name" => $brigadeName,
                    "username" => $brigadePhone,
                    "password" => $brigadePhone,
                    "password2" => $brigadePhone,
                    "email" => $email,
                    "groups" => [2],
                    "dealer_type" => 0,
                    "dealer_id" => 1
                ];
                $user = new JUser;
                if (!$user->bind($data)) {
                    throw new Exception($user->getError());
                }
                if (!$user->save()) {
                    throw new Exception($user->getError());
                }
            }
            if(!empty($user)){
                if(!empty($groups)){
                    foreach ($groups as $id){
                        $usersModel->addGroupToExistUser($brigadePhone, 1, $id);
                    }
                }
            }
            die(json_encode(true));
	    }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function createUser(){
	    try{
	        $jinput = JFactory::getApplication()->input;
	        $name = $jinput->getString('name');
	        $phone = $jinput->getString('phone');
	        $email = $jinput->getString('email');
            $groups = $jinput->get('groups',[],'ARRAY');
            $city = $jinput->get('city','','STRING');
            $withClient = $jinput->get('with_client',0,'INT');

            $currentUser = JFactory::getUser();

            if(!empty($phone)){
                $phone = mb_ereg_replace('[^\d]', '', $phone);
                if (mb_substr($phone, 0, 1) == '9' && strlen($phone) == 10) {
                    $phone = '7' . $phone;
                }
                if (strlen($phone) != 11) {

                    die(json_encode((object)["type"=>"error","message"=>"Неверный формат номера телефона!"]));
                }
                if (mb_substr($phone, 0, 1) != '7') {
                    $phone = substr_replace($phone, '7', 0, 1);
                }
            }
            $usersModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
            $user = $usersModel->getUserByUsername($phone);

            if(empty($user)){
                if(empty($email)){
                    $email = $phone."@none";
                }
                $data = [
                    "name" => $name,
                    "username" => $phone,
                    "password" => $phone,
                    "password2" => $phone,
                    "email" => $email,
                    "groups" => [2],
                    "dealer_type" => 0,
                    "require_reset" => 0,
                    "dealer_id" => $currentUser->dealer_id
                ];
                $user = new JUser;
                if (!$user->bind($data)) {
                    throw new Exception($user->getError());
                }
                if (!$user->save()) {
                    throw new Exception($user->getError());
                }
            }
            if(!empty($user)){
                if(!empty($groups)){
                    foreach ($groups as $id){
                        $usersModel->addGroupToExistUser($phone, $currentUser->dealer_id, $id);
                    }
                }
            }
            if($withClient && empty($user->associated_client)){
                /*если с клиентом*/

                $client_data['client_name'] = mb_ereg_replace('[^\dA-Za-zА-ЯЁа-яё ]', '', $name);
                $client_data['client_contacts'] = mb_ereg_replace('[^\d]', '', $phone);
                $client_data['email'] = $email;
                $client_data['dealer_id'] = $user->id;
                $clientFormModel = Gm_ceilingHelpersGm_ceiling::getModel('clientform');
                $client_id = $clientFormModel->save($client_data);
                $dealer_info_model = Gm_ceilingHelpersGm_ceiling::getModel('Dealer_info', 'Gm_ceilingModel');
                $dealer_info_model->update_city($user->dealer_id,$city);


                $usersModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
                $usersModel->updateAssocClient($user->id,$client_id);
            }
            die(json_encode(true));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function createMountServiceUser(){
	    try{
	        $user = JFactory::getUser();
	        $dealer = JFactory::getUser($user->dealer_id);
	        $usersModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
	        $result = $usersModel->createMountServiceUser($dealer);
	        die(json_encode($result));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function deleteUserFromGroup(){
	    try{
	        $jinput = JFactory::getApplication()->input;
	        $userId = $jinput->getInt('id');
	        $groupId = $jinput->getInt('group');
	        $user = JFactory::getUser();
	        $usersModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
	        $result = $usersModel->deleteUserFromGroup($userId,$groupId,$user->dealer_id);
	        die(json_encode($result));
        }
        catch(Exception $e){
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

    function saveBuilderDopCosts(){
	    try{
            $jinput = JFactory::getApplication()->input;
            $costSum = $jinput->get('cost_sum','','STRING');
            $costComment = $jinput->get('cost_comment','','STRING');
            $checksUpload = $jinput->getInt('checks_upload');
            $builderId = $jinput->getInt('builder_id');
            $images = [];
            if(!empty($checksUpload)){
                foreach ($_FILES as $file){
                    if(exif_imagetype($file['tmp_name'])){
                        $images[] = $file;
                    }
                }
            }
            if(!empty($images)){
                if (!is_dir('additional_builder_costs/'.$builderId)) {
                    if (!mkdir('additional_builder_costs/'.$builderId, 0777, true)) {
                        throw new Exception('Dir not maked', 500);
                    }
                }
                $dir = 'additional_builder_costs/'.$builderId.'/';
                $checks = [];
                foreach ($images as $file) {
                    $md5 = md5($builderId.microtime().$file['name']);
                    if (is_uploaded_file($file['tmp_name'])) {
                        if (move_uploaded_file($file['tmp_name'], $dir.$md5)) {
                        } else {
                            throw new Exception('Uploaded file not moved', 500);
                        }
                    } else {
                        throw new Exception('File not uploaded', 500);
                    }
                    $checks[] = $md5;
                }
                $checks = implode(',',$checks);
                $dopCostModel = Gm_ceilingHelpersGm_ceiling::getModel('buildersdopcosts');
                $dopCostModel->save($builderId,$costSum,$costComment,$checks);
            }
            else{
                $result = (object)array("type"=>"error","text"=>"Отсутствует изображение чека!");
            }
            die(json_encode($result));

        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getBuildersDopCost(){
	    try{
            $jinput = JFactory::getApplication()->input;
            $builderId = $jinput->get('builder_id');
            $dopCostModel = Gm_ceilingHelpersGm_ceiling::getModel('buildersdopcosts');
            $result = $dopCostModel->getData($builderId);
            die(json_encode($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function registerClient(){
        try{
            $jinput = JFactory::getApplication()->input;
            $clientId = $jinput->getInt('client_id');
            $phone = $jinput->get('phone','','STRING');
            $email = $jinput->get('email','','STRING');
            $clientModel = Gm_ceilingHelpersGm_ceiling::getModel('client');
            $client = $clientModel->getClientById($clientId);
            $userModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
            $user = $userModel->getUserByUsername($phone);
            $text = '';
            if(empty($user)){
                Gm_ceilingHelpersGm_ceiling::registerUser($client->client_name,$phone,$email,$clientId,2,0);
                die(json_encode(true));
            }
            else{
                if($user->dealer_type == 2){
                    $text ='Кабинет клиента уже создан!';
                }
                if($user->dealer_type == 3 || $user->dealer_type == 8){
                    $text ='Пользователь с таким логином является отделочником или оконщиком!';
                }
            }
            $error = (object)array('type'=>'error','text'=>$text);
            die(json_encode($error));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

    function getManufacturerInfo(){
        try{
            $userModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
            $manufacturersInfo = $userModel->getManufacturersInfo();
            die(json_encode($manufacturersInfo));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function checkCode(){
	    try{
            $result = (object)array("type"=>"","data"=>"");
            $jinput = JFactory::getApplication()->input;
            $phone = $jinput->get('phone','','STRING');
            $code = $jinput->getInt('code');
            $userModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
            $user = $userModel->getUserByUsername($phone);
            $now = date('Y-m-d H:i:s');
            $codeCreationTime = $user->code_creation_time;
            $diff = mktime($now) - mktime($codeCreationTime);
            if($diff >=3600){
                $result->type = 'error';
                $result->data = 'Код устарел!';
            }
            else{
                if($user->verification_code == $code){
                    $result->type = 'success';
                    $result->data = 'true';
                    $userModel->setVerificationCode($user->id,null);
                    Gm_ceilingHelpersGm_ceiling::forceLogin($user->id);
                }
                else{
                    $result->type = 'error';
                    $result->data = 'Введен неверный код!';
                }
            }
            die(json_encode($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getVisitors(){
	    try{
	        $jinput = JFactory::getApplication()->input;
	        $date_from = $jinput->get('date_from',date(),'STRING');
	        $date_to = $jinput->get('date_to',date(),'STRING');
	        $usersModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
	        $result = $usersModel->getVisitors($date_from,$date_to);
	        die(json_encode($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function delMounterDebtData(){
	    try{
	        $jinput = JFactory::getApplication()->input;
	        $id = $jinput->getInt('id');
	        $mounter = $jinput->getInt('mounter');
	        $debtModel = Gm_ceilingHelpersGm_ceiling::getModel('mountersDebt');
	        $debtModel->deleteDebt($id,$mounter);
	        die(json_encode(true));
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function registerNewUser(){
	    try {
            $user = JFactory::getUser();
            $userId = $user->get('id');
            $dealerId = $user->dealer_id;

            $jinput = JFactory::getApplication()->input;
            $name = $jinput->get('fio', '', 'STRING');
            $phone = $jinput->get('phone', '', 'STRING');
            $phone = preg_replace('/[\(\)\-\+\s]/', '', $phone);
            $email = $jinput->get('email', '', 'STRING');

            //генератор пароля
            $chars = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
            $max = 6;
            $size = StrLen($chars) - 1;
            $password = null;
            while ($max--) {
                $password .= $chars[rand(0, $size)];
            }
            //-----------------------------------
            jimport('joomla.user.helper');

            if ($dealerId == 1) {
                $group = 16;
            } else {
                $group = 13;
            }

            $data = [
                "name" => $name,
                "username" => $phone,
                "password" => $password,
                "email" => $email,
                "groups" => [2,$group],
                "dealer_id" => $dealerId,
                "requireReset" => 0
            ];

            $user = new JUser;
            if (!$user->bind($data)) {
               //throw new Exception($user->getError());
                $result = ["type"=>'error','text'=>$user->getError()];
                die(json_encode($result));
            }
            if (!$user->save()) {
                if($user->getError() == 'Имя пользователя занято') {
                    $userModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
                    $id_brigade = $userModel->addGroupToExistUser($phone, $dealerId, $group);
                }
               /* $result = ["type"=>'error','text'=>$user->getError()];
                die(json_encode($result));*/
            }

            // письмо
            $mailer = JFactory::getMailer();
            $config = JFactory::getConfig();
            $sender = [
                $config->get('mailfrom'),
                $config->get('fromname')
            ];
            $mailer->setSender($sender);
            $mailer->addRecipient($email);
            $body = "Здравствуйте. Вас зарегистрировали в системе \"CRM Master \" . Данные учетной записи: \n Логин: " . $phone . " \n Пароль: " . $password;
            $mailer->setSubject('Регистрация в CRM Master');
            $mailer->setBody($body);
            $send = $mailer->Send();
            $result = ["type"=>'success','text'=>'Пользователь зарегистрирован, данные для входа отправлены на указанный email.'];
            die(json_encode($result));
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function updateDopNumber(){
	    try{
            $jinput = JFactory::getApplication()->input;
            $userId = $jinput->getInt('userId');
            $dopNum = $jinput->get('number','','STRING');
            $userModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
            $userModel->updateDopNumber($userId,$dopNum);
            die(json_encode(true));
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getDealerIdMap(){
	    try {
	        $jinput = JFactory::getApplication()->input;
	        $phone = $jinput->get('phone','','STRING');
	        $pass = $jinput->get('code','','STRING');
        
	        $usersModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
	        $mapModel = Gm_ceilingHelpersGm_ceiling::getModel('users_dealer_id_map');
	        $user = $usersModel->getUserByUsername($phone);
            $verifyPass = JUserHelper::verifyPassword($pass, $user->password, $user->id);
        
            if($verifyPass){
	           $result = $mapModel->getDealerIdMap($user->id);
            }
            else{
                $result = (object)["error"=>'Некорректный логин/пароль!'];
            }
	        die(json_encode($result));
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function changeDataBeforeLogin(){
	    try{
	        $jinput = JFactory::getApplication()->input;
	        $dataId = $jinput->getInt('id');
	        $mapModel = Gm_ceilingHelpersGm_ceiling::getModel('users_dealer_id_map');
	        $usersModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
	        $data = $mapModel->getData($dataId);

	        $mapModel->setGroupActive($dataId,$data->user_id);
            $usersModel->updateUsersData($data);
            die(true);
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getUserInGroups(){
	    try{
	        $jinput = JFactory::getApplication()->input;
	        $groups = $jinput->getString('groups');
	        $user = JFactory::getUser();
	        $usersModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
	        $grouppedUsers = $usersModel->getUserInGroups($user->dealer_id,$groups);
	        die(json_encode($grouppedUsers));
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getUsersByGroupAndDealer(){
	    try{
            $jinput = JFactory::getApplication()->input;
            $groups = $jinput->getString('groups');
            $dealerId = $jinput->getInt('dealer_id');
            $usersModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
            $result = $usersModel->getUsersByGroupAndDealer($groups,$dealerId);
            die(json_encode($result));
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function addGroupToExistUser(){
	    try{
            $jinput = JFactory::getApplication()->input;
            $userId = $jinput->getInt('user_id');
            $group = $jinput->getInt('group_id');
            $user = JFactory::getUser($userId);
            $usersModel =  Gm_ceilingHelpersGm_ceiling::getModel('users');
            $usersModel->addGroupToExistUser($user->username,$user->dealer_id,$group);
            die(json_encode(true));
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function removeUserGroup(){
	    try{
            $jinput = JFactory::getApplication()->input;
            $userId = $jinput->getInt('id');
            $groupId = $jinput->getInt('group');
            $usersModel =  Gm_ceilingHelpersGm_ceiling::getModel('users');
            $usersModel->deleteGroup($groupId,$userId);
            die(json_encode(true));
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getUserByUsername(){
        try{
            $jinput = JFactory::getApplication()->input;
            $phone = $jinput->get('phone','','STRING');
            $phone = preg_replace('/[\(\)\-\+\s]/', '', $phone);
            $usersModel =  Gm_ceilingHelpersGm_ceiling::getModel('users');
            $result = $usersModel->getUserByUsername($phone);
            die(json_encode($result));
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getGroupsByParentGroup(){
	    try{
            $jinput = JFactory::getApplication()->input;
            $parentGroup = $jinput->getInt('parent');
            $usersModel =  Gm_ceilingHelpersGm_ceiling::getModel('users');
            $result = $usersModel->getGroupsByParentGroup($parentGroup);
            die(json_encode($result));
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function update(){
	    try{
	        $data = $_POST;
	        if(!empty($data)){
	            $id = $data['id'];
	            $phone = $data['phone'];
                $name = $data['name'];
                $surname = $data['surname'];
                $email = $data['email'];
                $password = $data['password'];
            }
	        else{
                $jinput = JFactory::getApplication()->input;
                $id = $jinput->getInt('id');
                $phone = $jinput->getString('phone');
                $name = $jinput->getString('name');
                $surname = $jinput->getString('surname');
                $email = $jinput->getString('email');
                $password = $jinput->getString('password');
            }
	        if(empty($id)){
	            die('{"result":{"type":"error","data":"Пустой id"}}');
            }
	        $usersModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
	        $result = $usersModel->update($id,$phone,$name,$surname,$email,$password);
	        die(json_encode($result));
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function changePassBAU(){
	    try{
            $result = (object)["type"=>'',"data"=>''];
            $data = $_POST;
            if(!empty($data)){
                $id = $data['id'];
                $oldPassword = $data['old_password'];
                $password = $data['password'];
            }
            else{
                $jinput = JFactory::getApplication()->input;
                $id = $jinput->getInt('id');
                $oldPassword = $jinput->getString('old_password');
                $password = $jinput->getString('password');

            }
            $usersModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
            if(!empty($id)){
                if(empty($password)){
                    $result->type = 'error';
                    $result->data = 'Пароль не может быть пустым';
                    die("{\"result\":".json_encode($result,JSON_UNESCAPED_UNICODE)."}");
                }
                if(empty($oldPassword)){
                    $result->type = 'error';
                    $result->data = 'Пустой старый пароль';
                    die("{\"result\":".json_encode($result,JSON_UNESCAPED_UNICODE)."}");
                }

                $user = JFactory::getUser($usersModel->getUserByAssociatedClient($id)->id);
                $verifyPass = JUserHelper::verifyPassword($oldPassword, $user->password, $user->id);
                if ($verifyPass) {
                    $usersModel->update($id,null,null,null,null,$password);
                    $result->type = 'success';
                    $result->data = $user->associated_client;
                    die("{\"result\":".json_encode($result,JSON_UNESCAPED_UNICODE)."}");
                }
                else{
                    $result->type = 'error';
                    $result->data = 'Неверный старый пароль';
                    die("{\"result\":".json_encode($result,JSON_UNESCAPED_UNICODE)."}");
                }
            }
            else{
                $result->type = 'error';
                $result->data = 'Пустой id';
                die("{\"result\":".json_encode($result,JSON_UNESCAPED_UNICODE)."}");
            }

        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getUser(){
	    try{
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->getInt('id');
            $phone = $jinput->getString('phone');
            $usersModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
            $user = $usersModel->getUserBAU($id,$phone);
            die('{"user":'.json_encode($user,JSON_UNESCAPED_UNICODE).'}');
	    }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

}
?>