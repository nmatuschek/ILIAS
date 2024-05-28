<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

class ilAuthLogoutPageEditorSettings
{
    public const MODE_IPE = 2;

    private array $languages = [];

    private static ?ilAuthLogoutPageEditorSettings $instance = null;
    private ilSetting $storage;

    private int $mode = 0;

    private ilLanguage $lng;

    public function __construct()
    {
        global $DIC;
        $this->lng = $DIC->language();

        $this->storage = new ilSetting('logout_editor');
        $this->read();
    }

    public static function getInstance(): ilAuthLogoutPageEditorSettings
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilAuthLogoutPageEditorSettings();
    }

    protected function getStorage(): ilSetting
    {
        return $this->storage;
    }

    public function setMode(int $a_mode): void
    {
        $this->mode = $a_mode;
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    /**
     * Get ilias editor language
     * @param string $a_langkey
     * @return string
     */
    public function getIliasEditorLanguage(string $a_langkey): string
    {
        if ($this->isIliasEditorEnabled($a_langkey)) {
            return $a_langkey;
        }
        if ($this->isIliasEditorEnabled($this->lng->getDefaultLanguage())) {
            return $this->lng->getDefaultLanguage();
        }
        return '';
    }

    /**
     * Enable editor for language
     */
    public function enableIliasEditor(string $a_langkey, bool $a_status): void
    {
        $this->languages[$a_langkey] = $a_status;
    }

    /**
     * Check if ilias editor is enabled for a language
     */
    public function isIliasEditorEnabled(string $a_langkey): bool
    {
        return $this->languages[$a_langkey] ?? false;
    }

    /**
     * Update settings
     */
    public function update(): void
    {
        $this->getStorage()->set('mode', (string) $this->getMode());

        foreach ($this->languages as $lngkey => $stat) {
            $this->storage->set($lngkey, (string) $stat);
        }
    }

    /**
     * Read settings
     */
    public function read(): void
    {
        $this->setMode((int) $this->getStorage()->get('mode', (string) self::MODE_IPE));

        // Language settings
        $this->languages = [];
        foreach ($this->lng->getInstalledLanguages() as $lngkey) {
            $this->enableIliasEditor($lngkey, (bool) $this->getStorage()->get($lngkey, ""));
        }
    }
}
