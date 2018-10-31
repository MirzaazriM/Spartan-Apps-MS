<?php
namespace Model\Service\Facade;

use Model\LockedState\LockedState;
use Model\Service\Facade\DataTypes\TagsType;
use Model\Service\Facade\DataTypes\WorkoutPlansType;
use Model\Service\Facade\DataTypes\NutritionsPlanType;
use Model\Service\Facade\DataTypes\RecepiesType;
use Model\Service\Facade\DataTypes\WorkoutsType;
use Model\Service\Facade\DataTypes\ExercisesType;
use Model\Service\Facade\DataTypes\PackagesType;

class DataFacade
{
    
    // Variables
    private $rawData;
    public $version;
    public $type;
    
    // Classes
    public $tagsType;
    public $workoutsPlanType;
    public $nutritionsPlanType;
    public $recepiesType;
    public $workoutsType;
    public $exercisesType;
    public $packageType;

    // locking variable
    public $lockingState;
    
    public function __construct($rawData, Int $version, string $type)
    {
        // Variables
        $this->rawData = $rawData;
        $this->version = $version;
        $this->type = $type;

        // Helper Classes
        $this->tagsType = new TagsType($this);
        $this->workoutsPlanType = new WorkoutPlansType($this);
        $this->nutritionsPlanType = new NutritionsPlanType($this);
        $this->recepiesType = new RecepiesType($this);
        $this->workoutsType = new WorkoutsType($this);
        $this->exercisesType = new ExercisesType($this);
        $this->packageType = new PackagesType($this);  //$this->packageType = new PackagesType($this, $version);

        // new locking/unlocking mechanism
        $this->lockingState = new LockedState($this->type);
    }
    
    
    /**
     * Handle Data
     * @return array
     */
    public function handle(): array
    {
        // call methods for formatting data
        $this->packages();
        $this->workoutPlans();
        $this->nutritionPlans();
        $this->recepies();
        $this->workouts();
        $this->exercises();
        $this->tags();

        // remove workout duplicates
        $workoutTemp = [];
        $ids = [];
        $workouts = $this->workoutsType->getDataTemp()[0];
        foreach($workouts as $workout){

            $id = $workout['id'];

            if(!in_array($id, $ids) && $id != "0"){
                array_push($ids, $id);
                array_push($workoutTemp, $workout);
            }
        }

        // remove exercise duplicates
        $exerciseTemp = [];
        $ids = [];
        $exercises = $this->exercisesType->getDataTemp()[0];
        foreach($exercises as $exercise){

            $id = $exercise['id'];

            if(!in_array($id, $ids)){
                array_push($ids, $id);
                array_push($exerciseTemp, $exercise);
            }
        }

        $response = [
            'packages' => $this->packageType->getDataTemp()[0],
            'training_plans' => $this->workoutsPlanType->getDataTemp()[0],
            'nutrition_plans' => $this->nutritionsPlanType->getDataTemp()[0],
            'workouts' => $workoutTemp, // $this->workoutsType->getDataTemp()[0],
            'recipes' => $this->recepiesType->getDataTemp()[0],
            'exercises' => $exerciseTemp,
            'tags' => $this->tagsType->getDataTemp()[0]
        ];
        
        return $response;
    }
    
    
    /**
     * Handle Packages
     */
    public function packages()
    {
        $this->packageType->setDataRaw($this->rawData);
        $this->packageType->handleData();
    }
    
    
    /**
     * Handle Workout Plans
     */
    public function workoutPlans()
    {
        $this->workoutsPlanType->handleData();
    }
    
    
    /**
     * Handle Nutrition Plans
     */
    public function nutritionPlans()
    {
        $this->nutritionsPlanType->handleData();
    }
    
    
    public function workouts()
    {
        $this->workoutsType->handleData();
    }
    
    
    /**
     * Hanlde Recepies
     */
    public function recepies()
    {
        $this->recepiesType->handleData();
    }
    
    
    public function exercises()
    {
        $this->exercisesType->handleData();
    }
    
    
    /**
     * Handle Tags
     */
    public function tags()
    {
        $this->tagsType->handleData();
    }
}

