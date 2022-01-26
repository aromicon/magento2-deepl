<?php
/**
 * @category  Aromicon
 * @package   Aromicon_Deepl
 * @author    Stefan Richter <richter@aromicon.de>
 * @copyright 2018 aromicon GmbH (http://www.aromicon.de)
 * @license   Commercial https://www.aromicon.de/magento-download-extensions-modules/de/license
 */

namespace Aromicon\Deepl\Controller\Adminhtml\Catalog\Attribute;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class Translate extends \Aromicon\Deepl\Controller\Adminhtml\Catalog
{

    const ADMIN_RESOURCE = 'Aromicon_Deepl::translate_attribute';

    /**
     * @var \Aromicon\Deepl\Model\Translator\Catalog\Attribute
     */
    private $attributeTranslator;

    /**
     * Translate constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Aromicon\Deepl\Model\Translator\Catalog\Attribute $attributeTranslator
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Aromicon\Deepl\Model\Translator\Catalog\Attribute $attributeTranslator
    ) {
        $this->attributeTranslator = $attributeTranslator;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $attributeId = $this->getRequest()->getParam('attribute_id');
        $store = $this->getRequest()->getParam('store');

        try {
            $this->attributeTranslator->translateAndCopy($attributeId, $store);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Attribute couldn\'t be translated. %1', $e->getMessage()));
        }

        $this->_redirect('catalog/product_attribute/edit', ['attribute_id' => $attributeId]);
    }
}
