<?php

//название класса должно быть Autoloader friendly

class TutsPlus_Demo_Model_Observer{

    //Метод который вызывается при event'e
//    $observer - ОБЯЗАТЕЛЬНО, ,благодаря ему он принимает данные полученные из dispatchEvent метода
    public function logCustomer($observer){

        //если бы был product было бы $observer->getProduct() , т.е get и любая entity которую я получаю
        $customer=$observer->getCustomer(); //в $customer вернулся объект

//        встроенная (built-in) функция log
        Mage::log($customer->getName() . " has logged in!", null, "customer.log"); /*Третий параметр - куда будут записываться логи - будет в magento/var/log */
    }
}