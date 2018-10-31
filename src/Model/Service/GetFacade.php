<?php
/**
 * Created by PhpStorm.
 * User: mirza
 * Date: 8/8/18
 * Time: 2:26 PM
 */

namespace Model\Service;


class GetFacade
{

    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }


    public function handleData() {
        // get adjusted data
        return $this->getData();
    }


    public function getData() {
        // call mapper for data
        $data = $this->connection->getApps();

        // convert data to array
        $data = $this->convertor($data);

        return $data;
    }


    public function convertor($data) {
        // convert collection
        $formatedData = [];
        for($i = 0; $i < count($data); $i++){
            $formatedData[$i]['id'] = $data[$i]->getId();
            $formatedData[$i]['name'] = $data[$i]->getName();
            $formatedData[$i]['identifier'] = $data[$i]->getIdentifier();
            $formatedData[$i]['date'] = $data[$i]->getDate();
            $formatedData[$i]['packages'] = $data[$i]->getPackages();
        }

        return $formatedData;
    }

}