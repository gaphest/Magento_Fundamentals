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
 * @package    Astrio_Video
 * @copyright  Copyright (c) 2010-2015 Astrio Agency (http://astrio.net)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Observer
 *
 * @category   Astrio
 * @package    Astrio_Video
 * @author     Demidov Ilya <i.demidov@astrio.net>
 */
class Astrio_Video_Model_Observer
{

    /**
     * Catalog product save after
     *
     * @param Varien_Event_Observer $observer observer
     * @throws Exception
     */
    public function catalogProductSaveAfter(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($product && $product->getId() && $product->hasData('astrio_video_data')) {

            /** @var  Astrio_Video_Model_Resource_Video_Collection $collection */
            $collection = Mage::getResourceModel('astrio_video/video_collection');
            $oldIds = $collection->addProductFilter($product)->getAllIds();

            $saveIds = array();
            $videoData = (array)$product->getData('astrio_video_data');

            foreach ($videoData as $data) {
                if (!empty($data['video_id'])) {
                    $saveIds[] = $data['video_id'];
                } else {
                    unset($data['video_id']);
                }

                $data['product_id'] = $product->getId();
                Mage::getModel('astrio_video/video')
                    ->setData($data)
                    ->save();
            }

            $deleteIds = array_diff($oldIds, $saveIds);

            foreach ($deleteIds as $id) {
                Mage::getModel('astrio_video/video')->setId($id)->delete();
            }
        }
    }

    /**
     * Catalog product prepare save
     *
     * @param Varien_Event_Observer $observer observer
     */
    public function catalogProductPrepareSave(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        /** @var Mage_Core_Controller_Request_Http $request */
        $request = $observer->getEvent()->getRequest();
        if ($request->getPost('astrio_video_save')) {
            $product->setData('astrio_video_data', (array)$request->getPost('astrio_video'));
        }
    }
}
