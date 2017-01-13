<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ElasticsearchBundle\Tests\Unit\ParamsModifier;

use ONGR\ElasticsearchBundle\ParamsModifier\QueryLogModifier;

class QueryLogModifierTest extends \PHPUnit_Framework_TestCase
{
    public function testApply()
    {
        $key = <<<'KEY'
test_prefixectionMe:invokeArgs-ork_Test:runTest-ork_Test:runBare-k_TestRe:run-ork_Test:run-rk_TestS:run-I_TestRu:doRun-xtUI_Com:run-xtUI_Com:main
KEY;
        $paramsLogService = new QueryLogModifier();
        $paramsLogService->setMaxDeep(12);
        $paramsLogService->setPrefix('test_prefix');
        $params = $paramsLogService->apply([
            'test_1' => true,
        ], 'test');
        $this->assertEquals([
            'body' => [
                'stats' => [$key]
            ],
            'test_1' => true,
        ], $params);
    }

    public function testApplyFiltered()
    {
        $paramsLogService = new QueryLogModifier();
        $paramsLogService->setMaxDeep(3);
        $paramsLogService->setIgnoreFilter(['ONGR\ElasticsearchBundle']);
        $paramsLogService->setPrefix('test_prefix');
        $params = $paramsLogService->apply([
            'test_1' => true,
        ], 'test');
        $this->assertEquals([
            'test_1' => true,
        ], $params);
    }
}
