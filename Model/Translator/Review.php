<?php
/**
 * @category  Aromicon
 * @package   Aromicon_
 * @author    Stefan Richter <richter@aromicon.de>
 * @copyright 2018 aromicon GmbH (http://www.aromicon.de)
 * @license   Commercial https://www.aromicon.de/magento-download-extensions-modules/de/license
 */
namespace Aromicon\Deepl\Model\Translator;

use Magento\Framework\Exception\LocalizedException;

class Review
{
    /**
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    protected $reviewCollectionFactory;

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @var \Aromicon\Deepl\Api\TranslatorInterface
     */
    protected $translator;

    /**
     * @var \Aromicon\Deepl\Helper\Config
     */
    protected $config;

    /**
     * Review constructor.
     * @param \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Aromicon\Deepl\Api\TranslatorInterface $translator
     * @param \Aromicon\Deepl\Helper\Config $config
     */
    public function __construct(
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Aromicon\Deepl\Api\TranslatorInterface $translator,
        \Aromicon\Deepl\Helper\Config $config
    ) {
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->reviewFactory = $reviewFactory;
        $this->translator = $translator;
        $this->config = $config;
    }

    /**
     * @param $reviewId int
     * @param $fromStoreId int
     * @param $toStoreId int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function translateAndCopy($reviewId, $toStoreId)
    {
        /** @var \Magento\Review\Model\Review $review */
        $review = $this->reviewFactory->create()->load($reviewId);

        if ($this->checkForDuplicate($review, $toStoreId)) {
            throw new LocalizedException(__('Duplicate found for Review ID %1', $reviewId));
        }

        $sourceLanguage = $this->config->getSourceLanguage();
        $targetLanguage = $this->config->getLanguageCodeByStoreId($toStoreId);

        $translatedTitle = $this->translator->translate($review->getTitle(), $sourceLanguage, $targetLanguage);
        $translatedDetail = $this->translator->translate($review->getDetail(), $sourceLanguage, $targetLanguage);

        $translatedReview = clone $review;

        $translatedReview->setId(null)->setDetailId(null)
            ->setStores([$toStoreId])
            ->setStoreId($toStoreId)
            ->setTitle($translatedTitle)
            ->setDetail($translatedDetail);

        $translatedReview->save();
        //Bug Fixing Wrong Date at Model Save
        $translatedReview->setCreatedAt($review->getCreatedAt())->save();
    }

    /**
     * @param \Magento\Review\Model\Review $review
     * @param int $toStoreId
     * @return bool
     */
    public function checkForDuplicate($review, $toStoreId)
    {
        /** @var \Magento\Review\Model\ResourceModel\Review\Collection $collection */
        $collection = $this->reviewCollectionFactory->create();
        $collection->addEntityFilter($review->getEntityId(), $review->getEntityPkValue())
            ->addStoreFilter($toStoreId)
            ->addStoreData()
            ->addFieldToFilter('nickname', $review->getNickname());

        return !empty($collection->getAllIds());
    }
}
