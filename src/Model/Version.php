<?php

namespace UserAgentParser\Model;

/**
 * Version model.
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 */
class Version
{
    public int|null $major = null {
        get {
            return $this->major;
        }
        set {
            $this->major = $value;

            $this->hydrateComplete();
        }
    }

    public int|null $minor = null {
        get {
            return $this->minor;
        }
        set {
            $this->minor = $value;

            $this->hydrateComplete();
        }
    }

    public int|null $patch = null {
        get {
            return $this->patch;
        }
        set {
            $this->patch = $value;

            $this->hydrateComplete();
        }
    }

    public string|null $alias = null{
        get {
            return $this->alias;
        }
        set {
            $this->alias = $value;

            $this->hydrateComplete();
        }
    }

    public string|null $complete = null {
        get {
            return $this->complete;
        }
        set {
            $left = preg_replace('/[0._]/', '', $value);
            if ($left === '') {
                $value = null;
            }

            $this->hydrateFromComplete($value);

            $this->complete = $value;
        }
    }

    private static array $notAllowedAlias = [
        'a',
        'alpha',
        'prealpha',

        'b',
        'beta',
        'prebeta',

        'rc',
    ];

    public function toArray(): array
    {
        return [
            'major' => $this->major,
            'minor' => $this->minor,
            'patch' => $this->patch,

            'alias' => $this->alias,

            'complete' => $this->complete,
        ];
    }

    private function hydrateComplete(): void
    {
        if ($this->major === null && $this->alias === null) {
            return;
        }

        $version = $this->major;

        if ($this->minor !== null) {
            $version .= '.' . $this->minor;
        }

        if ($this->patch !== null) {
            $version .= '.' . $this->patch;
        }

        if ($this->alias !== null) {
            $version = $this->alias . ' - ' . $version;
        }

        $this->complete = $version;
    }

    private function hydrateFromComplete(string|null $complete): void
    {
        $parts = $this->getCompleteParts($complete);

        $this->major = $parts['major'];
        $this->minor = $parts['minor'];
        $this->patch = $parts['patch'];
        $this->alias = $parts['alias'];
    }

    /**
     * @param mixed $complete
     *
     * @return array
     */
    private function getCompleteParts(string|null $complete): array
    {
        $versionParts = [
            'major' => null,
            'minor' => null,
            'patch' => null,
            'alias' => null,
        ];

        if ($complete !== null) {
            // only digits
            preg_match('/\\d+(?:[._]*\\d*)*/', $complete, $result);
            if (\count($result) > 0) {
                $parts = preg_split('/[._]/', $result[0]);

                if (isset($parts[0]) && $parts[0] != '') {
                    $versionParts['major'] = (int)$parts[0];
                }
                if (isset($parts[1]) && $parts[1] != '') {
                    $versionParts['minor'] = (int)$parts[1];
                }
                if (isset($parts[2]) && $parts[2] != '') {
                    $versionParts['patch'] = (int)$parts[2];
                }
            }

            // grab alias
            $result = preg_split('/\\d+(?:[._]*\\d*)*/', $complete);
            foreach ($result as $row) {
                $row = trim($row);

                if ($row === '') {
                    continue;
                }

                // do not use beta and other things
                if (\in_array($row, self::$notAllowedAlias)) {
                    continue;
                }

                $versionParts['alias'] = $row;
            }
        }

        return $versionParts;
    }
}
