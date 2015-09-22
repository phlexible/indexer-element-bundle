<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Indexer;

use Phlexible\Bundle\IndexerBundle\Document\DocumentFactory;
use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\IndexerElementBundle\Document\ElementDocument;
use Phlexible\Bundle\IndexerElementBundle\Event\MapDocumentEvent;
use Phlexible\Bundle\IndexerElementBundle\Indexer\DocumentApplier\DocumentApplierInterface;
use Phlexible\Bundle\IndexerElementBundle\Indexer\IndexibleVoter\IndexibleVoterInterface;
use Phlexible\Bundle\IndexerElementBundle\IndexerElementEvents;
use Phlexible\Bundle\SiterootBundle\Entity\Siteroot;
use Phlexible\Bundle\TreeBundle\ContentTree\ContentTreeNode;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Document mapper
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class DocumentMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IndexibleVoterInterface
     */
    private $voter;

    /**
     * @var DocumentApplierInterface
     */
    private $applier;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DocumentMapper
     */
    private $mapper;

    public function setUp()
    {
        $this->voter = $this->prophesize(IndexibleVoterInterface::class);
        $this->applier = $this->prophesize(DocumentApplierInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->mapper = new DocumentMapper(
            $this->voter->reveal(),
            $this->applier->reveal(),
            $this->dispatcher->reveal(),
            $this->logger->reveal()
        );
    }

    public function testMapIdentityReturnsNullOnDeniedVoter()
    {
        $document = new ElementDocument();
        $identity = new DocumentDescriptor(new DocumentIdentity('abc'), new ContentTreeNode(), new Siteroot(), 'de');

        $this->voter->isIndexible($identity)->willReturn(IndexibleVoterInterface::VOTE_DENY);

        $this->mapper->mapDocument($document, $identity);
    }

    public function testMapIdentityReturnsDocument()
    {
        $document = new ElementDocument();
        $identity = new DocumentDescriptor(new DocumentIdentity('abc'), new ContentTreeNode(), new Siteroot(), 'de');

        $document = $this->mapper->mapDocument($document, $identity);

        $this->assertInstanceOf(ElementDocument::class, $document);
    }

    public function testMapIdentityDispatchesMapDocumentEvent()
    {
        $document = new ElementDocument();
        $identity = new DocumentDescriptor(new DocumentIdentity('abc'), new ContentTreeNode(), new Siteroot(), 'de');

        $this->dispatcher->dispatch(IndexerElementEvents::MAP_DOCUMENT, Argument::type(MapDocumentEvent::class))->shouldBeCalled();

        $this->mapper->mapDocument($document, $identity);
    }
}