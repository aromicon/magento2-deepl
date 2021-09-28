<?php
/**
 * @category  Aromicon
 * @package   Aromicon_
 * @author    Stefan Richter <richter@aromicon.de>
 * @copyright 2018 aromicon GmbH (http://www.aromicon.de)
 * @license   Commercial https://www.aromicon.de/magento-download-extensions-modules/de/license
 */
namespace Aromicon\Deepl\Model\Translator\Catalog;

class Attribute
{
    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var \Aromicon\Deepl\Api\TranslatorInterface
     */
    private $translator;

    /**
     * @var \Aromicon\Deepl\Helper\Config
     */
    private $config;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    private $productResource;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Attribute
     */
    private $attributeResource;

    public function __construct(
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Aromicon\Deepl\Api\TranslatorInterface $translator,
        \Aromicon\Deepl\Helper\Config $config,
        \Magento\Catalog\Model\ResourceModel\Attribute $attributeResource
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->translator = $translator;
        $this->config = $config;
        $this->attributeResource = $attributeResource;
    }

    /**
     * @param $attributeId int
     * @param $fromStoreId int
     * @param $toStoreId int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function translateAndCopy($attributeId, $toStoreId)
    {
        /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
        $attribute = $this->attributeRepository->get(\Magento\Catalog\Model\Product::ENTITY, $attributeId);

        $sourceLanguage = $this->config->getSourceLanguage($toStoreId);
        $targetLanguage = $this->config->getLanguageCodeByStoreId($toStoreId);

        $labels = $attribute->getStoreLabels();
        $srcLabel = $attribute->getDefaultFrontendLabel();
        $labels[$toStoreId] = $this->translator->translate($srcLabel, $sourceLanguage, $targetLanguage);
        $attribute->setStoreLabels($labels);

        if (in_array($attribute->getFrontendInput(), ['select','multiselect'])
            && in_array(get_class($attribute->getSource()), [\Magento\Eav\Model\Entity\Attribute\Source\Table::class, null])) {
            $options = $attribute->getOptions();

            if (!empty($options)) {
                foreach ($options as $option) {
                    if ($option->getValue() == '') {
                        continue;
                    }

                    $srcOptionLabel = $option->getLabel();
                    $translatedOptionLabel = $this->translator
                        ->translate($srcOptionLabel, $sourceLanguage, $targetLanguage);
                    $this->saveAttributeOption($option->getValue(), $translatedOptionLabel, $toStoreId);
                }
            }
        }

        $this->attributeRepository->save($attribute);
    }

    /**
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @param \Magento\Eav\Api\Data\AttributeOptionInterface $option
     */
    protected function saveAttributeOption($optionId, $value, $storeId)
    {
        $connection = $this->attributeResource->getConnection();
        $table = $this->attributeResource->getTable('eav_attribute_option_value');

        $connection->delete($table, ['option_id = ?' => $optionId, 'store_id = ?' => $storeId]);

        $optionValue = [
            'option_id' => $optionId,
            'store_id' => $storeId,
            'value' => $value
        ];

        $connection->insert($table, $optionValue);
    }
}
