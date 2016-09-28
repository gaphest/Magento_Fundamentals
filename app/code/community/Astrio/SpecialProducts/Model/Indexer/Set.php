<?php
/**
 * Astrio Agency
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0).
 * It is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you are unable to obtain it through the world-wide-web, please send
 * an email to info@astrio.net so we can send you a copy immediately.
 *
 * @category   Astrio
 * @package    Astrio_SpecialProducts
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Astrio
 *
 * @category Astrio
 * @package  Astrio_SpecialProducts
 * @author   Eldaniz Gasymov <e.gasymov@astrio.net>
 */
class Astrio_SpecialProducts_Model_Indexer_Set extends Mage_Index_Model_Indexer_Abstract
{

    /**
     * Data key for matching result to be saved in
     */
    const EVENT_MATCH_RESULT_KEY = 'astrio_specialproducts_set_match_result';

    /**
     * @var array
     */
    protected $_matchedEntities = array(
        Astrio_SpecialProducts_Model_Set::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
        ),
    );

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('astrio_specialproducts/indexer_set');
    }

    /**
     * Retrieve Indexer name
     *
     * @return string
     */
    public function getName()
    {
        return Mage::helper('astrio_specialproducts')->__('Astrio Special Products');
    }

    /**
     * Retrieve Indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return Mage::helper('astrio_specialproducts')->__('Astrio Special Products');
    }

    /**
     * Register data required by process in event object
     *
     * @param  Mage_Index_Model_Event $event event
     * @return $this
     */
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, true);
        $entity = $event->getEntity();
        switch ($entity) {
            case Astrio_SpecialProducts_Model_Set::ENTITY:
                switch($event->getType())
                {
                    case Mage_Index_Model_Event::TYPE_SAVE:
                        $set = $event->getDataObject();
                        $event->addNewData('set_ids', array($set->getId()));
                        break;
                }
                break;
        }

        return $this;
    }

    /**
     * Process event
     *
     * @param  Mage_Index_Model_Event $event event
     * @return $this
     */
    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        /**
         * @var $resourceModel Astrio_SpecialProducts_Model_Resource_Indexer_Set
         */
        $data = $event->getNewData();
        if (!empty($data['product_ids'])) {
            $resourceModel = Mage::getResourceSingleton('astrio_specialproducts/indexer_set');
            try {
                $resourceModel->reindex($data['set_ids']);
            } catch (Exception $e) {

            }
        }

        return $this;
    }

    /**
     * Rebuild all index data
     */
    public function reindexAll()
    {
        /** @var $resourceModel Astrio_SpecialProducts_Model_Resource_Indexer_Set */
        $resourceModel = Mage::getResourceSingleton('astrio_specialproducts/indexer_set');
        $resourceModel->reindexAll();

        return $this;
    }
}
