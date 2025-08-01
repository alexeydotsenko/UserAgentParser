<?php

namespace UserAgentParser\Provider;

use Jenssegers\Agent\Agent;
use UserAgentParser\Exception\NoResultFoundException;
use UserAgentParser\Exception\PackageNotLoadedException;
use UserAgentParser\Model;

/**
 * Abstraction for jenssegers/agent.
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @see https://github.com/jenssegers/agent
 */
class JenssegersAgent extends AbstractProvider
{
    /**
     * Name of the provider.
     *
     * @var string
     */
    protected $name = 'JenssegersAgent';

    /**
     * Homepage of the provider.
     *
     * @var string
     */
    protected $homepage = 'https://github.com/jenssegers/agent';

    /**
     * Composer package name.
     *
     * @var string
     */
    protected $packageName = 'jenssegers/agent';

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
            'model' => false,
            'brand' => false,
            'type' => false,
            'isMobile' => true,
            'isTouch' => false,
        ],

        'bot' => [
            'isBot' => true,
            'name' => true,
            'type' => false,
        ],
    ];

    protected $defaultValues = [
        'general' => [],

        'browser' => [
            'name' => [
                '/^GenericBrowser$/i',
            ],
        ],
    ];

    /**
     * Used for unitTests mocking.
     *
     * @var Agent
     */
    private $parser;

    /**
     * @throws PackageNotLoadedException
     */
    public function __construct()
    {
        $this->checkIfInstalled();
    }

    /**
     * @return Agent
     */
    public function getParser()
    {
        if ($this->parser !== null) {
            return $this->parser;
        }

        return new Agent();
    }

    public function parse($userAgent, array $headers = [])
    {
        $parser = $this->getParser();
        $parser->setHttpHeaders($headers);
        $parser->setUserAgent($userAgent);

        /*
         * Since Mobile_Detect to a regex comparison on every call
         * We cache it here for all checks and hydration
         */
        $browserName = $parser->browser();
        $osName = $parser->platform();

        $resultCache = [
            'browserName' => $browserName,
            'browserVersion' => $parser->version($browserName),

            'osName' => $osName,
            'osVersion' => $parser->version($osName),

            'deviceModel' => $parser->device(),
            'isMobile' => $parser->isMobile(),

            'isRobot' => $parser->isRobot(),
            'botName' => $parser->robot(),
        ];

        // No result found?
        if ($this->hasResult($resultCache) !== true) {
            throw new NoResultFoundException('No result found for user agent: ' . $userAgent);
        }

        // Hydrate the model
        $result = new Model\UserAgent($this->getName(), $this->getVersion());
        $result->providerResultRaw = $resultCache;

        // Bot detection
        if ($resultCache['isRobot'] === true) {
            $this->hydrateBot($result->bot, $resultCache);

            return $result;
        }

        // hydrate the result
        $this->hydrateBrowser($result->browser, $resultCache);
        $this->hydrateOperatingSystem($result->operatingSystem, $resultCache);
        $this->hydrateDevice($result->device, $resultCache);

        return $result;
    }

    /**
     * @return bool
     */
    private function hasResult(array $resultRaw)
    {
        if ($resultRaw['isMobile'] === true || $resultRaw['isRobot'] === true) {
            return true;
        }

        if ($this->isRealResult($resultRaw['browserName'], 'browser', 'name') === true || $this->isRealResult($resultRaw['osName']) === true || $this->isRealResult($resultRaw['botName']) === true) {
            return true;
        }

        return false;
    }

    private function hydrateBot(Model\Bot $bot, array $resultRaw)
    {
        $bot->isBot = true;
        $bot->name = $this->getRealResult($resultRaw['botName']);
    }

    private function hydrateBrowser(Model\Browser $browser, array $resultRaw)
    {
        if ($this->isRealResult($resultRaw['browserName'], 'browser', 'name') === true) {
            $browser->name = $resultRaw['browserName'];
            $browser->version->complete = $this->getRealResult($resultRaw['browserVersion']);
        }
    }

    private function hydrateOperatingSystem(Model\OperatingSystem $os, array $resultRaw)
    {
        if ($this->isRealResult($resultRaw['osName']) === true) {
            $os->name = $resultRaw['osName'];
            $os->version->complete = $this->getRealResult($resultRaw['osVersion']);
        }
    }

    private function hydrateDevice(Model\Device $device, array $resultRaw)
    {
        if ($resultRaw['isMobile'] === true) {
            $device->isMobile = true;
        }
    }
}
