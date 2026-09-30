<?php

namespace Tests\Feature;

use Tests\TestCase;

class VisitorMarketTest extends TestCase
{
    public function test_home_page_shows_sterling_prices_by_default(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('From £10/month')
            ->assertSee('£25')
            ->assertDontSee('₦20,000');
    }

    public function test_home_page_shows_naira_prices_for_nigeria(): void
    {
        $this->withHeaders(['CF-IPCountry' => 'NG'])
            ->get('/')
            ->assertOk()
            ->assertSee('From ₦20,000/month')
            ->assertSee('₦50,000')
            ->assertSee('₦120,000')
            ->assertSee('₦0 per booking')
            ->assertDontSee('From £10/month');
    }

    public function test_home_page_shows_naira_prices_from_country_cookie(): void
    {
        $this->withUnencryptedCookie('cutcost_country', 'NG')
            ->get('/')
            ->assertOk()
            ->assertSee('₦20,000');
    }

    public function test_register_page_shows_naira_prices_for_nigeria(): void
    {
        $this->withHeaders(['CF-IPCountry' => 'NG'])
            ->get('/register')
            ->assertOk()
            ->assertSee('₦20,000')
            ->assertSee('₦50,000');
    }
}
