<?php
/**
 * @category  Aromicon
 * @package   Aromicon_Deepl
 * @author    Stefan Richter <richter@aromicon.de>
 * @copyright 2017 aromicon GmbH (http://www.aromicon.de)
 * @license   http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 */
namespace Aromicon\Deepl\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Locale\Bundle\LanguageBundle;
use Magento\Framework\Locale\Bundle\RegionBundle;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_DEFAULT_LOCALE = 'general/locale/code';
    const XML_PATH_DEFAULT_STORE = 'deepl/general/store';
    const XML_PATH_DEEPL_API_URL = 'deepl/api/url';
    const XML_PATH_DEEPL_API_KEY = 'deepl/api/key';
    const XML_PATH_DEEPL_CMS_PAGE_FIELDS = 'deepl/cms/page_fields';
    const XML_PATH_DEEPL_PRODUCT_FIELDS = 'deepl/product/product_fields';
    const XML_PATH_DEEPL_CATEGORY_FIELDS = 'deepl/category/category_fields';

    /**
     * @var \Aromicon\Deepl\Model\System\Config\PageFields
     */
    protected $pageFields;

    /**
     * @var \Aromicon\Deepl\Model\System\Config\ProductFields
     */
    protected $productFields;

    public function __construct(
        Context $context,
        \Aromicon\Deepl\Model\System\Config\PageFields $pageFields,
        \Aromicon\Deepl\Model\System\Config\ProductFields $productFields,
        \Aromicon\Deepl\Model\System\Config\CategoryFields $categoryFields
    ) {
        $this->pageFields = $pageFields;
        $this->productFields = $productFields;
        parent::__construct($context);
    }

    /**
     * Get Battery Deposit is included in Product Price
     * @return bool
     */
    public function getStoreLanguage($store = null)
    {
        $locale = $this->scopeConfig->getValue(
            self::XML_PATH_DEFAULT_LOCALE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );

        $currentLocale = 'en';
        $languages = (new LanguageBundle())->get($currentLocale)['Languages'];
        $countries = (new RegionBundle())->get($currentLocale)['Countries'];

        $language = \Locale::getPrimaryLanguage($locale);
        $country = \Locale::getRegion($locale);

        return  $label = $languages[$language];
    }

    public function getLanguageCodeByStoreId($storeId)
    {
        return mb_strtoupper(mb_substr($this->scopeConfig->getValue(
            self::XML_PATH_DEFAULT_LOCALE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        ), 0, 2));
    }

    public function getSourceLanguage()
    {
        $storeId = $this->scopeConfig->getValue(
            self::XML_PATH_DEFAULT_STORE
        );

        return $this->getLanguageCodeByStoreId($storeId);
    }

    public function getDeeplApiUrl()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DEEPL_API_URL
        );
    }

    public function getDeeplApiKey($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DEEPL_API_KEY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getTranslatablePageFields()
    {
        $fields = $this->scopeConfig->getValue(
            self::XML_PATH_DEEPL_CMS_PAGE_FIELDS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (empty($fields)) {
            $fields = [];
            foreach ($this->pageFields->toOptionArray() as $item) {
                $fields[] = $item['value'];
            };
        } else {
            $fields = explode(',', $fields);
        }

        return $fields;
    }

    public function getTranslatableProductFields()
    {
        $fields = $this->scopeConfig->getValue(
            self::XML_PATH_DEEPL_PRODUCT_FIELDS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (empty($fields)) {
            $fields = [];
            foreach ($this->pageFields->toOptionArray() as $item) {
                $fields[] = $item['value'];
            };
        } else {
            $fields = explode(',', $fields);
        }

        return $fields;
    }

    public function getTranslatableCategoryFields()
    {
        $fields = $this->scopeConfig->getValue(
            self::XML_PATH_DEEPL_CATEGORY_FIELDS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (empty($fields)) {
            $fields = [];
            foreach ($this->pageFields->toOptionArray() as $item) {
                $fields[] = $item['value'];
            };
        } else {
            $fields = explode(',', $fields);
        }

        return $fields;
    }
}
