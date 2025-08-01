<?php

namespace UserAgentParser\Provider;

use UAParser\Parser;
use UserAgentParser\Exception\NoResultFoundException;
use UserAgentParser\Exception\PackageNotLoadedException;
use UserAgentParser\Model;

/**
 * Abstraction for ua-parser/uap-php.
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @see https://github.com/ua-parser/uap-php
 */
class UAParser extends AbstractProvider
{
    /**
     * Name of the provider.
     *
     * @var string
     */
    protected $name = 'UAParser';

    /**
     * Homepage of the provider.
     *
     * @var string
     */
    protected $homepage = 'https://github.com/ua-parser/uap-php';

    /**
     * Composer package name.
     *
     * @var string
     */
    protected $packageName = 'ua-parser/uap-php';

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
            'isBot' => true,
            'name' => true,
            'type' => false,
        ],
    ];

    protected $defaultValues = [
        'general' => [
            '/^Other$/i',
        ],

        'device' => [
            'brand' => [
                '/^Generic/i',
                '/^unknown$/i',
            ],

            'model' => [
                '/^generic$/i',
                '/^Smartphone$/i',
                '/^Feature Phone$/i',
                '/^iOS-Device$/i',
                '/^Tablet$/i',
                '/^Touch$/i',
                '/^Windows$/i',
                '/^Windows Phone$/i',
                '/^Android$/i',
            ],
        ],

        'bot' => [
            'name' => [
                '/^Other$/i',
                '/^crawler$/i',
                '/^robot$/i',
                '/^crawl$/i',
                '/^Spider$/i',
            ],
        ],
    ];

    private $parser;

    /**
     * @param Parser $parser
     *
     * @throws PackageNotLoadedException
     */
    public function __construct(Parser $parser = null)
    {
        if ($parser === null) {
            $this->checkIfInstalled();
        }

        $this->parser = $parser;
    }

    /**
     * @return Parser
     */
    public function getParser()
    {
        if ($this->parser !== null) {
            return $this->parser;
        }

        $this->parser = Parser::create();

        return $this->parser;
    }

    public function parse($userAgent, array $headers = [])
    {
        $parser = $this->getParser();

        // @var $resultRaw \UAParser\Result\Client
        $resultRaw = $parser->parse($userAgent);

        // No result found?
        if ($this->hasResult($resultRaw) !== true) {
            throw new NoResultFoundException('No result found for user agent: ' . $userAgent);
        }

        // Hydrate the model
        $result = new Model\UserAgent($this->getName(), $this->getVersion());
        $result->providerResultRaw = $resultRaw;

        // Bot detection
        if ($this->isBot($resultRaw) === true) {
            $this->hydrateBot($result->bot, $resultRaw);

            return $result;
        }

        // hydrate the result
        $this->hydrateBrowser($result->browser, $resultRaw->ua);
        // renderingEngine not available
        $this->hydrateOperatingSystem($result->operatingSystem, $resultRaw->os);
        $this->hydrateDevice($result->device, $resultRaw->device);

        return $result;
    }

    /**
     * @return bool
     */
    private function hasResult(\UAParser\Result\Client $resultRaw)
    {
        if ($this->isBot($resultRaw) === true) {
            return true;
        }

        if ($this->isRealResult($resultRaw->ua->family)) {
            return true;
        }

        if ($this->isRealResult($resultRaw->os->family)) {
            return true;
        }

        if ($this->isRealResult($resultRaw->device->model, 'device', 'model')) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    private function isBot(\UAParser\Result\Client $resultRaw)
    {
        if ($resultRaw->device->family === 'Spider') {
            return true;
        }

        return false;
    }

    private function hydrateBot(Model\Bot $bot, \UAParser\Result\Client $resultRaw)
    {
        $bot->isBot = true;
        $bot->name = $this->getRealResult($resultRaw->ua->family, 'bot', 'name');
    }

    private function hydrateBrowser(Model\Browser $browser, \UAParser\Result\UserAgent $uaRaw)
    {
        $browser->name = $this->getRealResult($uaRaw->family);

        $browser->version = $this->getRealResult($uaRaw->major);
        $browser->version = $this->getRealResult($uaRaw->minor);
        $browser->version = $this->getRealResult($uaRaw->patch);
    }

    private function hydrateOperatingSystem(Model\OperatingSystem $os, \UAParser\Result\OperatingSystem $osRaw)
    {
        $os->name = $this->getRealResult($osRaw->family);

        $os->version = $this->getRealResult($osRaw->major);
        $os->version = $this->getRealResult($osRaw->minor);
        $os->version = $this->getRealResult($osRaw->patch);
    }

    /**
     * @param Model\UserAgent $device
     */
    private function hydrateDevice(Model\Device $device, \UAParser\Result\Device $deviceRaw)
    {
        $device->model = $this->getRealResult($deviceRaw->model, 'device', 'model');
        $device->brand = $this->getRealResult($deviceRaw->brand, 'device', 'brand');
    }
}
