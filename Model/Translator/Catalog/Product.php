<?php
/**
 * @category  Aromicon
 * @package   Aromicon_
 * @author    Stefan Richter <richter@aromicon.de>
 * @copyright 2018 aromicon GmbH (http://www.aromicon.de)
 * @license   Commercial https://www.aromicon.de/magento-download-extensions-modules/de/license
 */
namespace Aromicon\Deepl\Model\Translator\Catalog;

class Product
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Aromicon\Deepl\Api\TranslatorInterface
     */
    private $translator;

    /**
     * @var \Aromicon\Deepl\Helper\Config
     */
    private $config;

    private $productResource;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Aromicon\Deepl\Api\TranslatorInterface $translator,
        \Aromicon\Deepl\Helper\Config $config,
        \Magento\Catalog\Model\ResourceModel\Product $productResource
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
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function translateAndCopy($productId, $toStoreId)
    {
        $sourceProduct = $this->productRepository->getById($productId, true, $this->config->getSourceStoreId($toStoreId));
        $product = $this->productRepository->getById($productId, true, $toStoreId);

        $sourceLanguage = $this->config->getSourceLanguage($toStoreId);
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
            $this->productResource->saveAttribute($product, $field);
        }
    }
}
