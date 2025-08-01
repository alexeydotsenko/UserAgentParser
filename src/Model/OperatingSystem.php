<?php

namespace UserAgentParser\Model;

/**
 * Operating system model.
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 */
class OperatingSystem
{
    public string|null $name = null;

    public Version $version;

    public function __construct()
    {
        $this->version = new Version();
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'version' => $this->version->toArray(),
        ];
    }
}
