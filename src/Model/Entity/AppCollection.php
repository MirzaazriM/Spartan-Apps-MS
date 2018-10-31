<?php
/**
 * Created by PhpStorm.
 * User: mirza
 * Date: 6/28/18
 * Time: 12:09 PM
 */

namespace Model\Entity;


use Component\Collection;
use Model\Contract\HasId;

class AppCollection extends Collection
{

    public function buildEntity(): HasId
    {
        // TODO: Implement buildEntity() method.
    }
}