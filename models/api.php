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
class Gm_ceilingModelApi extends JModelList
{
    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    protected function getListQuery()
    {
        // Создаем запрос
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        return $query;
    }

    public function addClient($data)
    {
        $db = $this->getDbo();
        $answer = null;
        $column = null;
        $values = null;
        $date_create = null;
        $client = null;

        try {
            $column = array();
            $values = array();
            foreach ($data as $key => $value) {
                if (!empty($value)) {
                    $column[] = $key;
                    $values[] = $db->quote($value);
                }
            }
            $date_create = (empty($data->created)) ? "IS NULL" : "= '" . $data->created . "'";
        } catch (Exception $e) {
            $answer = "ERROR! ClientColVal! " . $e->getMessage();
        }

        if (empty($answer)) {
            try {
                $query = $db->getQuery(true);
                $query
                    ->insert($db->quoteName('#__gm_ceiling_clients'))
                    ->columns($db->quoteName($column))
                    ->values(implode(',', $values));
                $db->setQuery($query);
                $queryText = (string)$query;
                file_put_contents('components/com_gm_ceiling/views/api/history/client/add/1_clients_SQL.txt', $queryText);
                $test = $db->execute();

                if (!empty($test)) {
                    $query = $db->getQuery(true);
                    $query->from('`#__gm_ceiling_clients` AS clients')
                        ->select('MAX(clients.id) AS id')
                        ->where('clients.created ' . $date_create);
                    $queryText = (string)$query;
                    file_put_contents('components/com_gm_ceiling/views/api/history/client/add/2_clients_SQL.txt', $queryText);
                    $db->setQuery($query);
                    $client = $db->loadObject();
                    $client = $client->id;
                }
            } catch (Exception $e) {
                $answer = "ERROR! ClientsSQL! " . $e->getMessage();
            } catch (mysqli_sql_exception $e) {
                $answer = "ERROR SQL! ClientsSQL! " . $e->getMessage();
            }
        }

        return (object)array("client" => $client, "answer" => $answer);
    }

    public function addContacts($data, $client_id)
    {
        $db = $this->getDbo();
        $answer = null;
        $column = null;
        $values = null;
        $phone = null;
        $contacts = array();
        $queryText = null;

        foreach ($data as $k => $contact) {
            if (!empty($answer)) break;

            try {
                $column = array();
                $values = array();
                foreach ($contact as $key => $value) {
                    if (!empty($value)) {
                        if ($key == "client_id") {
                            $column[] = $key;
                            $values[] = $db->quote($client_id);
                        } else {
                            $column[] = $key;
                            $values[] = $db->quote($value);
                        }
                    }
                }

                $phone = (empty($contact->phone)) ? "IS NULL" : "= '" . $contact->phone . "'";
            } catch (Exception $e) {
                $answer = "ERROR! ContactsColVal! " . $e->getMessage();
            }

            try {
                $query = $db->getQuery(true);
                $query
                    ->insert($db->quoteName('#__gm_ceiling_clients_contacts'))
                    ->columns($db->quoteName($column))
                    ->values(implode(',', $values));
                $queryText = (string)$query;
                file_put_contents('components/com_gm_ceiling/views/api/history/client/add/1_contacts_SQL.txt', $queryText);
                $db->setQuery($query);
                $test = $db->execute();

                if (!empty($test)) {
                    $query = $db->getQuery(true);
                    $query->from('`#__gm_ceiling_clients_contacts` AS contacts')
                        ->select('MAX(contacts.id) AS id')
                        ->where('contacts.phone ' . $phone);
                    $queryText = (string)$query;
                    file_put_contents('components/com_gm_ceiling/views/api/history/client/add/2_contacts_SQL.txt', $queryText);
                    $db->setQuery($query);
                    $contactTemp = $db->loadObject();
                    $contacts[] = (object)array("id" => $contactTemp->id);
                }
            } catch (Exception $e) {
                $answer = "ERROR! ContactsSQL! " . $e->getMessage() . " -//- " . $queryText;
            } catch (mysqli_sql_exception $e) {
                $answer = "ERROR SQL! ContactsSQL! " . $e->getMessage() . " -//- " . $queryText;
            }
        }

        return (object)array("contacts" => $contacts, "answer" => $answer);
    }

    public function addProject($data)
    {
        $db = $this->getDbo();
        $answer = null;
        $column = null;
        $values = null;
        $date_create = null;
        $project = null;

        try {
            $column = array();
            $values = array();
            foreach ($data as $key => $value) {
                if (!empty($value)) {
                    $column[] = $key;
                    $values[] = $db->quote($value);
                }
            }

            $date_create = (empty($data->checked_out_time)) ? "IS NULL" : "= '" . $data->checked_out_time . "'";
        } catch (Exception $e) {
            $answer = "ERROR! ProColVal! " . $e->getMessage();
        }

        if (empty($answer)) {
            try {
                $query = $db->getQuery(true);
                $query
                    ->insert($db->quoteName('#__gm_ceiling_projects'))
                    ->columns($db->quoteName($column))
                    ->values(implode(',', $values));
                $db->setQuery($query);
                $test = $db->execute();

                if (!empty($test)) {
                    $query = $db->getQuery(true);
                    $query->from('`#__gm_ceiling_projects` AS project')
                        ->select('MAX(project.id) AS id')
                        ->where('project.checked_out_time ' . $date_create);
                    $db->setQuery($query);
                    $project = $db->loadObject();
                    $project = $project->id;
                }
            } catch (Exception $e) {
                $answer = "ERROR! ProSQL! " . $e->getMessage();
            } catch (mysqli_sql_exception $e) {
                $answer = "ERROR SQL! ProSQL! " . $e->getMessage();
            }
        }

        return (object)array("project" => $project, "answer" => $answer);
    }

    public function addCalculation($data, $project_id)
    {
        $db = $this->getDbo();
        $answer = null;
        $column = null;
        $values = null;
        $date_create = null;
        $calculations = array();
        $queryText = null;

        foreach ($data as $k => $calculation) {
            if (!empty($answer)) break;

            try {
                $column = array();
                $values = array();
                foreach ($calculation as $key => $value) {
                    if (!empty($value)) {
                        if ($key == "project_id") {
                            $column[] = $key;
                            $values[] = $db->quote($project_id);
                        } else {
                            $column[] = $key;
                            $values[] = $db->quote($value);
                        }
                    }
                }

                $date_create = (empty($calculation->checked_out_time)) ? "IS NULL" : "= '" . $calculation->checked_out_time . "'";
            } catch (Exception $e) {
                $answer = "ERROR! CalColVal! " . $e->getMessage();
            }

            try {
                $query = $db->getQuery(true);
                $query
                    ->insert($db->quoteName('#__gm_ceiling_calculations'))
                    ->columns($db->quoteName($column))
                    ->values(implode(',', $values));
                $queryText = (string)$query;
                file_put_contents('components/com_gm_ceiling/views/api/history/project/add/api_pc_1_CalQuery.txt', $queryText);
                $db->setQuery($query);
                $test = $db->execute();

                if (!empty($test)) {
                    $query = $db->getQuery(true);
                    $query->from('`#__gm_ceiling_calculations` AS calc')
                        ->select('MAX(calc.id) AS id')
                        ->where('calc.checked_out_time ' . $date_create);
                    $queryText = (string)$query;
                    file_put_contents('components/com_gm_ceiling/views/api/history/project/add/api_pc_2_CalQuery.txt', $queryText);
                    $db->setQuery($query);
                    $calculationTest = $db->loadObject();
                    $calculations[] = (object)array("id" => $calculationTest->id);
                    //$calculations[] = $calculationTest;
                }
            } catch (Exception $e) {
                $answer = "ERROR! CalSQL! " . $e->getMessage() . " -//- " . $queryText;
            } catch (mysqli_sql_exception $e) {
                $answer = "ERROR SQL! CalSQL! " . $e->getMessage() . " -//- " . $queryText;
            }
        }
        return (object)array("calculations" => $calculations, "answer" => $answer);
    }

    public function addFixtures($data)
    {
        $db = $this->getDbo();
        $answer = null;
        $column = null;
        $values = null;
        // $date_create = null;
        $fixtures = array();
        $queryText = null;

        foreach ($data as $k => $fixture) {
            if (!empty($answer)) break;

            try {
                $column = array();
                $values = array();
                foreach ($fixture as $key => $value) {
                    if (!empty($value)) {

                        $column[] = $key;
                        $values[] = $db->quote($value);

                    }
                }

                //$phone = (empty($contact->phone)) ? "IS NULL" : "= '" . $contact->phone . "'";
            } catch (Exception $e) {
                $answer = "ERROR! FixturesColVal! " . $e->getMessage();
            }

            try {
                $query = $db->getQuery(true);
                $query
                    ->insert($db->quoteName('#__gm_ceiling_fixtures'))
                    ->columns($db->quoteName($column))
                    ->values(implode(',', $values));
                $queryText = (string)$query;
                file_put_contents('components/com_gm_ceiling/views/api/history/fixture/add/1_fixtures_SQL.txt', $queryText);
                $db->setQuery($query);
                $test = $db->execute();

                if (!empty($test)) {
                    $query = $db->getQuery(true);
                    $query->from('`#__gm_ceiling_fixtures` AS fixture')
                        ->select('MAX(fixture.id) AS id')
                        ->where('fixture.calculation_id  = ' . $db->quote($fixture->calculation_id) .
                            ' AND fixture.n13_count = ' . $db->quote($fixture->n13_count) .
                            ' AND fixture.n13_type = ' . $db->quote($fixture->n13_type) .
                            ' AND fixture.n13_size = ' . $db->quote($fixture->n13_size));
                    $queryText = (string)$query;
                    file_put_contents('components/com_gm_ceiling/views/api/history/fixture/add/2_fixtures_SQL.txt', $queryText);
                    $db->setQuery($query);
                    $fixtureTemp = $db->loadObject();
                    $fixtures[] = (object)array("id" => $fixtureTemp->id);
                }
            } catch (Exception $e) {
                $answer = "ERROR! FixtureSQL! " . $e->getMessage() . " -//- " . $queryText;
            } catch (mysqli_sql_exception $e) {
                $answer = "ERROR SQL! FixtureSQL! " . $e->getMessage() . " -//- " . $queryText;
            }
        }

        return (object)array("fixtures" => $fixtures, "answer" => $answer);
    }

    public function addCornices($data)
    {
        $db = $this->getDbo();
        $answer = null;
        $column = null;
        $values = null;
        // $date_create = null;
        $cornices = array();
        $queryText = null;

        foreach ($data as $k => $cornice) {
            if (!empty($answer)) break;

            try {
                $column = array();
                $values = array();
                foreach ($cornice as $key => $value) {
                    if (!empty($value)) {

                        $column[] = $key;
                        $values[] = $db->quote($value);

                    }
                }

                //$phone = (empty($contact->phone)) ? "IS NULL" : "= '" . $contact->phone . "'";
            } catch (Exception $e) {
                $answer = "ERROR! CornicesColVal! " . $e->getMessage();
            }

            try {
                $query = $db->getQuery(true);
                $query
                    ->insert($db->quoteName('#__gm_ceiling_cornice'))
                    ->columns($db->quoteName($column))
                    ->values(implode(',', $values));
                $queryText = (string)$query;
                file_put_contents('components/com_gm_ceiling/views/api/history/cornice/add/1_cornices_SQL.txt', $queryText);
                $db->setQuery($query);
                $test = $db->execute();

                if (!empty($test)) {
                    $query = $db->getQuery(true);
                    $query->from('`#__gm_ceiling_cornice` AS cornice')
                        ->select('MAX(cornice.id) AS id')
                        ->where('cornice.calculation_id  = ' . $db->quote($cornice->calculation_id) .
                            ' AND cornice.n15_count = ' . $db->quote($cornice->n15_count) .
                            ' AND cornice.n15_type = ' . $db->quote($cornice->n15_type) .
                            ' AND cornice.n15_size = ' . $db->quote($cornice->n15_size));
                    $queryText = (string)$query;
                    file_put_contents('components/com_gm_ceiling/views/api/history/cornice/add/2_cornices_SQL.txt', $queryText);
                    $db->setQuery($query);
                    $corniceTemp = $db->loadObject();
                    $cornices[] = (object)array("id" => $corniceTemp->id);
                }
            } catch (Exception $e) {
                $answer = "ERROR! corniceSQL! " . $e->getMessage() . " -//- " . $queryText;
            } catch (mysqli_sql_exception $e) {
                $answer = "ERROR SQL! corniceSQL! " . $e->getMessage() . " -//- " . $queryText;
            }
        }

        return (object)array("cornices" => $cornices, "answer" => $answer);
    }

    public function addEcolas($data)
    {
        $db = $this->getDbo();
        $answer = null;
        $column = null;
        $values = null;
        // $date_create = null;
        $ecolas = array();
        $queryText = null;

        foreach ($data as $k => $ecola) {
            if (!empty($answer)) break;

            try {
                $column = array();
                $values = array();
                foreach ($ecola as $key => $value) {
                    if (!empty($value)) {

                        $column[] = $key;
                        $values[] = $db->quote($value);

                    }
                }

                //$phone = (empty($contact->phone)) ? "IS NULL" : "= '" . $contact->phone . "'";
            } catch (Exception $e) {
                $answer = "ERROR! ecolasColVal! " . $e->getMessage();
            }

            try {
                $query = $db->getQuery(true);
                $query
                    ->insert($db->quoteName('#__gm_ceiling_ecola'))
                    ->columns($db->quoteName($column))
                    ->values(implode(',', $values));
                $queryText = (string)$query;
                file_put_contents('components/com_gm_ceiling/views/api/history/ecola/add/1_ecolas_SQL.txt', $queryText);
                $db->setQuery($query);
                $test = $db->execute();

                if (!empty($test)) {
                    $query = $db->getQuery(true);
                    $query->from('`#__gm_ceiling_ecola` AS ecola')
                        ->select('MAX(ecola.id) AS id')
                        ->where('ecola.calculation_id  = ' . $db->quote($ecola->calculation_id) .
                            ' AND ecola.n26_count = ' . $db->quote($ecola->n26_count) .
                            ' AND ecola.n26_illuminator = ' . $db->quote($ecola->n26_illuminator) .
                            ' AND ecola.n26_lamp = ' . $db->quote($ecola->n26_lamp));
                    $queryText = (string)$query;
                    file_put_contents('components/com_gm_ceiling/views/api/history/ecola/add/2_ecolas_SQL.txt', $queryText);
                    $db->setQuery($query);
                    $ecolaTemp = $db->loadObject();
                    $ecolas[] = (object)array("id" => $ecolaTemp->id);
                }
            } catch (Exception $e) {
                $answer = "ERROR! ecolasSQL! " . $e->getMessage() . " -//- " . $queryText;
            } catch (mysqli_sql_exception $e) {
                $answer = "ERROR SQL! ecolasSQL! " . $e->getMessage() . " -//- " . $queryText;
            }
        }

        return (object)array("ecolas" => $ecolas, "answer" => $answer);
    }


    public function addHoods($data)
    {
        $db = $this->getDbo();
        $answer = null;
        $column = null;
        $values = null;
        // $date_create = null;
        $hoods = array();
        $queryText = null;

        foreach ($data as $k => $hood) {
            if (!empty($answer)) break;

            try {
                $column = array();
                $values = array();
                foreach ($hood as $key => $value) {
                    if (!empty($value)) {

                        $column[] = $key;
                        $values[] = $db->quote($value);

                    }
                }

                //$phone = (empty($contact->phone)) ? "IS NULL" : "= '" . $contact->phone . "'";
            } catch (Exception $e) {
                $answer = "ERROR! hoodsColVal! " . $e->getMessage();
            }

            try {
                $query = $db->getQuery(true);
                $query
                    ->insert($db->quoteName('#__gm_ceiling_hoods'))
                    ->columns($db->quoteName($column))
                    ->values(implode(',', $values));
                $queryText = (string)$query;
                file_put_contents('components/com_gm_ceiling/views/api/history/hood/add/1_hoods_SQL.txt', $queryText);
                $db->setQuery($query);
                $test = $db->execute();

                if (!empty($test)) {
                    $query = $db->getQuery(true);
                    $query->from('`#__gm_ceiling_hoods` AS hood')
                        ->select('MAX(hood.id) AS id')
                        ->where('hood.calculation_id  = ' . $db->quote($hood->calculation_id) .
                            ' AND hood.n22_count = ' . $db->quote($hood->n22_count) .
                            ' AND hood.n22_type = ' . $db->quote($hood->n22_type) .
                            ' AND hood.n22_size = ' . $db->quote($hood->n22_size));
                    $queryText = (string)$query;
                    file_put_contents('components/com_gm_ceiling/views/api/history/hood/add/2_hoods_SQL.txt', $queryText);
                    $db->setQuery($query);
                    $hoodTemp = $db->loadObject();
                    $hoods[] = (object)array("id" => $hoodTemp->id);
                }
            } catch (Exception $e) {
                $answer = "ERROR! hoodsSQL! " . $e->getMessage() . " -//- " . $queryText;
            } catch (mysqli_sql_exception $e) {
                $answer = "ERROR SQL! hoodsSQL! " . $e->getMessage() . " -//- " . $queryText;
            }
        }

        return (object)array("hoods" => $hoods, "answer" => $answer);
    }

    public function addPipes($data)
    {
        $db = $this->getDbo();
        $answer = null;
        $column = null;
        $values = null;
        // $date_create = null;
        $pipes = array();
        $queryText = null;

        foreach ($data as $k => $pipe) {
            if (!empty($answer)) break;

            try {
                $column = array();
                $values = array();
                foreach ($pipe as $key => $value) {
                    if (!empty($value)) {

                        $column[] = $key;
                        $values[] = $db->quote($value);

                    }
                }

                //$phone = (empty($contact->phone)) ? "IS NULL" : "= '" . $contact->phone . "'";
            } catch (Exception $e) {
                $answer = "ERROR! PipesColVal! " . $e->getMessage();
            }

            try {
                $query = $db->getQuery(true);
                $query
                    ->insert($db->quoteName('#__gm_ceiling_pipes'))
                    ->columns($db->quoteName($column))
                    ->values(implode(',', $values));
                $queryText = (string)$query;
                file_put_contents('components/com_gm_ceiling/views/api/history/pipe/add/1_pipes_SQL.txt', $queryText);
                $db->setQuery($query);
                $test = $db->execute();

                if (!empty($test)) {
                    $query = $db->getQuery(true);
                    $query->from('`#__gm_ceiling_pipes` AS pipes')
                        ->select('MAX(pipes.id) AS id')
                        ->where('pipes.calculation_id  = ' . $db->quote($pipe->calculation_id) .
                            ' AND pipes.n14_count = ' . $db->quote($pipe->n14_count) .
                            ' AND pipes.n14_size = ' . $db->quote($pipe->n14_size));
                    $queryText = (string)$query;
                    file_put_contents('components/com_gm_ceiling/views/api/history/pipe/add/2_pipes_SQL.txt', $queryText);
                    $db->setQuery($query);
                    $pipeTemp = $db->loadObject();
                    $pipes[] = (object)array("id" => $pipeTemp->id);
                }
            } catch (Exception $e) {
                $answer = "ERROR! pipesSQL! " . $e->getMessage() . " -//- " . $queryText;
            } catch (mysqli_sql_exception $e) {
                $answer = "ERROR SQL! pipesSQL! " . $e->getMessage() . " -//- " . $queryText;
            }
        }

        return (object)array("pipes" => $pipes, "answer" => $answer);
    }

    public function addDiffusers($data)
    {
        $db = $this->getDbo();
        $answer = null;
        $column = null;
        $values = null;
        // $date_create = null;
        $diffusers = array();
        $queryText = null;

        foreach ($data as $k => $diffuser) {
            if (!empty($answer)) break;

            try {
                $column = array();
                $values = array();
                foreach ($diffuser as $key => $value) {
                    if (!empty($value)) {

                        $column[] = $key;
                        $values[] = $db->quote($value);

                    }
                }

                //$phone = (empty($contact->phone)) ? "IS NULL" : "= '" . $contact->phone . "'";
            } catch (Exception $e) {
                $answer = "ERROR! diffuserColVal! " . $e->getMessage();
            }

            try {
                $query = $db->getQuery(true);
                $query
                    ->insert($db->quoteName('#__gm_ceiling_diffusers'))
                    ->columns($db->quoteName($column))
                    ->values(implode(',', $values));
                $queryText = (string)$query;
                file_put_contents('components/com_gm_ceiling/views/api/history/diffuser/add/1_diffusers_SQL.txt', $queryText);
                $db->setQuery($query);
                $test = $db->execute();

                if (!empty($test)) {
                    $query = $db->getQuery(true);
                    $query->from('`#__gm_ceiling_diffusers` AS diffuser')
                        ->select('MAX(diffuser.id) AS id')
                        ->where('diffuser.calculation_id  = ' . $db->quote($diffuser->calculation_id) .
                            ' AND diffuser.n23_count = ' . $db->quote($diffuser->n23_count) .
                            ' AND diffuser.n23_size = ' . $db->quote($diffuser->n23_size));
                    $queryText = (string)$query;
                    file_put_contents('components/com_gm_ceiling/views/api/history/diffuser/add/2_diffusers_SQL.txt', $queryText);
                    $db->setQuery($query);
                    $diffuserTemp = $db->loadObject();
                    $diffusers[] = (object)array("id" => $diffuserTemp->id);
                }
            } catch (Exception $e) {
                $answer = "ERROR! diffuserSQL! " . $e->getMessage() . " -//- " . $queryText;
            } catch (mysqli_sql_exception $e) {
                $answer = "ERROR SQL! diffuserSQL! " . $e->getMessage() . " -//- " . $queryText;
            }
        }

        return (object)array("diffusers" => $diffusers, "answer" => $answer);
    }

    public function authorization($data)
    {
        $db = $this->getDbo();
        $answer = null;
        //$column = null;
        //$values = null;
        // $date_create = null;
        $authorizations = array();
        $queryText = null;

        foreach ($data as $k => $authorization) {
            if (!empty($answer)) break;

            try {
                $query = $db->getQuery(true);
                $query->from('`#__users` AS users')
                    ->select('users.id as id')
                    ->where('users.username  = ' . $db->quote($authorization->username) .
                        ' AND users.password = ' . $db->quote($authorization->password));
                $queryText = (string)$query;
                file_put_contents('components/com_gm_ceiling/views/api/history/authorization/2_authorization_SQL.txt', $queryText);
                $db->setQuery($query);
                $authorizationTemp = $db->loadObject();
                $authorizations[] = (object)array("id" => $authorizationTemp->id);

            } catch (Exception $e) {
                $answer = "ERROR! authorizationTempSQL! " . $e->getMessage() . " -//- " . $queryText;
            } catch (mysqli_sql_exception $e) {
                $answer = "ERROR SQL! authorizationTempSQL! " . $e->getMessage() . " -//- " . $queryText;
            }
        }

        return (object)array("authorizations" => $authorizations, "answer" => $answer);
    }

    public function selectProject($id)
    {
        $db = $this->getDbo();
        $answer = null;
        $projects = null;
        $calculations = array();
        $fixtures = array();
        $cornices = array();
        $diffusers = array();
        $ecolas = array();
        $hoods = array();
        $pipes = array();

        $images = array();

        $queryText = null;
        $calcTemp = null;
        $fixTemp = null;
        $corniceTemp = null;
        $diffuserTemp = null;
        $ecolaTemp = null;
        $hoodTemp = null;
        $pipeTemp = null;


        try {
            $query = $db->getQuery(true);
            $query->from('`#__gm_ceiling_projects` AS projects')
                ->select('projects.*')
                ->where('projects.dealer_id  = ' . $db->quote($id));
            $queryText = (string)$query;
            file_put_contents('components/com_gm_ceiling/views/api/history/authorization/select/1_projects_SQL.txt', $queryText);
            $db->setQuery($query);
            $projectTemp = $db->loadObjectList();
            file_put_contents('components/com_gm_ceiling/views/api/history/authorization/select/1_projects.txt', json_encode($projectTemp));

            $projects = $projectTemp;

            foreach ($projectTemp as $k => $pr) {
                $query = $db->getQuery(true);
                $query->from('`#__gm_ceiling_calculations` as calc')
                    ->select('calc.id, calc.calc_image, calc.cut_image')
                    ->where('calc.project_id  = ' . $db->quote($pr->id));
                $db->setQuery($query);
                $images[$k] = $db->loadObjectList();

                $query = $db->getQuery(true);
                $query->from('`#__gm_ceiling_calculations`')
                    ->select('`id`, `ordering`, `state`, `checked_out`, `checked_out_time`, `created_by`, `modified_by`, `dealer_id`, `calculation_title`, `project_id`, `n1`, `n2`, `n3`, `n4`, `n5`, `n6`, `n7`, `n8`, `n9`, `n10`, `n11`, `n12`, `n16`, `n17`, `n18`, `n19`, `n20`, `n21`, `n24`, `n25`, `n27`, `components_sum`, `canvases_sum`, `mounting_sum`, `transport`, `dop_krepezh`, `extra_components`, `extra_mounting`, `color`, `details`, `calc_data`, `cut_data`, `offcut_square`, `original_sketch`, `distance`, `distance_col`')
                    ->where('project_id  = ' . $db->quote($pr->id));
                $queryText = (string)$query;
                file_put_contents('components/com_gm_ceiling/views/api/history/authorization/select/1_calculations_'.$k.'_SQL.txt', $queryText);
                $db->setQuery($query);
                $calcTemp = $db->loadObjectList();
                file_put_contents('components/com_gm_ceiling/views/api/history/authorization/select/1_calculations_'.$k.'.txt', json_encode($calcTemp));
                foreach ($calcTemp as $T) $calculations[] = $T;

                foreach ($calcTemp as $calc) {
                    $query = $db->getQuery(true);
                    $query->from('`#__gm_ceiling_fixtures` AS fix')
                        ->select('fix.*')
                        ->where('fix.calculation_id  = ' . $db->quote($calc->id));
                    $queryText = (string)$query;
                    file_put_contents('components/com_gm_ceiling/views/api/history/authorization/select/1_fixtures_SQL.txt', $queryText);
                    $db->setQuery($query);
                    $fixTemp = $db->loadObjectList();
                    foreach ($fixTemp as $T) $fixtures[] = $T;
                }
                foreach ($calcTemp as $calc) {
                    $query = $db->getQuery(true);
                    $query->from('`#__gm_ceiling_cornice` AS cornice')
                        ->select('cornice.*')
                        ->where('cornice.calculation_id  = ' . $db->quote($calc->id));
                    $queryText = (string)$query;
                    file_put_contents('components/com_gm_ceiling/views/api/history/authorization/select/1_cornice_SQL.txt', $queryText);
                    $db->setQuery($query);
                    $corniceTemp = $db->loadObjectList();
                    foreach ($corniceTemp as $T) $cornices[] = $T;
                }
                foreach ($calcTemp as $calc) {
                    $query = $db->getQuery(true);
                    $query->from('`#__gm_ceiling_diffusers` AS diffusers')
                        ->select('diffusers.*')
                        ->where('diffusers.calculation_id  = ' . $db->quote($calc->id));
                    $queryText = (string)$query;
                    file_put_contents('components/com_gm_ceiling/views/api/history/authorization/select/1_diffusers_SQL.txt', $queryText);
                    $db->setQuery($query);
                    $diffuserTemp = $db->loadObjectList();
                    foreach ($diffuserTemp as $T) $diffusers[] = $T;
                }
                foreach ($calcTemp as $calc) {
                    $query = $db->getQuery(true);
                    $query->from('`#__gm_ceiling_ecola` AS ecola')
                        ->select('ecola.*')
                        ->where('ecola.calculation_id  = ' . $db->quote($calc->id));
                    $queryText = (string)$query;
                    file_put_contents('components/com_gm_ceiling/views/api/history/authorization/select/1_ecola_SQL.txt', $queryText);
                    $db->setQuery($query);
                    $ecolaTemp = $db->loadObjectList();
                    foreach ($ecolaTemp as $T) $ecolas[] = $T;
                }
                foreach ($calcTemp as $calc) {
                    $query = $db->getQuery(true);
                    $query->from('`#__gm_ceiling_hoods` AS hoods')
                        ->select('hoods.*')
                        ->where('hoods.calculation_id  = ' . $db->quote($calc->id));
                    $queryText = (string)$query;
                    file_put_contents('components/com_gm_ceiling/views/api/history/authorization/select/1_hoods_SQL.txt', $queryText);
                    $db->setQuery($query);
                    $hoodTemp = $db->loadObjectList();
                    foreach ($hoodTemp as $T) $hoods[] = $T;
                }
                foreach ($calcTemp as $calc) {
                    $query = $db->getQuery(true);
                    $query->from('`#__gm_ceiling_pipes` AS pipes')
                        ->select('pipes.*')
                        ->where('pipes.calculation_id  = ' . $db->quote($calc->id));
                    $queryText = (string)$query;
                    file_put_contents('components/com_gm_ceiling/views/api/history/authorization/select/1_pipes_SQL.txt', $queryText);
                    $db->setQuery($query);
                    $pipeTemp = $db->loadObjectList();
                    foreach ($pipeTemp as $T) $pipes[] = $T;
                }

            }

            file_put_contents('components/com_gm_ceiling/views/api/history/authorization/select/1_calculation.txt', json_encode($calculations));

        } catch (Exception $e) {
            $answer = "ERROR! authorizationTempSQL! " . $e->getMessage() . " -//- " . $queryText;
        } catch (mysqli_sql_exception $e) {
            $answer = "ERROR SQL! authorizationTempSQL! " . $e->getMessage() . " -//- " . $queryText;
        }

        return (object)array("projects" => $projects, "calculations" => $calculations, "fixtures" => $fixtures,
            "cornices" => $cornices, "diffusers" => $diffusers, "ecolas" => $ecolas, "hoods" => $hoods,
            "pipes" => $pipes, "images" => $images, "answer" => $answer);
    }

    public function save_or_update_data_from_android($table, $data)
    {
        try
        {
            $db = $this->getDbo();
            $arr_ids = [];
            foreach ($data as $key => $value)
            {
                if (empty($data[$key]->android_id))
                {
                    return false;
                    throw new Exception('empty id!');
                }
                $android_id = $data[$key]->android_id;
                $query = $db->getQuery(true);
                $query->from("`$table`")
                    ->select("count(`id`) as `count`")
                    ->where("`android_id` = $android_id OR `id` = $android_id");
                $db->setQuery($query);
                $count = $db->loadObject()->count;
                $columns = '';
                $columns_values = '';
                if ($count == 0)
                {
                    foreach ($value as $column => $column_value)
                    {
                        $columns .= '`'.$column.'`,';
                        $columns_values .= '\''.$column_value.'\',';
                    }
                    $columns = substr($columns, 0, -1);
                    $columns_values = substr($columns_values, 0, -1);
                    
                    $query = $db->getQuery(true);
                    $query->insert("`$table`")
                        ->columns($columns)
                        ->values($columns_values);
                    $db->setQuery($query);
                    $db->execute();
                    $arr_ids[$key] = (object)array("old_id" => $android_id, "new_id" => $db->insertid());
                }
                else
                {
                    $query = $db->getQuery(true);
                    $query->update("`$table`");
                    foreach ($value as $column => $column_value)
                    {
                        $query->set("`$column` = '$column_value'");
                    }
                    $query->where("`android_id` = $android_id OR `id` = $android_id");
                    $db->setQuery($query);
                    $db->execute();

                    $query = $db->getQuery(true);
                    $query->select("`id`");
                    $query->from("`$table`");
                    $query->where("`android_id` = $android_id OR `id` = $android_id");
                    $db->setQuery($query);
                    $id = $db->loadObject()->id;

                    $arr_ids[$key] = (object)array("old_id" => $android_id, "new_id" => $id);
                }
            }
            return $arr_ids;
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function update_android_ids_from_android($table, $data)
    {
        try
        {
            $db = $this->getDbo();
            $arr_ids = [];
            foreach ($data as $key => $value)
            {
                if (empty($data[$key]->id))
                {
                    return false;
                    throw new Exception('empty id!');
                }
                $id = $data[$key]->id;
                $query = $db->getQuery(true);
                $query->update("`$table`");
                $query->set("`android_id` = '$id'");
                $query->where("`id` = $id");
                $db->setQuery($query);
                $db->execute();
                $arr_ids[$key] = (object)array("new_android_id" => $id);
            }
            return $arr_ids;
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function delete_from_android($table, $data)
    {
        try
        {
            $db = $this->getDbo();
            $arr_ids = [];
            foreach ($data as $key => $value)
            {
                if (empty($data[$key]->id))
                {
                    throw new Exception('empty id!');
                }
                $id = $data[$key]->id;
                $query = $db->getQuery(true);
                $query->delete("`$table`");
                $query->where("`id` = $id");
                $db->setQuery($query);
                $db->execute();
                $arr_ids[$key] = (object)array("delete_id" => $id);
            }
            return $arr_ids;
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    
    public function get_data_android($data)
    {
        try
        {
            $db = $this->getDbo();

            $change_time = $db->escape($data->change_time, false);
            $dealer_id = $db->escape($data->dealer_id, false);

            //проекты
            $query = $db->getQuery(true);
            $query->select("*");
            $query->from("`rgzbn_gm_ceiling_projects`");
            $query->where("`dealer_id` = $dealer_id");
            $db->setQuery($query);
            $list_projects = $db->loadObjectList();

            if (count($list_projects) > 0)
            {
                //клиенты
                $where = "";
                foreach ($list_projects as $key => $value)
                {
                    $id = $value->client_id;
                    if ($key == count($list_projects) - 1)
                    {
                        $where .= "`id`=$id OR `dealer_id` = $dealer_id";
                    }
                    else
                    {
                        $where .= "`id`=$id OR ";
                    }
                }
                
                $query = $db->getQuery(true);
                $query->select("*");
                $query->from("`rgzbn_gm_ceiling_clients`");
                $query->where($where);
                $db->setQuery($query);
                $list_clients = $db->loadObjectList();
            }
            else
            {
                $list_clients = array();
            }

            if (count($list_clients) > 0)
            {
                //контакты
                $where = "";
                foreach ($list_clients as $key => $value)
                {
                    $id = $value->id;
                    if ($key == count($list_clients) - 1)
                    {
                        $where .= "`client_id`=$id";
                    }
                    else
                    {
                        $where .= "`client_id`=$id OR ";
                    }
                }

                $query = $db->getQuery(true);
                $query->select("*");
                $query->from("`rgzbn_gm_ceiling_clients_contacts`");
                $query->where($where);
                $db->setQuery($query);
                $list_contacts = $db->loadObjectList();
            }
            else
            {
                $list_contacts = array();
            }

            if (count($list_projects) > 0)
            {
                //калькуляции
                $where = "";
                foreach ($list_projects as $key => $value)
                {
                    $id = $value->id;
                    if ($key == count($list_projects) - 1)
                    {
                        $where .= "`project_id`=$id";
                    }
                    else
                    {
                        $where .= "`project_id`=$id OR ";
                    }
                }

                $query = $db->getQuery(true);
                $query->select("*");
                $query->from("`rgzbn_gm_ceiling_calculations`");
                $query->where($where);
                $db->setQuery($query);
                $list_calculations = $db->loadObjectList();
            }
            else
            {
                $list_calculations = array();
            }

            if (count($list_calculations) > 0)
            {
                //остальное
                $where = "";
                foreach ($list_calculations as $key => $value)
                {
                    $id = $value->id;
                    if ($key == count($list_calculations) - 1)
                    {
                        $where .= "`calculation_id`=$id";
                    }
                    else
                    {
                        $where .= "`calculation_id`=$id OR ";
                    }
                }

                //трубы
                $query = $db->getQuery(true);
                $query->select("*");
                $query->from("`rgzbn_gm_ceiling_pipes`");
                $query->where($where);
                $db->setQuery($query);
                $list_pipes = $db->loadObjectList();

                //экола
                $query = $db->getQuery(true);
                $query->select("*");
                $query->from("`rgzbn_gm_ceiling_ecola`");
                $query->where($where);
                $db->setQuery($query);
                $list_ecola = $db->loadObjectList();

                //светильники
                $query = $db->getQuery(true);
                $query->select("*");
                $query->from("`rgzbn_gm_ceiling_fixtures`");
                $query->where($where);
                $db->setQuery($query);
                $list_fixtures = $db->loadObjectList();

                //вентиляции
                $query = $db->getQuery(true);
                $query->select("*");
                $query->from("`rgzbn_gm_ceiling_hoods`");
                $query->where($where);
                $db->setQuery($query);
                $list_hoods = $db->loadObjectList();

                //дифузоры
                $query = $db->getQuery(true);
                $query->select("*");
                $query->from("`rgzbn_gm_ceiling_diffusers`");
                $query->where($where);
                $db->setQuery($query);
                $list_diffusers = $db->loadObjectList();

                //корнизы
                $query = $db->getQuery(true);
                $query->select("*");
                $query->from("`rgzbn_gm_ceiling_cornice`");
                $query->where($where);
                $db->setQuery($query);
                $list_cornice = $db->loadObjectList();

                //профиль
                $query = $db->getQuery(true);
                $query->select("*");
                $query->from("`rgzbn_gm_ceiling_profil`");
                $query->where($where);
                $db->setQuery($query);
                $list_profil = $db->loadObjectList();
            }
            else
            {
                $list_pipes = array();
                $list_ecola = array();
                $list_fixtures = array();
                $list_hoods = array();
                $list_diffusers = array();
                $list_cornice = array();
                $list_profil = array();
            }

            $result = [];
            $result['rgzbn_gm_ceiling_clients'] = $list_clients;
            $result['rgzbn_gm_ceiling_clients_contacts'] = $list_contacts;
            $result['rgzbn_gm_ceiling_projects'] = $list_projects;
            $result['rgzbn_gm_ceiling_calculations'] = $list_calculations;
            $result['rgzbn_gm_ceiling_pipes'] = $list_pipes;
            $result['rgzbn_gm_ceiling_ecola'] = $list_ecola;
            $result['rgzbn_gm_ceiling_fixtures'] = $list_fixtures;
            $result['rgzbn_gm_ceiling_hoods'] = $list_hoods;
            $result['rgzbn_gm_ceiling_diffusers'] = $list_diffusers;
            $result['rgzbn_gm_ceiling_cornice'] = $list_cornice;
            $result['rgzbn_gm_ceiling_profil'] = $list_profil;

            $change_time = strtotime($change_time);

            foreach ($result as $key1 => $value1)
            {
                foreach ($value1 as $key2 => $value2)
                {
                    $time_from_db = strtotime($value2->change_time);
                    if ($time_from_db < $change_time)
                    {
                        unset($result[$key1][$key2]);
                    }
                }
                $result[$key1] = array_values($result[$key1]);
            }

            $bool = false;
            foreach ($result as $key => $value)
            {
                if (count($result[$key]) != 0)
                {
                    $bool = true;
                    break;
                }
            }

            if ($bool == true)
            {
                return $result;
            }
            else
            {
                return null;
            }
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function getUserId($name)
    {
        try
        {
            $date = date("d.m.Y H:i:s");
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query->from("`#__users`")
                ->select("MIN(`id`) as id")
                ->where("`username` LIKE '$name' OR `email` LIKE '$name'");

            $queryText = (string)$query;
            file_put_contents('components/com_gm_ceiling/views/api/history/authorization/select/USER_SQL.txt', (string)$date . "\n\n" . $queryText);

            $db->setQuery($query);
            $user = $db->loadObject();

            file_put_contents('components/com_gm_ceiling/views/api/history/authorization/select/USER.txt', (string)$date . "\n\n" . (string)empty($user));
            return ((empty($user))?null:$user->id);
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