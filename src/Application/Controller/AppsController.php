<?php
/**
 * Created by PhpStorm.
 * User: mirza
 * Date: 6/28/18
 * Time: 8:34 AM
 */

namespace Application\Controller;


use Application\Controller\Helper\GetAccessToken;
use Model\Entity\AppsSku;
use Model\Entity\AppsSkuCollection;
use Model\Entity\ResponseBootstrap;
use Model\Service\AppsService;
use Symfony\Component\HttpFoundation\Request;

class AppsController
{

    private $appsService;


    public function __construct(AppsService $appsService)
    {
        $this->appsService = $appsService;
    }


    /**
     * Get single or all apps
     *
     * @param Request $request
     * @return ResponseBootstrap
     */
    public function get(Request $request):ResponseBootstrap {
        // get data
        $id = $request->get('id');

        // if data is set
        if(isset($id)){
            // return data
            return $this->appsService->getApp($id);
        }else {
            // return data
            return $this->appsService->getApps();
        }
    }


    /**
     * Delete app
     *
     * @param Request $request
     * @return ResponseBootstrap
     */
    public function delete(Request $request):ResponseBootstrap {
        // get data
        $id = $request->get('id');

        // create response object in case of failure
        $response = new ResponseBootstrap();

        // check if data is set
        if(isset($id)){
            return $this->appsService->deleteApp($id);
        }else {
            $response->setStatus(404);
            $response->setMessage('Bad request');
        }

        // return data
        return $response;
    }


    /**
     * Add app
     *
     * @param Request $request
     * @return ResponseBootstrap
     */
    public function post(Request $request):ResponseBootstrap {
        // get data
        $dat = json_decode($request->getContent(), true);
        $name = $dat['name'];
        $identifier = $dat['identifier'];
        $data = $dat['collection'];

        // create response object in case of failure
        $response = new ResponseBootstrap();

        // create collection
        $collection = new AppsSkuCollection();

        // loop through data
        foreach($data as $d){
            // create entity
            $appSku = new AppsSku();

            // set values
            $appSku->setSku($d['sku']);
            $appSku->setPackageId($d['package']);

            // add to the collection
            $collection->addEntity($appSku);
        }

        // check data and call service
        if(isset($name) && isset($identifier) && isset($collection)){
            return $this->appsService->createApp($name, $identifier, $collection);
        }else {
            $response->setStatus(404);
            $response->setMessage('Bad request');
        }

        // return response
        return $response;
    }


    /**
     * Edit app
     *
     * @param Request $request
     * @return ResponseBootstrap
     */
    public function put(Request $request):ResponseBootstrap {
        // get data
        $dat = json_decode($request->getContent(), true);
        $id = $dat['id'];
        $name = $dat['name'];
        $identifier = $dat['identifier'];
        $data = $dat['collection'];

        // create response object in case of failure
        $response = new ResponseBootstrap();

        // create collection
        $collection = new AppsSkuCollection();

        // loop through data
        foreach($data as $d){
            // create entity
            $appSku = new AppsSku();

            // set values
            $appSku->setSku($d['sku']);
            $appSku->setPackageId($d['package']);

            // add to the collection
            $collection->addEntity($appSku);
        }

        // check if data is set
        if(isset($id) && isset($name) && isset($identifier) && isset($collection)){
            return $this->appsService->editApp($id, $name, $identifier, $collection);
        }else {
            $response->setStatus(404);
            $response->setMessage('Bad request');
        }

        // return response
        return $response;
    }


    /**
     * Get data by app
     *
     * @param Request $request
     * @return ResponseBootstrap
     */
    public function getData(Request $request):ResponseBootstrap {
        // get data
        $app = $request->get('app');
        $state = $request->get('state');
        $lang = $request->get('lang');
        $type = $request->get('type');

        // create response object
        $response = new ResponseBootstrap();

        // check if data is set
        if(isset($app) && isset($lang) && isset($state) && isset($type)){
            return  $this->appsService->getDataByApp($app, $lang, $state, $type);
        }else {
            $response->setStatus(404);
            $response->setMessage('Bad request');
        }

        // return data
        return $response;
    }

}