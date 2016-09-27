<?php
//Мой helper

//все helper'ы должны быть наследниками   Mage_Core_Helper_Abstract
class TutsPlus_Demo_Helper_Customer extends Mage_Core_Helper_Abstract{

    public function sayHi(){
        echo 'hi';
    }

}