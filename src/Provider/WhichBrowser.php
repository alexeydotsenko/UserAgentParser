<?php

namespace UserAgentParser\Provider;

use UserAgentParser\Exception\NoResultFoundException;
use UserAgentParser\Exception\PackageNotLoadedException;
use UserAgentParser\Model;
use WhichBrowser\Parser as WhichBrowserParser;

/**
 * Abstraction for whichbrowser/parser.
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @author Niels Leenheer <niels@leenheer.nl>
 * @license MIT
 *
 * @see https://github.com/WhichBrowser/Parser
 */
class WhichBrowser extends AbstractProvider
{
    /**
     * Name of the provider.
     *
     * @var string
     */
    protected $name = 'WhichBrowser';

    /**
     * Homepage of the provider.
     *
     * @var string
     */
    protected $homepage = 'https://github.com/WhichBrowser/Parser';

    /**
     * Composer package name.
     *
     * @var string
     */
    protected $packageName = 'whichbrowser/parser';

    protected $detectionCapabilities = [
        'browser' => [
            'name' => true,
            'version' => true,
        ],

        'renderingEngine' => [
            'name' => true,
            'version' => true,
        ],

        'operatingSystem' => [
            'name' => true,
            'version' => true,
        ],

        'device' => [
            'model' => true,
            'brand' => true,
            'type' => true,
            'isMobile' => true,
            'isTouch' => false,
        ],

        'bot' => [
            'isBot' => true,
            'name' => true,
            'type' => false,
        ],
    ];

    /**
     * Used for unitTests mocking.
     *
     * @var WhichBrowserParser
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
     * @return WhichBrowserParser
     */
    public function getParser(array $headers)
    {
        if ($this->parser !== null) {
            return $this->parser;
        }

        return new WhichBrowserParser($headers);
    }

    public function parse($userAgent, array $headers = [])
    {
        $headers['User-Agent'] = $userAgent;

        $parser = $this->getParser($headers);

        // No result found?
        if ($parser->isDetected() !== true) {
            throw new NoResultFoundException('No result found for user agent: ' . $userAgent);
        }

        // Hydrate the model
        $result = new Model\UserAgent($this->getName(), $this->getVersion());
        $result->providerResultRaw = $parser->toArray();

        // Bot detection
        if ($parser->getType() === 'bot') {
            $this->hydrateBot($result->bot, $parser->browser);

            return $result;
        }

        // hydrate the result
        $this->hydrateBrowser($result->browser, $parser->browser);
        $this->hydrateRenderingEngine($result->renderingEngine, $parser->engine);
        $this->hydrateOperatingSystem($result->operatingSystem, $parser->os);
        $this->hydrateDevice($result->device, $parser->device, $parser);

        return $result;
    }

    private function hydrateBot(Model\Bot $bot, \WhichBrowser\Model\Browser $browserRaw)
    {
        $bot->isBot = true;
        $bot->name = $this->getRealResult($browserRaw->getName());
    }

    private function hydrateBrowser(Model\Browser $browser, \WhichBrowser\Model\Browser $browserRaw)
    {
        if ($this->isRealResult($browserRaw->getName(), 'browser', 'name') === true) {
            $browser->name = $browserRaw->getName();
            $browser->version->complete = $this->getRealResult($browserRaw->getVersion());

            return;
        }

        if (isset($browserRaw->using) && $browserRaw->using instanceof \WhichBrowser\Model\Using) {
            // @var $usingRaw \WhichBrowser\Model\Using
            $usingRaw = $browserRaw->using;

            if ($this->isRealResult($usingRaw->getName()) === true) {
                $browser->name = $usingRaw->getName();

                $browser->version->complete = $this->getRealResult($usingRaw->getVersion());
            }
        }
    }

    private function hydrateRenderingEngine(Model\RenderingEngine $engine, \WhichBrowser\Model\Engine $engineRaw)
    {
        $engine->name = $this->getRealResult($engineRaw->getName());
        $engine->version->complete = $this->getRealResult($engineRaw->getVersion());
    }

    private function hydrateOperatingSystem(Model\OperatingSystem $os, \WhichBrowser\Model\Os $osRaw)
    {
        $os->name = $this->getRealResult($osRaw->getName());
        $os->version->complete = $this->getRealResult($osRaw->getVersion());
    }

    private function hydrateDevice(Model\Device $device, \WhichBrowser\Model\Device $deviceRaw, WhichBrowserParser $parser)
    {
        $device->model = $this->getRealResult($deviceRaw->getModel());
        $device->brand = $this->getRealResult($deviceRaw->getManufacturer());
        $device->type = $this->getRealResult($parser->getType());

        if ($parser->isMobile() === true) {
            $device->isMobile = true;
        }
    }
}
