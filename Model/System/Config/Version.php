<?php
/**
 * @category  Aromicon
 * @package   Aromicon_Deepl
 * @author    Stefan Richter <richter@aromicon.de>
 * @copyright 2018 aromicon GmbH (http://www.aromicon.de)
 * @license   Commercial https://www.aromicon.de/magento-download-extensions-modules/de/license
 */
namespace Aromicon\Deepl\Model\System\Config;

class Version implements \Magento\Framework\Option\ArrayInterface
{
    const FREE = 'free';

    const PRO = 'pro';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::FREE, 'label' => __('Free')],
            ['value' => self::PRO, 'label' => __('Pro')],
        ];
    }
}
