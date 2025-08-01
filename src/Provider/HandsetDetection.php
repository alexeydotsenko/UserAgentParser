<?php

namespace UserAgentParser\Provider;

use HandsetDetection as Parser;
use UserAgentParser\Exception\InvalidArgumentException;
use UserAgentParser\Exception\NoResultFoundException;
use UserAgentParser\Model;

/**
 * Abstraction for ua-parser/uap-php.
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @see https://github.com/HandsetDetection/php-apikit
 */
class HandsetDetection extends AbstractProvider
{
    /**
     * Name of the provider.
     *
     * @var string
     */
    protected $name = 'HandsetDetection';

    /**
     * Homepage of the provider.
     *
     * @var string
     */
    protected $homepage = 'https://github.com/HandsetDetection/php-apikit';

    /**
     * Composer package name.
     *
     * @var string
     */
    protected $packageName = 'handsetdetection/php-apikit';

    protected $detectionCapabilities = [
        'browser' => [
            'name' => true,
            'version' => true,
        ],

        'renderingEngine' => [
            'name' => false,
            'version' => false,
        ],

        'operatingSystem' => [
            'name' => true,
            'version' => true,
        ],

        'device' => [
            'model' => true,
            'brand' => true,
            'type' => false,
            'isMobile' => false,
            'isTouch' => false,
        ],

        'bot' => [
            'isBot' => false,
            'name' => false,
            'type' => false,
        ],
    ];

    protected $defaultValues = [
        'general' => [
            '/^generic$/i',
        ],

        'device' => [
            'model' => [
                '/analyzer/i',
                '/bot/i',
                '/crawler/i',
                '/library/i',
                '/spider/i',
            ],
        ],
    ];

    /**
     * @var Parser\HD4
     */
    private $parser;

    public function __construct(Parser\HD4 $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @return Parser\HD4
     */
    public function getParser()
    {
        return $this->parser;
    }

    public function parse($userAgent, array $headers = [])
    {
        $headers['User-Agent'] = $userAgent;

        $parser = $this->getParser();
        // $config = $parser->config;

        // $parser = new Parser\HD4($config);

        // No result found?
        $result = $parser->deviceDetect($headers);
        $resultRaw = $parser->getReply();

        if ($result !== true) {
            if (isset($resultRaw['status']) && $resultRaw['status'] == '299') {
                throw new InvalidArgumentException('You need to warm-up the cache first to use this provider');
            }

            throw new NoResultFoundException('No result found for user agent: ' . $userAgent);
        }

        // No result found?
        if (!isset($resultRaw['hd_specs']) || $this->hasResult($resultRaw['hd_specs']) !== true) {
            throw new NoResultFoundException('No result found for user agent: ' . $userAgent);
        }

        // Hydrate the model
        $result = new Model\UserAgent($this->getName(), $this->getVersion());
        $result->providerResultRaw = $resultRaw['hd_specs'];

        // hydrate the result
        $this->hydrateBrowser($result->browser, $resultRaw['hd_specs']);
        // renderingEngine not available
        $this->hydrateOperatingSystem($result->operatingSystem, $resultRaw['hd_specs']);
        $this->hydrateDevice($result->device, $resultRaw['hd_specs']);

        return $result;
    }

    /**
     * @return bool
     */
    private function hasResult(array $resultRaw)
    {
        if (isset($resultRaw['general_browser']) && $this->isRealResult($resultRaw['general_browser'])) {
            return true;
        }

        if (isset($resultRaw['general_platform']) && $this->isRealResult($resultRaw['general_platform'])) {
            return true;
        }

        if (isset($resultRaw['general_model']) && $this->isRealResult($resultRaw['general_model'], 'device', 'model') && $this->isRealResult($resultRaw['general_vendor'], 'device', 'brand')) {
            return true;
        }

        return false;
    }

    private function hydrateBrowser(Model\Browser $browser, array $resultRaw)
    {
        if (isset($resultRaw['general_browser'])) {
            $browser->name = $this->getRealResult($resultRaw['general_browser']);
        }
        if (isset($resultRaw['general_browser_version'])) {
            $browser->version->setComplete($this->getRealResult($resultRaw['general_browser_version']));
        }
    }

    private function hydrateOperatingSystem(Model\OperatingSystem $os, array $resultRaw)
    {
        if (isset($resultRaw['general_platform'])) {
            $os->name = $this->getRealResult($resultRaw['general_platform']);
        }
        if (isset($resultRaw['general_platform_version'])) {
            $os->version->setComplete($this->getRealResult($resultRaw['general_platform_version']));
        }
    }

    /**
     * @param Model\UserAgent $device
     */
    private function hydrateDevice(Model\Device $device, array $resultRaw)
    {
        if (isset($resultRaw['general_model']) && $this->isRealResult($resultRaw['general_model'], 'device', 'model') && isset($resultRaw['general_vendor']) && $this->isRealResult($resultRaw['general_vendor'], 'device', 'brand')) {
            $device->model = $this->getRealResult($resultRaw['general_model'], 'device', 'model');
            $device->brand = $this->getRealResult($resultRaw['general_vendor'], 'device', 'brand');
        }
    }
}
