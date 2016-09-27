<?php
//Override
//
//ОБЯЗАТЕЛЬНО нужно наследовать оригинальный класс

//Преимущество этого метода override'a что мы четко видим что поменяли и не нужно копировать весь класс

class TutsPlus_Catalog_Model_Category extends Mage_Catalog_Model_Category {

//    Override метода getChildren()
    public function getChildren()
    {
        echo "<br> МЕТОД getChildren ПЕРЕПИСАН <br>";
        return $this->getResource()->getChildren($this, false);
    }

}