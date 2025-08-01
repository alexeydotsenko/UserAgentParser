<?php

namespace UserAgentParser\Model;

/**
 * Device model.
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 */
class Device
{
    public string|null $model = null;

    public string|null $brand = null;

    public string|null $type = null;

    public bool|null $isMobile = null;

    public bool|null $isTouch = null;

    public function toArray(): array
    {
        return [
            'model' => $this->model,
            'brand' => $this->brand,
            'type' => $this->type,
            'isMobile' => $this->isMobile,
            'isTouch' => $this->isTouch,
        ];
    }
}
