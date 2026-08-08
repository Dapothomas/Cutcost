<?php

namespace Tests\Unit;

use App\Support\BrandColor;
use PHPUnit\Framework\TestCase;

class BrandColorTest extends TestCase
{
    public function test_normalize_accepts_hex_with_or_without_hash(): void
    {
        $this->assertSame('#0F766E', BrandColor::normalize('#0f766e'));
        $this->assertSame('#0F766E', BrandColor::normalize('0f766e'));
    }

    public function test_normalize_rejects_invalid_values(): void
    {
        $this->assertNull(BrandColor::normalize('red'));
        $this->assertNull(BrandColor::normalize('#fff'));
        $this->assertNull(BrandColor::normalize(null));
    }

    public function test_tokens_are_generated_for_valid_hex(): void
    {
        $tokens = BrandColor::tokens('#0F766E');

        $this->assertIsArray($tokens);
        $this->assertArrayHasKey('primary', $tokens);
        $this->assertArrayHasKey('sidebar_background', $tokens);
        $this->assertArrayHasKey('background', $tokens);
        $this->assertMatchesRegularExpression('/^\d+ \d+% \d+%$/', $tokens['primary']);
        $this->assertMatchesRegularExpression('/^\d+ \d+% \d+%$/', $tokens['sidebar_background']);
    }
}
