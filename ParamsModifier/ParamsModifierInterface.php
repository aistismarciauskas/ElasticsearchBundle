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

interface ParamsModifierInterface
{
    const TYPE_SEARCH = 'search';

    const TYPE_SCROLL = 'scroll';

    /**
     * @param array  $params
     * @param string $type
     *
     * @return mixed
     */
    public function apply(array $params, $type);
}
