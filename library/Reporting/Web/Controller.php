<?php

// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web;

use Icinga\Authentication\Auth;
use ipl\Orm\Query;
use ipl\Stdlib\Filter;
use ipl\Web\Compat\CompatController;

class Controller extends CompatController
{
    /**
     * @param Query $query
     * @param string $column
     * @return void
     */
    protected function applyRestriction(Query $query, string $column)
    {
        $restrictions = Auth::getInstance()->getRestrictions('reporting/prefix');
        $prefixes = [];
        foreach ($restrictions as $restriction) {
            $prefixes = array_merge(
                $prefixes,
                explode(', ', trim($restriction))
            );
        }

        if (! empty($prefixes)) {
            foreach ($prefixes as $prefix) {
                $query->orFilter(Filter::like($column, $prefix . '*'));
            }
        }
    }
}
