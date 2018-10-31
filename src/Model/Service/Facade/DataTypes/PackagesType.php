<?php
namespace Model\Service\Facade\DataTypes;

use Model\Service\Facade\DataFacade;

class PackagesType
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
            // add package tags
            $this->parent->tagsType->setDataRaw($data['tags']);
            
            // add workout plans
            $this->parent->workoutsPlanType->setDataRaw($data['training_plans']);
            
            // add nutrition plan tags
            $this->parent->nutritionsPlanType->setDataRaw($data['nutrition_plans']);

            unset($data['nutrition_plans']);
            unset($data['training_plans']);
            unset($data['tags']);

            // form packages data
            if(!in_array($data, $this->dataTemp)){
                array_push($this->dataTemp, $data);
            }
        }
    }
    
    
}

