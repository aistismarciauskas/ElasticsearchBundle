<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ElasticsearchBundle\Result;

use ONGR\ElasticsearchBundle\ORM\Repository;

/**
 * DocumentScanIterator class.
 */
class DocumentScanIterator extends DocumentIterator
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var string
     */
    private $scrollDuration;

    /**
     * @var string
     */
    private $scrollId;

    /**
     * @var int
     */
    private $key = 0;

    /** @var bool */
    private $cleanup = false;

    /** @var int  */
    private $maxKey = null;

    /**
     * @param Repository $repository
     *
     * @return DocumentScanIterator
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * @param string $scrollDuration
     *
     * @return DocumentScanIterator
     */
    public function setScrollDuration($scrollDuration)
    {
        $this->scrollDuration = $scrollDuration;

        return $this;
    }

    /**
     * @param string $scrollId
     *
     * @return DocumentScanIterator
     */
    public function setScrollId($scrollId)
    {
        $this->scrollId = $scrollId;

        return $this;
    }

    /**
     * @return string
     */
    public function getScrollId()
    {
        return $this->scrollId;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->getTotalCount();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->key = 0;
        $this->maxKey = null;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        if (array_key_exists($this->key, $this->documents)) {
            return true;
        }

        $raw = $this->repository->scan($this->scrollId, $this->scrollDuration, Repository::RESULTS_RAW);

        $chunkSize = count($raw['hits']['hits']);
        if ($chunkSize === 0) {
            return false;
        }

        $this->setScrollId($raw['_scroll_id']);

        $this->documents = [];
        foreach ($raw['hits']['hits'] as $key => $value) {
            $this->documents[$key + $this->key] = $value;
        }

        // Clean up.
        if ($this->cleanup === false && count($this->converted) >= $chunkSize * 2) {
            $this->cleanup = true;
        }

        if ($this->cleanup === true) {
            $tmp = $this->converted;
            $this->converted = array_slice($tmp, -$chunkSize, $chunkSize, true);
            unset($set);
            unset($tmp);
            $this->cleanup = false;
        }

        if ($this->maxKey === null) {
            $this->maxKey = $this->count() - 1;
        }

        $valid = isset($this->documents[$this->key]);
        if (!$valid && $this->key < $this->maxKey) {
            throw new IteratorException('Iteration terminated, not all items iterated');
        }
        return $valid;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->key++;
    }
}
