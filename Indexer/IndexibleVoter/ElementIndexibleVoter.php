<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Indexer\IndexibleVoter;

use Phlexible\Bundle\ElementBundle\ElementService;
use Phlexible\Bundle\IndexerElementBundle\Indexer\DocumentDescriptor;
use Psr\Log\LoggerInterface;

/**
 * Element indexible voter
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ElementIndexibleVoter implements IndexibleVoterInterface
{
    /**
     * @var ElementService
     */
    private $elementService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ElementService  $elementService
     * @param LoggerInterface $logger
     */
    public function __construct(ElementService $elementService, LoggerInterface $logger)
    {
        $this->elementService = $elementService;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function isIndexible(DocumentDescriptor $descriptor)
    {
        $node = $descriptor->getNode();
        $siteroot = $descriptor->getSiteroot();

        $skipElementtypeIds = explode(',', $siteroot->getProperty('element_indexer.skip_elementtype_ids'));

        // skip elementtype?
        $element       = $this->elementService->findElement($node->getTypeId());
        $elementtypeId = $element->getElementtypeId();
        if (in_array($elementtypeId, $skipElementtypeIds)) {
            $this->logger->info("TreeNode {$node->getId()} not indexed, elementtype id in skip list");

            return self::VOTE_DENY;
        }

        // skip non full elements
        $elementtype = $this->elementService->findElementtype($element);
        if ('full' !== $elementtype->getType()) {
            // ElementtypeVersion::TYPE_FULL
            $this->logger->info("TreeNode {$node->getId()} not indexed, not a full element");

            return self::VOTE_DENY;
        }

        return self::VOTE_ALLOW;
    }
}