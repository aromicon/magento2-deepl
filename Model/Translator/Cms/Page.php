<?php
/**
 * @category  Aromicon
 * @package   Aromicon_
 * @author    Stefan Richter <richter@aromicon.de>
 * @copyright 2018 aromicon GmbH (http://www.aromicon.de)
 * @license   Commercial https://www.aromicon.de/magento-download-extensions-modules/de/license
 */
namespace Aromicon\Deepl\Model\Translator\Cms;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Framework\Exception\LocalizedException;

class Page
{
    /**
     * @var \Magento\Cms\Api\PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var \Magento\Cms\Api\Data\PageInterfaceFactory
     */
    private $pageInterfaceFactory;

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
    private $storeManagement;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    private $filterManager;

    /**
     * Page constructor.
     * @param \Magento\Cms\Api\PageRepositoryInterface $pageRepository
     * @param \Magento\Cms\Api\Data\PageInterfaceFactory $pageInterfaceFactory
     * @param \Aromicon\Deepl\Api\TranslatorInterface $translator
     * @param \Aromicon\Deepl\Helper\Config $config
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagement
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     */
    public function __construct(
        \Magento\Cms\Api\PageRepositoryInterface $pageRepository,
        \Magento\Cms\Api\Data\PageInterfaceFactory $pageInterfaceFactory,
        \Aromicon\Deepl\Api\TranslatorInterface $translator,
        \Aromicon\Deepl\Helper\Config $config,
        \Magento\Store\Model\StoreManagerInterface $storeManagement,
        \Magento\Framework\Filter\FilterManager $filterManager
    ) {
        $this->pageRepository = $pageRepository;
        $this->pageInterfaceFactory = $pageInterfaceFactory;
        $this->translator = $translator;
        $this->config = $config;
        $this->storeManagement = $storeManagement;
        $this->filterManager = $filterManager;
    }

    /**
     * @param $pageId int
     * @param $fromStoreId int
     * @param $toStoreId int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function translateAndCopy($pageId, $toStoreId)
    {
        $page = $this->pageRepository->getById($pageId);

        $sourceLanguage = $this->config->getSourceLanguage();
        $targetLanguage = $this->config->getLanguageCodeByStoreId($toStoreId);
        /** @var  \Magento\Cms\Api\Data\PageInterface $translatedPage */
        $translatedPage = $this->pageInterfaceFactory->create();

        $translatedPage->setData($page->getData())
            ->setPageId(null)
            ->setCreatedAt(null)
            ->setUpdatedAt(null)
            ->setStoreId($toStoreId);

        $pageFields = $this->config->getTranslatablePageFields();

        foreach ($pageFields as $field) {
            if ($page->getData($field) == '') {
                continue;
            }

            $translatedText = $this->translator->translate($page->getData($field), $sourceLanguage, $targetLanguage);

            if ($field == 'identifier') {
                $translatedText = $this->filterManager->translitUrl($translatedText);
            }

            $translatedPage->setData($field, $translatedText);
        }

        $this->removeStoreFromPage($page, $toStoreId);

        $this->pageRepository->save($page);
        $this->pageRepository->save($translatedPage);
    }

    /**
     * @param PageInterface $page
     * @param $storeId
     * @return array
     */
    protected function removeStoreFromPage($page, $storeId)
    {
        if (isset($page->getStoreId()[0]) && $page->getStoreId()[0] == 0) {
            $allStores = $this->storeManagement->getStores();
            foreach ($allStores as $store) {
                $allStoreIds[] = $store->getId();
            }
        } else {
            $allStoreIds = $page->getStoreId();
        }

        return $page->setStoreId(array_diff($allStoreIds, [$storeId]));
    }
}
