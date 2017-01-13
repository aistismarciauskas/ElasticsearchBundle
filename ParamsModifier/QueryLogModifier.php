<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ElasticsearchBundle\ParamsModifier;

class QueryLogModifier implements ParamsModifierInterface
{
    /**
     * @var string
     */
    private $prefix = '';

    /**
     * @var int
     */
    private $maxDeep = 12;

    /**
     * @var string[]
     */
    private $ignoreFilter = [];

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @return int
     */
    public function getMaxDeep()
    {
        return $this->maxDeep;
    }

    /**
     * @param int $maxDeep
     */
    public function setMaxDeep($maxDeep)
    {
        $this->maxDeep = $maxDeep;
    }

    /**
     * @return \string[]
     */
    public function getIgnoreFilter()
    {
        return $this->ignoreFilter;
    }

    /**
     * @param \string[] $ignoreFilter
     */
    public function setIgnoreFilter($ignoreFilter)
    {
        $this->ignoreFilter = $ignoreFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(array $params, $type)
    {
        $statKey = $this->buildStatsKey();
        if (empty($statKey)) {
            return $params;
        }
        if (isset($params['body']) && isset($params['body']['stats']) && is_array($params['body']['stats'])) {
            $params['body']['stats'][] = $statKey;
        } else {
            $params['body']['stats'] = [$statKey];
        }

        return $params;
    }

    /**
     * @return string
     */
    protected function buildStatsKey()
    {
        $backStack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $this->maxDeep);
        $stats = array_map(
            function ($stat) {
                $className = array_slice(explode('\\', $stat['class']), -1, 1)[0];
                $functionName = $stat['function'];

                return (strlen($className) > 12 ? substr(
                        $className,
                        strlen($className) - 12,
                        8
                    ) : $className) . ':' . (strlen($functionName) > 12 ? substr(
                        $functionName,
                        0,
                        12
                    ) : $functionName);
            },
            array_filter(
                $backStack,
                function ($stat) {
                    return isset($stat['class']) && $this->isStackAllowed($stat)
                        && strpos($stat['function'], '__') === false;
                }
            )
        );

        $resultKey = substr(
                array_reduce(
                    $stats,
                    function ($previous, $val) {
                        return $previous . ($previous ? '-' : '') . $val;
                    },
                    ''
                ),
                0,
                1000
            );

        if (!$resultKey) {
            return null;
        }
        return $this->prefix . $resultKey;
    }

    /**
     * @param array $stack
     *
     * @return bool
     */
    protected function isStackAllowed($stack)
    {
        foreach ($this->ignoreFilter as $value) {
            if (strpos($stack['class'], $value) !== false) {
                return false;
            }
        }

        return true;
    }
}
