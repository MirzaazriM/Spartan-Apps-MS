<?php
namespace Model\Service\Facade\DataTypes;

use Model\Service\Facade\DataFacade;

class RecepiesType
{
    
    private $parent;
    private $dataTemp = [];
    private $dataRaw = [];
    
    
    public function __construct(DataFacade $parent)
    {
        $this->parent = $parent;
    }
    
    
    public function getDataTemp():array
    {
        return $this->parent->tagsType->handleVersions($this->dataTemp);
    }
    
    
    public function setDataRaw($raw)
    {
        $this->dataRaw = array_merge($this->dataRaw, (array)$raw);
    }
    
    
    public function handleData()
    {
        foreach($this->dataRaw as $data){
            // add tags
            $this->parent->tagsType->setDataRaw($data['tags']);
            
            // return tag ids
            $data['tags'] = $this->parent->tagsType->handleIds($data['tags']);
                        
            // form recepies
            if(!in_array($data, $this->dataTemp)){
                array_push($this->dataTemp, $data);
            }
        }
    }
}

