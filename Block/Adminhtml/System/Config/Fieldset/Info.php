<?php
/**
 * @category  Aromicon
 * @package   Aromicon_Deepl
 * @author    Stefan Richter <richter@aromicon.de>
 * @copyright 2018 aromicon GmbH (http://www.aromicon.de)
 * @license   Commercial https://www.aromicon.de/magento-download-extensions-modules/de/license
 */
namespace Aromicon\Deepl\Block\Adminhtml\System\Config\Fieldset;

use Magento\Config\Block\System\Config\Form\Fieldset;

/**
 * Fieldset renderer with url attached to comment.
 */
class Info extends Fieldset
{
    private $config;
    private $client;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \Aromicon\Deepl\Helper\Config $config,
        \Aromicon\Deepl\Model\Client\Deepl $client,
        array $data = []
    ) {
        $this->config = $config;
        $this->client = $client;
        parent::__construct($context, $authSession, $jsHelper, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _getHeaderCommentHtml($element)
    {

        if ($this->config->getDeeplApiKey()) {
            $comment = '
            <a href="https://www.deepl.com/?ref=aromicon" target="_blank" style="width: 100%; display:block; text-align: center;">
            <img src="'.$this->getViewFileUrl('Aromicon_Deepl::images/deepl_banner_api.jpg').'" alt="Deepl Pro Banner." title="Deepl Pro Banner"/>
</a>
        ';
            if ($this->client->isValid()) {
                $comment .= __('<p style="margin-left: 33%; margin-top: 20px"><strong>Character Count:</strong> %1</p>', $this->client->getCharacterCount());
                $comment .= __('<p style="margin-left: 33%"><strong>Character Limit:</strong> %1</p>', $this->client->getCharacterLimit());
            } else {
                $comment .= __('<p style="margin-left: 33%; margin-top: 20px; color: red"><strong>Authorization failed. Please check your API Key.</strong></p>');
            }
            $element->setComment($comment);
        } else {
            $comment = '
            <a href="https://www.deepl.com/pro-registration.html?ref=aromicon" target="_blank" style="margin-bottom: 20px;width: 100%; display:block; text-align: center;">
            <img src="'.$this->getViewFileUrl('Aromicon_Deepl::images/deepl_banner.jpg').'" alt="Deepl Pro Banner." title="Deepl Pro Banner"/>
</a>
        ';
            $comment .= __('<p style="margin-left: 33%; margin-top: 20px"><strong>No API Key provided</strong></p>');
            $element->setComment($comment);
        }

        return parent::_getHeaderCommentHtml($element);
    }
}
