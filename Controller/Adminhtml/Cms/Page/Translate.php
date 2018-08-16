<?php
/**
 * @category  Aromicon
 * @package   Aromicon_Deepl
 * @author    Stefan Richter <richter@aromicon.de>
 * @copyright 2018 aromicon GmbH (http://www.aromicon.de)
 * @license   Commercial https://www.aromicon.de/magento-download-extensions-modules/de/license
 */

namespace Aromicon\Deepl\Controller\Adminhtml\Cms\Page;

use Magento\Framework\Exception\LocalizedException;

class Translate extends \Aromicon\Deepl\Controller\Adminhtml\Cms
{

    /**
     * @var \Aromicon\Deepl\Model\Translator\Cms\Page
     */
    protected $cmsTranslator;

    /**
     * Translate constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Aromicon\Deepl\Model\Translator\Cms\Page $cmsTranslator
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Aromicon\Deepl\Model\Translator\Cms\Page $cmsTranslator
    ) {
        $this->cmsTranslator = $cmsTranslator;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $pageId = $this->getRequest()->getParam('page_id');
        $store = $this->getRequest()->getParam('store');

        try {
            $this->cmsTranslator->translateAndCopy($pageId, $store);
            $this->messageManager->addSuccess(__('Page "%1" was copied & translated to Store %2.', $pageId, $store));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(__('Page couldn\'t be translated. %1', $e->getMessage()));
        }

        $this->_redirect('cms/page');
    }
}