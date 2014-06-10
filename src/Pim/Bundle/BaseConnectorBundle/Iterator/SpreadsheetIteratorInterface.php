<?php

namespace Pim\Bundle\BaseConnectorBundle\Iterator;

/**
 * Common interface for spreadsheet iterators
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SpreadsheetIteratorInterface extends \Iterator
{
    /**
     * Returns the index of the current worksheet
     *
     * @return int
     */
    public function getWorksheetIndex();

    /**
     * Returns the name of the current worksheet
     *
     * @return string
     */
    public function getWorksheetName();
}
