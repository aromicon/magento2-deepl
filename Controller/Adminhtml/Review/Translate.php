<?php
/**
 * @category  Aromicon
 * @package   Aromicon_Deepl
 * @author    Stefan Richter <richter@aromicon.de>
 * @copyright 2018 aromicon GmbH (http://www.aromicon.de)
 * @license   Commercial https://www.aromicon.de/magento-download-extensions-modules/de/license
 */

namespace Aromicon\Deepl\Controller\Adminhtml\Review;

class Translate extends \Aromicon\Deepl\Controller\Adminhtml\Review
{

    /** @var \Aromicon\Deepl\Model\Translator\Review  */
    protected $reviewTranslator;

    /**
     * Translate constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Aromicon\Deepl\Model\Translator\Review $reviewTranslator
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Aromicon\Deepl\Model\Translator\Review $reviewTranslator
    ) {
        $this->reviewTranslator = $reviewTranslator;
        parent::__construct($context, $coreRegistry);
    }

    public function execute()
    {
        $reviewId = $this->getRequest()->getParam('review_id');
        $store = $this->getRequest()->getParam('store');

        try {
            $this->reviewTranslator->translateAndCopy($reviewId, $store);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Review couldn\'t be translated'));
        }

        $this->_redirect('review/product');
    }
}