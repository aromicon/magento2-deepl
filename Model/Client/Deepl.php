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
use Aromicon\Deepl\Helper\Config;
use Laminas\Http\Client;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filter\DirectiveProcessor\DependDirective;
use Magento\Framework\Filter\DirectiveProcessor\IfDirective;
use Magento\Framework\Filter\DirectiveProcessor\LegacyDirective;
use Magento\Framework\Filter\DirectiveProcessor\TemplateDirective;
use Magento\Framework\Filter\DirectiveProcessorInterface;

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
        'ZH-HANS',
        'ZH-HANT'
    ];


    private Config $config;
    private Client $client;
    private array $usage;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    private array $directives = [];
    protected array $directiveProcessors;

    public function __construct(
        Config $config,
        Client $client,
        \Psr\Log\LoggerInterface $logger,
        array $directiveProcessors = [],
    ) {
        $this->config = $config;
        $this->client = $client;
        $this->logger = $logger;

        if (empty($directiveProcessors)) {
            $directiveProcessors = [
                'depend' => ObjectManager::getInstance()->get(DependDirective::class),
                'if' => ObjectManager::getInstance()->get(IfDirective::class),
                'template' => ObjectManager::getInstance()->get(TemplateDirective::class),
                'legacy' => ObjectManager::getInstance()->get(LegacyDirective::class),
            ];
        }

        $this->directiveProcessors = $directiveProcessors;
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

        $text = $this->replaceDirectives($string);

        $post = $request->getPost();

        $post->set('auth_key', $this->config->getDeeplApiKey())
            ->set('text', $text)
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

        return $this->replacePlaceholders($translatedText);
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

    private function replaceDirectives($value)
    {
        foreach ($this->directiveProcessors as $directiveProcessor) {
            if (!$directiveProcessor instanceof DirectiveProcessorInterface) {
                throw new \InvalidArgumentException(
                    'Directive processors must implement ' . DirectiveProcessorInterface::class
                );
            }

            $pattern = $directiveProcessor->getRegularExpression();

            if (preg_match_all($pattern, $value, $constructions, PREG_SET_ORDER)) {
                foreach ($constructions as $construction) {
                    if (!empty($construction[0])) {
                        $replaceName = '#'.uniqid().'#';
                        $this->directives[$replaceName] = $construction[0];
                        $value = str_replace($construction[0], $replaceName, $value);
                    }
                }
            }
        }

        return $value;
    }

    private function replacePlaceholders($translatedString)
    {
        foreach ($this->directives as $replaceName => $directive) {
            $translatedString = str_replace($replaceName, $directive, $translatedString);
        }

        return $translatedString;
    }
}
