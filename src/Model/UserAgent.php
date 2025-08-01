<?php

namespace UserAgentParser\Model;

/**
 * User agent model.
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 */
class UserAgent
{
    public readonly string|null $providerName;

    public readonly string|null $providerVersion;

    public Browser $browser;

    public RenderingEngine $renderingEngine;

    public OperatingSystem $operatingSystem;

    public Device $device;

    public Bot $bot;

    public mixed $providerResultRaw;

    public function __construct(string|null $providerName = null, string|null $providerVersion = null)
    {
        $this->providerName = $providerName;
        $this->providerVersion = $providerVersion;

        $this->browser = new Browser();
        $this->renderingEngine = new RenderingEngine();
        $this->operatingSystem = new OperatingSystem();
        $this->device = new Device();
        $this->bot = new Bot();
    }

    public function isBot(): bool
    {
        if ($this->bot === true) {
            return true;
        }

        return false;
    }

    public function isMobile(): bool
    {
        if ($this->device === true) {
            return true;
        }

        return false;
    }

    public function toArray(bool $includeResultRaw = false): array
    {
        $data = [
            'browser' => $this->browser->toArray(),
            'renderingEngine' => $this->renderingEngine->toArray(),
            'operatingSystem' => $this->operatingSystem->toArray(),
            'device' => $this->device->toArray(),
            'bot' => $this->bot->toArray(),
        ];

        // should be only used for debug
        if ($includeResultRaw) {
            $data['providerResultRaw'] = $this->providerResultRaw;
        }

        return $data;
    }
}
