<?php
namespace Model\Service\Facade\DataTypes;

use Model\Service\Facade\DataFacade;

class TagsType
{
    
    private $parent;
    private $dataTemp = [];
    private $dataRaw = [];
    
    public function __construct(DataFacade $parent)
    {
        $this->parent = $parent;
    }
    
    
    /**
     * Get Versioned Data
     * @return array
     */
    public function getDataTemp():array
    {
        //die(print_r($this->dataTemp));
        return $this->handleVersions($this->dataTemp);
    }
    
    
    public function setDataRaw($raw)
    {
        $this->dataRaw = array_merge($this->dataRaw, (array)$raw);
    }
      
    
    public function handleData()
    {
        // tags
        foreach($this->dataRaw as $tag){

            if(!in_array($tag, $this->dataTemp)){
                array_push($this->dataTemp, $tag);
            }
        }
    }

    
    /**
     * Get Versioned Data
     * 
     * @return array
     */
    public function handleVersions($dataTemp):array
    {
        return [$dataTemp];
    }
    

    /**
     * Get Ids From Data
     * 
     * @param $data
     * @return array
     */
    public function handleIds($data):array
    {
        $tagIds = [];
        if(!empty($data)){
            foreach($data as $tag){

                // check if workout type is present in tag
                if(isset($tag['workout_type'])){
                    $tagData = [
                        'u_id' => $tag['id'],
                        'type' => $tag['workout_type']
                    ];
                    array_push($tagIds, $tagData);
                }else {
                    array_push($tagIds, $tag['id']);
                }

            }
        }

        return $tagIds;
    }
    
}

