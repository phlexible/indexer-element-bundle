<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPageBundle\Indexer\Mapper;

use Phlexible\Bundle\ElementBundle\ElementService;
use Phlexible\Bundle\IndexerBundle\Document\DocumentInterface;
use Phlexible\Bundle\IndexerPageBundle\Indexer\ContentFilter\ContentFilterInterface;
use Phlexible\Bundle\IndexerPageBundle\Indexer\ContentRenderer\ContentRendererInterface;
use Phlexible\Bundle\IndexerPageBundle\Indexer\ContentTitleExtractor\ContentTitleExtractorInterface;
use Phlexible\Bundle\IndexerPageBundle\Indexer\PageDocumentDescriptor;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Content document mapper.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ContentDocumentMapper implements PageDocumentMapperInterface
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
     * @var EventDispatcherInterface
     */
    private $dispatcher;

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

    public function mapDocument(DocumentInterface $document, PageDocumentDescriptor $descriptor)
    {
        $node = $descriptor->getNode();
        $language = $descriptor->getLanguage();

        $content = $this->contentRenderer->render($descriptor);

        if (!$content) {
            $this->logger->info("TreeNode {$node->getId()} not indexed, no result from renderNode()");

            return;
        }

        $content = $this->contentFilter->filter($content);

        $element = $this->elementService->findElement($node->getTypeId());
        $elementVersion = $this->elementService->findElementVersion($element, $node->getTree()->getPublishedVersion($node, $language));

        $title = $this->titleExtractor->extractTitle($content);
        if (!$title) {
            $title = $elementVersion->getPageTitle($language);
        }

        $elementtype = $this->elementService->findElementtype($element);

        $document->set('title', $title);
        $document->set('content', $content);
        $document->set('elementtype_id', $elementtype->getId());
        $document->set('elementtype', $elementtype->getUniqueId());
    }
}
