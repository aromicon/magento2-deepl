<?php
/**
 * @category  Aromicon
 * @package   Aromicon_Deepl
 * @author    Stefan Richter <richter@aromicon.de>
 * @copyright 2018 aromicon GmbH (http://www.aromicon.de)
 * @license   Commercial https://www.aromicon.de/magento-download-extensions-modules/de/license
 */

namespace Aromicon\Deepl\Controller\Adminhtml\Catalog\Product;

use Magento\Framework\Exception\LocalizedException;

class Translate extends \Aromicon\Deepl\Controller\Adminhtml\Catalog
{
    const ADMIN_RESOURCE = 'Aromicon_Deepl::translate_product';
    /**
     * @var \Aromicon\Deepl\Model\Translator\Catalog\Product
     */
    private $productTranslator;

    /**
     * Translate constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Aromicon\Deepl\Model\Translator\Catalog\Product $productTranslator
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Aromicon\Deepl\Model\Translator\Catalog\Product $productTranslator
    ) {
        $this->productTranslator = $productTranslator;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $store = $this->getRequest()->getParam('store');

        try {
            $this->productTranslator->translateAndCopy($productId, $store);
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Product couldn\'t be translated. %1', $e->getMessage()));
        }

        $this->_redirect('catalog/product/edit', ['id' => $productId]);
    }
}
