<?php
/**
 * @category  Aromicon
 * @package   Aromicon_
 * @author    Stefan Richter <richter@aromicon.de>
 * @copyright 2018 aromicon GmbH (http://www.aromicon.de)
 * @license   Commercial https://www.aromicon.de/magento-download-extensions-modules/de/license
 */

namespace Aromicon\Deepl\Block\Adminhtml\Review\Grid;

class Massaction extends \Magento\Backend\Block\Widget\Container
{
    public function _prepareLayout()
    {
        $reviewGrid = $this->getParentBlock();
        \Zend_Debug::dump($reviewGrid->getNameInLayout());
        return parent::_prepareLayout();
    }
}