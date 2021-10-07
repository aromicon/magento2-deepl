<?php
/**
 * @category  Aromicon
 * @package   Aromicon_
 * @author    Stefan Richter <richter@aromicon.de>
 * @copyright 2018 aromicon GmbH (http://www.aromicon.de)
 * @license   Commercial https://www.aromicon.de/magento-download-extensions-modules/de/license
 */

namespace Aromicon\Deepl\Model\Translator\Catalog;

use Aromicon\Deepl\Api\TranslatorInterface;
use Aromicon\Deepl\Helper\Config;
use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\Exception\LocalizedException;

class Product
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var Config
     */
    private $config;

    private $productResource;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        TranslatorInterface $translator,
        Config $config,
        ProductResource $productResource
    ) {
        $this->productRepository = $productRepository;
        $this->translator = $translator;
        $this->config = $config;
        $this->productResource = $productResource;
    }

    /**
     * @param $productId int
     * @param $fromStoreId int
     * @param $toStoreId int
     * @throws LocalizedException
     * @throws Exception
     */
    public function translateAndCopy($productId, $toStoreId)
    {
        $sourceProduct = $this->productRepository->getById($productId, true, $this->config->getSourceStoreId());
        $product = $this->productRepository->getById($productId, true, $toStoreId);

        $sourceLanguage = $this->config->getSourceLanguage();
        $targetLanguage = $this->config->getLanguageCodeByStoreId($toStoreId);

        $pageFields = $this->config->getTranslatableProductFields();

        foreach ($pageFields as $field) {
            if ($sourceProduct->getData($field) == '') {
                continue;
            }

            $translatedText = $this->translator
                ->translate($sourceProduct->getData($field), $sourceLanguage, $targetLanguage);

            if ($product->getData($field) == $translatedText) {
                continue;
            }

            $product->setData($field, $translatedText);
            $this->saveAttribute($product, $field);
        }
    }

    /**
     * @throws Exception
     */
    public function saveAttribute(ProductInterface $product, string $field): ProductResource
    {
        return $this->productResource->saveAttribute($product, $field);
    }
}
