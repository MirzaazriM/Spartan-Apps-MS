<?php
namespace Model\LockedState;

class RootPathCompression
{
    
    
    /**
     * Find Workotu Roots
     * 
     * @param int $id
     * @param array $packages
     * @return array
     */
    public function findWorkoutPlanRoots(int $id, array $packages):array
    {
        $roots = [];
        
        foreach($packages as $package){
            
            if(in_array($id,$package['training_plans'])){
                array_push($roots, $package['sku']);
            }
      
        }
        
        return $roots;
    }
    
    
    /**
     * Find Nutrition Roots
     * 
     * @param int $id
     * @param array $packages
     * @return array
     */
    public function findNutritinoPlanRoots(int $id, array $packages):array
    {
        $roots = [];
        
        foreach($packages as $package){
            
            if(in_array($id,$package['nutrition_plans'])){
                array_push($roots, $package['sku']);
            }
            
        }
        
        return $roots;
    }
    
    
    /**
     * Find Workout Roots
     * 
     * @param int $id
     * @param array $packages
     * @param array $workoutPlans
     * @return array
     */
    public function findWorkoutRoots(int $id, array $packages, array $workoutPlans)
    {
        $plans = [];
        
        // take from workout plan roots 
        foreach($workoutPlans as $workout){
            
            if(in_array($id,$workout['workouts'])){
                array_push($plans, $workout['id']);
            }
            
        }
          
        $roots = [];
        // take from plan roots
        foreach($plans as $plan){
            $roots = array_merge($roots, $this->findWorkoutPlanRoots($plan, $packages));
        }
        
        return $roots;
    }
    
    
    /**
     * Analyze Consistency 
     * 
     * @param array $roots
     * @return boolean
     */
    public function analyzeKind(array $roots)
    {
        $isConsistent = true;
        foreach($roots as $root){
            foreach($roots as $rootPrim){
                if($root !==  $rootPrim){
                    $isConsistent = false;
                }
            }
        }

        return $isConsistent;
    }
    
    
    /**
     * Analyze Lock State
     * 
     * @param array $roots
     * @param string $type
     * @return string
     */
    public function analyzeLockState(array $roots, string $type): string
    {
        $state = "unlocked";
                
        // if all of the same packages type (free/pro)
        if($this->analyzeKind($roots)){
            if($roots[0] != "free"){
                $state = "locked";
            }
            
            if($roots[0] == "pro" && $type == "pro"){
                $state = "unlocked";
            }
        }
        // if not consistent kind
        else{
            // if free type
            if($type == "free"){
     
                foreach($roots as $root){
                    
                    // if custom package
                    if($root !== 'free'){
                        $state = "locked";
                    }
                    // if not custom package
                    else {
                        $state = "unlocked";
                    }  
                      
                }

            }
        }
        
        return $state;
    }
    
}

