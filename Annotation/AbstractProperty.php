<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ElasticsearchBundle\Annotation;

use ONGR\ElasticsearchBundle\Mapping\Caser;
use ONGR\ElasticsearchBundle\Mapping\DumperInterface;

/**
 * Makes sure that annotations are well formatted.
 */
abstract class AbstractProperty implements DumperInterface
{
    /**
     * {@inheritdoc}
     */
    public function dump(array $exclude = [])
    {
        $array = array_diff_key(
            array_filter(
                get_object_vars($this),
                function ($value) {
                    return $value || is_bool($value);
                }
            ),
            array_flip(array_merge(['name', 'objectName', 'multiple'], $exclude))
        );

        if (!array_key_exists('doc_values', $array) && array_key_exists('type', $array)) {
            $isCoreType = in_array(
                $array['type'],
                ['integer', 'long', 'float', 'double', 'boolean', 'null', 'ip', 'geo_point', 'geo_shape', 'date']
            );
            if ($isCoreType || $array['type'] === 'string'
                && array_key_exists('index', $array) && $array['index'] === 'not_analyzed'
            ) {
                $array['docValues'] = true;
            }
        }

        return array_combine(
            array_map(
                function ($key) {
                    return Caser::snake($key);
                },
                array_keys($array)
            ),
            array_values($array)
        );
    }
}
