<?php
/**
 * Created by PhpStorm.
 * User: mirza
 * Date: 6/28/18
 * Time: 8:34 AM
 */

namespace Model\Service;

use Exception;
use Model\Core\Helper\Monolog\MonologSender;
use Model\Entity\AppsSku;
use Model\Entity\AppsSkuCollection;
use Model\Entity\ResponseBootstrap;
use Model\Mapper\AppsMapper;
use Model\Service\Facade\DataFacade;
use Model\Service\Helper\AuthHelper;

class AppsService
{

    private $appsMapper;
    private $configuration;
    private $monologHelper;

    public function __construct(AppsMapper $appsMapper)
    {
        $this->appsMapper = $appsMapper;
        $this->configuration = $appsMapper->getConfiguration();
        $this->monologHelper = new MonologSender();
    }


    /**
     * Get single app service
     *
     * @param int $id
     * @return ResponseBootstrap
     */
    public function getApp(int $id):ResponseBootstrap {

        try {
            // create response object
            $response = new ResponseBootstrap();

            // create entity and set its values
            $entity = new AppsSku();
            $entity->setId($id);

            // get response from database
            $res = $this->appsMapper->getApp($entity);
            $id = $res->getId();

            // check data and set response
            if(isset($id)){
                $response->setStatus(200);
                $response->setMessage('Success');
                $response->setData([
                    'id' => $res->getId(),
                    'name' => $res->getName(),
                    'identifier' => $res->getIdentifier(),
                    'date' => $res->getDate(),
                    'packages' => $res->getPackages()
                ]);
            }else {
                $response->setStatus(204);
                $response->setMessage('No content');
            }

            // return data
            return $response;

        }catch (Exception $e){
            // send monolog record
            $this->monologHelper->sendMonologRecord($this->configuration, 1000, "Get app service: " . $e->getMessage());

            $response->setStatus(404);
            $response->setMessage('Invalid data');
            return $response;
        }
    }


    /**
     * Get all apps
     *
     * @return ResponseBootstrap
     */
    public function getApps():ResponseBootstrap {

        try {
            // create response object
            $response = new ResponseBootstrap();

            // create get facade object and handle apps data
            $facade = new GetFacade($this->appsMapper);
            $data = $facade->handleData();

            // check data and set response
            if(!empty($data)){
                $response->setStatus(200);
                $response->setMessage('Success');
                $response->setData(
                    $data
                );
            }else {
                $response->setStatus(204);
                $response->setMessage('No content');
            }

            // return data
            return $response;

        }catch (Exception $e){
            // send monolog record
            $this->monologHelper->sendMonologRecord($this->configuration, 1000, "Get apps service: " . $e->getMessage());

            $response->setStatus(404);
            $response->setMessage('Invalid data');
            return $response;
        }
    }


    /**
     * Delete app service
     *
     * @param int $id
     * @return ResponseBootstrap
     */
    public function deleteApp(int $id):ResponseBootstrap {

        try {
            // create response object
            $response = new ResponseBootstrap();

            // create entity and set its values
            $entity = new AppsSku();
            $entity->setId($id);

            // get response from database
            $res = $this->appsMapper->deleteApp($entity)->getResponse();

            // check data and set response
            if($res[0] == 200){
                $response->setStatus(200);
                $response->setMessage('Success');
            }else {
                $response->setStatus(304);
                $response->setMessage('Not modified');
            }

            // return response
            return $response;

        }catch (Exception $e){
            // send monolog record
            $this->monologHelper->sendMonologRecord($this->configuration, 1000, "Delete app service: " . $e->getMessage());

            $response->setStatus(404);
            $response->setMessage('Invalid data');
            return $response;
        }
    }


    /**
     * Add app
     *
     * @param string $name
     * @param AppsSkuCollection $appSku
     * @return ResponseBootstrap
     */
    public function createApp(string $name, string $identifier, AppsSkuCollection $appSku):ResponseBootstrap {

        try {
            // create response object
            $response = new ResponseBootstrap();

            // create entity and set its values
            $entity = new AppsSku();
            $entity->setCollection($appSku);
            $entity->setName($name);
            $entity->setIdentifier($identifier);

            // get response from database
            $res = $this->appsMapper->createApp($entity)->getResponse();

            // check data and set response
            if($res[0] == 200){
                $response->setStatus(200);
                $response->setMessage('Success');
            }else {
                $response->setStatus(304);
                $response->setMessage('Not modified');
            }

            // return response
            return $response;

        }catch (Exception $e){
            // send monolog record
            $this->monologHelper->sendMonologRecord($this->configuration, 1000, "Create app service: " . $e->getMessage());

            $response->setStatus(404);
            $response->setMessage('Invalid data');
            return $response;
        }
    }


    /**
     * Edit app
     *
     * @param int $id
     * @param string $name
     * @param AppsSkuCollection $appSku
     * @return ResponseBootstrap
     */
    public function editApp(int $id, string $name, string $identifier, AppsSkuCollection $appSku):ResponseBootstrap {

        try {
            // create response object
            $response = new ResponseBootstrap();

            // create entity and set its values
            $entity = new AppsSku();
            $entity->setId($id);
            $entity->setCollection($appSku);
            $entity->setName($name);
            $entity->setIdentifier($identifier);

            // get response from database
            $res = $this->appsMapper->editApp($entity)->getResponse();

            // check data and set response
            if($res[0] == 200){
                $response->setStatus(200);
                $response->setMessage('Success');
            }else {
                $response->setStatus(304);
                $response->setMessage('Not modified');
            }

            // return response
            return $response;

        }catch (Exception $e){
            // send monolog record
            $this->monologHelper->sendMonologRecord($this->configuration, 1000, "Edit app service: " . $e->getMessage());

            $response->setStatus(404);
            $response->setMessage('Invalid data');
            return $response;
        }
    }


    /**
     * Get data by app
     *
     * @param string $app
     * @param string $lang
     * @param string $state
     * @param string $type
     * @return ResponseBootstrap
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getDataByApp(string $app, string $lang, string $state, string $type):ResponseBootstrap {

        try {
            // create response object
            $response = new ResponseBootstrap();

            // set sku as pro - it returns the same data for any type of sku
            $sku = "pro";

            // call packages MS for core data
            $client = new \GuzzleHttp\Client();
            $result = $client->request('GET', $this->configuration['packages_url'] . '/packages/packages?lang=' . $lang . '&state=R&app=' . $app . '&type=' . $sku, []);
            $data = json_decode($result->getBody()->getContents(), true);

            // get packages data in facade object
            $dataFacade = new DataFacade($data, 0, $type);
            $data = $dataFacade->handle();

            // set response
            if(!empty($data)){
                $response->setStatus(200);
                $response->setMessage('Success');
                $response->setData(
                    $data[$type]
                );
            }else {
                $response->setStatus(204);
                $response->setMessage('No content');
            }

            // return data
            return $response;

        }catch (\Exception $e){
            // send monolog record
            $this->monologHelper->sendMonologRecord($this->configuration, 1000, "Get data by app service: " . $e->getMessage());

            $response->setStatus(404);
            $response->setMessage('Invalid data');
            return $response;
        }
    }
}