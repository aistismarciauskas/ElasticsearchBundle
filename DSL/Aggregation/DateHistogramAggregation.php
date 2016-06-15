<?php

namespace ONGR\ElasticsearchBundle\DSL\Aggregation;

/**
 * Very similar to HistogramAggregation, but contains additional settings.
 */
class DateHistogramAggregation extends HistogramAggregation
{
    /**
     * @var string
     */
    protected $missing;

    /**
     * @var string
     */
    protected $offset;

    /**
     * @var string
     */
    protected $timeZone;

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'date_histogram';
    }

    /**
     * {@inheritdoc}
     */
    public function getArray()
    {
        $out = array_filter(
            [
                'field' => $this->getField(),
                'interval' => $this->getInterval(),
                'min_doc_count' => $this->getMinDocCount(),
                'extended_bounds' => $this->getExtendedBounds(),
                'keyed' => $this->isKeyed(),
                'order' => $this->getOrder(),
                'missing' => $this->getMissing(),
                'offset' => $this->getOffset(),
                'time_zone' => $this->getTimeZone(),
            ],
            function ($val) {
                return ($val || is_numeric($val));
            }
        );
        $this->checkRequiredParameters($out, ['field', 'interval']);

        return $out;
    }

    /**
     * @return string
     */
    public function getMissing()
    {
        return $this->missing;
    }

    /**
     * @param string $missing
     *
     * @return DateHistogramAggregation
     */
    public function setMissing($missing)
    {
        $this->missing = $missing;

        return $this;
    }

    /**
     * @return string
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param string $offset
     *
     * @return DateHistogramAggregation
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTimeZone()
    {
        return $this->timeZone;
    }

    /**
     * @param mixed $timeZone
     *
     * @return DateHistogramAggregation
     */
    public function setTimeZone($timeZone)
    {
        $this->timeZone = $timeZone;

        return $this;
    }
}
