<?php
namespace Model\Service\Facade\DataTypes;

use Model\LockedState\LockedState;
use Model\Service\Facade\DataFacade;

class WorkoutPlansType
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

            // add workouts
            $this->parent->workoutsType->setDataRaw($data['workouts']);
            
            // return tags ids
            $data['tags'] = $this->parent->tagsType->handleIds($data['tags']);

            unset($data['ids']);
            unset($data['workouts']);
            unset($data['tags']);

            // form workout plan
            if(!in_array($data, $this->dataTemp)){
                array_push($this->dataTemp, $data);
            }
        }
    }
    
}

