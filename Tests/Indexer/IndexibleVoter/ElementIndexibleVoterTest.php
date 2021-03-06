<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPageBundle\Tests\Indexer\IndexibleVoter;

use Phlexible\Bundle\ElementBundle\ElementService;
use Phlexible\Bundle\ElementBundle\Entity\Element;
use Phlexible\Bundle\ElementtypeBundle\Model\Elementtype;
use Phlexible\Bundle\IndexerBundle\Document\DocumentIdentity;
use Phlexible\Bundle\IndexerPageBundle\Indexer\IndexibleVoter\ElementIndexibleVoter;
use Phlexible\Bundle\IndexerPageBundle\Indexer\PageDocumentDescriptor;
use Phlexible\Bundle\SiterootBundle\Entity\Siteroot;
use Phlexible\Bundle\TreeBundle\ContentTree\ContentTreeNode;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

/**
 * Element indexible voter.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 *
 * @covers \Phlexible\Bundle\IndexerPageBundle\Indexer\IndexibleVoter\ElementIndexibleVoter
 */
class ElementIndexibleVoterTest extends TestCase
{
    /**
     * @var ElementIndexibleVoter
     */
    private $voter;

    /**
     * @var ElementService
     */
    private $elementService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->elementService = $this->prophesize(ElementService::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->voter = new ElementIndexibleVoter($this->elementService->reveal(), $this->logger->reveal());
    }

    public function testVoteWillReturnDenyOnSkipElementtypeId()
    {
        $element = new Element();
        $element->setElementtypeId(345);
        $node = new ContentTreeNode();
        $node->setId(123);
        $node->setTypeId(234);
        $siteroot = new Siteroot();
        $siteroot->setProperty('page_indexer.skip_elementtype_ids', '345,456');
        $identity = new DocumentIdentity('page_74_de');
        $descriptor = new PageDocumentDescriptor($identity, $node, $siteroot, 'de');

        $this->elementService->findElement(234)->willReturn($element);
        $this->logger->info('TreeNode 123 not indexed, elementtype id in skip list')->shouldBeCalled();

        $result = $this->voter->isIndexible($descriptor);

        $this->assertSame(ElementIndexibleVoter::VOTE_DENY, $result);
    }

    public function testVoteWillReturnDenyOnOtherThanFullElement()
    {
        $element = new Element();
        $element->setElementtypeId(345);
        $elementtype = new Elementtype();
        $elementtype->setId(345);
        $elementtype->setType('part');
        $node = new ContentTreeNode();
        $node->setId(123);
        $node->setTypeId(234);
        $siteroot = new Siteroot();
        $identity = new DocumentIdentity('page_74_de');
        $descriptor = new PageDocumentDescriptor($identity, $node, $siteroot, 'de');

        $this->elementService->findElement(234)->willReturn($element);
        $this->elementService->findElementtype($element)->willReturn($elementtype);
        $this->logger->info('TreeNode 123 not indexed, not a full element')->shouldBeCalled();

        $result = $this->voter->isIndexible($descriptor);

        $this->assertSame(ElementIndexibleVoter::VOTE_DENY, $result);
    }

    public function testVoteWillReturnAllow()
    {
        $element = new Element();
        $element->setElementtypeId(345);
        $elementtype = new Elementtype();
        $elementtype->setId(345);
        $elementtype->setType('full');
        $node = new ContentTreeNode();
        $node->setId(123);
        $node->setTypeId(234);
        $siteroot = new Siteroot();
        $identity = new DocumentIdentity('page_74_de');
        $descriptor = new PageDocumentDescriptor($identity, $node, $siteroot, 'de');

        $this->elementService->findElement(234)->willReturn($element);
        $this->elementService->findElementtype($element)->willReturn($elementtype);
        $this->logger->info(Argument::cetera())->shouldNotBeCalled();

        $result = $this->voter->isIndexible($descriptor);

        $this->assertSame(ElementIndexibleVoter::VOTE_ALLOW, $result);
    }
}
