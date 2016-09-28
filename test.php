<?php
//ПРОСТО ТЕСТОВЫЙ ФАЙЛ


//Mage.php - starting point в инициализации системы
//тоже самое делает index.php
require_once 'app/Mage.php';

Mage::app();
//
//////объект созданного класса
//
////$product=new TutsPlus_Demo_Model_Product;
//
//////дергаю созданный мною метод
////$product->sayHello();
//
////но такой вариант не совсем правильный
//$customer=new Mage_Customer_Model_Session;
//
////НАДО ТАК - делает тоже самое с помощью Factory Pattern - возвращает объект
////ТАК СОЗДАЮТСЯ ОБЪЕКТЫ КЛАССОВ
//
////Magento пойдет в Mage/Customer/etc/config.xml в <models> (т.к getModel()) >> <customer> >> <class> и увидит Mage_Customer_Model
////добавит session и получит Mage_Customer_Model_Session и теперь autoloader.php распарсит это в путь Mage/Customer/Model/Session.php
//
//$customer2= Mage::getModel("customer/session");
//
//
//echo get_class($customer2);  /*класс этого объекта - Mage_Customer_Model_Session*/
//
////т.к мы зарегистрировали module в TutsPlus_Demo.xml он знает что он находится в local/TutsPlus поэтому обратиться к  local/TutsPlus/Demo/etc/config.xml
//
//$product=Mage::getModel('demo/product');
//
//$product->sayHello();
//
////вызываем объект класса HELPER'a
//$helper = Mage::helper('demo/customer');
//
//$helper->sayHi();
//
////Mage_Catalog_Model_Category.php (в нем есть метод getChildren который возвращает string айдишников детей)
////НО нам нужно уточнить КАКАЯ категория
//$category = Mage::getModel("catalog/category")->load(4); /*4-id категории*/
//
//var_dump($category->getChildren());
//
//$product=Mage::getModel('catalog/product')->load(895);
//
////var_dump($product);
//
//echo $product->getData('name');
////тоже самое, однако этого метода getName в системе нету
////когда вызываю метод которого нету прежде чем php выкинет ошибку он вызывает магический метод __call в /lib/Varien/Object.php
////он проверяет начинается ли метод c get/set/uns/has и в итоге вызовет тот же самый getData('name')
//echo $product->getName();
//
////поменяет атрибут Name, но в БД Name останется тем же
//$product->setName('MyCustomName');
//
////а вот так поменяет атрибут Name и в БД!
//$product->setName('MyCustomName')->save();
//
//echo $product->getName();

//но если мы хотим получить коллекцию продуктов то надо
$products=Mage::getModel('catalog/product')->getCollection();

//ВСЕГДА ДЕЛАТЬ ТАК (ПО ДРУГОМУ НЕ РАБОТАЕТ) - загружать у коллекции только те атрибуты которые нам будут нужны

$products=Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('name');

//если нужно несколько аттрибутов то ->addAttributeToSelect(array('name','price'))
//плюс можно делать фильтрацию ->addFieldToFilter('price', array("lt"=>50)) - цена less than(lt) 50
//$products=Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect(array('name','price'))->addFieldToFilter('price', array("lt"=>50));


//и коллекцию можно перебирать в foreach
foreach($products as $product){
    var_dump($product->getName());
}