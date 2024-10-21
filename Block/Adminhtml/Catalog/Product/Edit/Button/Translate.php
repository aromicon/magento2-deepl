<?php
/**
 * @category  Aromicon
 * @package   Aromicon_Deepl
 * @author    Stefan Richter <richter@aromicon.de>
 * @copyright 2018 aromicon GmbH (http://www.aromicon.de)
 * @license   Commercial https://www.aromicon.de/magento-download-extensions-modules/de/license
 */
namespace Aromicon\Deepl\Block\Adminhtml\Catalog\Product\Edit\Button;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Ui\Component\Control\Container;
use Magento\Framework\AuthorizationInterface;

/**
 * Class Save
 */
class Translate extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic
{
    private $authorization;
    private $storeManagement;
    private $config;

    public function __construct(
        Context $context,
        Registry $registry,
        \Aromicon\Deepl\Helper\Config $config,
        \Magento\Store\Model\StoreManagerInterface $storeManagement,
        AuthorizationInterface $authorization
    ) {
        $this->config = $config;
        $this->storeManagement = $storeManagement;
        $this->authorization = $authorization;
        parent::__construct($context, $registry);
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        if ($this->getProduct()->isReadonly()
            || !$this->config->hasApiKey()
            || !$this->authorization->isAllowed('Aromicon_Deepl::translate_product')) {
            return [];
        }

        return [
            'label' => __('Translate'),
            'class' => 'save options-scrollable',
            'class_name' => Container::SPLIT_BUTTON,
            'options' => $this->getOptions(),
        ];
    }

    /**
     * Retrieve options
     *
     * @return array
     */
    protected function getOptions()
    {
        $stores = $this->storeManagement->getStores();
        foreach ($stores as $store) {
            $options[] = [
                'label' => __($store->getName().' '.$this->config->getStoreLanguage($store)),
                'onclick' => sprintf("location.href = '%s';", $this->getUrl('aromicon_deepl/catalog_product/translate', [
                        'product_id' => $this->getProduct()->getId(),
                        'store' => $store->getId()
                    ]
                )),
            ];
        }

        return $options;
    }
}
