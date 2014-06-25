<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Query;

use Phlexible\Bundle\IndexerBundle\Query\AbstractQuery;

/**
 * EID query
 *
 * @author Phillip Look <pl@brainbits.net>
 */
class EidQuery extends AbstractQuery
{
    /**
     * @var array
     */
    protected $_fields = array('eid');

    /**
     * @var array
     */
    protected $documentTypes = array('elements');

    /**
     * @var string
     */
    protected $label = 'EID search';

    public function parseInput($input)
    {
        $this->setFilters(
            array(
                'eid' => (integer)$input
            )
        );
        return $this;
    }
}