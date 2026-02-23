<?php

// SPDX-FileCopyrightText: 2019 Icinga GmbH <https://icinga.com>
// SPDX-License-Identifier: GPL-3.0-or-later

namespace Icinga\Module\Reporting\Common;

trait Macros
{
    protected $macros;

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getMacro($name)
    {
        return $this->macros[$name] ?: null;
    }

    /**
     * @return mixed
     */
    public function getMacros()
    {
        return $this->macros;
    }

    /**
     * @param mixed $macros
     *
     * @return $this
     */
    public function setMacros($macros)
    {
        $this->macros = $macros;

        return $this;
    }

    public function resolveMacros($subject)
    {
        $macros = [];

        foreach ((array) $this->macros as $key => $value) {
            $macros['${' . $key . '}'] = $value;
        }

        return str_replace(array_keys($macros), array_values($macros), $subject);
    }
}
