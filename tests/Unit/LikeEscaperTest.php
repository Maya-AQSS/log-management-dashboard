<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\LikeEscaper;
use PHPUnit\Framework\TestCase;

class LikeEscaperTest extends TestCase
{
    /**
     * @dataProvider patternProvider
     */
    public function test_escape_like_pattern(string $input, string $expected): void
    {
        $this->assertSame($expected, LikeEscaper::escapeLikePattern($input));
    }

    public static function patternProvider(): array
    {
        return [
            ['foo', 'foo'],
            ['100% seguro', '100!% seguro'],
            ['_hidden', '!_hidden'],
            ['!important', '!!important'],
            ['50%!_!', '50!%!!!_!!'],
            ['normal', 'normal'],
        ];
    }
}
