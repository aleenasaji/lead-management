<?php

namespace Tests\Unit;

use App\Enums\LeadStatus;
use PHPUnit\Framework\TestCase;

class LeadStatusTest extends TestCase
{
    public function test_lead_status_values_match_the_assessment(): void
    {
        $this->assertSame(
            ['New', 'Contacted', 'Converted'],
            array_column(LeadStatus::cases(), 'value'),
        );
    }
}
