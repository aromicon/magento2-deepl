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
    const XML_PATH_DEEPL_LOG_ENABLE = 'deepl/log/enable_log';

    /**
     * @var \Aromicon\Deepl\Model\System\Config\PageFields
     */
    private $pageFields;

    /**
     * @var \Aromicon\Deepl\Model\System\Config\ProductFields
     */
    private $productFields;

    /**
     * @var \Aromicon\Deepl\Model\System\Config\ProductFields
     */
    private $categoryFields;

    public function __construct(
        Context $context,
        \Aromicon\Deepl\Model\System\Config\PageFields $pageFields,
        \Aromicon\Deepl\Model\System\Config\ProductFields $productFields,
        \Aromicon\Deepl\Model\System\Config\CategoryFields $categoryFields
    ) {
        $this->pageFields = $pageFields;
        $this->productFields = $productFields;
        $this->categoryFields = $categoryFields;
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

        $language = \Locale::getPrimaryLanguage($locale);

        return  $label = $languages[$language];
    }

    /**
     * @param $storeId
     * @return mixed|null|string|string[]
     */
    public function getLanguageCodeByStoreId($storeId)
    {
        return mb_strtoupper(mb_substr($this->scopeConfig->getValue(
            self::XML_PATH_DEFAULT_LOCALE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        ), 0, 2));
    }

    /**
     * @return mixed|null|string|string[]
     */
    public function getSourceStoreId()
    {
        $storeId = $this->scopeConfig->getValue(
            self::XML_PATH_DEFAULT_STORE
        );

        return $storeId;
    }

    /**
     * @return mixed|null|string|string[]
     */
    public function getSourceLanguage()
    {
        $storeId = $this->scopeConfig->getValue(
            self::XML_PATH_DEFAULT_STORE
        );

        return $this->getLanguageCodeByStoreId($storeId);
    }

    /**
     * @return mixed
     */
    public function getDeeplApiUrl()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DEEPL_API_URL
        );
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function hasApiKey($storeId = null)
    {
        return !empty($this->getDeeplApiKey($storeId));
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getDeeplApiKey($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DEEPL_API_KEY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return array|mixed
     */
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

    /**
     * @return array|mixed
     */
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

    /**
     * @return array|mixed
     */
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

    /**
     * @return mixed
     */
    public function isLogEnabled()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DEEPL_LOG_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
