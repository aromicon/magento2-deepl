<?php
/**
 * @category  Aromicon
 * @package   Aromicon_
 * @author    Stefan Richter <richter@aromicon.de>
 * @copyright 2018 aromicon GmbH (http://www.aromicon.de)
 * @license   Commercial https://www.aromicon.de/magento-download-extensions-modules/de/license
 */
namespace Aromicon\Deepl\Model\Translator\Cms;

use Magento\Framework\Exception\LocalizedException;

class Block
{
    /**
     * @var \Magento\Cms\Api\BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @var \Magento\Cms\Api\Data\BlockInterfaceFactory
     */
    private $blockInterfaceFactory;

    /**
     * @var \Aromicon\Deepl\Api\TranslatorInterface
     */
    private $translator;

    /**
     * @var \Aromicon\Deepl\Helper\Config
     */
    private $config;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * Block constructor.
     * @param \Magento\Cms\Api\BlockRepositoryInterface $blockRepository
     * @param \Magento\Cms\Api\Data\BlockInterfaceFactory $blockInterfaceFactory
     * @param \Aromicon\Deepl\Api\TranslatorInterface $translator
     * @param \Aromicon\Deepl\Helper\Config $config
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Cms\Api\BlockRepositoryInterface $blockRepository,
        \Magento\Cms\Api\Data\BlockInterfaceFactory $blockInterfaceFactory,
        \Aromicon\Deepl\Api\TranslatorInterface $translator,
        \Aromicon\Deepl\Helper\Config $config,
        \Magento\Store\Model\StoreManagerInterface $storeManagement
    ) {
        $this->blockRepository = $blockRepository;
        $this->blockInterfaceFactory = $blockInterfaceFactory;
        $this->translator = $translator;
        $this->config = $config;
        $this->storeManager = $storeManagement;
    }

    /**
     * @param $blockId int
     * @param $fromStoreId int
     * @param $toStoreId int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function translateAndCopy($blockId, $toStoreId)
    {
        /** @var \Magento\Cms\Model\Block $block */
        $block = $this->blockRepository->getById($blockId);

        $sourceLanguage = $this->config->getSourceLanguage();
        $targetLanguage = $this->config->getLanguageCodeByStoreId($toStoreId);
        /** @var  \Magento\Cms\Api\Data\BlockInterface $translatedBlock */
        $translatedBlock = $this->blockInterfaceFactory->create();
        $translatedTitle = $this->translator->translate($block->getTitle(), $sourceLanguage, $targetLanguage);
        $translatedContent = $this->translator->translate($block->getContent(), $sourceLanguage, $targetLanguage);

        $translatedBlock
            ->setTitle($translatedTitle)
            ->setContent($translatedContent)
            ->setIsActive($block->isActive())
            ->setIdentifier($block->getIdentifier())
            ->setStoreId($toStoreId);

        $this->removeStoreFromBlock($block, $toStoreId);

        $this->blockRepository->save($block);
        $this->blockRepository->save($translatedBlock);
    }

    /**
     * @param \Magento\Cms\Model\Block $block
     * @param $storeId
     */
    protected function removeStoreFromBlock($block, $storeId)
    {
        if (isset($block->getStoreId()[0]) && $block->getStoreId()[0] == 0) {
            $allStores = $this->storeManager->getStores();

            foreach ($allStores as $store) {
                $allStoreIds[] = $store->getId();
            }
        } else {
            $allStoreIds = $block->getStoreId();
        }

        $block->setStoreId(array_diff($allStoreIds, [$storeId]));
    }
}
