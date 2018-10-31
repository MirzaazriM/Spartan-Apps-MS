<?php

namespace Model\LockedState;

class LockedState extends RootPathCompression
{

    private $packages;
    private $workoutPlans;
    private $nutritionPlans;
    private $workouts;
    private $type;


    public function __construct(string $type)
    {
        $this->type = $type;
    }


    public function constructPackages(array $packages)
    {
        $this->packages = $packages;
    }


    public function constructWorkoutPlans(array $workoutPlans)
    {
        $this->workoutPlans = $workoutPlans;
    }


    public function constructNutritionPlans(array $nutritionPlans)
    {
        $this->nutritionPlans = $nutritionPlans;
    }

    public function constructWorkouts(array $workouts)
    {
        $this->workouts = $workouts;
    }

    /**
     * Will determine if a wokrout plan is locked or unlocked
     * 
     * @param int $id
     * @return string
     */
    public function workoutPlanKind(int $id):string
    {        
        // get roots and determine if diwerse
        $roots = $this->findWorkoutPlanRoots($id, $this->packages);
        
        return $this->analyzeLockState($roots, $this->type);    
    }
    
    
    /**
     * Will determine if a nutrition plan is locked or unloced
     * 
     * @param int $id
     * @return string
     */
    public function nutritionPlanKind(int $id):string
    {
        // get roots and determine if diwerse
        $roots = $this->findNutritinoPlanRoots($id, $this->packages);
        
        return $this->analyzeLockState($roots, $this->type);    
    }
    
    
    /**
     * Will determine if a workout is locked or unlocked
     * 
     * @param int $id
     * @return string
     */
    public function workoutKind(int $id):string
    {
        // get roots and determine if diwerse
        $roots = $this->findWorkoutRoots($id, $this->packages, $this->workoutPlans);
        
        return $this->analyzeLockState($roots, $this->type);    
    }
    

}

