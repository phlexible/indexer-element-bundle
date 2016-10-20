<?php

/*
 * This file is part of the phlexible indexer page package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\IndexerPagerBundle\Tests\Indexer\ContentFilter;

use Phlexible\Bundle\IndexerPagerBundle\Indexer\ContentFilter\ChainContentFilter;
use Phlexible\Bundle\IndexerPagerBundle\Indexer\ContentFilter\ContentFilterInterface;

/**
 * Chain content filter test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ChainContentFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testFilterChain()
    {
        $filter1 = $this->prophesize(ContentFilterInterface::class);
        $filter2 = $this->prophesize(ContentFilterInterface::class);

        $filter1->filter('test')->willReturn('test1');
        $filter2->filter('test1')->willReturn('test2');

        $filter = new ChainContentFilter(array($filter1->reveal(), $filter2->reveal()));
        $result = $filter->filter('test');

        $this->assertSame('test2', $result);
    }
}
