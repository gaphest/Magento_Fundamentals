<?php

//IndexController дергается по default(если не указан в url (указан только FrontName))

//название - Namespace_Module_имяКонтроллера и обязательно наследовать Mage_Core_Controller_Front_Action
class TutsPlus_Demo_IndexController extends Mage_Core_Controller_Front_Action{

//    у названия Actionа должен быть Action в конце
    public function sayHelloAction(){

//        чтобы получить параметры переданные URL - передаются после Action( например index.php/demo/index/sayHello/id/5/val/6)) здесь id=5, val=6
        var_dump($this->getRequest()->getParams());
        //чтобы получить только один параметр
        var_dump($this->getRequest()->getParam('id'));

    }

    //этот Action дергается по default'у (если указан только FrontName или Controller)
    public function indexAction(){
        echo 'Default action';
    }

}