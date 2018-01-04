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

/**
 * Stock list controller class.
 *
 * @since  1.6
 */
class Gm_ceilingControllerApi extends JControllerLegacy
{
    /**
     * Proxy for getModel.
     *
     * @param   string $name The model name. Optional.
     * @param   string $prefix The class prefix. Optional
     * @param   array $config Configuration array for model. Optional
     *
     * @return object    The model
     *
     * @since    1.6
     */
    public function &getModel($name = 'Api', $prefix = 'Gm_ceilingModel', $config = array())
    {
        return parent::getModel($name, $prefix, array('ignore_request' => true));
    }

    public function addClientFromAndroid()
    {
        $date = date("d.m.Y H:i:s");
        $files = "components/com_gm_ceiling/views/api/history/client/add/";
        file_put_contents($files . '1_call.txt', (string)$date);

        $client = null;
        $contacts = null;
        $text = null;
        $answer = null;

        try {
            $POST = $_POST;

            $text = "";
            foreach ($POST as $k => $v) $text .= "{ " . $k . ":" . $v . " } \n\n ";
            file_put_contents($files . '2_post.txt', (string)$date . "\n\n" . $text);

            $client = json_decode($POST['client']);
            $text = "{ ";
            foreach ($client as $key => $value)
                $text .= $key . " : " . $value . " , ";
            $text .= " }";
            file_put_contents($files . '2_client.txt', (string)$date . "\n\n" . $text);

            $contacts = json_decode($_POST['contacts']);
            $text = "[ ";
            foreach ($contacts as $key => $value) {
                $text .= "{ ";
                foreach ($value as $k => $v)
                    $text .= $k . " : " . $v . " , ";
                $text .= " }, ";
            }
            $text .= " ]";
            file_put_contents($files . '2_contact.txt', (string)$date . "\n\n" . $text);
        } catch (Exception $e) {
            file_put_contents($files . '2_error.txt', (string)$date . "\n\n" . $e->getMessage());
            $answer = (object)array("ERROR" => "ERROR! page = 2, " . $e->getMessage());
        }

        if (empty($answer)) {
            try {
                $model = $this->getModel();

                $client = $model->addClient($client);
                $text = $client->answer;
                $client = $client->client;

                file_put_contents($files . '3_client.txt', (string)$date . " \n\n " . json_encode($client));

                if (!empty($text)) {
                    file_put_contents($files . '3_model_client.txt', (string)$date . "\n\n" . $text);
                    $answer = (object)array("ERROR" => "ERROR MODEL! page = 3, " . $text);
                } else {
                    $contacts = $model->addContacts($contacts, $client);
                    $text = $contacts->answer;
                    $contacts = $contacts->contacts;

                    file_put_contents($files . '3_contacts.txt', (string)$date . " \n\n " . json_encode($contacts));

                    if (!empty($text)) {
                        file_put_contents($files . '3_model_contacts.txt', (string)$date . "\n\n" . $text);
                        $answer = (object)array("ERROR" => "ERROR MODEL! page = 3, " . $text);
                    }
                }
                if (empty($answer)) $answer = (object)array("client" => $client, "contacts" => $contacts);
            } catch (Exception $e) {
                file_put_contents($files . '3_error.txt', (string)$date . "\n\n" . $e->getMessage());
                $answer = (object)array("ERROR" => "ERROR! page = 3, " . $e->getMessage());
            }
        }
        //$answer_pr = $this->addProjectCalculationFromAndroid(true);
        //$answer->project = $answer_pr->project;

        $answer = json_encode($answer);
        file_put_contents($files . '4_result.txt', (string)$date . " \n\n " . $answer);
        die($answer);
    }

    public function addFixtureFromAndroid()
    {
        $date = date("d.m.Y H:i:s");
        $files = "components/com_gm_ceiling/views/api/history/fixtures/add/";
        file_put_contents($files . '1_call.txt', (string)$date);

        $fixtures = null;
        $text = null;
        $answer = null;

        try {
            $POST = $_POST;

            $text = "";
            foreach ($POST as $k => $v) $text .= "{ " . $k . ":" . $v . " } \n\n ";
            file_put_contents($files . '2_post.txt', (string)$date . "\n\n" . $text);

            $fixtures = json_decode($_POST['fixtures']);
            $text = "[ ";
            foreach ($fixtures as $key => $value) {
                $text .= "{ ";
                foreach ($value as $k => $v)
                    $text .= $k . " : " . $v . " , ";
                $text .= " }, ";
            }
            $text .= " ]";
            file_put_contents($files . '2_fixtures.txt', (string)$date . "\n\n" . $text);
        } catch (Exception $e) {
            file_put_contents($files . '2_error.txt', (string)$date . "\n\n" . $e->getMessage());
            $answer = (object)array("ERROR" => "ERROR! page = 2, " . $e->getMessage());
        }

        if (empty($answer)) {
            try {
                $model = $this->getModel();
                $fixtures = $model->addFixtures($fixtures);
                $text = $fixtures->answer;
                $fixtures = $fixtures->fixtures;

                file_put_contents($files . '3_fixtures.txt', (string)$date . " \n\n " . json_encode($fixtures));

                if (!empty($text)) {
                    file_put_contents($files . '3_model_fixtures.txt', (string)$date . "\n\n" . $text);
                    $answer = (object)array("ERROR" => "ERROR MODEL! page = 3, " . $text);
                }
                if (empty($answer)) $answer = (object)array("fixtures" => $fixtures);
            } catch (Exception $e) {
                file_put_contents($files . '3_error.txt', (string)$date . "\n\n" . $e->getMessage());
                $answer = (object)array("ERROR" => "ERROR! page = 3, " . $e->getMessage());
            }
        }

        $answer = json_encode($answer);
        file_put_contents($files . '4_result.txt', (string)$date . " \n\n " . $answer);
        die($answer);
    }

    public function addCorniceFromAndroid()
    {
        $date = date("d.m.Y H:i:s");
        $files = "components/com_gm_ceiling/views/api/history/cornice/add/";
        file_put_contents($files . '1_call.txt', (string)$date);

        $cornices = null;
        $text = null;
        $answer = null;

        try {
            $POST = $_POST;

            $text = "";
            foreach ($POST as $k => $v) $text .= "{ " . $k . ":" . $v . " } \n\n ";
            file_put_contents($files . '2_post.txt', (string)$date . "\n\n" . $text);

            $cornices = json_decode($_POST['cornices']);
            $text = "[ ";
            foreach ($cornices as $key => $value) {
                $text .= "{ ";
                foreach ($value as $k => $v)
                    $text .= $k . " : " . $v . " , ";
                $text .= " }, ";
            }
            $text .= " ]";
            file_put_contents($files . '2_cornices.txt', (string)$date . "\n\n" . $text);
        } catch (Exception $e) {
            file_put_contents($files . '2_error.txt', (string)$date . "\n\n" . $e->getMessage());
            $answer = (object)array("ERROR" => "ERROR! page = 2, " . $e->getMessage());
        }

        if (empty($answer)) {
            try {
                $model = $this->getModel();
                $cornices = $model->addCornices($cornices);
                $text = $cornices->answer;
                $cornices = $cornices->cornices;

                file_put_contents($files . '3_cornices.txt', (string)$date . " \n\n " . json_encode($cornices));

                if (!empty($text)) {
                    file_put_contents($files . '3_model_cornices.txt', (string)$date . "\n\n" . $text);
                    $answer = (object)array("ERROR" => "ERROR MODEL! page = 3, " . $text);
                }
                if (empty($answer)) $answer = (object)array("cornices" => $cornices);
            } catch (Exception $e) {
                file_put_contents($files . '3_error.txt', (string)$date . "\n\n" . $e->getMessage());
                $answer = (object)array("ERROR" => "ERROR! page = 3, " . $e->getMessage());
            }
        }

        $answer = json_encode($answer);
        file_put_contents($files . '4_result.txt', (string)$date . " \n\n " . $answer);
        die($answer);
    }


    public function addEcolaFromAndroid()
    {
        $date = date("d.m.Y H:i:s");
        $files = "components/com_gm_ceiling/views/api/history/ecola/add/";
        file_put_contents($files . '1_call.txt', (string)$date);

        $ecolas = null;
        $text = null;
        $answer = null;

        try {
            $POST = $_POST;

            $text = "";
            foreach ($POST as $k => $v) $text .= "{ " . $k . ":" . $v . " } \n\n ";
            file_put_contents($files . '2_post.txt', (string)$date . "\n\n" . $text);

            $ecolas = json_decode($_POST['ecolas']);
            $text = "[ ";
            foreach ($ecolas as $key => $value) {
                $text .= "{ ";
                foreach ($value as $k => $v)
                    $text .= $k . " : " . $v . " , ";
                $text .= " }, ";
            }
            $text .= " ]";
            file_put_contents($files . '2_ecolas.txt', (string)$date . "\n\n" . $text);
        } catch (Exception $e) {
            file_put_contents($files . '2_error.txt', (string)$date . "\n\n" . $e->getMessage());
            $answer = (object)array("ERROR" => "ERROR! page = 2, " . $e->getMessage());
        }

        if (empty($answer)) {
            try {
                $model = $this->getModel();
                $ecolas = $model->addEcolas($ecolas);
                $text = $ecolas->answer;
                $ecolas = $ecolas->ecolas;

                file_put_contents($files . '3_ecolas.txt', (string)$date . " \n\n " . json_encode($ecolas));

                if (!empty($text)) {
                    file_put_contents($files . '3_model_ecolas.txt', (string)$date . "\n\n" . $text);
                    $answer = (object)array("ERROR" => "ERROR MODEL! page = 3, " . $text);
                }
                if (empty($answer)) $answer = (object)array("ecolas" => $ecolas);
            } catch (Exception $e) {
                file_put_contents($files . '3_error.txt', (string)$date . "\n\n" . $e->getMessage());
                $answer = (object)array("ERROR" => "ERROR! page = 3, " . $e->getMessage());
            }
        }

        $answer = json_encode($answer);
        file_put_contents($files . '4_result.txt', (string)$date . " \n\n " . $answer);
        die($answer);
    }

    public function addHoodFromAndroid()
    {
        $date = date("d.m.Y H:i:s");
        $files = "components/com_gm_ceiling/views/api/history/hood/add/";
        file_put_contents($files . '1_call.txt', (string)$date);

        $hoods = null;
        $text = null;
        $answer = null;

        try {
            $POST = $_POST;

            $text = "";
            foreach ($POST as $k => $v) $text .= "{ " . $k . ":" . $v . " } \n\n ";
            file_put_contents($files . '2_post.txt', (string)$date . "\n\n" . $text);

            $hoods = json_decode($_POST['hoods']);
            $text = "[ ";
            foreach ($hoods as $key => $value) {
                $text .= "{ ";
                foreach ($value as $k => $v)
                    $text .= $k . " : " . $v . " , ";
                $text .= " }, ";
            }
            $text .= " ]";
            file_put_contents($files . '2_hoods.txt', (string)$date . "\n\n" . $text);
        } catch (Exception $e) {
            file_put_contents($files . '2_error.txt', (string)$date . "\n\n" . $e->getMessage());
            $answer = (object)array("ERROR" => "ERROR! page = 2, " . $e->getMessage());
        }

        if (empty($answer)) {
            try {
                $model = $this->getModel();
                $hoods = $model->addHoods($hoods);
                $text = $hoods->answer;
                $hoods = $hoods->hoods;

                file_put_contents($files . '3_hoods.txt', (string)$date . " \n\n " . json_encode($hoods));

                if (!empty($text)) {
                    file_put_contents($files . '3_model_hoods.txt', (string)$date . "\n\n" . $text);
                    $answer = (object)array("ERROR" => "ERROR MODEL! page = 3, " . $text);
                }
                if (empty($answer)) $answer = (object)array("hoods" => $hoods);
            } catch (Exception $e) {
                file_put_contents($files . '3_error.txt', (string)$date . "\n\n" . $e->getMessage());
                $answer = (object)array("ERROR" => "ERROR! page = 3, " . $e->getMessage());
            }
        }

        $answer = json_encode($answer);
        file_put_contents($files . '4_result.txt', (string)$date . " \n\n " . $answer);
        die($answer);
    }

    public function addPipeFromAndroid()
    {
        $date = date("d.m.Y H:i:s");
        $files = "components/com_gm_ceiling/views/api/history/pipe/add/";
        file_put_contents($files . '1_call.txt', (string)$date);

        $pipes = null;
        $text = null;
        $answer = null;

        try {
            $POST = $_POST;

            $text = "";
            foreach ($POST as $k => $v) $text .= "{ " . $k . ":" . $v . " } \n\n ";
            file_put_contents($files . '2_post.txt', (string)$date . "\n\n" . $text);

            $pipes = json_decode($_POST['pipes']);
            $text = "[ ";
            foreach ($pipes as $key => $value) {
                $text .= "{ ";
                foreach ($value as $k => $v)
                    $text .= $k . " : " . $v . " , ";
                $text .= " }, ";
            }
            $text .= " ]";
            file_put_contents($files . '2_pipes.txt', (string)$date . "\n\n" . $text);
        } catch (Exception $e) {
            file_put_contents($files . '2_error.txt', (string)$date . "\n\n" . $e->getMessage());
            $answer = (object)array("ERROR" => "ERROR! page = 2, " . $e->getMessage());
        }

        if (empty($answer)) {
            try {
                $model = $this->getModel();
                $pipes = $model->addPipes($pipes);
                $text = $pipes->answer;
                $pipes = $pipes->pipes;

                file_put_contents($files . '3_pipes.txt', (string)$date . " \n\n " . json_encode($pipes));

                if (!empty($text)) {
                    file_put_contents($files . '3_model_pipes.txt', (string)$date . "\n\n" . $text);
                    $answer = (object)array("ERROR" => "ERROR MODEL! page = 3, " . $text);
                }
                if (empty($answer)) $answer = (object)array("pipes" => $pipes);
            } catch (Exception $e) {
                file_put_contents($files . '3_error.txt', (string)$date . "\n\n" . $e->getMessage());
                $answer = (object)array("ERROR" => "ERROR! page = 3, " . $e->getMessage());
            }
        }

        $answer = json_encode($answer);
        file_put_contents($files . '4_result.txt', (string)$date . " \n\n " . $answer);
        die($answer);
    }

    public function addDiffuserFromAndroid()
    {
        $date = date("d.m.Y H:i:s");
        $files = "components/com_gm_ceiling/views/api/history/diffuser/add/";
        file_put_contents($files . '1_call.txt', (string)$date);

        $diffusers = null;
        $text = null;
        $answer = null;

        try {
            $POST = $_POST;

            $text = "";
            foreach ($POST as $k => $v) $text .= "{ " . $k . ":" . $v . " } \n\n ";
            file_put_contents($files . '2_post.txt', (string)$date . "\n\n" . $text);

            $diffusers = json_decode($_POST['diffusers']);
            $text = "[ ";
            foreach ($diffusers as $key => $value) {
                $text .= "{ ";
                foreach ($value as $k => $v)
                    $text .= $k . " : " . $v . " , ";
                $text .= " }, ";
            }
            $text .= " ]";
            file_put_contents($files . '2_diffusers.txt', (string)$date . "\n\n" . $text);
        } catch (Exception $e) {
            file_put_contents($files . '2_error.txt', (string)$date . "\n\n" . $e->getMessage());
            $answer = (object)array("ERROR" => "ERROR! page = 2, " . $e->getMessage());
        }

        if (empty($answer)) {
            try {
                $model = $this->getModel();
                $diffusers = $model->addDiffusers($diffusers);
                $text = $diffusers->answer;
                $diffusers = $diffusers->diffusers;

                file_put_contents($files . '3_diffusers.txt', (string)$date . " \n\n " . json_encode($diffusers));

                if (!empty($text)) {
                    file_put_contents($files . '3_model_diffusers.txt', (string)$date . "\n\n" . $text);
                    $answer = (object)array("ERROR" => "ERROR MODEL! page = 3, " . $text);
                }
                if (empty($answer)) $answer = (object)array("diffusers" => $diffusers);
            } catch (Exception $e) {
                file_put_contents($files . '3_error.txt', (string)$date . "\n\n" . $e->getMessage());
                $answer = (object)array("ERROR" => "ERROR! page = 3, " . $e->getMessage());
            }
        }

        $answer = json_encode($answer);
        file_put_contents($files . '4_result.txt', (string)$date . " \n\n " . $answer);
        die($answer);
    }


    public function addProjectCalculationFromAndroid($flag = false)
    {
        $date = date("d.m.Y H:i:s");
        $files = "components/com_gm_ceiling/views/api/history/project/add/";
        file_put_contents($files . '1_call.txt', (string)$date);

        $project = null;
        $calculations = null;
        $text = "";
        $answer = null;

        try {
            $POST = $_POST;

            foreach ($POST as $k => $v) $text .= "{ " . $k . ":" . $v . " } \n\n ";
            file_put_contents($files . '2_post.txt', ((string)$date) . " \n\n " . $text);

            $i = 0;
            $calc_image = array();
            while (!empty($POST['calc_image_' . $i]) && $i < 100)
                $calc_image[] = $POST['calc_image_' . $i++];

            $i = 0;
            $cut_image = array();
            while (!empty($POST['cut_image_' . $i]) && $i < 100)
                $cut_image[] = $POST['cut_image_' . $i++];


            $project = json_decode($_POST["project"]);
            $text = "{ ";
            foreach ($project as $key => $value)
                $text .= $key . " : " . $value . " , ";
            $text .= " }";
            file_put_contents($files . '2_project.txt', (string)$date . " \n\n " . $text);
            $calculations = json_decode($_POST["calculation"]);
            $text = "[ ";
            foreach ($calculations as $key => $value) {
                $value->calc_image = base64_decode(str_replace('data:image/png;base64,', '', $calc_image[$key]));
                $value->cut_image = base64_decode(str_replace('data:image/png;base64,', '', $cut_image[$key]));
                $text .= "{ ";
                foreach ($value as $k => $v)
                    $text .= $k . " : " . $v . " , ";
                $text .= " }, ";
            }
            $text .= " ]";
            file_put_contents($files . '2_calculation.txt', (string)$date . " \n\n " . $text);
        } catch (Exception $e) {
            file_put_contents($files . '2_error.txt', (string)$date . " \n\n " . $e->getMessage());
            $answer = (object)array("ERROR" => "ERROR! page = 2, " . $e->getMessage());
        }

        if (empty($answer)) {
            try {
                $model = $this->getModel();

                $project = $model->addProject($project);
                $text = $project->answer;
                $project = $project->project;
                file_put_contents($files . '3_project.txt', (string)$date . " \n\n " . $project);
                if (!empty($text)) {
                    file_put_contents($files . '3_error_project.txt', (string)$date . " \n\n " . $text);
                    $answer = (object)array("ERROR" => "ERROR! page = 3, " . $text);
                } else {
                    $calculations = $model->addCalculation($calculations, $project);
                    $text = $calculations->answer;
                    $calculations = $calculations->calculations;
                    $textt = "[ ";
                    foreach ($calculations as $v) {
                        $textt .= $v->id . ", ";
                    }
                    $textt .= " ]";
                    file_put_contents($files . '3_calculations.txt', (string)$date . " \n\n " . $textt);

                    if (!empty($text)) {
                        file_put_contents($files . '3_error_calculations.txt', (string)$date . " \n\n " . $text);
                        $answer = (object)array("ERROR" => "ERROR! id = 3, " . $text);
                    }
                }
                if (empty($answer)) $answer = (object)array("project" => $project, "calculation" => $calculations);
            } catch (Exception $e) {
                file_put_contents($files . '3_error.txt', (string)$date . " \n\n " . $e->getMessage());
                $answer = (object)array("ERROR" => "ERROR! page = 3, " . $e->getMessage());
            }
        }
        file_put_contents($files . '4_result.txt', (string)$date . " \n\n " . json_encode($answer));
        if ($flag) return $answer;
        else die(json_encode($answer));
    }

    public function Authorization_FromAndroid()
    {
        $date = date("d.m.Y H:i:s");
        $files = "components/com_gm_ceiling/views/api/history/authorization/";
        file_put_contents($files . '1_call.txt', (string)$date);

        $authorization = null;
        $text = null;
        $answer = null;
        $calc_image = null;
        $cut_image = null;
        $verifyPass = null;

        try {
            $POST = $_POST;

            $text = "";
            foreach ($POST as $k => $v) $text .= $k . " - " . $v . " \n\n ";
            file_put_contents($files . '2_post.txt', (string)$date . "\n\n" . $text);

            $authorization = json_decode($POST['authorizations']);
            $text = "";
            foreach ($authorization as $key => $auth)
                $text .= $key . " - " . json_encode($auth) . "\n";
            file_put_contents($files . '2_authorization.txt', (string)$date . "\n\n" . $text);

        } catch (Exception $e) {
            file_put_contents($files . '2_error.txt', (string)$date . "\n\n" . $e->getMessage());
            $answer = (object)array("ERROR" => "ERROR! page = 2, " . $e->getMessage());
        }

        if (empty($answer)) {
            try {
                $model = $this->getModel();
                $user = JFactory::getUser($model->getUserId($authorization->username));
                file_put_contents($files . '3_user.txt', (string)$date . " \n\n " . $user);
                $Password = $authorization->password;// пароль

                $verifyPass = JUserHelper::verifyPassword($Password, $user->password, $user->id);
                file_put_contents($files . '3_verifyPass.txt', (string)$date . " \n\n " . json_encode($verifyPass));
                if ($verifyPass) {
                    $project = $model->selectProject($user->id);
                    $text = $project->answer;
                    $calculations = $project->calculations;
                    $fixtures = $project->fixtures;
                    $cornices = $project->cornices;
                    $diffusers = $project->diffusers;
                    $ecolas = $project->ecolas;
                    $hoods = $project->hoods;
                    $pipes = $project->pipes;
                    $images = $project->images;
                    $project = $project->projects;
                    file_put_contents($files . '3_proj.txt', (string)$date . " \n\n " . json_encode($project));
                    file_put_contents($files . '3_calc.txt', (string)$date . " \n\n " . json_encode($calculations));
                    file_put_contents($files . '3_fix.txt', (string)$date . " \n\n " . json_encode($fixtures));
                    file_put_contents($files . '3_cornices.txt', (string)$date . " \n\n " . json_encode($cornices));
                    file_put_contents($files . '3_diffusers.txt', (string)$date . " \n\n " . json_encode($diffusers));
                    file_put_contents($files . '3_ecolas.txt', (string)$date . " \n\n " . json_encode($ecolas));
                    file_put_contents($files . '3_hoods.txt', (string)$date . " \n\n " . json_encode($hoods));
                    file_put_contents($files . '3_pipes.txt', (string)$date . " \n\n " . json_encode($pipes));
                    if (!empty($text)) {
                        file_put_contents($files . '3_model_proj.txt', (string)$date . "\n\n" . $text);
                        $answer = (object)array("ERROR" => "ERROR MODEL! page = 3, " . $text);
                    }
                    if (empty($answer))
                        $answer = (object)array("user" => (object)["id" => $user->id, "name" => $user->name,
                            "username" => $user->username, "email" => $user->email, "dealer_id" => $user->dealer_id],
                            "project" => $project, "calculations" => $calculations, "fixtures" => $fixtures, "cornices" => $cornices,
                            "diffusers" => $diffusers, "ecolas" => $ecolas, "hoods" => $hoods, "pipes" => $pipes);
                    else $answer = (object)array('0');
                }
            }
            catch (Exception $e) {
                    file_put_contents($files . '3_error.txt', (string)$date . "\n\n" . $e->getMessage());
                    $answer = (object)array("ERROR" => "ERROR! page = 3, " . $e->getMessage());
                }
        }

        $response = json_encode($answer);
        $response = substr($response, 0, -1);
        $response .= ',"images":[';
        $del_z = false;
        foreach ($images as $key1 => $value1) {
            foreach ($images[$key1] as $key2 => $value2) {
                $response .= '{"calc_id":"' . $value2->id . '","calc_image":"data:image/png;base64,' . base64_encode($value2->calc_image) . '","cut_image":"data:image/png;base64,' . base64_encode($value2->cut_image) . '"},';
                $del_z = true;
            }
        }
        if ($del_z) {
            $response = substr($response, 0, -1);
        }
        $response .= ']}';
        $response = ($verifyPass)?$response:"{\"error\" : \"User does not exist\"}";
        file_put_contents($files . '4_result.txt', (string)$date . " \n\n " . $response);
        die($response);
    }


        public
        function addDataFromAndroid()
        {
            try {
                $model = Gm_ceilingHelpersGm_ceiling::getModel('api');
                $result = [];
                foreach ($_POST as $key => $value) {
                    $table_name = $key;
                    $table_data = json_decode($_POST[$key]);
                    $result[$key] = $model->save_or_update_data_from_android($table_name, $table_data);
                }
                if (!$result) {
                    die(json_encode($_POST));
                }
                die(json_encode($result));
            }
            catch(Exception $e)
            {
                $date = date("d.m.Y H:i:s");
                $files = "components/com_gm_ceiling/";
                file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
                throw new Exception('Ошибка!', 500);
            }
        }

        public
        function checkDataFromAndroid()
        {
            try {
                $model = Gm_ceilingHelpersGm_ceiling::getModel('api');
                $result = [];
                foreach ($_POST as $key => $value) {
                    $table_name = $key;
                    $table_data = json_decode($_POST[$key]);
                    $result[$key] = $model->update_android_ids_from_android($table_name, $table_data);
                }
                if (!$result) {
                    die(json_encode($_POST));
                }
                die(json_encode($result));
            }
            catch(Exception $e)
            {
                $date = date("d.m.Y H:i:s");
                $files = "components/com_gm_ceiling/";
                file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
                throw new Exception('Ошибка!', 500);
            }
        }

        public
        function addImagesFromAndroid()
        {
            try {
                $model = Gm_ceilingHelpersGm_ceiling::getModel('api');
                if (!empty($_POST['calculation_images'])) {
                    $data = json_decode($_POST['calculation_images']);
                    $calc_image = base64_decode(str_replace('data:image/png;base64,', '', $data->calc_image));
                    $cut_image = base64_decode(str_replace('data:image/png;base64,', '', $data->cut_image));

                    $filename = md5("calculation_sketch".$data->android_id);
                    file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/calculation_images/' . $filename . ".png", $calc_image);

                    $filename = md5("cut_sketch".$data->android_id);
                    file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/cut_images/' . $filename . ".png", $cut_image);
                }
                die(json_encode($data->android_id));
            }
            catch(Exception $e)
            {
                $date = date("d.m.Y H:i:s");
                $files = "components/com_gm_ceiling/";
                file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
                throw new Exception('Ошибка!', 500);
            }
        }

        public
        function deleteDataFromAndroid()
        {
            try
            {
                $model = Gm_ceilingHelpersGm_ceiling::getModel('api');
                foreach ($_POST as $key => $value) {
                    $table_name = $key;
                    $table_data = json_decode($_POST[$key]);
                    $result = $model->delete_from_android($table_name, $table_data);
                }
                die(json_encode($result));
            }
            catch(Exception $e)
            {
                $date = date("d.m.Y H:i:s");
                $files = "components/com_gm_ceiling/";
                file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
                throw new Exception('Ошибка!', 500);
            }
        }

        public
        function sendDataToAndroid()
        {
            try
            {
                $model = Gm_ceilingHelpersGm_ceiling::getModel('api');
                if(!empty($_POST['synchronization']))
                {
                    $table_data = json_decode($_POST['synchronization']);
                    $result = $model->get_data_android($table_data);
                }
                die(json_encode($result));
            }
            catch(Exception $e)
            {
                $date = date("d.m.Y H:i:s");
                $files = "components/com_gm_ceiling/";
                file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
                throw new Exception('Ошибка!', 500);
            }
        }

        public
        function sendImagesToAndroid()
        {
            try
            {
                $model = Gm_ceilingHelpersGm_ceiling::getModel('api');
                if(!empty($_POST['calculation_images']))
                {
                    $data = json_decode($_POST['calculation_images']);

                    $filename = md5("calculation_sketch".$data->id);
                    $calc_image = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/calculation_images/' . $filename . ".png");

                    $filename = md5("cut_sketch".$data->id);
                    $cut_image = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/cut_images/' . $filename . ".png");                  

                    $result = '{"id":';
                    $result .= '"'.$data->id.'",';
                    $result .= '"calc_image":';
                    $result .= '"data:image/png;base64,'.base64_encode($calc_image).'",';
                    $result .= '"cut_image":';
                    $result .= '"data:image/png;base64,'.base64_encode($cut_image).'"}';
                }
                die($result);
            }
            catch(Exception $e)
            {
                $date = date("d.m.Y H:i:s");
                $files = "components/com_gm_ceiling/";
                file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
                throw new Exception('Ошибка!', 500);
            }
        }

    }

