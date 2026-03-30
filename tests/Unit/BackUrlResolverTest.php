<?php

namespace Tests\Unit;

use App\Support\BackUrlResolver;
use Tests\TestCase;

class BackUrlResolverTest extends TestCase
{
    public function test_is_list_index_url(): void
    {
        $logsIndex = route('logs.index');
        $archivedIndex = route('archived-logs.index');

        $this->assertTrue(BackUrlResolver::isListIndexUrl($logsIndex, 'logs.index'));
        $this->assertTrue(BackUrlResolver::isListIndexUrl($logsIndex.'?page=2', 'logs.index'));
        $this->assertFalse(BackUrlResolver::isListIndexUrl($logsIndex.'/99', 'logs.index'));

        $this->assertTrue(BackUrlResolver::isListIndexUrl($archivedIndex, 'archived-logs.index'));
        $this->assertFalse(BackUrlResolver::isListIndexUrl($archivedIndex.'/3', 'archived-logs.index'));
    }

    public function test_is_active_log_detail_url(): void
    {
        $detail = route('logs.show', ['id' => 7]);

        $this->assertTrue(BackUrlResolver::isActiveLogDetailUrl($detail));
        $this->assertFalse(BackUrlResolver::isActiveLogDetailUrl(route('logs.index')));
    }
}
