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

/**
 * Class arWhereCollection
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arWhereCollection extends arStatementCollection
{
    public function asSQLStatement(): string
    {
        $return = '';
        if ($this->hasStatements()) {
            $return .= ' WHERE ';
            $wheres = $this->getWheres();
            $last = end($wheres);
            foreach ($wheres as $where) {
                $return .= $where->asSQLStatement($this->getAr());
                if ($where !== $last) {
                    $return .= ' ' . $where->getLink() . ' ';
                }
            }
        }

        return $return;
    }

    /**
     * @return arWhere[]
     */
    public function getWheres(): array
    {
        return $this->statements;
    }
}
