<?php
/**
 * @category  Aromicon
 * @package   Aromicon_Deepl
 * @author    Stefan Richter <richter@aromicon.de>
 * @copyright 2018 aromicon GmbH (http://www.aromicon.de)
 * @license   Commercial https://www.aromicon.de/magento-download-extensions-modules/de/license
 */

namespace Aromicon\Deepl\Model\Client;

use Aromicon\Deepl\Api\TranslatorInterface;
use Laminas\Http\Client;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Magento\Framework\Exception\LocalizedException;

class Deepl implements TranslatorInterface
{
    const AVAILABLE_LANGUAGES = [
        'AR',
        'BG',
        'CS',
        'DA',
        'DE',
        'EL',
        'EN-GB',
        'EN-US',
        'EN',
        'ES',
        'ET',
        'FI',
        'FR',
        'HU',
        'ID',
        'IT',
        'JA',
        'KO',
        'LT',
        'LV',
        'NB',
        'NL',
        'PL',
        'PT-PT',
        'PT-BR',
        'PT',
        'RO',
        'RU',
        'SK',
        'SL',
        'SV',
        'TR',
        'UK',
        'ZH',
        'ZH-HANT',
    ];
    /**
     * @var \Aromicon\Deepl\Helper\Config
     */
    private $config;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var int
     */
    private $usage;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        \Aromicon\Deepl\Helper\Config $config,
        Client $client,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * Check if API ist Working
     * @return bool
     * @throws LocalizedException
     */
    public function isValid()
    {
        return $this->getCharacterCount() !== false;
    }

    /**
     * Translate String from source language to target language
     * @param $string
     * @param $sourceLanguage
     * @param $targetLanguage
     * @return mixed
     * @throws LocalizedException
     */
    public function translate($string, $sourceLanguage, $targetLanguage)
    {
        $client = $this->getClient();
        $request = $client->getRequest()
            ->setUri($this->config->getDeeplApiUrl() . 'translate')
            ->setMethod(\Laminas\Http\Request::METHOD_POST);

        if (!in_array($targetLanguage, self::AVAILABLE_LANGUAGES)) {
            throw new LocalizedException(__('Target Language is not available!'));
        }

        $post = $request->getPost();
        $post->set('auth_key', $this->config->getDeeplApiKey())
            ->set('text', $string)
            ->set('source_lang', $sourceLanguage)
            ->set('target_lang', $targetLanguage)
            ->set('tag_handling', $this->config->getTagHandling())
            ->set('preserve_formatting', 1)
            ->set('split_sentences', $this->config->getSplitSentences());

        if (in_array($targetLanguage, array('DE', 'FR', 'IT', 'ES', 'NL', 'PL', 'PT-PT', 'PT-BR', 'RU'))) {
            $post->set('formality', $this->config->getFormality());
        }

        $request->setPost($post);
        $result = $client->send($request);

        if ($this->config->isLogEnabled()) {
            $this->logger->info('Deepl Request: ', [$post]);
            $this->logger->info('Deepl Response: ', [$result]);
        }

        if ($this->_hasError($result)) {
            $this->_handleError($result);
        }

        $translate = json_decode($result->getBody(), true);

        if (!isset($translate['translations'][0]['text'])) {
            throw new LocalizedException(__('Translation is empty.'));
        }

        $translatedText = str_replace(['{{{', '}}}'], ['{{', '}}'], $translate['translations'][0]['text']);

        return $translatedText;
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    public function getUsage()
    {
        $client = $this->getClient();
        $request = $client->getRequest()
            ->setUri($this->config->getDeeplApiUrl() . 'usage')
            ->setMethod(Request::METHOD_GET);

        $query = $request->getQuery();
        $query->set('auth_key', $this->config->getDeeplApiKey());

        $request->setQuery($query);
        $result = $client->send($request);

        if ($this->_hasError($result)) {
            $this->_handleError($result);
        }

        $usage = json_decode($result->getBody(), true);

        if (!isset($usage['character_count'])) {
            throw new LocalizedException(__('Usage is empty.'));
        }

        return $this->usage = $usage;
    }

    /**
     * @return int|false
     * @throws LocalizedException
     */
    public function getCharacterLimit()
    {
        if (!$this->usage) {
            try {
                $this->getUsage();
            } catch (LocalizedException $e) {
                $this->logger->error($e->getMessage());
                return false;
            }
        }

        if (!isset($this->usage['character_limit'])) {
            return 0;
        }

        return $this->usage['character_limit'];
    }

    /**
     * @return int|false
     * @throws LocalizedException
     */
    public function getCharacterCount()
    {
        if (!$this->usage) {
            try {
                $this->getUsage();
            } catch (LocalizedException $e) {
                $this->logger->error($e->getMessage());
                return false;
            }
        }

        if (!isset($this->usage['character_count'])) {
            return 0;
        }

        return $this->usage['character_count'];
    }

    /**
     * @param Response $response
     */
    protected function _hasError($response)
    {
        return $response->getStatusCode() != 200;
    }

    /**
     * @param Response $response
     * @throws LocalizedException
     */
    protected function _handleError($response)
    {
        $status = $response->getStatusCode();
        if ($status == 400) {
            throw new LocalizedException(__('Wrong request, please check error message and your parameters.'));
        } elseif ($status == 403) {
            throw new LocalizedException(__('Authorization failed. Please supply a valid auth_key parameter.'));
        } elseif ($status == 413) {
            throw new LocalizedException(__('Request Entity Too Large. The request size exceeds the current limit.'));
        } elseif ($status == 429) {
            throw new LocalizedException(__('Too many requests. Please wait and send your request once again.'));
        } elseif ($status == 456) {
            throw new LocalizedException(__('Quota exceeded. The character limit has been reached.'));
        } else {
            throw new LocalizedException(__('Status %1. %2.', $status, $response->getReasonPhrase()));
        }
    }

    /**
     * @return Client
     */
    private function getClient(): Client
    {
        return $this->client->setOptions(
            [
                'timeout' => $this->config->getTimeout()
            ]
        );
    }
}
