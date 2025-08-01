<?php

namespace UserAgentParser\Provider;

use BrowscapPHP\Browscap;
use DateTime;
use stdClass;
use UserAgentParser\Exception\InvalidArgumentException;
use UserAgentParser\Exception\NoResultFoundException;
use UserAgentParser\Model;

/**
 * Abstraction for all browscap types.
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @see https://github.com/browscap/browscap-php
 */
abstract class AbstractBrowscap extends AbstractProvider
{
    /**
     * Homepage of the provider.
     *
     * @var string
     */
    protected $homepage = 'https://github.com/browscap/browscap-php';

    /**
     * Composer package name.
     *
     * @var string
     */
    protected $packageName = 'browscap/browscap-php';

    protected $defaultValues = [
        'general' => [
            '/^unknown$/i',
        ],

        'browser' => [
            'name' => [
                '/^Default Browser$/i',
            ],
        ],

        'device' => [
            'model' => [
                '/^general/i',
                '/desktop$/i',
            ],
        ],

        'bot' => [
            'name' => [
                '/^General Crawlers/i',
                '/^Generic/i',
            ],
        ],
    ];

    /**
     * @var Browscap
     */
    private $parser;

    public function __construct(Browscap $parser, $expectedType = '')
    {
        $this->parser = $parser;

        if ($parser->getCache()->getType() === null) {
            throw new InvalidArgumentException('You need to warm-up the cache first to use this provider');
        }

        if ($expectedType !== $parser->getCache()->getType()) {
            throw new InvalidArgumentException('Expected the "' . $expectedType . '" data file. Instead got the "' . $parser->getCache()->getType() . '" data file');
        }
    }

    public function getVersion()
    {
        return $this->getParser()
            ->getCache()
            ->getVersion();
    }

    public function getUpdateDate(): ?\DateTimeInterface
    {
        $releaseDate = $this->getParser()
            ->getCache()
            ->getReleaseDate();

        return DateTime::createFromFormat('D, d M Y H:i:s O', $releaseDate);
    }

    public function getParser(): Browscap
    {
        return $this->parser;
    }

    public function parse($userAgent, array $headers = []): Model\UserAgent
    {
        $parser = $this->getParser();

        // @var $resultRaw \stdClass
        $resultRaw = $parser->getBrowser($userAgent);

        // No result found?
        if ($this->hasResult($resultRaw) !== true) {
            throw new NoResultFoundException('No result found for user agent: ' . $userAgent);
        }

        // Hydrate the model
        $result = new Model\UserAgent($this->getName(), $this->getVersion());
        $result->providerResultRaw = $resultRaw;

        // Bot detection (does only work with full_php_browscap.ini)
        if ($this->isBot($resultRaw) === true) {
            $this->hydrateBot($result->bot, $resultRaw);

            return $result;
        }

        // hydrate the result
        $this->hydrateBrowser($result->browser, $resultRaw);
        $this->hydrateRenderingEngine($result->renderingEngine, $resultRaw);
        $this->hydrateOperatingSystem($result->operatingSystem, $resultRaw);
        $this->hydrateDevice($result->device, $resultRaw);

        return $result;
    }

    /**
     * @return bool
     */
    private function hasResult(stdClass $resultRaw)
    {
        if (isset($resultRaw->browser) && $this->isRealResult($resultRaw->browser, 'browser', 'name') === true) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    private function isBot(stdClass $resultRaw)
    {
        if (isset($resultRaw->crawler) && $resultRaw->crawler === true) {
            return true;
        }

        return false;
    }

    private function hydrateBot(Model\Bot $bot, stdClass $resultRaw)
    {
        $bot->isBot = true;

        if (isset($resultRaw->browser)) {
            $bot->name = $this->getRealResult($resultRaw->browser, 'bot', 'name');
        }

        if (isset($resultRaw->issyndicationreader) && $resultRaw->issyndicationreader === true) {
            $bot->type = 'RSS';
        } elseif (isset($resultRaw->browser_type)) {
            $bot->type = $this->getRealResult($resultRaw->browser_type);
        }
    }

    private function hydrateBrowser(Model\Browser $browser, stdClass $resultRaw)
    {
        if (isset($resultRaw->browser)) {
            $browser->name = $this->getRealResult($resultRaw->browser, 'browser', 'name');
        }

        if (isset($resultRaw->version)) {
            $browser->version->complete = $this->getRealResult($resultRaw->version);
        }
    }

    private function hydrateRenderingEngine(Model\RenderingEngine $engine, stdClass $resultRaw)
    {
        if (isset($resultRaw->renderingengine_name)) {
            $engine->name = $this->getRealResult($resultRaw->renderingengine_name);
        }

        if (isset($resultRaw->renderingengine_version)) {
            $engine->version->complete = $this->getRealResult($resultRaw->renderingengine_version);
        }
    }

    private function hydrateOperatingSystem(Model\OperatingSystem $os, stdClass $resultRaw)
    {
        if (isset($resultRaw->platform)) {
            $os->name = $this->getRealResult($resultRaw->platform);
        }

        if (isset($resultRaw->platform_version)) {
            $os->version->complete = $this->getRealResult($resultRaw->platform_version);
        }
    }

    /**
     * @param Model\UserAgent $device
     */
    private function hydrateDevice(Model\Device $device, stdClass $resultRaw)
    {
        if (isset($resultRaw->device_name)) {
            $device->model = $this->getRealResult($resultRaw->device_name, 'device', 'model');
        }

        if (isset($resultRaw->device_brand_name)) {
            $device->brand = $this->getRealResult($resultRaw->device_brand_name);
        }

        if (isset($resultRaw->device_type)) {
            $device->type = $this->getRealResult($resultRaw->device_type);
        }

        if (isset($resultRaw->ismobiledevice) && $this->isRealResult($resultRaw->ismobiledevice) === true && $resultRaw->ismobiledevice === true) {
            $device->isMobile = true;
        }

        if (isset($resultRaw->device_pointing_method) && $resultRaw->device_pointing_method == 'touchscreen') {
            $device->isTouch = true;
        }
    }
}
