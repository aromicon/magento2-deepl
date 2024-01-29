<?php
/**
 * @category  Aromicon
 * @package   Aromicon_
 * @author    Stefan Richter <richter@aromicon.de>
 * @copyright 2018 aromicon GmbH (http://www.aromicon.de)
 * @license   Commercial https://www.aromicon.de/magento-download-extensions-modules/de/license
 */
namespace Aromicon\Deepl\Model\Translator\Catalog;

use Magento\Eav\Api\Data\AttributeOptionInterface;

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
     * @var \Magento\Catalog\Model\ResourceModel\Attribute
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

        $sourceStoreId = $this->config->getSourceStoreId($toStoreId);
        $sourceLanguage = $this->config->getSourceLanguage($toStoreId);
        $targetLanguage = $this->config->getLanguageCodeByStoreId($toStoreId, true);

        $labels = $attribute->getStoreLabels();
        $srcLabel = array_key_exists($sourceStoreId, $labels)
            ? $labels[$sourceStoreId]
            : $attribute->getDefaultFrontendLabel();
        $labels[$toStoreId] = $this->translator->translate($srcLabel, $sourceLanguage, $targetLanguage);

        $attribute->setStoreLabels($labels);
        $this->attributeRepository->save($attribute);

        $attribute->setStoreId($sourceStoreId);

        if (in_array($attribute->getFrontendInput(), ['select', 'multiselect'])
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

                    $this->saveAttributeOption($option, $translatedOptionLabel, $toStoreId);
                }
            }
        }
    }

    /**
     * @param AttributeOptionInterface $option
     * @param string $value
     * @param int|string $storeId
     */
    protected function saveAttributeOption($option, $value, $storeId)
    {
        $optionId = $option->getValue();
        $connection = $this->attributeResource->getConnection();
        $table = $this->attributeResource->getTable('eav_attribute_option_value');

        $select = $connection->select()
                    ->from($table)
                    ->where('option_id = ?', $optionId)
                    ->where('store_id = ?', $storeId);

        $result = $connection->fetchOne($select);

        if ($result) {
            $connection->update($table, ['value' => $value], ['option_id = ?' => $optionId, 'store_id = ?' => $storeId]);
        } else {
            $optionValue = [
                'option_id' => $optionId,
                'store_id' => $storeId,
                'value' => $value
            ];

            $connection->insert($table, $optionValue);
        }
    }
}
