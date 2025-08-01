<?php

namespace UserAgentParser\Model;

/**
 * Bot model.
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 */
class Bot
{
    public bool|null $isBot = null;

    public string|null $name = null;

    public string|null $type = null;

    public function toArray(): array
    {
        return [
            'isBot' => $this->isBot,
            'name' => $this->name,
            'type' => $this->type,
        ];
    }
}
