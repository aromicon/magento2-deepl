<?php
/**
 * @category  Aromicon
 * @package   Aromicon_Deepl
 * @author    Stefan Richter <richter@aromicon.de>
 * @copyright 2018 aromicon GmbH (http://www.aromicon.de)
 * @license   Commercial https://www.aromicon.de/magento-download-extensions-modules/de/license
 */
namespace Aromicon\Deepl\Model\System\Config;

class PageFields implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'title', 'label' => __('Page Title')],
            ['value' => 'content', 'label' => __('Content')],
            ['value' => 'content_heading', 'label' => __('Content Heading')],
            ['value' => 'meta_keywords', 'label' => __('Meta Keywords')],
            ['value' => 'meta_description', 'label' => __('Meta Description')],
            ['value' => 'meta_title', 'label' => __('Meta Title')],
            ['value' => 'identifier', 'label' => __('URL Key')],
        ];
    }
}