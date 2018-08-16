<?php
/**
 * @category  Aromicon
 * @package   Aromicon_Deepl
 * @author    Stefan Richter <richter@aromicon.de>
 * @copyright 2018 aromicon GmbH (http://www.aromicon.de)
 * @license   Commercial https://www.aromicon.de/magento-download-extensions-modules/de/license
 */

namespace Aromicon\Deepl\Controller\Adminhtml\Cms;

class Translate extends \Aromicon\Deepl\Controller\Adminhtml\Cms
{
    /**
     * @var \Aromicon\Deepl\Model\Translator\Cms\Block
     */
    private $cmsTranslator;

    /**
     * Translate constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Aromicon\Deepl\Model\Translator\Cms\Block $cmsTranslator
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Aromicon\Deepl\Model\Translator\Cms\Block $cmsTranslator
    ) {
        $this->cmsTranslator = $cmsTranslator;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $blockId = $this->getRequest()->getParam('block_id');
        $store = $this->getRequest()->getParam('store');

        try {
            $this->cmsTranslator->translateAndCopy($blockId, $store);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Block couldn\'t be translated'));
        }

        $this->_redirect('cms/block');
    }
}