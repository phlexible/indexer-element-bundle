<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\IndexerElementBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Index command
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class IndexCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('indexer-element:index')
            ->setDescription('Index element document.')
            ->addArgument('documentId', InputArgument::REQUIRED, 'Document ID')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $documentId = $input->getArgument('documentId');

        ini_set('memory_limit', -1);

        $container = $this->getContainer();

        $indexer = $container->get('phlexible_indexer_element.indexer');
        $storage = $indexer->getStorage();

        $output->writeln('Indexer: ' . $indexer->getName());
        $output->writeln('  Storage: ' . get_class($storage));
        $output->writeln('    DSN: ' . $storage->getConnectionString());

        $document = $indexer->buildDocument($documentId);

        if (!$document) {
            $output->writeln("<error>Document $documentId could not be loaded.</error>");

            return 1;
        }

        $output->writeln('Document: ' . get_class($document) . ' ' . $document->getIdentifier());

        $update = $storage->createUpdate()
            ->addDocument($document)
            ->commit();

        $storage->execute($update);

        return 0;
    }

}
