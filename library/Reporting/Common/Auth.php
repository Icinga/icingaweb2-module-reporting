<?php

/* Icinga Reporting | (c) 2023 Icinga GmbH | GPLv2 */

namespace Icinga\Module\Reporting\Common;

use Icinga\Authentication\Auth as IcingaAuth;
use Icinga\Exception\ConfigurationError;
use ipl\Orm\Query;
use ipl\Stdlib\Filter;
use ipl\Stdlib\Filter\Rule;
use ipl\Web\Filter\QueryString;

trait Auth
{
    /**
     * Apply restrictions of this module
     *
     * @param Query $query
     */
    protected function applyRestrictions(Query $query): void
    {
        $auth = IcingaAuth::getInstance();
        $restrictions = $auth->getRestrictions('reporting/reports');

        $queryFilter = Filter::any();
        foreach ($restrictions as $restriction) {
            $queryFilter->add($this->parseRestriction($restriction, 'reporting/reports'));
        }

        $query->filter($queryFilter);
    }

    /**
     * Parse the query string of the given restriction
     *
     * @param string $queryString
     * @param string $restriction
     * @param ?callable $onCondition
     *
     * @return Rule
     */
    protected function parseRestriction(
        string $queryString,
        string $restriction,
        callable $onCondition = null
    ): Filter\Rule {
        $parser = QueryString::fromString($queryString);
        if ($onCondition) {
            $parser->on(QueryString::ON_CONDITION, $onCondition);
        }

        return $parser->on(
            QueryString::ON_CONDITION,
            function (Filter\Condition $condition) use ($restriction, $queryString) {
                $allowedColumns = ['report.name', 'report.author'];
                if (in_array($condition->getColumn(), $allowedColumns, true)) {
                    return;
                }

                throw new ConfigurationError(
                    t(
                        'Cannot apply restriction %s using the filter %s.'
                        . ' You can only use the following columns: %s'
                    ),
                    $restriction,
                    $queryString,
                    implode(', ', $allowedColumns)
                );
            }
        )->parse();
    }
}
