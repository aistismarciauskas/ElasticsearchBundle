<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ElasticsearchBundle\Tests\Unit\Result;

use ONGR\ElasticsearchBundle\ORM\Repository;
use ONGR\ElasticsearchBundle\Result\DocumentScanIterator;

class DocumentScanIteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test for Document Scan iterator.
     */
    public function testIterator()
    {
        $rawData = [
            'hits' => [
                'total' => 2,
                'hits' => [],
            ],
        ];

        $repository = $this->getMockBuilder('ONGR\ElasticsearchBundle\ORM\Repository')
            ->disableOriginalConstructor()
            ->getMock();

        $repository->expects($this->at(0))
            ->method('scan')
            ->with('test_id', '5m', Repository::RESULTS_RAW)
            ->willReturn(
                [
                    '_scroll_id' => 'updated_id_1',
                    'hits' => [
                        'total' => 8,
                        'hits' => [['id' => 1]],
                    ],
                ]
            );

        $repository->expects($this->at(1))
            ->method('scan')
            ->with('updated_id_1', '5m', Repository::RESULTS_RAW)
            ->willReturn(
                [
                    '_scroll_id' => 'updated_id_2',
                    'hits' => [
                        'total' => 8,
                        'hits' => [['id' => 2]],
                    ],
                ]
            );

        $converter = $this->getMockBuilder('ONGR\ElasticsearchBundle\Result\Converter')
            ->disableOriginalConstructor()
            ->getMock();

        $converter->expects($this->exactly(2))
            ->method('convertToDocument')
            ->willReturnArgument(0);

        $iterator = new DocumentScanIterator($rawData, [], []);
        $iterator->setConverter($converter);
        $iterator->setRepository($repository)
            ->setScrollId('test_id')
            ->setScrollDuration('5m');

        $this->assertCount(2, $iterator);

        $this->assertTrue($iterator->valid());

        $result = [];
        foreach ($iterator as $key => $val) {
            $result[] = [$key, $val];
        }

        $this->assertEquals([[0 => 0, ['id' => 1]], [0 => 1, ['id' => 2]]], $result);
    }

    /**
     * Test for Document Scan iterator.
     * @expectedException \ONGR\ElasticsearchBundle\Result\IteratorException
     * @expectedExceptionMessage Iteration terminated, no data in scan returned
     */
    public function testNoDataIterator()
    {
        $rawData = [
            'hits' => [
                'total' => 8,
                'hits' => [],
            ],
        ];

        $repository = $this->getMockBuilder('ONGR\ElasticsearchBundle\ORM\Repository')
            ->disableOriginalConstructor()
            ->getMock();

        $repository->expects($this->at(0))
            ->method('scan')
            ->with('test_id', '5m', Repository::RESULTS_RAW)
            ->willReturn(
                [
                    '_scroll_id' => 'updated_id_1',
                    'hits' => [
                        'total' => 8,
                        'hits' => [['id' => 1]],
                    ],
                ]
            );

        $repository->expects($this->at(1))
            ->method('scan')
            ->with('updated_id_1', '5m', Repository::RESULTS_RAW)
            ->willReturn(
                [
                    '_scroll_id' => 'updated_id_2',
                    'hits' => [
                        'total' => 8,
                        'hits' => [['id' => 1]],
                    ],
                ]
            );

        $converter = $this->getMockBuilder('ONGR\ElasticsearchBundle\Result\Converter')
            ->disableOriginalConstructor()
            ->getMock();

        $converter->expects($this->exactly(2))
            ->method('convertToDocument')
            ->willReturnArgument(0);

        $iterator = new DocumentScanIterator($rawData, [], []);
        $iterator->setConverter($converter);
        $iterator->setRepository($repository)
            ->setScrollId('test_id')
            ->setScrollDuration('5m');

        $this->assertCount(8, $iterator);

        $this->assertTrue($iterator->valid());
        foreach ($iterator as $key => $val) {
            // Do nothing.
        }
    }

    /**
     * Test for Document Scan iterator.
     * @expectedException \ONGR\ElasticsearchBundle\Result\IteratorException
     * @expectedExceptionMessage Iteration terminated, not all items iterated
     */
    public function testMiddleStopIterator()
    {
        $rawData = [
            'hits' => [
                'total' => 3,
                'hits' => [],
            ],
        ];

        $repository = $this->getMockBuilder('ONGR\ElasticsearchBundle\ORM\Repository')
            ->disableOriginalConstructor()
            ->getMock();

        $repository->expects($this->at(0))
            ->method('scan')
            ->with('test_id', '5m', Repository::RESULTS_RAW)
            ->willReturn(
                [
                    '_scroll_id' => 'updated_id_1',
                    'hits' => [
                        'total' => 3,
                        'hits' => [['id' => 1]],
                    ],
                ]
            );

        $repository->expects($this->at(1))
            ->method('scan')
            ->with('updated_id_1', '5m', Repository::RESULTS_RAW)
            ->willReturn(
                [
                    '_scroll_id' => 'updated_id_2',
                    'hits' => [
                        'total' => 3,
                        'hits' => [['id' => 2]],
                    ],
                ]
            );

        $repository->expects($this->at(2))
            ->method('scan')
            ->with('updated_id_2', '5m', Repository::RESULTS_RAW)
            ->willReturn(
                [
                    '_scroll_id' => 'updated_id_2',
                    'hits' => [
                        'total' => 3,
                        'hits' => [2 => ['id' => 3]],
                    ],
                ]
            );

        $converter = $this->getMockBuilder('ONGR\ElasticsearchBundle\Result\Converter')
            ->disableOriginalConstructor()
            ->getMock();

        $converter->expects($this->exactly(2))
            ->method('convertToDocument')
            ->willReturnArgument(0);

        $iterator = new DocumentScanIterator($rawData, [], []);
        $iterator->setConverter($converter);
        $iterator->setRepository($repository)
            ->setScrollId('test_id')
            ->setScrollDuration('5m');

        $this->assertCount(3, $iterator);

        $this->assertTrue($iterator->valid());
        foreach ($iterator as $key => $val) {
            // Do nothing.
        }
    }
}
