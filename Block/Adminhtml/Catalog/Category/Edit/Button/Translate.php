<?php
/**
 * @category  Aromicon
 * @package   Aromicon_Deepl
 * @author    Stefan Richter <richter@aromicon.de>
 * @copyright 2018 aromicon GmbH (http://www.aromicon.de)
 * @license   Commercial https://www.aromicon.de/magento-download-extensions-modules/de/license
 */
namespace Aromicon\Deepl\Block\Adminhtml\Catalog\Category\Edit\Button;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Ui\Component\Control\Container;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Catalog\Block\Adminhtml\Category\AbstractCategory;
/**
 * Class Translate
 */
class Translate extends AbstractCategory implements ButtonProviderInterface
{
    private $storeManagement;
    private $config;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Aromicon\Deepl\Helper\Config $config,
        \Magento\Store\Model\StoreManagerInterface $storeManagement,
        array $data = []
    ) {
        $this->config = $config;
        $this->storeManagement = $storeManagement;
        parent::__construct($context, $categoryTree, $registry, $categoryFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        if ($this->getCategory()->isReadonly() || !$this->config->hasApiKey()) {
            return [];
        }

        return [
            'label' => __('Translate'),
            'class' => 'save',
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
                'onclick' => sprintf("location.href = '%s';", $this->getUrl('aromicon_deepl/catalog_category/translate', [
                        'category_id' => $this->getCategory()->getId(),
                        'store' => $store->getId()
                    ]
                )),
            ];
        }

        return $options;
    }
}
