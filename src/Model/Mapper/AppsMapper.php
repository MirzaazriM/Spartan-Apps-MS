<?php
/**
 * Created by PhpStorm.
 * User: mirza
 * Date: 6/28/18
 * Time: 8:35 AM
 */

namespace Model\Mapper;

use PDO;
use PDOException;
use Component\DataMapper;
use Model\Entity\AppsSku;
use Model\Entity\AppsSkuCollection;
use Model\Entity\Shared;

class AppsMapper extends  DataMapper
{

    public function getConfiguration()
    {
        return $this->configuration;
    }


    /**
     * Fetch single app
     *
     * @param AppsSku $app
     * @return AppsSkuCollection
     */
    public function getApp(AppsSku $app):AppsSku {

        // create response object
        $response = new AppsSku();

        try {
            // set database instructions
            $sql = "SELECT 
                        a.id,
                        a.name,
                        a.identifier,
                        a.date,
                        GROUP_CONCAT(DISTINCT ap.id) AS package_ids
                       /* ap.package_child,
                        ap.sku */
                    FROM apps AS a 
                    LEFT JOIN app_packages AS ap ON a.id = ap.app_parent 
                    WHERE a.id = ?";
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                $app->getId()
            ]);

            // fetch data
            $data = $statement->fetch(PDO::FETCH_ASSOC);

            // set ids to array
            $ids = explode(',', $data['package_ids']);

            // set packages data variable
            $packagesData = [];

            // get packages data
            $sql = "SELECT 
                      ap.*,
                      p.thumbnail,
                      p.raw_name,
                      p.state,
                      pn.name,
                      pn.language 
                    FROM app_packages AS ap 
                    LEFT JOIN package AS p ON p.id = ap.package_child 
                    LEFT JOIN package_name AS pn ON pn.package_parent = p.id
                    WHERE ap.id = ?";
            $statement = $this->connection->prepare($sql);
            foreach ($ids as $id){
                $statement->execute([
                    $id
                ]);

                // fetch data
                $packageData = $statement->fetch(PDO::FETCH_ASSOC);
                $packageData['thumbnail'] = $this->configuration['asset_link'] . $packageData['thumbnail'];
                // set data to array
                array_push($packagesData, $packageData);
            }

            // set response
            $response->setId($data['id']);
            $response->setName($data['name']);
            $response->setIdentifier($data['identifier']);
            $response->setDate($data['date']);
            $response->setPackages($packagesData);

        }catch(PDOException $e){
            // send monolog record in case of failure
            $this->monologHelper->sendMonologRecord($this->configuration, $e->errorInfo[1], "Get app mapper: " . $e->getMessage());
        }

        // return data
        return $response;
    }


    /**
     * Fetch all apps
     *
     * @param AppsSku $app
     * @return AppsSkuCollection
     */
    public function getApps():AppsSkuCollection {

        // create response object
        $response = new AppsSkuCollection();

        try {
            // set database instructions
            $sql = "SELECT 
                        a.id,
                        a.name,
                        a.identifier,
                        a.date,
                        GROUP_CONCAT(DISTINCT ap.id) AS package_ids
                    FROM apps AS a 
                    LEFT JOIN app_packages AS ap ON a.id = ap.app_parent
                    GROUP BY a.id";
            $statement = $this->connection->prepare($sql);
            $statement->execute();

            // fetch data
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);

            // loop throug apps and collect their packages
            foreach($data as $row){
                // get ids
                $ids = explode(',', $row['package_ids']);

                // set packages data variable
                $packagesData = [];

                // get packages data
                $sql = "SELECT 
                          ap.*,
                          p.thumbnail 
                        FROM app_packages AS ap
                        LEFT JOIN package AS p ON ap.package_child = p.id
                        WHERE ap.id = ?";
                $statement = $this->connection->prepare($sql);
                foreach ($ids as $id){
                    $statement->execute([
                        $id
                    ]);

                    // fetch data
                    $packageData = $statement->fetch(PDO::FETCH_ASSOC);
                    $packageData['thumbnail'] = $this->configuration['asset_link'] . $packageData['thumbnail'];

                        // set data in array
                    array_push($packagesData, $packageData);
                }

                // create app entity and set its values
                $app = new AppsSku();
                $app->setId($row['id']);
                $app->setName($row['name']);
                $app->setIdentifier($row['identifier']);
                $app->setDate($row['date']);
                $app->setPackages($packagesData);

                // add app to the collection
                $response->addEntity($app);
            }

        }catch(PDOException $e){
            $response->setStatusCode(204);

            // send monolog record in case of failure
            $this->monologHelper->sendMonologRecord($this->configuration, $e->errorInfo[1], "Get apps mapper: " . $e->getMessage());
        }

        // return data
        return $response;
    }


    /**
     * @param AppsSku $app
     * @return Shared
     */
    public function deleteApp(AppsSku $app):Shared {

        // create response object
        $response = new Shared();

        try {
            // set database instructions
            $sql = "DELETE 
                      a.*,
                      ap.*
                    FROM apps AS a 
                    LEFT JOIN app_packages AS ap ON a.id = ap.app_parent
                    WHERE a.id = ?";
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                $app->getId()
            ]);

            // set status code
            if($statement->rowCount() > 0){
                $response->setResponse([200]);
            }else {
                $response->setResponse([304]);
            }

        }catch(PDOException $e){
            $response->setResponse([304]);

            // send monolog record in case of failure
            $this->monologHelper->sendMonologRecord($this->configuration, $e->errorInfo[1], "Delete app mapper: " . $e->getMessage());
        }

        // return data
        return $response;
    }


    /**
     * Insert new app
     *
     * @param AppsSku $app
     * @return Shared
     */
    public function createApp(AppsSku $app):Shared {

        // create response object
        $response = new Shared();

        try {
            // begin transaction
            $this->connection->beginTransaction();

            // set database instructions
            $sql = "INSERT INTO apps (name, identifier) VALUES (?,?)";
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                $app->getName(),
                $app->getIdentifier()
            ]);

            // get id of inserted app
            $lastId = $this->connection->lastInsertId();

            // insert skus
            $sql = "INSERT INTO app_packages (app_parent, package_child, sku) VALUES (?,?,?)";
            $statement = $this->connection->prepare($sql);
            // get skus and package ids
            $skus = $app->getCollection();
            foreach($skus as $sku){
                // execute query
                $statement->execute([
                    $lastId,
                    $sku->getPackageId(),
                    $sku->getSku()
                ]);
            }

            // set response
            $response->setResponse([200]);

            // commit transaction
            $this->connection->commit();

        }catch(PDOException $e){
            // rollback everything in case of failure
            $this->connection->rollBack();
            $response->setResponse([304]);

            // send monolog record in case of failure
            $this->monologHelper->sendMonologRecord($this->configuration, $e->errorInfo[1], "Create app mapper: " . $e->getMessage());
        }

        // return response
        return $response;
    }


    /**
     * Edit app
     *
     * @param AppsSku $app
     * @return Shared
     */
    public function editApp(AppsSku $app):Shared {

        // create response object
        $response = new Shared();

        try {
            // begin transaction
            $this->connection->beginTransaction();

            // set database instructions
            $sql = "UPDATE apps SET name = ?, identifier = ? WHERE id = ?";
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                $app->getName(),
                $app->getIdentifier(),
                $app->getId()
            ]);

            // delete skus
            $sqlDelete = "DELETE FROM app_packages WHERE app_parent = ?";
            $statementDelete = $this->connection->prepare($sqlDelete);
            $statementDelete->execute([
                $app->getId()
            ]);

            // update skus
            $sqlSku = "INSERT INTO
                            app_packages (app_parent, package_child, sku)
                            VALUES (?,?,?)
                        ON DUPLICATE KEY
                        UPDATE
                            app_parent = VALUES(app_parent),
                            package_child = VALUES(package_child),
                            sku = VALUES(sku)";
            $statementSku = $this->connection->prepare($sqlSku);

            // loop through data and make updates if neccesary
            $skus = $app->getCollection();
            foreach($skus as $sku){
                $statementSku->execute([
                    $app->getId(),
                    $sku->getPackageId(),
                    $sku->getSku()
                ]);
            }

            // set status code
            if($statement->rowCount() > 0 or $statementSku->rowCount() > 0){
                $response->setResponse([200]);
            }else {
                $response->setResponse([304]);
            }

            // commit transaction
            $this->connection->commit();

        }catch(PDOException $e){
            // rollback everything in case of failure
            $this->connection->rollBack();
            $response->setResponse([304]);

            // send monolog record in case of failure
            $this->monologHelper->sendMonologRecord($this->configuration, $e->errorInfo[1], "Edit app mapper: " . $e->getMessage());
        }

        // return response
        return $response;
    }

}