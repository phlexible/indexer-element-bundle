<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Indexer\DocumentApplier;

use Phlexible\Bundle\ElementBundle\ElementService;
use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;
use Phlexible\Bundle\IndexerElementBundle\Indexer\ContentFilter\ContentFilterInterface;
use Phlexible\Bundle\IndexerElementBundle\Indexer\DocumentDescriptor;
use Phlexible\Bundle\IndexerElementBundle\Indexer\ContentRenderer\ContentRendererInterface;
use Phlexible\Bundle\IndexerElementBundle\Indexer\ContentTitleExtractor\ContentTitleExtractor;
use Phlexible\Bundle\IndexerElementBundle\Indexer\ContentTitleExtractor\ContentTitleExtractorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Content document applier
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ContentDocumentApplier implements DocumentApplierInterface
{
    /**
     * @var ElementService
     */
    private $elementService;

    /**
     * @var ContentFilterInterface
     */
    private $contentFilter;

    /**
     * @var ContentTitleExtractorInterface
     */
    private $titleExtractor;

    /**
     * @var ContentRendererInterface
     */
    private $contentRenderer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ElementService                 $elementService
     * @param ContentFilterInterface         $contentFilter
     * @param ContentTitleExtractorInterface $titleExtractor
     * @param ContentRendererInterface       $contentRenderer
     * @param EventDispatcherInterface       $dispatcher
     * @param LoggerInterface                $logger
     */
    public function __construct(
        ElementService $elementService,
        ContentFilterInterface $contentFilter,
        ContentTitleExtractorInterface $titleExtractor,
        ContentRendererInterface $contentRenderer,
        EventDispatcherInterface $dispatcher,
        LoggerInterface $logger
    ) {
        $this->elementService = $elementService;
        $this->contentFilter = $contentFilter;
        $this->titleExtractor = $titleExtractor;
        $this->contentRenderer = $contentRenderer;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;

    }

    /**
     * @param DocumentInterface $document
     * @param DocumentDescriptor   $descriptor
     */
    public function apply(DocumentInterface $document, DocumentDescriptor $descriptor)
    {
        $node = $descriptor->getNode();
        $language = $descriptor->getLanguage();

        $content = $this->contentRenderer->render($descriptor);

        if (!$content) {
            $this->logger->info("TreeNode {$node->getId()} not indexed, no result from renderNode()");

            return;
        }

        $content = $this->contentFilter->filter($content);

        $element        = $this->elementService->findElement($node->getTypeId());
        $elementVersion = $this->elementService->findElementVersion($element, $node->getTree()->getPublishedVersion($node, $language));

        $title = $this->titleExtractor->extractTitle($content);
        if (!$title) {
            $title = $elementVersion->getPageTitle($language);
        }

        $elementtype = $this->elementService->findElementtype($element);

        $document->set('title', $title);
        $document->set('content', $content);
        $document->set('elementtypeId', $elementtype->getId());
        $document->set('elementtype', $elementtype->getUniqueId());
    }
}