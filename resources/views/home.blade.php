@php
    $naira = $currency === 'NGN';
    $demoPrice = fn (int $gbp, int $ngn) => $naira ? '₦'.number_format($ngn) : '£'.$gbp;

    $appHost = parse_url((string) config('app.url'), PHP_URL_HOST) ?: '';
    $bookLink = (in_array($appHost, ['', 'localhost', '127.0.0.1'], true) ? 'cutcost.app' : $appHost).'/book/north-and-co';

    $plans = [
        [
            'name' => 'Starter',
            'slug' => 'starter',
            'price' => \App\Enums\SubscriptionPlan::Starter->priceLabel($currency),
            'description' => 'For solo stylists getting organised.',
            'featured' => false,
            'features' => ['1 stylist seat', 'Client CRM & notes', 'Services & pricing', 'Manual bookings', 'Private booking link'],
            'cta' => 'Get started',
        ],
        [
            'name' => 'Shop',
            'slug' => 'shop',
            'price' => \App\Enums\SubscriptionPlan::Shop->priceLabel($currency),
            'description' => 'Everything a busy shop floor needs.',
            'featured' => true,
            'features' => ['Up to 5 stylists', 'Full team scheduling', 'Client self-booking', 'Today’s dashboard', 'Email support'],
            'cta' => 'Start with Shop',
        ],
        [
            'name' => 'Studio',
            'slug' => 'studio',
            'price' => \App\Enums\SubscriptionPlan::Studio->priceLabel($currency),
            'description' => 'For multi-chair salons and growing teams.',
            'featured' => false,
            'features' => ['Unlimited stylists', 'Multiple services & tiers', 'Priority support', 'Advanced booking rules', 'Dedicated onboarding'],
            'cta' => 'Talk to us',
        ],
    ];

    /*
     * Illustration system. Every character on the page is drawn by $person in a
     * 120×292 local box (head ≈ 26–64, shoulders ≈ 86, waist ≈ 162, feet ≈ 292),
     * so proportions, line weight and shading stay consistent between scenes.
     */
    $skinTones = [
        'a' => ['#f3d9c6', '#e2bca3'],
        'b' => ['#dcaa84', '#c68f68'],
        'c' => ['#a9724d', '#915d3c'],
        'd' => ['#70472f', '#5b3824'],
    ];

    $person = function (array $o) use ($skinTones): string {
        $o += [
            'x' => 0, 'y' => 0, 's' => 1, 'flip' => false,
            'skin' => 'a', 'hair' => 'fade', 'hc' => 'hsl(var(--foreground))',
            'top' => 'hsl(var(--primary))', 'outfit' => 'tee', 'legs' => '#414755',
            'prop' => null, 'beard' => false, 'glasses' => false,
            'mood' => 'smile', 'full' => false, 'cls' => '', 'blink' => 0,
        ];
        [$sk, $sh] = $skinTones[$o['skin']];
        $hc = $o['hc'];
        $top = $o['top'];
        $ink = 'hsl(var(--foreground))';
        $s = $o['s'];
        $tf = $o['flip']
            ? 'translate('.($o['x'] + 120 * $s).' '.$o['y'].') scale(-'.$s.' '.$s.')'
            : 'translate('.$o['x'].' '.$o['y'].') scale('.$s.')';
        $hijab = $o['hair'] === 'hijab';
        $full = $o['full'];

        $back = match ($o['hair']) {
            'afro' => "<circle cx='60' cy='38' r='24' fill='{$hc}'/>",
            'bun' => "<circle cx='60' cy='18' r='9' fill='{$hc}'/>",
            'long' => "<path d='M42 44C39 27 48 20 60 20S81 27 78 44L83 102C72 108 48 108 37 102Z' fill='{$hc}'/>",
            'bob' => "<path d='M41 62C36 36 46 21 60 21S84 36 79 62C75 65 70 64 68 60H52C50 64 45 65 41 62Z' fill='{$hc}'/>",
            'locs' => "<g fill='{$hc}'><rect x='39' y='34' width='7' height='60' rx='3.5'/><rect x='46' y='40' width='6' height='52' rx='3'/><rect x='68' y='40' width='6' height='52' rx='3'/><rect x='74' y='34' width='7' height='60' rx='3.5'/></g>",
            'hijab' => "<path d='M37 58C35 32 46 18 60 18S85 32 83 58C85 72 90 84 97 94H23C30 84 35 72 37 58Z' fill='{$hc}'/><path d='M42 50C43 64 50 72 60 72S77 64 78 50C82 64 82 76 76 86H44C38 76 38 64 42 50Z' fill='{$hc}'/><path d='M60 72C70 72 77 64 78 50C82 64 82 76 76 86H66Z' fill='{$ink}' opacity='.12'/>",
            default => '',
        };

        $front = match ($o['hair']) {
            'fade' => "<path d='M44.6 41C43.2 27 51 21.5 60 21.5S76.8 27 75.4 41C73 34 67 31 60 31S47 34 44.6 41Z' fill='{$hc}'/><path d='M45 43C44.6 37.5 45.2 34.5 47 33V48ZM75 43C75.4 37.5 74.8 34.5 73 33V48Z' fill='{$hc}' opacity='.35'/>",
            'buzz' => "<path d='M45 40C44.2 29 51 24.6 60 24.6S75.8 29 75 40C72 35.4 66 33.6 60 33.6S48 35.4 45 40Z' fill='{$hc}' opacity='.85'/>",
            'afro' => "<path d='M44.5 41C46 30 52 27 60 27S74 30 75.5 41C72 36 66 34.5 60 34.5S48 36 44.5 41Z' fill='{$hc}'/>",
            'curls' => "<g fill='{$hc}'><circle cx='46' cy='36' r='5.5'/><circle cx='51' cy='29' r='6'/><circle cx='59' cy='25.5' r='6.5'/><circle cx='67' cy='27' r='6'/><circle cx='73' cy='33' r='5.5'/><circle cx='75.5' cy='40' r='4'/><circle cx='44.5' cy='42' r='3.6'/></g>",
            'bun' => "<path d='M44 43C42.4 28 51 23 60 23S77.6 28 76 43C73 34.5 66 30.5 58 31.5C52 32.5 47 36 44 43Z' fill='{$hc}'/>",
            'long' => "<path d='M44 47C42 29 50 23 60 23S78 29 76 47C73 37 68 33 62 32C57 37 51 40 44 47Z' fill='{$hc}'/>",
            'bob' => "<path d='M44 45C43 30 51 24 60 24S77 30 76 45C71 38 64 33 55 33C51 36 47 40 44 45Z' fill='{$hc}'/>",
            'locs' => "<path d='M43.5 42C42.5 27 51 21.5 60 21.5S77.5 27 76.5 42C73 35 67 32 60 32S47 35 43.5 42Z' fill='{$hc}'/>",
            'hijab' => "<path d='M43 41C44.5 29.5 51.5 25.5 60 25.5S75.5 29.5 77 41C73 35 67 32 60 32S47 35 43 41Z' fill='{$hc}'/>",
            default => '',
        };

        $torso = $full
            ? "<path d='M28 166C24 132 25 104 46 86.5L53.5 83L60 93L66.5 83L74 86.5C95 104 96 132 92 166Z' fill='{$top}'/><path d='M79 90C93 104 95 134 92 166H84C86 134 85 108 79 90Z' fill='{$ink}' opacity='.1'/>"
            : "<path d='M20 164C19 120 28 95 46 86.5L53.5 83L60 93L66.5 83L74 86.5C92 95 101 120 100 164Z' fill='{$top}'/><path d='M80 91C94 101 100 124 100 164H88C89 130 87 108 80 91Z' fill='{$ink}' opacity='.1'/>";

        $apronEnd = $full ? 214 : 164;
        $outfit = match ($o['outfit']) {
            'apron' => "<path d='M43 106H77L82 {$apronEnd}H38Z' fill='{$ink}'/><path d='M48 88L45 106M72 88L75 106' stroke='{$ink}' stroke-width='2.4'/><rect x='52' y='120' width='16' height='11' rx='2' fill='hsl(var(--card))' opacity='.16'/>",
            'jacket' => "<path d='M53.5 83L60 93L66.5 83L64 132H56Z' fill='hsl(var(--card))'/><path d='M53.5 83L60 96L57 134L44 97ZM66.5 83L60 96L63 134L76 97Z' fill='{$ink}' opacity='.18'/>",
            'knit' => "<path d='M53.5 83L60 93L66.5 83Z' fill='{$top}'/><rect x='51.5' y='75' width='17' height='13' rx='5' fill='{$top}'/><path d='M53 79.5H67M53 83.5H67' stroke='{$ink}' opacity='.14'/>",
            'tunic' => "<path d='M60 93V160' stroke='{$ink}' opacity='.14' stroke-width='1.4'/><circle cx='60' cy='104' r='1.4' fill='{$ink}' opacity='.25'/><circle cx='60' cy='116' r='1.4' fill='{$ink}' opacity='.25'/>",
            'cape' => "<path d='M60 77C34 79 16 104 10 164H110C104 104 86 79 60 77Z' fill='hsl(var(--card))' stroke='hsl(var(--border))' stroke-width='1.6'/><path d='M49 79Q60 86 71 79' stroke='hsl(var(--pink))' stroke-width='3' stroke-linecap='round'/><path d='M40 110C38 130 36 146 34 164M82 110C84 130 86 146 88 164' stroke='hsl(var(--border))' stroke-width='1.4'/>",
            default => '',
        };

        $legs = $full
            ? "<path d='M31 160H89L86 284H67L61.5 192H58.5L53 284H34Z' fill='{$o['legs']}'/><rect x='30' y='281' width='27' height='10' rx='5' fill='{$ink}'/><rect x='63' y='281' width='27' height='10' rx='5' fill='{$ink}'/>"
            : '';

        $armL = "<path d='M23 104C17 128 18 152 22 180H32C30 154 31 130 36 110Z' fill='{$top}'/><ellipse cx='27' cy='184' rx='5.5' ry='6.5' fill='{$sk}'/>";
        $armR = "<path d='M97 104C103 128 102 152 98 180H88C90 154 89 130 84 110Z' fill='{$top}'/><ellipse cx='93' cy='184' rx='5.5' ry='6.5' fill='{$sk}'/>";
        $hanging = '';
        if ($full && ! in_array($o['prop'], ['tablet', 'stress'], true)) {
            $hanging = $armL.($o['prop'] ? '' : $armR);
        }

        $prop = match ($o['prop']) {
            'phone' => "<path d='M92 98C101 110 100 126 90 134L76 131C82 125 86 117 84 106Z' fill='{$top}'/><rect x='64' y='104' width='16' height='27' rx='3.2' fill='{$ink}'/><rect x='65.8' y='106.4' width='12.4' height='20.6' rx='1.8' fill='hsl(var(--primary))'/><rect x='67.6' y='110' width='8.8' height='2.2' rx='1.1' fill='hsl(var(--card))' opacity='.85'/><rect x='67.6' y='114.4' width='6' height='2.2' rx='1.1' fill='hsl(var(--card))' opacity='.55'/><path d='M70 124C74 121 80 122 81 127C82 132 77 135 72 134C68 133 66 128 70 124Z' fill='{$sk}'/><ellipse class='lp-thumb' cx='72.5' cy='121' rx='2.3' ry='3.2' fill='{$sh}'/>",
            'tablet' => "<path d='M24 104C19 120 24 133 37 135L43 127C35 125 32 117 34 108Z' fill='{$top}'/><path d='M96 104C101 120 96 133 83 135L77 127C85 125 88 117 86 108Z' fill='{$top}'/><rect x='37' y='105' width='46' height='32' rx='4' fill='{$ink}'/><rect x='39.5' y='107.5' width='41' height='27' rx='2.5' fill='hsl(var(--card))'/><rect x='42.5' y='110.5' width='14' height='3' rx='1.5' fill='hsl(var(--primary))'/><rect x='42.5' y='116.5' width='35' height='3.6' rx='1.5' fill='hsl(var(--accent))'/><rect class='lp-tab-row' x='42.5' y='122' width='35' height='3.6' rx='1.5' fill='hsl(var(--primary) / .3)'/><rect x='42.5' y='127.5' width='24' height='3.6' rx='1.5' fill='hsl(var(--accent))'/><ellipse cx='40' cy='128' rx='4.8' ry='5.6' fill='{$sk}'/><ellipse cx='80' cy='128' rx='4.8' ry='5.6' fill='{$sk}'/>",
            'clippers' => "<path d='M92 98C104 104 112 116 110 128L98 129C98 120 94 112 86 107Z' fill='{$top}'/><g class='lp-clip'><rect x='98.5' y='104' width='9' height='24' rx='3.5' fill='#414755'/><rect x='98' y='100.5' width='10' height='5' rx='1.2' fill='hsl(var(--muted-foreground))'/><rect x='101' y='112' width='4' height='7' rx='2' fill='hsl(var(--primary))'/></g><ellipse cx='103' cy='128' rx='5.6' ry='5' fill='{$sk}'/>",
            'scissors' => "<path d='M92 98C104 104 112 116 110 128L98 129C98 120 94 112 86 107Z' fill='{$top}'/><g class='lp-snip' stroke='hsl(var(--muted-foreground))' stroke-width='2.2' stroke-linecap='round'><path d='M103 126L98 101M103 126L109 102'/></g><ellipse cx='103' cy='128' rx='5.6' ry='5' fill='{$sk}'/>",
            'stress' => "<path d='M24 164C22 124 28 94 38 70L46 72C40 96 36 124 36 164Z' fill='{$top}'/><path d='M96 164C98 124 92 94 82 70L74 72C80 96 84 124 84 164Z' fill='{$top}'/><ellipse cx='44' cy='58' rx='5' ry='7' fill='{$sk}'/><ellipse cx='76' cy='58' rx='5' ry='7' fill='{$sk}'/>",
            default => '',
        };

        $ears = $hijab ? '' : "<ellipse cx='44.6' cy='47' rx='2.8' ry='4.3' fill='{$sh}'/><ellipse cx='75.4' cy='47' rx='2.8' ry='4.3' fill='{$sh}'/>";
        $face = "<path d='M45 44.5C45 31.5 51.5 26 60 26S75 31.5 75 44.5C75 56.5 68.5 64 60 64S45 56.5 45 44.5Z' fill='{$sk}'/><path d='M69 30C73.5 33 75 38 75 44.5C75 56.5 68.5 64 60 64C66 60 70 53 70 44.5C70 38 69.5 33 69 30Z' fill='{$sh}' opacity='.5'/>";
        $beard = $o['beard'] ? "<path d='M45.2 47C45.6 60 52 67.5 60 67.5S74.4 60 74.8 47C73 53.5 68.5 56 60 56S47 53.5 45.2 47Z' fill='{$hc}'/><path d='M55 53.8Q60 51.4 65 53.8Q60 55.4 55 53.8Z' fill='{$hc}'/>" : '';
        $browPath = $o['mood'] === 'stress' ? 'M51.5 41.2L56 39.4M68.5 41.2L64 39.4' : 'M51.6 41Q54 39.6 56.4 40.8M63.6 40.8Q66 39.6 68.4 41';
        $brows = "<path d='{$browPath}' stroke='{$ink}' stroke-opacity='.72' stroke-width='1.3' stroke-linecap='round'/>";
        $eyes = $o['mood'] === 'calm'
            ? "<path d='M52.2 46.6Q54 44.8 55.8 46.6M64.2 46.6Q66 44.8 67.8 46.6' stroke='{$ink}' stroke-width='1.4' stroke-linecap='round'/>"
            : "<g class='lp-eye' style='animation-delay:{$o['blink']}s'><ellipse cx='54' cy='46' rx='1.5' ry='1.9' fill='{$ink}'/><ellipse cx='66' cy='46' rx='1.5' ry='1.9' fill='{$ink}'/></g>";
        $nose = "<path d='M60.6 46.8Q62.2 50.6 59.6 51.4' stroke='{$sh}' stroke-width='1.3' stroke-linecap='round'/>";
        $mouthStroke = $o['beard'] ? 'hsl(var(--card) / .75)' : 'hsl(var(--foreground) / .5)';
        $mouth = $o['mood'] === 'stress'
            ? "<path d='M56.4 57Q58.2 55.6 60 57T63.6 57' stroke='{$mouthStroke}' stroke-width='1.3' stroke-linecap='round'/>"
            : "<path d='M56.3 55.4Q60 58.4 63.7 55.4' stroke='{$mouthStroke}' stroke-width='1.3' stroke-linecap='round'/>";
        $glasses = $o['glasses'] ? "<g stroke='{$ink}' stroke-width='1.3'><rect x='49' y='41.6' width='9.6' height='7.6' rx='3'/><rect x='61.4' y='41.6' width='9.6' height='7.6' rx='3'/><path d='M58.6 44.6H61.4'/></g>" : '';
        $neck = "<path d='M53.5 58H66.5V82L60 90L53.5 82Z' fill='{$sh}'/>";

        return "<g class='lp-person {$o['cls']}' transform='{$tf}' fill='none'>"
            .$legs
            .($hijab ? '' : $back)
            .$neck.$torso.$outfit.$hanging
            .($hijab ? $back : '')
            .$ears.$face.$beard.$brows.$eyes.$nose.$mouth.$glasses.$front
            .$prop
            .'</g>';
    };

    $avatar = fn (array $o, string $cls = 'h-8 w-8') => "<svg viewBox='36 18 48 48' class='lp-av {$cls}' aria-hidden='true'>".$person($o).'</svg>';

    // Barber chair, origin at the centre of its base on the floor. Split so a seated client can sit between.
    $chairBack = fn (float $x, float $y, float $s = 1) => "<g transform='translate({$x} {$y}) scale({$s})'><ellipse cx='0' cy='-9' rx='60' ry='10' fill='#414755'/><rect x='-8' y='-78' width='16' height='66' fill='hsl(var(--muted-foreground))'/><rect x='-36' y='-44' width='72' height='8' rx='4' fill='hsl(var(--muted-foreground))'/><rect x='-50' y='-196' width='100' height='104' rx='22' fill='hsl(var(--foreground))'/><rect x='-24' y='-214' width='48' height='20' rx='10' fill='#414755'/><path d='M-38 -184C-38 -190 -34 -192 -28 -192' stroke='hsl(var(--card) / .18)' stroke-width='5' stroke-linecap='round'/></g>";
    $chairFront = fn (float $x, float $y, float $s = 1) => "<g transform='translate({$x} {$y}) scale({$s})'><rect x='-62' y='-106' width='124' height='30' rx='13' fill='hsl(var(--foreground))'/><rect x='-76' y='-128' width='32' height='11' rx='5.5' fill='#414755'/><rect x='44' y='-128' width='32' height='11' rx='5.5' fill='#414755'/><rect x='-66' y='-118' width='6' height='16' fill='#414755'/><rect x='60' y='-118' width='6' height='16' fill='#414755'/></g>";
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-market="{{ $currency }}">
    <head>
        @include('partials.market-detect')
        <script>document.documentElement.classList.add('lp-js');</script>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="description" content="Cutcost is the private CRM and booking platform for salons, barbershops and stylists — clients, staff, services and appointments in one place, with a booking link that belongs to your business.">
        <title>Cutcost — Run your shop. Fill your chair.</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/blade.js'])
        <style>
            /* ================= Cutcost homepage — page-owned styles ================= */
            html { scroll-behavior: smooth; }
            section[id] { scroll-margin-top: 4.5rem; }

            .lp {
                --pink: 340 78% 66%;
                --pink-deep: 339 58% 40%;
                --blush: 343 80% 96%;
                color: hsl(var(--foreground));
                background: hsl(var(--background));
                overflow-x: clip;
            }

            .lp-rose { color: hsl(var(--pink-deep)); }

            .lp-hl {
                background-image: linear-gradient(hsl(var(--pink) / 0.55), hsl(var(--pink) / 0.55));
                background-repeat: no-repeat;
                background-size: 100% 0.14em;
                background-position: 0 86%;
            }

            .lp-wrap {
                width: 100%;
                max-width: 1200px;
                margin-inline: auto;
                padding-inline: 1.25rem;
            }

            @media (min-width: 640px) {
                .lp-wrap { padding-inline: 2rem; }
            }

            /* ---------- Type ---------- */
            .lp-display {
                font-size: clamp(2.85rem, 7vw, 5.4rem);
                font-weight: 800;
                letter-spacing: -0.048em;
                line-height: 0.94;
            }

            .lp-h2 {
                font-size: clamp(2.05rem, 4.4vw, 3.5rem);
                font-weight: 800;
                letter-spacing: -0.042em;
                line-height: 1.02;
                text-wrap: balance;
            }

            .lp-h3 {
                font-size: clamp(1.35rem, 2.1vw, 1.7rem);
                font-weight: 700;
                letter-spacing: -0.028em;
                line-height: 1.15;
            }

            .lp-kicker {
                display: inline-flex;
                align-items: center;
                gap: 0.65rem;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: 0.13em;
                text-transform: uppercase;
                color: hsl(var(--muted-foreground));
            }

            .lp-kicker b {
                font-weight: 700;
                color: hsl(var(--pink-deep));
                letter-spacing: 0.04em;
            }

            .lp-kicker i {
                display: block;
                width: 22px;
                height: 2px;
                border-radius: 99px;
                background: hsl(var(--pink) / 0.8);
            }

            .lp-lede {
                max-width: 38rem;
                font-size: clamp(1.02rem, 1.25vw, 1.14rem);
                line-height: 1.65;
                color: hsl(var(--muted-foreground));
            }

            .lp-label {
                font-size: 10.5px;
                font-weight: 600;
                letter-spacing: 0.12em;
                text-transform: uppercase;
                color: hsl(var(--muted-foreground));
            }

            /* ---------- Nav ---------- */
            .lp-nav {
                position: sticky;
                top: 0;
                z-index: 50;
                border-bottom: 1px solid transparent;
                background: hsl(var(--background) / 0.88);
                backdrop-filter: saturate(1.4) blur(12px);
                transition: border-color 0.2s ease;
            }

            .lp-nav.is-solid { border-color: hsl(var(--border)); }

            .lp-menu {
                max-height: 0;
                overflow: hidden;
                opacity: 0;
                background: hsl(var(--background));
                transition: max-height 0.35s ease, opacity 0.25s ease;
            }

            .lp-menu.is-open { max-height: 24rem; opacity: 1; }

            .lp-burger span {
                display: block;
                width: 18px;
                height: 1.5px;
                background: hsl(var(--foreground));
                transition: transform 0.25s ease, opacity 0.2s ease;
            }

            .lp-burger.is-open span:nth-child(1) { transform: translateY(6.5px) rotate(45deg); }
            .lp-burger.is-open span:nth-child(2) { opacity: 0; }
            .lp-burger.is-open span:nth-child(3) { transform: translateY(-6.5px) rotate(-45deg); }

            /* ---------- Buttons & surfaces ---------- */
            .lp-btn {
                transition: transform 0.16s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.16s ease, background-color 0.15s ease;
            }

            .lp-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 16px -8px hsl(var(--primary) / 0.55); }
            .lp-btn.btn-secondary:hover { box-shadow: 0 6px 16px -10px rgb(16 24 40 / 0.25); }
            .lp-btn:active { transform: translateY(0) scale(0.975); box-shadow: none; transition-duration: 0.06s; }

            .lp-btn-lg {
                height: 3rem;
                padding-inline: 1.5rem;
                border-radius: 0.7rem;
                font-size: 15px;
            }

            .lp-lift {
                transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.3s ease, border-color 0.3s ease;
            }

            .lp-lift:hover {
                transform: translateY(-4px);
                box-shadow: 0 18px 40px -22px rgb(16 24 40 / 0.28);
                border-color: hsl(var(--primary) / 0.25);
            }

            .ui {
                overflow: hidden;
                border: 1px solid hsl(var(--border));
                border-radius: 14px;
                background: hsl(var(--card));
                box-shadow: 0 1px 2px rgb(16 24 40 / 0.04), 0 24px 48px -32px rgb(16 24 40 / 0.3);
            }

            .ui-bar {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                border-bottom: 1px solid hsl(var(--border));
                padding: 0.7rem 1rem;
                font-size: 12.5px;
                font-weight: 600;
            }

            .lp-av {
                flex-shrink: 0;
                overflow: hidden;
                border-radius: 999px;
                background: hsl(var(--accent));
            }

            .lp-pill {
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                border-radius: 999px;
                border: 1px solid hsl(var(--border));
                background: hsl(var(--card));
                padding: 0.3rem 0.7rem;
                font-size: 12px;
                font-weight: 500;
            }

            /* ---------- Reveal ---------- */
            .lp-js .lp-rv {
                opacity: 0;
                transform: translateY(26px);
                transition: opacity 0.8s cubic-bezier(0.22, 1, 0.36, 1), transform 0.8s cubic-bezier(0.22, 1, 0.36, 1);
                transition-delay: calc(var(--i, 0) * 90ms);
            }

            .lp-js .lp-rv.is-in { opacity: 1; transform: none; }

            /* ---------- Idle motion (finite, except a slow blink) ---------- */
            .lp-eye {
                transform-box: fill-box;
                transform-origin: center;
                animation: lp-blink 6s ease-in-out infinite;
            }

            @keyframes lp-blink {
                0%, 94%, 100% { transform: scaleY(1); }
                96.5% { transform: scaleY(0.1); }
            }

            .lp-clip {
                transform-box: fill-box;
                transform-origin: 50% 100%;
                animation: lp-buzz 2.4s ease-in-out 1.2s 4;
            }

            @keyframes lp-buzz {
                0%, 60%, 100% { transform: rotate(0); }
                70% { transform: rotate(-8deg); }
                80% { transform: rotate(6deg); }
                90% { transform: rotate(-4deg); }
            }

            .lp-snip {
                transform-box: fill-box;
                transform-origin: 50% 100%;
                animation: lp-snip 1.6s ease-in-out 1s 5;
            }

            @keyframes lp-snip {
                0%, 50%, 100% { transform: scaleX(1); }
                70% { transform: scaleX(0.35); }
            }

            .lp-thumb {
                transform-box: fill-box;
                transform-origin: center bottom;
                animation: lp-tap 1.1s ease-in-out 0.9s 3;
            }

            @keyframes lp-tap {
                0%, 100% { transform: translateY(0); }
                45% { transform: translateY(-2.4px); }
            }

            /* ---------- Hero ---------- */
            .lp-line {
                display: block;
                overflow: hidden;
                padding-bottom: 0.06em;
            }

            .lp-line > span {
                display: block;
                animation: lp-line-up 0.75s cubic-bezier(0.22, 1, 0.36, 1) both;
                animation-delay: calc(var(--i, 0) * 90ms);
            }

            @keyframes lp-line-up {
                from { transform: translateY(104%); }
                to { transform: none; }
            }

            .lp-in {
                animation: lp-fade-up 0.7s cubic-bezier(0.22, 1, 0.36, 1) both;
                animation-delay: calc(var(--i, 0) * 90ms);
            }

            @keyframes lp-fade-up {
                from { opacity: 0; transform: translateY(12px); }
                to { opacity: 1; transform: none; }
            }

            .lp-live {
                position: relative;
                width: 7px;
                height: 7px;
                border-radius: 99px;
                background: hsl(var(--success));
            }

            .lp-live::after {
                content: '';
                position: absolute;
                inset: -4px;
                border-radius: inherit;
                border: 1.5px solid hsl(var(--success) / 0.45);
                animation: lp-ping 2s ease-out 3;
            }

            @keyframes lp-ping {
                from { transform: scale(0.5); opacity: 1; }
                to { transform: scale(1.5); opacity: 0; }
            }

            .lp-stage {
                position: relative;
                width: 100%;
                max-width: 640px;
                margin-inline: auto;
                aspect-ratio: 600 / 560;
            }

            .lp-stage > svg {
                position: absolute;
                inset: 0;
                width: 100%;
                height: 100%;
                overflow: visible;
            }

            .lp-art {
                animation: lp-art-in 0.9s cubic-bezier(0.22, 1, 0.36, 1) both;
                animation-delay: calc(240ms + var(--i, 0) * 120ms);
            }

            @keyframes lp-art-in {
                from { opacity: 0; transform: translateY(18px); }
                to { opacity: 1; transform: none; }
            }

            .lp-flow {
                stroke-dasharray: 5 7;
                animation: lp-flow-dash 1.6s linear 1.2s 3;
            }

            @keyframes lp-flow-dash {
                to { stroke-dashoffset: -48; }
            }

            .lp-fc {
                position: absolute;
                z-index: 3;
                border: 1px solid hsl(var(--border));
                border-radius: 14px;
                background: hsl(var(--card));
                box-shadow: 0 1px 2px rgb(16 24 40 / 0.05), 0 16px 36px -18px rgb(16 24 40 / 0.3);
                padding: 0.7rem 0.8rem;
                font-size: 12px;
                line-height: 1.35;
                animation: lp-fc-in 0.7s cubic-bezier(0.22, 1, 0.36, 1) both;
                animation-delay: calc(520ms + var(--i, 0) * 110ms);
                transition: opacity 0.45s ease, transform 0.55s cubic-bezier(0.22, 1, 0.36, 1);
            }

            @keyframes lp-fc-in {
                from { opacity: 0; transform: translateY(14px) scale(0.97); }
                to { opacity: 1; transform: none; }
            }

            .fc-appt {
                border-color: hsl(var(--pink) / 0.4);
                background: hsl(var(--blush));
            }

            .fc-appt .lp-label { color: hsl(var(--pink-deep)); }

            .fc-chair {
                border-color: hsl(var(--pink) / 0.35);
                background: hsl(var(--blush));
            }

            .fc-day { left: 55%; top: 3%; width: 42%; }
            .fc-appt { left: 27%; top: 37.5%; width: 33%; }
            .fc-client { left: 1%; top: 52%; width: 34%; }
            .fc-staff { left: 2%; top: 81%; width: 31%; }
            .fc-ok { right: 1%; top: 33%; width: 35%; }
            .fc-chair { left: 48.5%; top: 64%; padding: 0.35rem 0.6rem; border-radius: 999px; font-size: 11px; font-weight: 600; }

            .fc-row {
                display: flex;
                align-items: center;
                gap: 0.55rem;
                border-radius: 8px;
                padding: 0.32rem 0.45rem;
                font-size: 11.5px;
                white-space: nowrap;
                transition: background-color 0.4s ease;
            }

            .fc-row b { width: 2.4rem; flex-shrink: 0; font-weight: 600; font-variant-numeric: tabular-nums; }
            .fc-row span { overflow: hidden; text-overflow: ellipsis; }

            .fc-slot { background: hsl(var(--primary) / 0.08); color: hsl(var(--primary-deep)); font-weight: 600; }
            .fc-slot .slot-open { display: none; color: hsl(var(--muted-foreground)); font-weight: 500; }

            .lp-hero.is-pre:not(.is-slot) .fc-slot { background: transparent; border: 1px dashed hsl(var(--border)); }
            .lp-hero.is-pre:not(.is-slot) .slot-open { display: inline; }
            .lp-hero.is-pre:not(.is-slot) .slot-fill { display: none; }
            .lp-hero.is-slot .fc-slot { animation: lp-slot-flash 1.1s ease; }

            @keyframes lp-slot-flash {
                0% { background: hsl(var(--primary) / 0.28); }
                100% { background: hsl(var(--primary) / 0.08); }
            }

            .lp-hero.is-pre .fc-appt,
            .lp-hero.is-pre .fc-ok,
            .lp-hero.is-pre .fc-chair { animation-delay: 0s; }

            .lp-hero.is-pre:not(.is-appt) .fc-appt,
            .lp-hero.is-pre:not(.is-ok) .fc-ok,
            .lp-hero.is-pre:not(.is-ok) .fc-chair {
                opacity: 0;
                transform: translateY(10px) scale(0.96);
                animation: none;
            }

            .lp-tab-row { transition: fill 0.4s ease; }
            .lp-hero.is-pre:not(.is-ok) .lp-tab-row { fill: hsl(var(--accent)); }

            .lp-check path {
                stroke-dasharray: 20;
                stroke-dashoffset: 0;
            }

            .lp-hero.is-ok .lp-check path { animation: lp-draw-check 0.5s ease 0.15s both; }

            @keyframes lp-draw-check {
                from { stroke-dashoffset: 20; }
                to { stroke-dashoffset: 0; }
            }

            .lp-ghost {
                position: absolute !important;
                z-index: 20;
                pointer-events: none;
                margin: 0 !important;
                background: hsl(var(--card)) !important;
                border-color: hsl(var(--primary) / 0.35) !important;
                box-shadow: 0 18px 40px -12px hsl(var(--primary) / 0.45) !important;
            }

            @media (max-width: 639px) {
                .fc-client, .fc-staff { display: none; }
                .fc-day { left: 50%; width: 49%; top: 1%; padding: 0.55rem; }
                .fc-appt { left: 26%; width: 42%; top: 40%; }
                .fc-ok { width: 43%; top: 30%; }
                .fc-chair { left: 42%; top: 66%; }
                .lp-fc { font-size: 10.5px; padding: 0.5rem 0.55rem; border-radius: 11px; }
                .fc-row { font-size: 10px; padding: 0.22rem 0.3rem; gap: 0.35rem; }
                .fc-row b { width: 1.9rem; }
                .fc-row:nth-child(4) { display: none; }
            }

            /* ---------- Value strip ---------- */
            .lp-strip-viz {
                display: flex;
                height: 44px;
                align-items: center;
            }

            .lp-js .lp-strip .sv-slide { opacity: 0; transform: translateX(-10px); }
            .lp-js .lp-strip.is-in .sv-slide { animation: lp-sv-slide 0.6s cubic-bezier(0.22, 1, 0.36, 1) 0.5s forwards; }
            @keyframes lp-sv-slide { to { opacity: 1; transform: none; } }

            .lp-js .lp-strip .sv-drop { opacity: 0; transform: translateY(-8px); }
            .lp-js .lp-strip.is-in .sv-drop { animation: lp-sv-slide 0.55s cubic-bezier(0.22, 1, 0.36, 1) 0.75s forwards; }

            .lp-strip .sv-dot { transition: fill 0.4s ease 1s; }
            .lp-js .lp-strip:not(.is-in) .sv-dot { fill: hsl(var(--input)); }

            .lp-strip .sv-tick { stroke-dasharray: 16; stroke-dashoffset: 0; }
            .lp-js .lp-strip:not(.is-in) .sv-tick { stroke-dashoffset: 16; }
            .lp-js .lp-strip.is-in .sv-tick { animation: lp-draw-check-16 0.5s ease 1.2s both; }
            @keyframes lp-draw-check-16 { from { stroke-dashoffset: 16; } to { stroke-dashoffset: 0; } }

            /* ---------- Chaos → organisation ---------- */
            .lp-chaos { --p: 1; position: relative; }
            .lp-chaos.is-scrub { height: 250vh; }

            .lp-chaos.is-scrub .lp-chaos-pin {
                position: sticky;
                top: 0;
                display: flex;
                height: 100vh;
                align-items: center;
                overflow: hidden;
            }

            .lp-cstage {
                position: relative;
                width: 100%;
                max-width: 560px;
                margin-inline: auto;
                aspect-ratio: 1 / 1;
                container-type: inline-size;
            }

            .lp-chaos.is-scrub .lp-cstage { max-width: min(560px, 74vh); }

            .lp-chip {
                --k: clamp(0, calc((var(--p) - var(--d)) * 2.6), 1);
                position: absolute;
                left: var(--l);
                top: var(--t);
                z-index: 4;
                width: min(40cqw, 210px);
                border: 1px solid hsl(var(--border));
                border-radius: 12px;
                background: hsl(var(--card));
                box-shadow: 0 12px 26px -16px rgb(16 24 40 / 0.35);
                padding: 0.6rem 0.7rem;
                font-size: clamp(10.5px, 2.3cqw, 12.5px);
                line-height: 1.35;
                opacity: calc(1 - var(--k) * 1.2);
                transform: translate(calc(var(--tx) * 1cqw * var(--k)), calc(var(--ty) * 1cqw * var(--k))) rotate(calc(var(--r) * (1 - var(--k)))) scale(calc(1 - var(--k) * 0.5));
            }

            .lp-own-a {
                position: absolute;
                left: 50%;
                bottom: 0;
                z-index: 1;
                width: 44cqw;
                opacity: clamp(0, calc(1 - var(--p) * 2.4), 1);
                transform: translateX(-50%) scale(calc(1 - var(--p) * 0.25));
                transform-origin: 50% 100%;
            }

            .lp-cboard {
                position: absolute;
                left: 9%;
                top: 11%;
                z-index: 2;
                width: 82%;
                opacity: clamp(0, calc((var(--p) - 0.2) * 3), 1);
                transform: translateY(calc((1 - var(--p)) * 5cqw)) scale(calc(0.9 + var(--p) * 0.1));
            }

            .lp-cboard .ui-bar { font-size: clamp(11px, 2.4cqw, 12.5px); }

            .lp-crow {
                --k2: clamp(0, calc((var(--p) - var(--d)) * 4), 1);
                opacity: var(--k2);
                transform: translateX(calc((1 - var(--k2)) * -3cqw));
            }

            .lp-own-b {
                position: absolute;
                left: -3%;
                bottom: -5%;
                z-index: 5;
                width: 27cqw;
                opacity: clamp(0, calc((var(--p) - 0.65) * 4), 1);
                transform: translateY(calc((1 - var(--p)) * 6cqw));
            }

            .lp-cprog-bar { width: calc(var(--p) * 100%); }
            .lp-cprog-a { opacity: calc(1 - var(--p) * 0.55); }
            .lp-cprog-b { color: hsl(var(--primary)); opacity: calc(0.35 + var(--p) * 0.65); }

            @media (max-width: 639px) {
                .lp-crow { padding: 0.45rem 0.7rem; gap: 0.5rem; }
                .lp-crow > span:first-child { width: 3.9rem; font-size: 10.5px; }
                .lp-crow p:first-child { font-size: 11.5px; }
                .lp-crow p + p { font-size: 10px; }
            }

            /* ---------- Features ---------- */
            .lp-cal-col { height: calc(7 * 2.75rem); }

            .lp-cal-col::before {
                content: '';
                position: absolute;
                inset: 0;
                background-image: linear-gradient(hsl(var(--border) / 0.7) 1px, transparent 1px);
                background-size: 100% 2.75rem;
                pointer-events: none;
            }

            .lp-blk {
                position: absolute;
                left: 4px;
                right: 4px;
                overflow: hidden;
                border-left: 3px solid;
                border-radius: 6px;
                padding: 0.3rem 0.45rem;
                font-size: 11px;
                line-height: 1.25;
            }

            .lp-blk-sched { border-color: hsl(var(--primary)); background: hsl(var(--primary) / 0.09); color: hsl(var(--primary-deep)); }
            .lp-blk-done { border-color: hsl(var(--success)); background: hsl(var(--success) / 0.08); color: hsl(var(--success)); }
            .lp-blk-pay { border-color: hsl(var(--warning)); background: hsl(var(--warning) / 0.09); color: hsl(var(--warning)); }
            .lp-blk-noshow { border-color: hsl(var(--destructive)); background: hsl(var(--destructive) / 0.07); color: hsl(var(--destructive)); }

            .lp-switch {
                position: relative;
                width: 34px;
                height: 20px;
                flex-shrink: 0;
                border-radius: 999px;
                background: hsl(var(--input));
            }

            .lp-switch::after {
                content: '';
                position: absolute;
                top: 3px;
                left: 3px;
                width: 14px;
                height: 14px;
                border-radius: 999px;
                background: hsl(var(--card));
                box-shadow: 0 1px 2px rgb(16 24 40 / 0.2);
            }

            .lp-switch.is-on { background: hsl(var(--primary)); }
            .lp-switch.is-on::after { left: 17px; }

            /* ---------- Booking journey ---------- */
            .lp-jstep {
                display: flex;
                flex-direction: column;
                gap: 0.35rem;
                border-top: 2px solid hsl(var(--border));
                padding-top: 0.6rem;
                color: hsl(var(--muted-foreground));
                transition: color 0.3s ease, border-color 0.3s ease;
            }

            @media (min-width: 1024px) {
                .lp-jstep {
                    flex-direction: row;
                    align-items: center;
                    gap: 0.75rem;
                    border-top: 0;
                    border-left: 2px solid hsl(var(--border));
                    padding: 0.55rem 0 0.55rem 0.9rem;
                }
            }

            .lp-jnum {
                display: flex;
                width: 1.6rem;
                height: 1.6rem;
                flex-shrink: 0;
                align-items: center;
                justify-content: center;
                border-radius: 999px;
                background: hsl(var(--secondary));
                font-size: 11.5px;
                font-weight: 700;
                transition: background-color 0.3s ease, color 0.3s ease;
            }

            .lp-jstep.is-done,
            .lp-jstep.is-now { color: hsl(var(--foreground)); border-color: hsl(var(--primary)); }
            .lp-jstep.is-done .lp-jnum,
            .lp-jstep.is-now .lp-jnum { background: hsl(var(--primary)); color: hsl(var(--primary-foreground)); }
            .lp-jstep.is-now .lp-jnum { box-shadow: 0 0 0 4px hsl(var(--primary) / 0.15); }

            .lp-phone {
                width: 284px;
                max-width: 100%;
                border-radius: 2.4rem;
                background: hsl(var(--foreground));
                padding: 10px;
                box-shadow: 0 40px 70px -30px rgb(16 24 40 / 0.45), inset 0 0 0 1.5px hsl(var(--card) / 0.08);
            }

            .lp-phone-screen {
                position: relative;
                overflow: hidden;
                border-radius: 1.85rem;
                background: hsl(var(--card));
            }

            .lp-phone-screen::before {
                content: '';
                position: absolute;
                top: 9px;
                left: 50%;
                width: 76px;
                height: 20px;
                border-radius: 999px;
                background: hsl(var(--foreground));
                transform: translateX(-50%);
            }

            .lp-jscreen {
                position: absolute;
                inset: 0;
                padding: 1rem;
                visibility: hidden;
                opacity: 0;
                transform: translateX(18px);
                transition: opacity 0.35s ease, transform 0.45s cubic-bezier(0.22, 1, 0.36, 1), visibility 0s linear 0.45s;
            }

            .lp-jscreen.is-on {
                visibility: visible;
                opacity: 1;
                transform: none;
                transition-delay: 0s;
            }

            .lp-opt {
                display: flex;
                align-items: center;
                gap: 0.6rem;
                border: 1px solid hsl(var(--border));
                border-radius: 10px;
                padding: 0.6rem 0.7rem;
                font-size: 12.5px;
                transition: border-color 0.25s ease, background-color 0.25s ease, box-shadow 0.25s ease;
            }

            .lp-opt.is-pick {
                border-color: hsl(var(--primary));
                background: hsl(var(--primary) / 0.06);
                box-shadow: 0 0 0 3px hsl(var(--primary) / 0.1);
            }

            .lp-journey-run .lp-jscreen.is-on .lp-opt.is-pick { animation: lp-pick 0.5s ease 0.6s both; }

            @keyframes lp-pick {
                from { border-color: hsl(var(--border)); background: transparent; box-shadow: none; }
            }

            .lp-jcta { transition: opacity 0.3s ease; }
            .lp-journey-done .lp-jcta { opacity: 0.45; }

            .lp-jslot .lp-jslot-open { display: none; }
            .lp-jslot:not(.is-filled) .lp-jslot-open { display: inline; }
            .lp-jslot:not(.is-filled) .lp-jslot-fill { display: none; }
            .lp-jslot.is-filled { background: hsl(var(--primary) / 0.06); }
            .lp-jslot.is-landed { animation: lp-slot-flash 1.2s ease; }

            /* ---------- Marketplace vs direct ---------- */
            .lp-mkt {
                display: grid;
                gap: 0.4rem;
                border-radius: 1.4rem;
                background: hsl(var(--foreground));
                padding: 0.9rem 0.55rem;
            }

            .lp-mkt > div:first-child { background: hsl(var(--card) / 0.1); color: hsl(var(--card) / 0.6); }

            .lp-mkt-row {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                border-radius: 9px;
                background: hsl(var(--card));
                padding: 0.4rem 0.5rem;
            }

            .lp-mkt-row.is-you { opacity: 0.55; outline: 1.5px dashed hsl(var(--primary) / 0.6); outline-offset: -1.5px; }

            .lp-direct {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 0.25rem;
            }

            .lp-direct-node {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 0.5rem;
                text-align: center;
            }

            .lp-direct-line {
                position: relative;
                display: block;
                width: 2px;
                height: 34px;
                overflow: hidden;
                border-radius: 2px;
                background: hsl(var(--primary) / 0.15);
            }

            .lp-direct-line > span {
                position: absolute;
                inset: 0;
                background: hsl(var(--primary));
                transform: scaleY(0);
                transform-origin: top;
                transition: transform 0.6s cubic-bezier(0.22, 1, 0.36, 1);
            }

            .lp-direct-line:nth-of-type(2) > span { transition-delay: 0.45s; }
            .is-in .lp-direct-line > span,
            html:not(.lp-js) .lp-direct-line > span { transform: none; }

            @media (min-width: 640px) {
                .lp-direct { flex-direction: row; justify-content: space-between; align-items: flex-start; }
                .lp-direct-node { flex: 0 0 auto; }
                .lp-direct-line { flex: 1; width: auto; height: 2px; margin-top: 2.5rem; }
                .lp-direct-line > span { transform: scaleX(0); transform-origin: left; }
            }

            /* ---------- How it works ---------- */
            .lp-path {
                position: relative;
                display: grid;
                gap: 2rem;
            }

            .lp-path-track {
                position: absolute;
                left: calc(1.25rem - 1px);
                top: 1.25rem;
                bottom: 1.25rem;
                width: 2px;
                overflow: hidden;
                border-radius: 2px;
                background: hsl(var(--border));
            }

            .lp-path-track > span {
                position: absolute;
                inset: 0;
                background: hsl(var(--primary));
                transform-origin: top;
                transition: transform 2s cubic-bezier(0.45, 0, 0.2, 1);
            }

            .lp-js .lp-path:not(.is-in) .lp-path-track > span { transform: scaleY(0); }

            .lp-pstep {
                position: relative;
                display: grid;
                grid-template-columns: 2.5rem minmax(0, 1fr);
                column-gap: 1rem;
                row-gap: 0.9rem;
            }

            .lp-pstep > :not(.lp-pnode) { grid-column: 2; }

            .lp-pnode {
                position: relative;
                z-index: 1;
                grid-row: span 2;
                display: flex;
                width: 2.5rem;
                height: 2.5rem;
                align-items: center;
                justify-content: center;
                border: 2px solid hsl(var(--primary));
                border-radius: 999px;
                background: hsl(var(--primary));
                font-size: 12px;
                font-weight: 700;
                color: hsl(var(--primary-foreground));
                transition: background-color 0.4s ease, border-color 0.4s ease, color 0.4s ease;
                transition-delay: calc(var(--i) * 380ms);
            }

            .lp-js .lp-path:not(.is-in) .lp-pnode {
                border-color: hsl(var(--border));
                background: hsl(var(--background));
                color: hsl(var(--muted-foreground));
            }

            .lp-pstep > :not(.lp-pnode) {
                transition: opacity 0.6s ease, transform 0.6s cubic-bezier(0.22, 1, 0.36, 1);
                transition-delay: calc(var(--i) * 380ms + 120ms);
            }

            .lp-js .lp-path:not(.is-in) .lp-pstep > :not(.lp-pnode) { opacity: 0; transform: translateY(14px); }

            @media (min-width: 1024px) {
                .lp-path { grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 1.25rem; }
                .lp-path-track { top: calc(1.25rem - 1px); bottom: auto; left: 1.25rem; right: calc((100% - 5rem) / 5 - 1.25rem); width: auto; height: 2px; }
                .lp-path-track > span { transform-origin: left; }
                .lp-js .lp-path:not(.is-in) .lp-path-track > span { transform: scaleX(0); }
                .lp-pstep { display: flex; flex-direction: column; gap: 1.1rem; }
            }

            .lp-pviz {
                position: relative;
                display: flex;
                height: 9rem;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                border: 1px solid hsl(var(--border));
                border-radius: 18px;
                background: hsl(var(--secondary) / 0.8);
            }

            .lp-mini {
                border: 1px solid hsl(var(--border));
                border-radius: 10px;
                background: hsl(var(--card));
                padding: 0.55rem 0.65rem;
            }

            .lp-mini-field {
                display: flex;
                align-items: center;
                margin-top: 0.2rem;
                border: 1px solid hsl(var(--input));
                border-radius: 6px;
                padding: 0.22rem 0.45rem;
                font-size: 11.5px;
                font-weight: 500;
            }

            .lp-caret {
                width: 1.5px;
                height: 12px;
                margin-left: 1px;
                background: hsl(var(--primary));
                animation: lp-caret 1s steps(1) 6;
            }

            @keyframes lp-caret { 50% { opacity: 0; } }

            .lp-mini-chip {
                display: flex;
                justify-content: space-between;
                border: 1px solid hsl(var(--border));
                border-radius: 8px;
                background: hsl(var(--card));
                padding: 0.35rem 0.55rem;
                font-size: 11.5px;
                font-weight: 500;
            }

            .lp-mini-chip b { font-weight: 600; color: hsl(var(--muted-foreground)); }

            .lp-bubble {
                width: fit-content;
                max-width: 100%;
                border: 1px solid hsl(var(--border));
                border-radius: 12px 12px 12px 4px;
                background: hsl(var(--card));
                padding: 0.3rem 0.55rem;
                font-size: 11px;
            }

            .lp-bubble-link {
                border-color: hsl(var(--primary) / 0.25);
                background: hsl(var(--accent));
                color: hsl(var(--primary-deep));
                font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
                font-size: 10.5px;
            }

            .lp-bubble-in {
                margin-left: auto;
                border-color: transparent;
                border-radius: 12px 12px 4px 12px;
                background: hsl(var(--primary));
                color: hsl(var(--primary-foreground));
            }

            .lp-mini-hit { box-shadow: 0 0 0 3px hsl(var(--primary) / 0.2); }

            /* ---------- Day in the life ---------- */
            .lp-moment {
                position: relative;
                display: flex;
                align-items: flex-start;
                gap: 0.9rem;
                border: 1px solid hsl(var(--border));
                border-radius: 16px;
                background: hsl(var(--background));
                padding: 0.9rem 1rem;
                opacity: 0.5;
                transition: opacity 0.4s ease, border-color 0.4s ease, background-color 0.4s ease, box-shadow 0.4s ease;
            }

            .lp-moment.is-on {
                opacity: 1;
                border-color: hsl(var(--primary) / 0.28);
                background: hsl(var(--card));
                box-shadow: 0 12px 26px -20px hsl(var(--primary) / 0.6);
            }

            .lp-micon {
                display: flex;
                width: 2.4rem;
                height: 2.4rem;
                flex-shrink: 0;
                align-items: center;
                justify-content: center;
                border-radius: 12px;
                background: hsl(var(--secondary));
                color: hsl(var(--muted-foreground));
                transition: background-color 0.4s ease, color 0.4s ease;
            }

            .lp-micon svg { width: 18px; height: 18px; }
            .lp-moment.is-on .lp-micon { background: hsl(var(--primary)); color: hsl(var(--primary-foreground)); }

            @media (min-width: 1024px) {
                .lp-day-l .lp-moment { flex-direction: row-reverse; text-align: right; }
                .lp-day-l .lp-moment p:first-child { justify-content: flex-end; }

                .lp-day-l .lp-moment::after,
                .lp-day-r .lp-moment::before {
                    content: '';
                    position: absolute;
                    top: 50%;
                    width: 3rem;
                    height: 2px;
                    background: hsl(var(--border));
                    transition: background-color 0.4s ease;
                }

                .lp-day-l .lp-moment::after { left: 100%; }
                .lp-day-r .lp-moment::before { right: 100%; }
                .lp-moment.is-on::after,
                .lp-moment.is-on::before { background: hsl(var(--primary) / 0.45); }
            }

            .lp-feed { transition: opacity 0.4s ease, transform 0.4s ease, border-color 0.4s ease, background-color 0.4s ease; }
            .lp-feed:not(.is-on) { opacity: 0; transform: translateY(6px); }
            .lp-feed.is-latest { border-color: hsl(var(--primary) / 0.35); background: hsl(var(--primary) / 0.05); }
            .lp-hub-bar { transition: width 0.8s ease; }

            /* ---------- Product showcase ---------- */
            .lp-app { display: grid; grid-template-columns: minmax(0, 1fr); }

            @media (min-width: 768px) {
                .lp-app { grid-template-columns: 212px minmax(0, 1fr); }
            }

            .lp-app-side {
                flex-direction: column;
                border-right: 1px solid hsl(var(--sidebar-border));
                background: hsl(var(--sidebar-background));
                color: hsl(var(--sidebar-foreground));
                padding: 1.25rem 0.75rem;
            }

            .lp-arow.is-new { background: hsl(var(--primary) / 0.05); }
            .lp-arow.is-hold { display: none; }
            .lp-arow.is-drop { animation: lp-row-in 0.8s cubic-bezier(0.22, 1, 0.36, 1); }

            @keyframes lp-row-in {
                from { opacity: 0; transform: translateY(-8px); background: hsl(var(--primary) / 0.2); }
            }

            .lp-callout {
                position: absolute;
                z-index: 2;
                align-items: center;
                gap: 0.45rem;
                border-radius: 999px;
                background: hsl(var(--foreground));
                padding: 0.45rem 0.8rem;
                font-size: 12px;
                font-weight: 600;
                color: hsl(var(--card));
                box-shadow: 0 12px 26px -12px rgb(16 24 40 / 0.5);
            }

            .lp-callout::before {
                content: '';
                width: 6px;
                height: 6px;
                border-radius: 99px;
                background: hsl(var(--primary));
                box-shadow: 0 0 0 3px hsl(var(--primary) / 0.3);
            }

            /* ---------- Pricing ---------- */
            .lp-fee-line {
                stroke-dasharray: 1;
                stroke-dashoffset: 0;
                transition: stroke-dashoffset 1.4s cubic-bezier(0.45, 0, 0.2, 1);
            }

            .lp-js [data-fee]:not(.is-in) .lp-fee-line { stroke-dashoffset: 1; }

            .lp-plan-featured {
                box-shadow: 0 0 0 1px hsl(var(--pink) / 0.28), 0 34px 64px -38px hsl(var(--pink) / 0.55);
            }

            /* ---------- Closing & footer ---------- */
            .lp-btn-ghost-light {
                border: 1px solid hsl(0 0% 100% / 0.22);
                background: transparent;
                color: hsl(0 0% 100%);
            }

            .lp-btn-ghost-light:hover { background: hsl(0 0% 100% / 0.08); }

            .lp-close-tag {
                position: absolute;
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                border-radius: 999px;
                background: hsl(var(--card));
                padding: 0.35rem 0.7rem;
                font-size: 11.5px;
                font-weight: 600;
                color: hsl(var(--foreground));
                box-shadow: 0 8px 20px -10px rgb(16 24 40 / 0.35);
            }

            .lp-flink {
                display: inline-flex;
                min-height: 2rem;
                align-items: center;
                color: hsl(var(--muted-foreground));
                transition: color 0.15s ease;
            }

            .lp-flink:hover { color: hsl(var(--foreground)); }

            /* ---------- Reduced motion ---------- */
            @media (prefers-reduced-motion: reduce) {
                html { scroll-behavior: auto; }
                .lp *,
                .lp *::before,
                .lp *::after {
                    animation: none !important;
                    transition: none !important;
                }
                .lp-js .lp-rv { opacity: 1; transform: none; }
                .lp-js .lp-strip .sv-slide,
                .lp-js .lp-strip .sv-drop { opacity: 1; transform: none; }
                [data-journey-replay] { display: none; }
            }
        </style>
    </head>
    <body class="lp font-sans antialiased">
        {{-- Header --}}
        <header id="lp-nav" class="lp-nav">
            <div class="lp-wrap flex h-16 items-center justify-between">
                <a href="{{ route('home') }}" class="transition-opacity hover:opacity-80" aria-label="Cutcost home">
                    <span class="brand-logo brand-logo-gradient">Cut<span class="brand-logo-accent">cost</span></span>
                </a>
                <nav class="hidden items-center gap-1 md:flex" aria-label="Main">
                    <a href="#features" class="btn-ghost">Product</a>
                    <a href="#booking" class="btn-ghost">Booking page</a>
                    <a href="#how-it-works" class="btn-ghost">How it works</a>
                    <a href="#pricing" class="btn-ghost">Pricing</a>
                    <span class="mx-2 h-5 w-px bg-border" aria-hidden="true"></span>
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-primary lp-btn">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn-ghost">Log in</a>
                        <a href="{{ route('register') }}" class="btn-primary lp-btn ml-1">Create your shop</a>
                    @endauth
                </nav>
                <button type="button" id="lp-menu-btn" class="lp-burger -mr-2 flex h-11 w-11 flex-col items-center justify-center gap-[5px] md:hidden" aria-label="Open menu" aria-expanded="false" aria-controls="lp-menu">
                    <span></span><span></span><span></span>
                </button>
            </div>
            <div id="lp-menu" class="lp-menu border-t border-border md:hidden">
                <div class="lp-wrap flex flex-col gap-1 py-3">
                    <a href="#features" class="btn-ghost h-11 justify-start text-[15px]">Product</a>
                    <a href="#booking" class="btn-ghost h-11 justify-start text-[15px]">Booking page</a>
                    <a href="#how-it-works" class="btn-ghost h-11 justify-start text-[15px]">How it works</a>
                    <a href="#pricing" class="btn-ghost h-11 justify-start text-[15px]">Pricing</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-primary mt-2 h-11 justify-center">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn-ghost h-11 justify-start text-[15px]">Log in</a>
                        <a href="{{ route('register') }}" class="btn-primary mt-2 h-11 justify-center">Create your shop</a>
                    @endauth
                </div>
            </div>
        </header>

        <main>
        {{-- Hero --}}
        <section class="lp-hero" data-hero>
            <div class="lp-wrap grid items-center gap-8 pb-14 pt-8 sm:gap-10 sm:pt-14 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)] lg:gap-6 lg:pb-24 lg:pt-16">
                <div class="relative z-[2]">
                    <p class="lp-kicker lp-in" style="--i:0"><span class="lp-live" aria-hidden="true"></span>Private CRM &amp; booking for salons</p>
                    <h1 class="lp-display mt-5">
                        <span class="lp-line"><span style="--i:1">Run your shop.</span></span>
                        <span class="lp-line"><span class="text-primary" style="--i:2">Fill your <span class="lp-hl">chair.</span></span></span>
                    </h1>
                    <p class="lp-lede lp-in mt-6 max-w-[31rem]" style="--i:3">Manage clients, staff, services and appointments in one place — with a private booking link that belongs to your business.</p>
                    <div class="lp-in mt-8 flex flex-wrap items-center gap-3" style="--i:4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn-primary lp-btn lp-btn-lg">Open dashboard</a>
                        @else
                            <a href="{{ route('register') }}" class="btn-primary lp-btn lp-btn-lg">Create your shop</a>
                        @endauth
                        <a href="#how-it-works" class="btn-secondary lp-btn lp-btn-lg">See how it works</a>
                    </div>
                    <p class="lp-in mt-5 text-[13px] text-muted-foreground" style="--i:5">From {{ $fromPrice }}/month · No marketplace fees · Cancel anytime.</p>
                </div>

                <div class="lp-stage" data-hero-stage role="img" aria-label="A client books a skin fade on their phone. The appointment drops into the shop’s schedule for today, and the stylist beside the empty chair receives the confirmed booking.">
                    <svg viewBox="0 0 600 560" fill="none" aria-hidden="true">
                        <defs>
                            <clipPath id="hero-vignette"><circle cx="130" cy="168" r="91" /></clipPath>
                            <clipPath id="hero-room"><rect x="16" y="64" width="568" height="480" rx="36" /></clipPath>
                        </defs>

                        <rect x="16" y="64" width="568" height="480" rx="36" fill="hsl(var(--accent))" />
                        <g clip-path="url(#hero-room)">
                            <g stroke="hsl(var(--primary) / .07)" stroke-width="2">
                                <path d="M470 64V486M494 64V486M518 64V486M542 64V486M566 64V486" />
                            </g>
                            <rect x="16" y="486" width="568" height="60" fill="hsl(var(--primary) / .07)" />
                            <path d="M16 486H584" stroke="hsl(var(--primary) / .12)" stroke-width="2" />
                        </g>

                        {{-- Mirror station --}}
                        <g class="lp-art" style="--i:0">
                            <path d="M286 344V202A74 74 0 0 1 434 202V344Z" fill="hsl(var(--card))" stroke="hsl(var(--border))" stroke-width="3" />
                            <path d="M298 336V204A62 62 0 0 1 422 204V336Z" fill="hsl(var(--secondary))" />
                            <path d="M318 222L360 180M318 262L400 180" stroke="hsl(var(--card))" stroke-width="7" stroke-linecap="round" />
                            <rect x="268" y="344" width="184" height="12" rx="6" fill="hsl(var(--foreground))" />
                            <rect x="284" y="318" width="14" height="26" rx="4" fill="hsl(var(--pink))" />
                            <rect x="287" y="310" width="8" height="9" rx="2" fill="hsl(var(--foreground))" />
                            <rect x="304" y="327" width="18" height="17" rx="5" fill="hsl(var(--card))" stroke="hsl(var(--border))" stroke-width="2" />
                            <rect x="404" y="337" width="34" height="6" rx="3" fill="hsl(var(--muted-foreground))" />
                        </g>

                        {{-- The empty chair that gets booked --}}
                        <g class="lp-art" style="--i:1">
                            {!! $chairBack(362, 522, 0.86) !!}
                            {!! $chairFront(362, 522, 0.86) !!}
                        </g>

                        {{-- Stylist receiving the booking --}}
                        <g class="lp-art" style="--i:2">
                            <ellipse cx="512" cy="533" rx="52" ry="7" fill="hsl(var(--foreground) / .08)" />
                            {!! $person(['x' => 452, 'y' => 244, 's' => 0.98, 'flip' => true, 'full' => true, 'skin' => 'c', 'hair' => 'locs', 'top' => 'hsl(var(--primary-deep))', 'outfit' => 'apron', 'prop' => 'tablet', 'blink' => 1.4]) !!}
                        </g>

                        {{-- Client booking from their phone --}}
                        <g class="lp-art" style="--i:0">
                            <circle cx="130" cy="168" r="97" fill="hsl(var(--card))" stroke="hsl(var(--border))" stroke-width="3" />
                            <g clip-path="url(#hero-vignette)">
                                <rect x="36" y="76" width="190" height="190" fill="hsl(var(--secondary))" />
                                <rect x="52" y="214" width="156" height="70" rx="22" fill="hsl(var(--primary) / .14)" />
                                <path d="M168 96h40v52h-40z" fill="hsl(var(--card))" stroke="hsl(var(--border))" stroke-width="2" />
                                <path d="M188 96v52M168 122h40" stroke="hsl(var(--border))" stroke-width="2" />
                                {!! $person(['x' => 46, 'y' => 96, 's' => 1.3, 'skin' => 'b', 'hair' => 'curls', 'hc' => '#5b3824', 'top' => 'hsl(var(--pink-deep))', 'outfit' => 'knit', 'prop' => 'phone', 'blink' => 0.4]) !!}
                            </g>
                        </g>

                        {{-- Booking flow lines --}}
                        <path class="lp-flow" d="M214 236C262 236 280 150 330 126" stroke="hsl(var(--pink))" stroke-width="2" stroke-linecap="round" />
                        <path class="lp-flow" d="M520 206C534 226 532 244 520 258" stroke="hsl(var(--pink))" stroke-width="2" stroke-linecap="round" />
                    </svg>

                    <div class="lp-fc fc-day" style="--i:0">
                        <div class="flex items-center justify-between gap-2">
                            <p class="lp-label">Today · Maya</p>
                            <span class="text-[10.5px] font-medium text-muted-foreground">Wed 30</span>
                        </div>
                        <div class="mt-2 space-y-0.5">
                            <div class="fc-row"><b>11:00</b><span>Jordan L. · Skin fade</span></div>
                            <div class="fc-row"><b>12:30</b><span>Sam R. · Beard trim</span></div>
                            <div class="fc-row fc-slot" data-hero-slot><b>14:00</b><span class="slot-open">Open slot</span><span class="slot-fill">Alex M. · Skin fade</span></div>
                            <div class="fc-row"><b>15:15</b><span>Riley K. · Blow dry</span></div>
                        </div>
                    </div>

                    <div class="lp-fc fc-appt" data-hero-appt style="--i:2">
                        <p class="lp-label text-primary">New appointment</p>
                        <p class="mt-1 font-semibold">Skin fade · 45 min</p>
                        <p class="text-muted-foreground">Today 14:00 · with Maya · {{ $demoPrice(25, 8000) }}</p>
                    </div>

                    <div class="lp-fc fc-client flex items-center gap-2.5" style="--i:1">
                        {!! $avatar(['skin' => 'b', 'hair' => 'curls', 'hc' => '#5b3824', 'top' => 'hsl(var(--pink-deep))'], 'h-9 w-9') !!}
                        <div class="min-w-0">
                            <p class="truncate font-semibold">Alex Morgan</p>
                            <p class="truncate text-muted-foreground">Regular · last visit 12 Aug</p>
                        </div>
                    </div>

                    <div class="lp-fc fc-staff flex items-center gap-2.5" style="--i:3">
                        {!! $avatar(['skin' => 'c', 'hair' => 'locs', 'top' => 'hsl(var(--primary-deep))'], 'h-9 w-9') !!}
                        <div class="min-w-0">
                            <p class="truncate font-semibold">Maya Chen</p>
                            <p class="flex items-center gap-1.5 truncate text-muted-foreground"><span class="h-1.5 w-1.5 shrink-0 rounded-full bg-success"></span>Chair 2 · Taking bookings</p>
                        </div>
                    </div>

                    <div class="lp-fc fc-ok flex items-start gap-2.5" style="--i:4">
                        <svg class="lp-check mt-0.5 h-6 w-6 shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="12" r="12" fill="hsl(var(--success) / .12)" />
                            <path d="M7.5 12.3l3 3 6-6.3" fill="none" stroke="hsl(var(--success))" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="min-w-0">
                            <p class="font-semibold">Booking confirmed</p>
                            <p class="text-muted-foreground">Alex M. · 14:00 · Chair 2</p>
                        </div>
                    </div>

                    <div class="lp-fc fc-chair flex items-center gap-1.5" style="--i:5">
                        <span class="h-1.5 w-1.5 rounded-full" style="background:hsl(var(--pink))"></span>Chair 2 · Booked 14:00
                    </div>
                </div>
            </div>
        </section>

        {{-- 1. Value strip --}}
        <section class="lp-strip lp-rv border-y border-border bg-card" data-reveal aria-label="What Cutcost covers">
            <div class="lp-wrap grid grid-cols-2 lg:grid-cols-4">
                <div class="flex items-center gap-4 border-b border-r border-border py-6 pr-4 lg:border-b-0">
                    <div class="lp-strip-viz w-[72px]" aria-hidden="true">
                        <div class="flex -space-x-2">
                            {!! $avatar(['skin' => 'a', 'hair' => 'bob', 'hc' => '#414755', 'top' => 'hsl(var(--primary))'], 'h-8 w-8 ring-2 ring-card') !!}
                            {!! $avatar(['skin' => 'd', 'hair' => 'buzz', 'top' => 'hsl(var(--foreground))'], 'h-8 w-8 ring-2 ring-card') !!}
                            <span class="sv-slide inline-flex">{!! $avatar(['skin' => 'b', 'hair' => 'curls', 'hc' => '#5b3824', 'top' => 'hsl(var(--primary))'], 'h-8 w-8 ring-2 ring-card') !!}</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-[14px] font-semibold">Clients</p>
                        <p class="mt-0.5 text-[12.5px] leading-snug text-muted-foreground">Profiles, notes, visit history.</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 border-b border-border py-6 pl-4 lg:border-b-0 lg:border-r lg:pr-4">
                    <div class="lp-strip-viz w-[72px]" aria-hidden="true">
                        <svg viewBox="0 0 64 44" class="h-11 w-16">
                            <rect x="1" y="1" width="62" height="42" rx="7" fill="hsl(var(--card))" stroke="hsl(var(--border))" stroke-width="1.5" />
                            <path d="M1 12H63" stroke="hsl(var(--border))" stroke-width="1.5" />
                            <rect x="7" y="17" width="14" height="8" rx="2" fill="hsl(var(--accent))" />
                            <rect x="25" y="17" width="14" height="8" rx="2" fill="hsl(var(--accent))" />
                            <rect x="43" y="17" width="14" height="8" rx="2" fill="hsl(var(--accent))" />
                            <rect x="7" y="29" width="14" height="8" rx="2" fill="hsl(var(--accent))" />
                            <rect class="sv-drop" x="25" y="29" width="14" height="8" rx="2" fill="hsl(var(--pink))" />
                            <rect x="43" y="29" width="14" height="8" rx="2" fill="hsl(var(--accent))" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[14px] font-semibold">Bookings</p>
                        <p class="mt-0.5 text-[12.5px] leading-snug text-muted-foreground">Every chair’s diary, one book.</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 border-r border-border py-6 pr-4 lg:px-4">
                    <div class="lp-strip-viz w-[72px]" aria-hidden="true">
                        <svg viewBox="0 0 64 44" class="h-11 w-16">
                            @foreach ([[12, 'a'], [32, 'b'], [52, 'c']] as $k => $t)
                                <circle cx="{{ $t[0] }}" cy="18" r="9" fill="hsl(var(--accent))" />
                                <circle cx="{{ $t[0] }}" cy="16" r="4" fill="{{ ['#e2bca3', '#c68f68', '#915d3c'][$k] }}" />
                                <path d="M{{ $t[0] - 6 }} 26c1-4 3.5-6 6-6s5 2 6 6" fill="{{ $k === 1 ? 'hsl(var(--primary))' : 'hsl(var(--foreground))' }}" />
                                <circle class="{{ $k === 2 ? 'sv-dot' : '' }}" cx="{{ $t[0] + 6.5 }}" cy="25" r="3" fill="hsl(var(--success))" stroke="hsl(var(--card))" stroke-width="1.5" />
                            @endforeach
                            <rect x="4" y="34" width="56" height="5" rx="2.5" fill="hsl(var(--secondary))" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[14px] font-semibold">Team</p>
                        <p class="mt-0.5 text-[12.5px] leading-snug text-muted-foreground">Who’s on the floor today.</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 py-6 pl-4">
                    <div class="lp-strip-viz w-[72px]" aria-hidden="true">
                        <svg viewBox="0 0 64 44" class="h-11 w-16">
                            <rect x="1" y="12" width="62" height="20" rx="10" fill="hsl(var(--accent))" stroke="hsl(var(--primary) / .3)" stroke-width="1.5" />
                            <path d="M11 22a3.5 3.5 0 0 1 3.5-3.5h3M21 22a3.5 3.5 0 0 1-3.5 3.5h-3M13.5 22h5" stroke="hsl(var(--primary))" stroke-width="1.6" stroke-linecap="round" fill="none" />
                            <rect x="26" y="19.5" width="20" height="5" rx="2.5" fill="hsl(var(--primary) / .35)" />
                            <circle cx="53.5" cy="22" r="6" fill="hsl(var(--pink-deep))" />
                            <path class="sv-tick" d="M50.8 22.2l1.9 1.9 3.6-3.8" stroke="hsl(var(--card))" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[14px] font-semibold">Private booking link</p>
                        <p class="mt-0.5 text-[12.5px] leading-snug text-muted-foreground">Clients book you — only you.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 2. Chaos → organisation --}}
        <section id="chaos" class="lp-chaos" data-chaos aria-labelledby="chaos-title">
            <div class="lp-chaos-pin">
                <div class="lp-wrap grid items-center gap-12 py-20 sm:py-28 lg:grid-cols-[minmax(0,0.82fr)_minmax(0,1.18fr)] lg:gap-16 lg:py-0">
                    <div class="lp-rv">
                        <p class="lp-kicker"><b>01</b><i></i>Chaos → organisation</p>
                        <h2 id="chaos-title" class="lp-h2 mt-4">Your salon shouldn’t feel this chaotic</h2>
                        <p class="lp-lede mt-5">WhatsApp threads, Instagram DMs, missed calls, a notebook by the till and a rota nobody can find. Cutcost pulls it into one book the whole team works from.</p>
                        <div class="mt-9 max-w-sm" aria-hidden="true">
                            <div class="flex justify-between text-[12px] font-semibold">
                                <span class="lp-cprog-a">Messages everywhere</span>
                                <span class="lp-cprog-b">One system</span>
                            </div>
                            <div class="mt-2.5 h-1 overflow-hidden rounded-full bg-secondary">
                                <div class="lp-cprog-bar h-full rounded-full bg-primary"></div>
                            </div>
                        </div>
                    </div>

                    <div class="lp-cstage" role="img" aria-label="A salon owner surrounded by WhatsApp messages, Instagram DMs, a missed call, a notebook, a staff rota and a sticky note. The pieces collapse into one Cutcost list of bookings, client notes and staff changes, and the owner relaxes with a tablet.">
                        <svg class="lp-own-a" viewBox="0 0 120 166" aria-hidden="true">
                            {!! $person(['skin' => 'd', 'hair' => 'afro', 'top' => 'hsl(var(--foreground))', 'outfit' => 'jacket', 'prop' => 'stress', 'mood' => 'stress', 'blink' => 0.8]) !!}
                        </svg>

                        @foreach ([
                            ['l' => '0%', 't' => '3%', 'r' => '-6deg', 'tx' => 22, 'ty' => 24, 'd' => 0.04, 'src' => 'WhatsApp', 'tone' => 'text-success', 'body' => 'Can I move Saturday to 3? Need a fade before the wedding'],
                            ['l' => '58%', 't' => '0%', 'r' => '5deg', 'tx' => -14, 'ty' => 28, 'd' => 0.1, 'src' => 'Instagram DM', 'tone' => 'lp-rose', 'body' => 'Hiya are you free Thursday? Colour + cut'],
                            ['l' => '64%', 't' => '30%', 'r' => '-4deg', 'tx' => -22, 'ty' => 8, 'd' => 0.16, 'src' => 'Missed call', 'tone' => 'text-destructive', 'body' => 'Unknown number · 2 min ago · no voicemail'],
                            ['l' => '-2%', 't' => '35%', 'r' => '4deg', 'tx' => 26, 'ty' => 2, 'd' => 0.22, 'src' => 'Notebook', 'tone' => 'text-warning', 'body' => 'Jordan — sensitive scalp. No lower than a 2.'],
                            ['l' => '2%', 't' => '66%', 'r' => '-3deg', 'tx' => 24, 'ty' => -20, 'd' => 0.28, 'src' => 'Staff rota', 'tone' => 'text-muted-foreground', 'body' => 'Priya off Fri?? who covers her 11:00'],
                            ['l' => '61%', 't' => '63%', 'r' => '6deg', 'tx' => -18, 'ty' => -22, 'd' => 0.34, 'src' => 'Sticky note', 'tone' => 'text-warning', 'body' => 'Walk-in wants 4pm — ask Omar'],
                        ] as $chip)
                            <div class="lp-chip" style="--l:{{ $chip['l'] }};--t:{{ $chip['t'] }};--r:{{ $chip['r'] }};--tx:{{ $chip['tx'] }};--ty:{{ $chip['ty'] }};--d:{{ $chip['d'] }}" aria-hidden="true">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.1em] {{ $chip['tone'] }}">{{ $chip['src'] }}</p>
                                <p class="mt-1">{{ $chip['body'] }}</p>
                            </div>
                        @endforeach

                        <div class="lp-cboard ui" aria-hidden="true">
                            <div class="ui-bar justify-between">
                                <span class="flex items-center gap-2"><span class="h-5 w-5 rounded-md bg-primary"></span>North &amp; Co. · This week</span>
                                <span class="badge-success">All in one book</span>
                            </div>
                            <div class="divide-y divide-border/70">
                                @foreach ([
                                    ['Sat 15:00', 'Jordan L. · Skin fade', 'Moved · was WhatsApp', 0.3],
                                    ['Thu 11:00', 'Riley K. · Colour & cut', 'Booked · was a DM', 0.38],
                                    ['Today 16:30', 'Sam R. · Beard trim', 'Booked via your link', 0.46],
                                    ['Note', 'Jordan L. · Sensitive scalp, no lower than a 2', 'Client note · was the notebook', 0.54],
                                    ['Team', 'Priya off Fri · 11:00 moved to Omar', 'Staff · was the rota', 0.62],
                                    ['Today 16:00', 'Walk-in · Cut & shape · Omar', 'Booked · was a sticky note', 0.7],
                                ] as $row)
                                    <div class="lp-crow flex items-center gap-3 px-3.5 py-2.5" style="--d:{{ $row[3] }}">
                                        <span class="w-[4.6rem] shrink-0 text-[11.5px] font-semibold tabular-nums">{{ $row[0] }}</span>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-[12.5px] font-medium">{{ $row[1] }}</p>
                                            <p class="truncate text-[11px] text-muted-foreground">{{ $row[2] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <svg class="lp-own-b" viewBox="0 0 120 166" aria-hidden="true">
                            {!! $person(['skin' => 'd', 'hair' => 'afro', 'top' => 'hsl(var(--foreground))', 'outfit' => 'jacket', 'prop' => 'tablet', 'mood' => 'calm']) !!}
                        </svg>
                    </div>
                </div>
            </div>
        </section>

        {{-- 3. Features --}}
        <section id="features" class="border-y border-border bg-card py-20 sm:py-28">
            <div class="lp-wrap">
                <div class="lp-rv grid gap-6 lg:grid-cols-2 lg:items-end">
                    <div>
                        <p class="lp-kicker"><b>02</b><i></i>The product</p>
                        <h2 class="lp-h2 mt-4">Everything your shop needs in one place</h2>
                    </div>
                    <p class="lp-lede lg:justify-self-end">Bookings, clients, team, services and the day’s schedule are one system — so nothing lives in a separate app, a separate chat or a separate notebook.</p>
                </div>

                {{-- Bookings --}}
                <article class="mt-16 grid gap-8 sm:mt-20 lg:grid-cols-[minmax(0,0.36fr)_minmax(0,0.64fr)] lg:items-center lg:gap-12">
                    <div class="lp-rv">
                        <p class="lp-label text-primary">Bookings</p>
                        <h3 class="lp-h3 mt-3">Every chair’s day, side by side.</h3>
                        <p class="mt-3 text-[15px] leading-relaxed text-muted-foreground">Each stylist gets a column. Book in by hand or let clients self-book — it lands in the same diary, with a status you can act on.</p>
                        <div class="mt-5 flex flex-wrap gap-2">
                            <span class="badge-outline badge-dot">Scheduled</span>
                            <span class="badge-success badge-dot">Completed</span>
                            <span class="badge-warning badge-dot">Awaiting payment</span>
                            <span class="badge-danger badge-dot">No show</span>
                        </div>
                    </div>
                    <div class="lp-rv relative" style="--i:1">
                        <div class="ui">
                            <div class="ui-bar justify-between">
                                <span>Bookings · Wed 30 Sep</span>
                                <span class="seg"><span class="seg-item seg-item-active h-7 px-2.5 text-[12px]">Day</span><span class="seg-item h-7 px-2.5 text-[12px]">List</span></span>
                            </div>
                            <div class="grid grid-cols-[2.6rem_repeat(3,minmax(0,1fr))] border-b border-border bg-secondary/50 text-[12px] font-semibold">
                                <span></span>
                                @foreach ([['Maya', 'c', 'locs', 'hsl(var(--foreground))'], ['Omar', 'd', 'fade', 'hsl(var(--foreground))'], ['Priya', 'b', 'long', '#414755']] as $st)
                                    <span class="flex items-center gap-2 border-l border-border px-2.5 py-2">
                                        {!! $avatar(['skin' => $st[1], 'hair' => $st[2], 'hc' => $st[3], 'top' => 'hsl(var(--primary-deep))'], 'h-6 w-6') !!}
                                        <span class="truncate">{{ $st[0] }}</span>
                                    </span>
                                @endforeach
                            </div>
                            <div class="grid grid-cols-[2.6rem_repeat(3,minmax(0,1fr))]">
                                <div class="text-[10.5px] font-medium tabular-nums text-muted-foreground">
                                    @foreach (['10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00'] as $h)
                                        <div class="h-11 pl-2 pt-1">{{ $h }}</div>
                                    @endforeach
                                </div>
                                @foreach ([
                                    [[0, 0.75, 'Jordan L.', 'Skin fade', 'done'], [1.5, 0.85, 'Riley K.', 'Cut & shape', 'done'], [4, 0.75, 'Alex M.', 'Skin fade', 'sched'], [5.25, 0.75, 'Mia T.', 'Blow dry', 'sched']],
                                    [[0.5, 0.5, 'Sam R.', 'Beard trim', 'done'], [2, 0.75, 'Dev P.', 'Skin fade', 'sched'], [3.5, 0.85, 'Walk-in', 'Cut & shape', 'sched'], [5, 0.5, 'Leo B.', 'Beard trim', 'noshow']],
                                    [[1, 1.5, 'Nia O.', 'Colour', 'pay'], [3, 0.85, 'Tom W.', 'Cut & shape', 'sched'], [5.5, 1, 'Ella G.', 'Blow dry', 'sched']],
                                ] as $col)
                                    <div class="lp-cal-col relative border-l border-border">
                                        @foreach ($col as $b)
                                            <div class="lp-blk lp-blk-{{ $b[4] }}" style="top:calc({{ $b[0] }} * 2.75rem + 2px);height:calc({{ $b[1] }} * 2.75rem - 4px)">
                                                <p class="truncate font-semibold">{{ $b[2] }}</p>
                                                <p class="truncate opacity-80">{{ $b[3] }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <svg class="pointer-events-none absolute -bottom-3 -right-10 hidden h-44 w-auto xl:block" viewBox="0 0 120 166" aria-hidden="true">
                            {!! $person(['skin' => 'd', 'hair' => 'fade', 'beard' => true, 'top' => 'hsl(var(--primary))', 'outfit' => 'apron', 'prop' => 'clippers', 'flip' => true, 'blink' => 2.1]) !!}
                        </svg>
                    </div>
                </article>

                {{-- Client CRM --}}
                <article class="mt-20 grid gap-8 sm:mt-28 lg:grid-cols-12 lg:items-center lg:gap-12">
                    <div class="lp-rv ui lg:col-span-7">
                        <div class="flex flex-wrap items-center gap-4 border-b border-border p-4 sm:p-5">
                            {!! $avatar(['skin' => 'a', 'hair' => 'fade', 'hc' => '#414755', 'top' => 'hsl(var(--foreground))'], 'h-12 w-12') !!}
                            <div class="min-w-0 flex-1">
                                <p class="text-[16px] font-semibold tracking-tight">Jordan Lewis</p>
                                <p class="text-[12.5px] text-muted-foreground">07700 900123 · jordan@example.com</p>
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                <span class="badge-outline">Regular</span>
                                <span class="badge-default">Usually with Maya</span>
                            </div>
                        </div>
                        <div class="grid sm:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)]">
                            <div class="p-4 sm:p-5">
                                <p class="lp-label">Visit history</p>
                                <div class="mt-3 space-y-1">
                                    @foreach ([['12 Aug', 'Skin fade', 'Maya'], ['15 Jul', 'Skin fade', 'Maya'], ['17 Jun', 'Cut & beard', 'Omar'], ['20 May', 'Skin fade', 'Maya']] as $v)
                                        <div class="flex items-center justify-between gap-3 rounded-lg px-2 py-1.5 text-[12.5px] odd:bg-secondary/60">
                                            <span class="w-14 shrink-0 font-medium tabular-nums">{{ $v[0] }}</span>
                                            <span class="min-w-0 flex-1 truncate">{{ $v[1] }} · {{ $v[2] }}</span>
                                            <span class="badge-success">Completed</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="border-t border-border p-4 sm:border-l sm:border-t-0 sm:p-5">
                                <p class="lp-label">Notes</p>
                                <div class="mt-3 rounded-lg border border-warning/25 bg-warning/[0.06] p-3 text-[12.5px] leading-relaxed">
                                    Sensitive scalp — no lower than a 2 on the sides. Likes a tidy neckline, books every 4 weeks.
                                </div>
                                <p class="mt-2 text-[11px] text-muted-foreground">Added by Maya · 12 Aug</p>
                                <div class="mt-4 grid grid-cols-2 gap-2 text-[12px]">
                                    <div class="rounded-lg border border-border p-2.5"><p class="text-muted-foreground">Visits</p><p class="mt-0.5 text-[15px] font-semibold tabular-nums">14</p></div>
                                    <div class="rounded-lg border border-border p-2.5"><p class="text-muted-foreground">Next</p><p class="mt-0.5 text-[15px] font-semibold tabular-nums">9 Sep</p></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="lp-rv lg:col-span-5" style="--i:1">
                        <svg viewBox="0 0 300 200" class="mb-6 h-auto w-full max-w-[300px]" aria-hidden="true">
                            <rect x="0" y="10" width="300" height="190" rx="22" fill="hsl(var(--accent))" />
                            <path d="M84 150V70A42 42 0 0 1 168 70V150Z" fill="hsl(var(--card))" stroke="hsl(var(--border))" stroke-width="2.5" />
                            <path d="M96 144V72A30 30 0 0 1 156 72V144Z" fill="hsl(var(--secondary))" />
                            <rect x="74" y="150" width="104" height="9" rx="4.5" fill="hsl(var(--foreground))" />
                            {!! $chairBack(126, 214, 0.62) !!}
                            {!! $person(['x' => 89, 'y' => 59, 's' => 0.62, 'skin' => 'a', 'hair' => 'fade', 'hc' => '#414755', 'outfit' => 'cape', 'mood' => 'calm']) !!}
                            {!! $chairFront(126, 214, 0.62) !!}
                            {!! $person(['x' => 190, 'y' => 40, 's' => 0.72, 'skin' => 'c', 'hair' => 'locs', 'top' => 'hsl(var(--primary-deep))', 'outfit' => 'apron', 'prop' => 'tablet', 'flip' => true, 'blink' => 1.1]) !!}
                            <g transform="translate(196 22)">
                                <rect width="94" height="32" rx="9" fill="hsl(var(--card))" stroke="hsl(var(--border))" stroke-width="1.5" />
                                <rect x="9" y="9" width="46" height="4.5" rx="2.2" fill="hsl(var(--warning) / .6)" />
                                <rect x="9" y="18" width="70" height="4.5" rx="2.2" fill="hsl(var(--border))" />
                            </g>
                        </svg>
                        <p class="lp-label text-primary">Client CRM</p>
                        <h3 class="lp-h3 mt-3">Know who’s in the chair before they sit down.</h3>
                        <p class="mt-3 text-[15px] leading-relaxed text-muted-foreground">Names, numbers, notes, visit history and who they usually sit with. The record lives in your shop — not inside someone else’s app.</p>
                    </div>
                </article>

                {{-- Team --}}
                <article class="mt-20 grid gap-8 sm:mt-28 lg:grid-cols-12 lg:items-center lg:gap-12">
                    <div class="lp-rv lg:order-2 lg:col-span-7" style="--i:1">
                        <div class="ui">
                            <div class="ui-bar justify-between">
                                <span>Stylists</span>
                                <span class="btn-primary pointer-events-none h-8 px-3 text-[12px]">Add stylist</span>
                            </div>
                            <div class="divide-y divide-border/70">
                                @foreach ([
                                    ['Dee Owens', 'Owner', 'Chair 1 · 5 today', true, ['skin' => 'd', 'hair' => 'afro', 'top' => 'hsl(var(--foreground))']],
                                    ['Maya Chen', 'Stylist', 'Chair 2 · 9 today', true, ['skin' => 'c', 'hair' => 'locs', 'top' => 'hsl(var(--primary-deep))']],
                                    ['Omar Reid', 'Stylist', 'Chair 3 · 7 today', true, ['skin' => 'd', 'hair' => 'fade', 'beard' => true, 'top' => 'hsl(var(--primary))']],
                                    ['Priya Shah', 'Stylist', 'Off today', false, ['skin' => 'b', 'hair' => 'long', 'hc' => '#414755', 'top' => 'hsl(var(--primary))']],
                                ] as $m)
                                    <div class="flex items-center gap-3 px-4 py-3">
                                        {!! $avatar($m[4], 'h-9 w-9') !!}
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-[13px] font-semibold">{{ $m[0] }}</p>
                                            <p class="truncate text-[12px] text-muted-foreground">{{ $m[2] }}</p>
                                        </div>
                                        <span class="{{ $m[1] === 'Owner' ? 'badge-outline' : 'badge-default' }} hidden sm:inline-flex">{{ $m[1] }}</span>
                                        <span class="lp-switch {{ $m[3] ? 'is-on' : '' }}" title="Taking bookings"></span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="lp-rv lg:order-1 lg:col-span-5">
                        <svg viewBox="0 0 300 180" class="mb-6 h-auto w-full max-w-[300px]" aria-hidden="true">
                            <rect x="0" y="12" width="300" height="168" rx="22" fill="hsl(var(--secondary))" />
                            @foreach ([[26, 'd', 'afro', 'hsl(var(--foreground))', 'jacket'], [100, 'c', 'locs', 'hsl(var(--primary-deep))', 'apron'], [174, 'd', 'fade', 'hsl(var(--primary))', 'apron']] as $k => $tm)
                                {!! $person(['x' => $tm[0], 'y' => 20 + ($k === 0 ? 0 : 8), 's' => 0.84, 'skin' => $tm[1], 'hair' => $tm[2], 'top' => $tm[3], 'outfit' => $tm[4], 'beard' => $k === 2, 'blink' => $k * 0.7]) !!}
                            @endforeach
                            <rect x="0" y="152" width="300" height="28" fill="hsl(var(--secondary))" />
                            @foreach ([56, 130, 204] as $k => $cx)
                                <rect x="{{ $cx - 26 }}" y="140" width="52" height="22" rx="11" fill="hsl(var(--card))" stroke="hsl(var(--border))" stroke-width="1.5" />
                                <circle cx="{{ $cx - 14 }}" cy="151" r="3.5" fill="{{ $k < 3 ? 'hsl(var(--success))' : 'hsl(var(--input))' }}" />
                                <rect x="{{ $cx - 6 }}" y="148.5" width="22" height="5" rx="2.5" fill="hsl(var(--border))" />
                            @endforeach
                        </svg>
                        <p class="lp-label text-primary">Team management</p>
                        <h3 class="lp-h3 mt-3">The right book for the right person.</h3>
                        <p class="mt-3 text-[15px] leading-relaxed text-muted-foreground">Add stylists, assign chairs and switch who’s taking bookings. Owners see the whole floor; stylists see their own day.</p>
                    </div>
                </article>

                {{-- Services + scheduling --}}
                <article class="mt-20 sm:mt-28">
                    <div class="lp-rv flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                        <div class="max-w-xl">
                            <p class="lp-label text-primary">Services &amp; daily scheduling</p>
                            <h3 class="lp-h3 mt-3">What you offer and when you’re open — exactly what clients can book.</h3>
                        </div>
                        <svg viewBox="0 0 120 120" class="hidden h-28 w-auto shrink-0 sm:block" aria-hidden="true">
                            <circle cx="60" cy="66" r="54" fill="hsl(var(--accent))" />
                            <g transform="translate(0 -6)">{!! $person(['skin' => 'b', 'hair' => 'hijab', 'hc' => 'hsl(var(--primary-deep))', 'top' => 'hsl(var(--foreground))', 'outfit' => 'tunic', 'prop' => 'scissors', 'blink' => 0.3]) !!}</g>
                        </svg>
                    </div>
                    <div class="mt-8 grid gap-5 lg:grid-cols-12">
                        <div class="lp-rv ui lg:col-span-7">
                            <div class="ui-bar justify-between"><span>Services</span><span class="text-[12px] font-medium text-muted-foreground">Shown on your booking page</span></div>
                            <div class="divide-y divide-border/70">
                                @foreach ([
                                    ['Skin fade', '45 min', $demoPrice(25, 8000), 'active'],
                                    ['Cut & shape', '50 min', $demoPrice(32, 10000), 'active'],
                                    ['Beard trim', '20 min', $demoPrice(12, 4000), 'active'],
                                    ['Colour & cut', '90 min', $demoPrice(68, 25000), 'active'],
                                    ['Kids’ cut', '30 min', $demoPrice(15, 5000), 'hidden'],
                                ] as $sv)
                                    <div class="flex items-center gap-3 px-4 py-3 text-[13px]">
                                        <span class="min-w-0 flex-1 truncate font-medium">{{ $sv[0] }}</span>
                                        <span class="w-14 text-right tabular-nums text-muted-foreground">{{ $sv[1] }}</span>
                                        <span class="w-16 text-right font-semibold tabular-nums">{{ $sv[2] }}</span>
                                        <span class="{{ $sv[3] === 'active' ? 'badge-success' : 'badge-muted' }} w-[4.4rem] justify-center">{{ $sv[3] === 'active' ? 'Active' : 'Hidden' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="lp-rv ui lg:col-span-5" style="--i:1">
                            <div class="ui-bar justify-between"><span>Opening hours</span><span class="badge-default">15-min slots</span></div>
                            <div class="space-y-0.5 p-3">
                                @foreach ([['Mon', 'Closed'], ['Tue', '09:00 – 18:00'], ['Wed', '09:00 – 18:00'], ['Thu', '10:00 – 20:00'], ['Fri', '09:00 – 20:00'], ['Sat', '08:00 – 17:00'], ['Sun', 'Closed']] as $d)
                                    <div class="flex items-center justify-between rounded-lg px-2.5 py-1.5 text-[12.5px] {{ $d[0] === 'Wed' ? 'bg-primary/[0.07] font-semibold text-primary-deep' : '' }}">
                                        <span class="w-10">{{ $d[0] }}</span>
                                        <span class="tabular-nums {{ $d[1] === 'Closed' ? 'text-muted-foreground' : '' }}">{{ $d[1] }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <p class="border-t border-border px-4 py-3 text-[12px] leading-relaxed text-muted-foreground">Clients only see times that fit your hours, the service length and the stylist’s free slots.</p>
                        </div>
                    </div>
                </article>
            </div>
        </section>

        {{-- 4. Booking journey --}}
        <section id="booking" class="relative py-20 sm:py-28" data-journey>
            <div class="lp-wrap">
                <div class="grid gap-12 lg:grid-cols-[minmax(0,0.9fr)_auto_minmax(0,1fr)] lg:items-center lg:gap-10">
                    <div class="lp-rv">
                        <p class="lp-kicker"><b>03</b><i></i>Direct booking</p>
                        <h2 class="lp-h2 mt-4">Your clients. Your booking page.</h2>
                        <p class="lp-lede mt-5">Share one link. Clients pick a service, a stylist and a time, confirm — and it’s in your calendar. No app to download, no directory of other shops.</p>
                        <ol class="lp-jsteps mt-8 grid grid-cols-4 gap-2 lg:grid-cols-1 lg:gap-1">
                            @foreach (['Service', 'Stylist', 'Time', 'Confirm'] as $k => $label)
                                <li class="lp-jstep is-done" data-jstep>
                                    <span class="lp-jnum">{{ $k + 1 }}</span>
                                    <span class="text-[13px] font-semibold">{{ $label }}</span>
                                    <span class="hidden text-[13px] text-muted-foreground lg:inline">{{ ['Skin fade · 45 min', 'Maya Chen', 'Today · 14:00', 'Booked in seconds'][$k] }}</span>
                                </li>
                            @endforeach
                        </ol>
                        <button type="button" class="btn-ghost lp-btn -ml-2.5 mt-4 h-11" data-journey-replay>
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7L3 8" /><path d="M3 3v5h5" /></svg>
                            Replay the booking
                        </button>
                    </div>

                    <div class="lp-rv mx-auto" style="--i:1">
                        <div class="lp-phone" aria-hidden="true">
                            <div class="lp-phone-screen">
                                <div class="flex items-center gap-2.5 border-b border-border px-4 pb-3 pt-7">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-[12px] font-bold text-primary-foreground">N</span>
                                    <div class="min-w-0">
                                        <p class="truncate text-[13px] font-semibold">North &amp; Co.</p>
                                        <p class="truncate text-[10.5px] text-muted-foreground">Leeds · Open today 09:00 – 18:00</p>
                                    </div>
                                </div>
                                <div class="relative h-[392px]">
                                    <div class="lp-jscreen" data-jscreen>
                                        <p class="lp-label">Step 1 of 4</p>
                                        <p class="mt-1.5 text-[15px] font-semibold">Choose a service</p>
                                        <div class="mt-3 space-y-2">
                                            @foreach ([['Skin fade', '45 min', $demoPrice(25, 8000), true], ['Cut & shape', '50 min', $demoPrice(32, 10000), false], ['Beard trim', '20 min', $demoPrice(12, 4000), false], ['Colour & cut', '90 min', $demoPrice(68, 25000), false]] as $o)
                                                <div class="lp-opt {{ $o[3] ? 'is-pick' : '' }}">
                                                    <span class="min-w-0 flex-1"><span class="block truncate font-medium">{{ $o[0] }}</span><span class="block text-[11px] text-muted-foreground">{{ $o[1] }}</span></span>
                                                    <span class="font-semibold tabular-nums">{{ $o[2] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="lp-jscreen" data-jscreen>
                                        <p class="lp-label">Step 2 of 4</p>
                                        <p class="mt-1.5 text-[15px] font-semibold">Choose a stylist</p>
                                        <div class="mt-3 space-y-2">
                                            @foreach ([['Maya Chen', 'Next free 14:00', ['skin' => 'c', 'hair' => 'locs', 'top' => 'hsl(var(--primary-deep))'], true], ['Omar Reid', 'Next free 15:30', ['skin' => 'd', 'hair' => 'fade', 'beard' => true, 'top' => 'hsl(var(--primary))'], false], ['Any stylist', 'First available', null, false]] as $o)
                                                <div class="lp-opt {{ $o[3] ? 'is-pick' : '' }}">
                                                    @if ($o[2])
                                                        {!! $avatar($o[2], 'h-8 w-8') !!}
                                                    @else
                                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-secondary text-[12px] font-semibold text-muted-foreground">?</span>
                                                    @endif
                                                    <span class="min-w-0 flex-1"><span class="block truncate font-medium">{{ $o[0] }}</span><span class="block text-[11px] text-muted-foreground">{{ $o[1] }}</span></span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="lp-jscreen" data-jscreen>
                                        <p class="lp-label">Step 3 of 4</p>
                                        <p class="mt-1.5 text-[15px] font-semibold">Pick a time</p>
                                        <div class="mt-3 grid grid-cols-5 gap-1 text-center text-[10.5px]">
                                            @foreach ([['Tue', '29'], ['Wed', '30'], ['Thu', '1'], ['Fri', '2'], ['Sat', '3']] as $day)
                                                <span class="rounded-lg border py-1.5 {{ $day[1] === '30' ? 'border-primary bg-primary text-primary-foreground' : 'border-border text-muted-foreground' }}"><span class="block">{{ $day[0] }}</span><span class="block text-[13px] font-semibold">{{ $day[1] }}</span></span>
                                            @endforeach
                                        </div>
                                        <div class="mt-3 grid grid-cols-3 gap-1.5">
                                            @foreach (['10:15', '11:45', '12:30', '14:00', '15:30', '16:45'] as $t)
                                                <span class="lp-opt justify-center px-1 py-2 font-medium tabular-nums {{ $t === '14:00' ? 'is-pick' : '' }}">{{ $t }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="lp-jscreen is-on" data-jscreen>
                                        <div class="flex flex-col items-center pt-4 text-center">
                                            <svg class="lp-jcheck h-14 w-14" viewBox="0 0 56 56" aria-hidden="true">
                                                <circle cx="28" cy="28" r="28" fill="hsl(var(--success) / .12)" />
                                                <path d="M18 28.5l7 7 13-14" fill="none" stroke="hsl(var(--success))" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <p class="mt-3 text-[16px] font-semibold">You’re booked</p>
                                            <p class="mt-0.5 text-[12px] text-muted-foreground">We’ve sent the details to your email.</p>
                                        </div>
                                        <div class="lp-jcard mt-5 rounded-xl border border-border bg-secondary/50 p-3 text-[12px]" data-jcard>
                                            <p class="font-semibold">Skin fade with Maya</p>
                                            <p class="mt-0.5 text-muted-foreground">Wed 30 Sep · 14:00 – 14:45</p>
                                            <p class="mt-0.5 text-muted-foreground">North &amp; Co., Leeds · {{ $demoPrice(25, 8000) }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="border-t border-border p-3">
                                    <span class="lp-jcta btn-primary pointer-events-none h-10 w-full justify-center text-[13px]" data-jcta>Confirm booking</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lp-rv" style="--i:2">
                        <div class="ui" aria-label="Maya’s calendar for today">
                            <div class="ui-bar justify-between">
                                <span class="flex items-center gap-2">{!! $avatar(['skin' => 'c', 'hair' => 'locs', 'top' => 'hsl(var(--primary-deep))'], 'h-6 w-6') !!}Maya · Today</span>
                                <span class="text-[12px] font-medium text-muted-foreground">Wed 30 Sep</span>
                            </div>
                            <div class="divide-y divide-border/70 text-[13px]">
                                @foreach ([['10:00', 'Jordan L.', 'Skin fade', 'badge-success', 'Completed'], ['11:30', 'Riley K.', 'Cut & shape', 'badge-success', 'Completed'], ['12:30', 'Lunch', '', '', '']] as $r)
                                    <div class="flex items-center gap-3 px-4 py-3">
                                        <span class="w-11 shrink-0 font-semibold tabular-nums {{ $r[1] === 'Lunch' ? 'text-muted-foreground' : '' }}">{{ $r[0] }}</span>
                                        <span class="min-w-0 flex-1 truncate {{ $r[1] === 'Lunch' ? 'text-muted-foreground' : '' }}">{{ $r[1] }}{{ $r[2] ? ' · '.$r[2] : '' }}</span>
                                        @if ($r[3])<span class="{{ $r[3] }} shrink-0">{{ $r[4] }}</span>@endif
                                    </div>
                                @endforeach
                                <div class="lp-jslot is-filled flex items-center gap-3 px-4 py-3" data-jslot>
                                    <span class="w-11 shrink-0 font-semibold tabular-nums text-primary">14:00</span>
                                    <span class="lp-jslot-open min-w-0 flex-1 text-muted-foreground">Open</span>
                                    <span class="lp-jslot-fill min-w-0 flex-1 truncate font-medium">Alex M. · Skin fade <span class="lp-rose ml-1 text-[11px] font-semibold">via your link</span></span>
                                    <span class="lp-jslot-fill badge-outline shrink-0">Scheduled</span>
                                </div>
                                @foreach ([['15:15', 'Mia T.', 'Blow dry'], ['16:30', 'Open', '']] as $r)
                                    <div class="flex items-center gap-3 px-4 py-3">
                                        <span class="w-11 shrink-0 font-semibold tabular-nums">{{ $r[0] }}</span>
                                        <span class="min-w-0 flex-1 truncate {{ $r[1] === 'Open' ? 'text-muted-foreground' : '' }}">{{ $r[1] }}{{ $r[2] ? ' · '.$r[2] : '' }}</span>
                                        @if ($r[2])<span class="badge-outline shrink-0">Scheduled</span>@endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <p class="mt-4 flex items-center gap-2 text-[13px] text-muted-foreground">
                            <svg class="h-4 w-4 shrink-0 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.07 0l2.83-2.83a5 5 0 0 0-7.07-7.07L11.5 4.5" /><path d="M14 11a5 5 0 0 0-7.07 0L4.1 13.83a5 5 0 0 0 7.07 7.07l1.33-1.4" /></svg>
                            <span class="truncate font-mono text-[12px]">{{ $bookLink }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 5. Marketplace vs Cutcost --}}
        <section id="compare" class="border-y border-border bg-secondary/50 py-20 sm:py-28">
            <div class="lp-wrap">
                <div class="lp-rv mx-auto max-w-3xl text-center">
                    <p class="lp-kicker"><b>04</b><i></i>Not a marketplace</p>
                    <h2 class="lp-h2 mt-4">Your clients shouldn’t be shown your competitors.</h2>
                </div>

                <div class="mt-14 grid gap-5 lg:grid-cols-2">
                    {{-- Marketplace side --}}
                    <div class="lp-rv relative overflow-hidden rounded-3xl border border-border bg-card p-6 sm:p-8">
                        <div class="flex items-center justify-between">
                            <p class="text-[13px] font-semibold text-muted-foreground">Marketplace apps</p>
                            <span class="badge-muted">Their platform</span>
                        </div>
                        <div class="mt-6 grid grid-cols-[minmax(0,1fr)_minmax(0,1.25fr)] items-end gap-4 sm:gap-6">
                            <svg viewBox="0 0 120 166" class="h-auto w-full max-w-[150px] grayscale-[0.2]" aria-hidden="true">
                                {!! $person(['skin' => 'b', 'hair' => 'curls', 'hc' => '#5b3824', 'top' => '#414755', 'outfit' => 'knit', 'prop' => 'phone', 'mood' => 'stress', 'blink' => 0.6]) !!}
                            </svg>
                            <div class="lp-mkt" aria-hidden="true">
                                <div class="rounded-lg bg-secondary px-2.5 py-2 text-[11px] text-muted-foreground">Barbers near you</div>
                                @foreach ([['Rival Cuts', 'Sponsored', '4.9'], ['Studio Three', 'Top rated', '4.8'], ['Fade Factory', '', '4.8'], ['Your shop', 'Ranked #4', '4.9']] as $k => $shop)
                                    <div class="lp-mkt-row {{ $k === 3 ? 'is-you' : '' }}">
                                        <span class="h-7 w-7 shrink-0 rounded-md {{ $k === 3 ? 'bg-primary/20' : 'bg-secondary' }}"></span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-[11.5px] font-semibold">{{ $shop[0] }}</span>
                                            <span class="block text-[10px] text-muted-foreground">★ {{ $shop[2] }}{{ $shop[1] ? ' · '.$shop[1] : '' }}</span>
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <ul class="mt-7 grid grid-cols-2 gap-2 text-[12.5px]">
                            @foreach (['Competing salons', 'Ranking algorithm', 'Platform fees', 'The app owns the client'] as $flag)
                                <li class="flex items-center gap-2 rounded-lg border border-border px-3 py-2.5 text-muted-foreground">
                                    <svg class="h-3.5 w-3.5 shrink-0 text-destructive" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 4l8 8M12 4l-8 8" /></svg>
                                    {{ $flag }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Cutcost side --}}
                    <div class="lp-rv relative overflow-hidden rounded-3xl border border-primary/30 bg-card p-6 ring-1 ring-primary/10 sm:p-8" style="--i:1">
                        <div class="flex items-center justify-between">
                            <p class="text-[13px] font-semibold text-primary">Cutcost</p>
                            <span class="badge-outline">Your business</span>
                        </div>
                        <div class="lp-direct mt-6" data-direct>
                            <div class="lp-direct-node">
                                <svg viewBox="36 18 48 70" class="h-20 w-auto" aria-hidden="true">
                                    {!! $person(['skin' => 'b', 'hair' => 'curls', 'hc' => '#5b3824', 'top' => 'hsl(var(--primary))', 'outfit' => 'knit']) !!}
                                </svg>
                                <span class="text-[12.5px] font-semibold">Client</span>
                            </div>
                            <span class="lp-direct-line" aria-hidden="true"><span></span></span>
                            <div class="lp-direct-node">
                                <span class="flex h-20 items-center"><span class="lp-pill border-primary/30 bg-accent font-mono text-[11px] text-primary-deep">/book/north-and-co</span></span>
                                <span class="text-[12.5px] font-semibold">Your booking link</span>
                            </div>
                            <span class="lp-direct-line" aria-hidden="true"><span></span></span>
                            <div class="lp-direct-node">
                                <svg viewBox="0 0 96 80" class="h-20 w-auto" aria-hidden="true">
                                    <rect x="8" y="22" width="80" height="56" rx="4" fill="hsl(var(--card))" stroke="hsl(var(--border))" stroke-width="2" />
                                    <path d="M4 22h88l-6-14H10z" fill="hsl(var(--primary))" />
                                    <path d="M18 8l-4 14M34 8l-2 14M50 8v14M66 8l2 14M82 8l4 14" stroke="hsl(var(--card))" stroke-width="3" />
                                    <rect x="16" y="34" width="30" height="22" rx="2" fill="hsl(var(--accent))" stroke="hsl(var(--border))" stroke-width="1.5" />
                                    <rect x="56" y="36" width="22" height="42" rx="2" fill="hsl(var(--foreground))" />
                                    <circle cx="73" cy="58" r="1.6" fill="hsl(var(--card))" />
                                    <rect x="20" y="62" width="22" height="4" rx="2" fill="hsl(var(--primary) / .4)" />
                                </svg>
                                <span class="text-[12.5px] font-semibold">Your shop</span>
                            </div>
                        </div>
                        <ul class="mt-7 grid grid-cols-2 gap-2 text-[12.5px]">
                            @foreach (['Only your shop on the page', 'No ranking, no sponsored slots', 'No cut of the booking', 'The client stays yours'] as $good)
                                <li class="flex items-center gap-2 rounded-lg border border-primary/20 bg-primary/[0.04] px-3 py-2.5">
                                    <svg class="h-3.5 w-3.5 shrink-0 text-primary" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3.5 8.5l3 3 6-7" /></svg>
                                    {{ $good }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        {{-- 6. How it works --}}
        <section id="how-it-works" class="py-20 sm:py-28">
            <div class="lp-wrap">
                <div class="lp-rv grid gap-6 lg:grid-cols-2 lg:items-end">
                    <div>
                        <p class="lp-kicker"><b>05</b><i></i>Setup</p>
                        <h2 class="lp-h2 mt-4">Running your shop shouldn’t need a manual</h2>
                    </div>
                    <p class="lp-lede lg:justify-self-end">Five steps from sign-up to a full day. Most of it is typing in what you already know about your shop.</p>
                </div>

                <ol class="lp-path mt-14 sm:mt-16" data-path>
                    <span class="lp-path-track" aria-hidden="true"><span></span></span>

                    <li class="lp-pstep" style="--i:0">
                        <span class="lp-pnode">01</span>
                        <div class="lp-pviz" aria-hidden="true">
                            <div class="lp-mini w-[72%]">
                                <p class="lp-label">Shop name</p>
                                <p class="lp-mini-field">North &amp; Co.<span class="lp-caret"></span></p>
                                <p class="lp-label mt-2">City</p>
                                <p class="lp-mini-field">Leeds</p>
                            </div>
                            {!! $avatar(['skin' => 'd', 'hair' => 'afro', 'top' => 'hsl(var(--foreground))'], 'absolute bottom-3 right-3 h-10 w-10 ring-2 ring-card') !!}
                        </div>
                        <div>
                            <p class="text-[15px] font-semibold">Create your shop</p>
                            <p class="mt-1 text-[13px] leading-relaxed text-muted-foreground">Name, city, opening hours.</p>
                        </div>
                    </li>

                    <li class="lp-pstep" style="--i:1">
                        <span class="lp-pnode">02</span>
                        <div class="lp-pviz" aria-hidden="true">
                            <div class="flex w-[82%] flex-col gap-1.5">
                                <span class="lp-mini-chip">Skin fade <b>45m</b></span>
                                <span class="lp-mini-chip">Beard trim <b>20m</b></span>
                                <div class="mt-1 flex -space-x-2">
                                    {!! $avatar(['skin' => 'c', 'hair' => 'locs', 'top' => 'hsl(var(--primary-deep))'], 'h-8 w-8 ring-2 ring-card') !!}
                                    {!! $avatar(['skin' => 'd', 'hair' => 'fade', 'beard' => true, 'top' => 'hsl(var(--primary))'], 'h-8 w-8 ring-2 ring-card') !!}
                                    {!! $avatar(['skin' => 'b', 'hair' => 'long', 'hc' => '#414755', 'top' => 'hsl(var(--primary))'], 'h-8 w-8 ring-2 ring-card') !!}
                                </div>
                            </div>
                        </div>
                        <div>
                            <p class="text-[15px] font-semibold">Add services &amp; staff</p>
                            <p class="mt-1 text-[13px] leading-relaxed text-muted-foreground">What you offer, who takes it.</p>
                        </div>
                    </li>

                    <li class="lp-pstep" style="--i:2">
                        <span class="lp-pnode">03</span>
                        <div class="lp-pviz" aria-hidden="true">
                            <div class="w-[84%] space-y-1.5">
                                <p class="lp-bubble">Book your next visit here:</p>
                                <p class="lp-bubble lp-bubble-link">/book/north-and-co</p>
                                <p class="lp-bubble lp-bubble-in">Booked for Thursday!</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-[15px] font-semibold">Share your link</p>
                            <p class="mt-1 text-[13px] leading-relaxed text-muted-foreground">Bio, WhatsApp, a QR by the till.</p>
                        </div>
                    </li>

                    <li class="lp-pstep" style="--i:3">
                        <span class="lp-pnode">04</span>
                        <div class="lp-pviz" aria-hidden="true">
                            <div class="grid w-[76%] grid-cols-4 gap-1">
                                @foreach (range(1, 12) as $cell)
                                    <span class="h-4 rounded {{ $cell === 7 ? 'lp-mini-hit bg-primary' : 'bg-card' }}"></span>
                                @endforeach
                            </div>
                            {!! $avatar(['skin' => 'b', 'hair' => 'curls', 'hc' => '#5b3824', 'top' => 'hsl(var(--primary))'], 'absolute bottom-3 left-3 h-10 w-10 ring-2 ring-card') !!}
                        </div>
                        <div>
                            <p class="text-[15px] font-semibold">Clients book</p>
                            <p class="mt-1 text-[13px] leading-relaxed text-muted-foreground">Straight into the right diary.</p>
                        </div>
                    </li>

                    <li class="lp-pstep" style="--i:4">
                        <span class="lp-pnode">05</span>
                        <div class="lp-pviz" aria-hidden="true">
                            <div class="w-[84%] space-y-1">
                                @foreach ([['10:00', 'badge-success', 'Done'], ['11:30', 'badge-outline', 'Next'], ['14:00', 'badge-outline', 'Booked']] as $r)
                                    <div class="flex items-center justify-between rounded-md bg-card px-2 py-1.5 text-[10.5px]">
                                        <b class="tabular-nums">{{ $r[0] }}</b>
                                        <span class="{{ $r[1] }} px-1.5 py-0.5 text-[9.5px]">{{ $r[2] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <p class="text-[15px] font-semibold">Run your day</p>
                            <p class="mt-1 text-[13px] leading-relaxed text-muted-foreground">The book, the notes, the chairs.</p>
                        </div>
                    </li>
                </ol>
            </div>
        </section>

        {{-- 7. Day in the life --}}
        <section id="workday" class="border-y border-border bg-card py-20 sm:py-28" data-day>
            <div class="lp-wrap">
                <div class="lp-rv mx-auto max-w-3xl text-center">
                    <p class="lp-kicker"><b>06</b><i></i>The workday</p>
                    <h2 class="lp-h2 mt-4">From first booking to last chair.</h2>
                    <p class="lp-lede mx-auto mt-5">Cutcost sits in the middle of the day. Every booking, arrival and change goes through one place, so nobody works from an old version of the diary.</p>
                </div>

                @php
                    $moments = [
                        ['08:45', 'Open', 'Hours, chairs and today’s book are already set.', 'Shop open · 3 stylists on the floor', '<path d="M4 20V9l8-5 8 5v11" /><path d="M9 20v-6h6v6" />'],
                        ['09:10', 'Bookings in', 'Your link fills gaps while you’re cutting.', 'New booking · Alex M. · 14:00 via your link', '<rect x="7" y="3" width="10" height="18" rx="2" /><path d="M11 17h2" />'],
                        ['10:00', 'Clients arrive', 'The book matches who walks in.', 'Arrived · Jordan L. · Skin fade with Maya', '<circle cx="12" cy="8" r="3.5" /><path d="M5 20c1-4 3.6-6 7-6s6 2 7 6" />'],
                        ['12:30', 'Regulars recognised', 'Notes and last visit, before they sit.', 'Regular · Riley K. · 9th visit · note on file', '<path d="M12 4l2.3 4.8 5.2.7-3.8 3.6.9 5.2L12 15.8l-4.6 2.5.9-5.2-3.8-3.6 5.2-.7z" />'],
                        ['15:00', 'Staff update', 'Priya’s 16:00 moves to Omar in two taps.', 'Moved · Nia O. 16:00 → Omar', '<path d="M4 8h13l-3-3M20 16H7l3 3" />'],
                        ['18:45', 'Close', 'Tomorrow’s first chair is already named.', 'Day closed · Tomorrow starts 09:00 with Sam R.', '<path d="M20 14.5A8 8 0 1 1 9.5 4a6.5 6.5 0 0 0 10.5 10.5z" />'],
                    ];
                @endphp

                <div class="lp-day mt-14 grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,390px)_minmax(0,1fr)] lg:items-center lg:gap-12">
                    @foreach ([[0, 1, 2], [3, 4, 5]] as $side => $ids)
                        <ol class="{{ $side === 0 ? 'lp-day-l' : 'lp-day-r lg:order-3' }} grid gap-3 lg:gap-6">
                            @foreach ($ids as $id)
                                @php $m = $moments[$id]; @endphp
                                <li class="lp-moment is-on" data-moment="{{ $id }}">
                                    <span class="lp-micon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $m[4] !!}</svg></span>
                                    <div class="min-w-0">
                                        <p class="flex items-baseline gap-2"><span class="text-[11.5px] font-semibold tabular-nums text-primary">{{ $m[0] }}</span><span class="text-[14px] font-semibold">{{ $m[1] }}</span></p>
                                        <p class="mt-0.5 text-[13px] leading-relaxed text-muted-foreground">{{ $m[2] }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    @endforeach

                    <div class="lp-hub ui order-first lg:order-2" aria-label="Cutcost’s record of the day">
                        <div class="flex items-center justify-between gap-3 bg-sidebar px-4 py-3 text-white">
                            <span class="brand-logo brand-logo-light brand-logo-sm">Cut<span class="brand-logo-accent">cost</span></span>
                            <span class="text-[12px] font-medium tabular-nums text-sidebar-foreground" data-day-clock>18:45</span>
                        </div>
                        <div class="h-1 bg-secondary"><div class="lp-hub-bar h-full bg-primary" data-day-bar style="width:100%"></div></div>
                        <ul class="space-y-1.5 p-3">
                            @foreach ($moments as $id => $m)
                                <li class="lp-feed is-on flex items-start gap-2.5 rounded-lg border border-border px-3 py-2.5" data-feed="{{ $id }}">
                                    <span class="mt-0.5 w-10 shrink-0 text-[11px] font-semibold tabular-nums text-muted-foreground">{{ $m[0] }}</span>
                                    <span class="text-[12.5px] leading-snug">{{ $m[3] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        {{-- 8. Product showcase --}}
        @php
            $icons = [
                'dashboard' => '<rect x="3" y="3" width="7" height="9" rx="1.5" /><rect x="14" y="3" width="7" height="5" rx="1.5" /><rect x="14" y="12" width="7" height="9" rx="1.5" /><rect x="3" y="16" width="7" height="5" rx="1.5" />',
                'clients' => '<circle cx="9" cy="8" r="3.5" /><path d="M2.5 20c.6-3.6 3.2-5.6 6.5-5.6s5.9 2 6.5 5.6" /><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18.5 14.6c1.8.7 2.8 2.5 3 5.4" />',
                'bookings' => '<rect x="3.5" y="5" width="17" height="15.5" rx="2.5" /><path d="M3.5 10h17M8 3v4M16 3v4" />',
                'services' => '<circle cx="6" cy="6" r="3" /><circle cx="6" cy="18" r="3" /><path d="M20 4L8.1 15.9M14.5 14.5L20 20M8.1 8.1L12 12" />',
                'staff' => '<circle cx="12" cy="7.5" r="3.5" /><path d="M5 20.5c.8-4 3.6-6.3 7-6.3s6.2 2.3 7 6.3" />',
                'payments' => '<rect x="2.5" y="5" width="19" height="14" rx="2.5" /><path d="M2.5 10h19M6.5 15h4" />',
                'settings' => '<circle cx="12" cy="12" r="3" /><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z" />',
            ];
            $svgIcon = fn (string $name, string $cls = 'h-4 w-4') => "<svg class='{$cls}' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round' aria-hidden='true'>{$icons[$name]}</svg>";
        @endphp
        <section id="today" class="overflow-hidden py-20 sm:py-28" data-showcase>
            <div class="lp-wrap">
                <div class="lp-rv grid gap-6 lg:grid-cols-2 lg:items-end">
                    <div>
                        <p class="lp-kicker"><b>07</b><i></i>The dashboard</p>
                        <h2 class="lp-h2 mt-4">Everything happening today. One screen.</h2>
                    </div>
                    <p class="lp-lede lg:justify-self-end">The screen you open each morning — today’s appointments, clients, stylists, services and your booking link, laid out exactly as the product is.</p>
                </div>

                <div class="lp-rv relative mt-14" style="--i:1">
                    <div class="lp-app ui" aria-hidden="true">
                        <aside class="lp-app-side hidden md:flex">
                            <span class="brand-logo brand-logo-light brand-logo-sm px-2.5">Cut<span class="brand-logo-accent">cost</span></span>
                            <div class="sidebar-shop-card mx-0 mt-5">
                                <span class="flex h-7 w-7 items-center justify-center rounded-md bg-primary text-[11px] font-bold text-white">N</span>
                                <span class="min-w-0"><span class="block truncate text-[12px] font-semibold text-white">North &amp; Co.</span><span class="block truncate text-[10.5px]">Shop plan</span></span>
                            </div>
                            <nav class="mt-5 grid gap-0.5">
                                @foreach ([['dashboard', 'Dashboard'], ['clients', 'Clients'], ['bookings', 'Bookings'], ['services', 'Services'], ['staff', 'Stylists'], ['payments', 'Payments'], ['settings', 'Settings']] as $k => $nav)
                                    <span class="sidebar-link {{ $k === 0 ? 'sidebar-link-active' : '' }}">{!! $svgIcon($nav[0]) !!}{{ $nav[1] }}</span>
                                @endforeach
                            </nav>
                        </aside>
                        <div class="min-w-0 bg-background">
                            <div class="flex items-end justify-between gap-3 border-b border-border bg-card px-4 py-4 sm:px-6">
                                <div class="min-w-0">
                                    <p class="page-band-eyebrow">Good morning, Dee</p>
                                    <p class="mt-1 truncate font-display text-xl font-semibold tracking-[-0.022em] sm:text-[24px]">North &amp; Co.</p>
                                    <p class="text-[12.5px] text-muted-foreground">Leeds · Wednesday 30 September</p>
                                </div>
                                <span class="btn-primary pointer-events-none shrink-0">+ New booking</span>
                            </div>
                            <div class="space-y-4 p-3 sm:p-6">
                                <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
                                    @foreach ([['clients', 'Clients', '132', 'In your CRM'], ['services', 'Services', '8', 'Bookable'], ['staff', 'Stylists', '4', 'Taking bookings'], ['bookings', 'Bookings', '486', 'All time']] as $stat)
                                        <div class="stat-card">
                                            <div class="flex items-center justify-between">
                                                <p class="stat-label">{{ $stat[1] }}</p>
                                                <span class="stat-card-icon">{!! $svgIcon($stat[0], 'h-[15px] w-[15px]') !!}</span>
                                            </div>
                                            <p class="stat-value mt-2.5">{{ $stat[2] }}</p>
                                            <p class="mt-1 text-[12px] text-muted-foreground">{{ $stat[3] }}</p>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="grid gap-4 lg:grid-cols-3">
                                    <div class="card lg:col-span-2">
                                        <div class="card-header-bordered flex-row items-center justify-between space-y-0">
                                            <div>
                                                <p class="card-title">Today's appointments</p>
                                                <p class="card-description">Wednesday 30 September</p>
                                            </div>
                                            <span class="btn-ghost">View all</span>
                                        </div>
                                        <div class="divide-y divide-border/70">
                                            @foreach ([
                                                ['10:00', 'Jordan Lewis', 'Skin fade · Maya', 'badge-success', 'Completed', false],
                                                ['11:30', 'Riley Kaur', 'Cut & shape · Maya', 'badge-success', 'Completed', false],
                                                ['12:00', 'Dev Patel', 'Skin fade · Omar', 'badge-outline', 'Scheduled', false],
                                                ['13:00', 'Nia Okafor', 'Colour & cut · Priya', 'badge-warning', 'Awaiting payment', false],
                                                ['14:00', 'Alex Morgan', 'Skin fade · Maya', 'badge-outline', 'Scheduled', true],
                                            ] as $row)
                                                <div class="lp-arow flex items-center justify-between gap-3 px-4 py-2.5 sm:px-5 {{ $row[5] ? 'is-new' : '' }}" @if ($row[5]) data-showcase-new @endif>
                                                    <div class="flex min-w-0 items-center gap-3">
                                                        <span class="flex h-9 w-[3.25rem] shrink-0 items-center justify-center rounded-md bg-secondary text-[12.5px] font-semibold tabular-nums">{{ $row[0] }}</span>
                                                        <div class="min-w-0">
                                                            <p class="truncate text-[13px] font-medium">{{ $row[1] }} @if ($row[5])<span class="lp-rose ml-1 text-[11px] font-semibold">New · via your link</span>@endif</p>
                                                            <p class="truncate text-[12px] text-muted-foreground">{{ $row[2] }}</p>
                                                        </div>
                                                    </div>
                                                    <span class="{{ $row[3] }} badge-dot shrink-0">{{ $row[4] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-4">
                                        <div class="card">
                                            <div class="card-header"><p class="card-title">Quick actions</p></div>
                                            <div class="card-content grid gap-1">
                                                @foreach ([['bookings', 'New booking'], ['clients', 'Add client'], ['services', 'Add service'], ['staff', 'Add stylist']] as $qa)
                                                    <span class="quick-action"><span class="quick-action-icon">{!! $svgIcon($qa[0], 'h-[15px] w-[15px]') !!}</span>{{ $qa[1] }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-header">
                                                <p class="card-title">Client booking link</p>
                                                <p class="card-description">Share so clients can book themselves</p>
                                            </div>
                                            <div class="card-content space-y-2.5">
                                                <code class="block break-all rounded-lg border border-border bg-secondary/70 px-3 py-2 text-[12px] leading-relaxed text-muted-foreground">https://{{ $bookLink }}</code>
                                                <div class="flex gap-2">
                                                    <span class="btn-secondary flex-1 justify-center">Open</span>
                                                    <span class="btn-primary flex-1 justify-center">Copy</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <span class="lp-callout hidden xl:flex" style="left:-1.5rem;top:47%">Today’s book</span>
                    <span class="lp-callout hidden xl:flex" style="right:-1.5rem;top:74%">Your private link</span>
                    <span class="lp-callout hidden xl:flex" style="right:-1.5rem;top:22%">Who’s taking bookings</span>
                </div>
            </div>
        </section>

        {{-- 9. Built for the people behind the chair --}}
        <section id="who" class="border-y border-border bg-secondary/50 py-20 sm:py-28">
            <div class="lp-wrap">
                <div class="lp-rv max-w-2xl">
                    <p class="lp-kicker"><b>08</b><i></i>Who it’s for</p>
                    <h2 class="lp-h2 mt-4">Built for the people behind the chair.</h2>
                </div>
                <div class="mt-12 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    {{-- Solo stylist --}}
                    <article class="lp-rv lp-lift flex flex-col overflow-hidden rounded-3xl border border-border bg-card" style="--i:0">
                        <svg viewBox="0 0 280 180" class="h-auto w-full" aria-hidden="true">
                            <rect width="280" height="180" fill="hsl(var(--accent))" />
                            <path d="M34 150V58A36 36 0 0 1 106 58V150Z" fill="hsl(var(--card))" stroke="hsl(var(--border))" stroke-width="2.5" />
                            <path d="M44 144V60A26 26 0 0 1 96 60V144Z" fill="hsl(var(--secondary))" />
                            <rect x="26" y="150" width="88" height="8" rx="4" fill="hsl(var(--foreground))" />
                            <path d="M232 150c-6-22 2-40 14-52M240 150c2-20 14-34 26-38M226 150c-14-14-18-30-12-44" stroke="hsl(var(--success) / .55)" stroke-width="5" stroke-linecap="round" />
                            <rect x="218" y="146" width="36" height="34" rx="6" fill="hsl(var(--card))" stroke="hsl(var(--border))" stroke-width="2" />
                            {!! $person(['x' => 110, 'y' => 26, 's' => 0.9, 'skin' => 'a', 'hair' => 'long', 'hc' => '#5b3824', 'top' => 'hsl(var(--pink-deep))', 'outfit' => 'tunic', 'prop' => 'scissors', 'blink' => 0.5]) !!}
                        </svg>
                        <div class="flex flex-1 flex-col p-6">
                            <p class="lp-label">One chair</p>
                            <h3 class="mt-2 text-[18px] font-semibold tracking-tight">Solo stylists</h3>
                            <p class="mt-2 flex-1 text-[13.5px] leading-relaxed text-muted-foreground">Your chair, your regulars, your link. Keep every client’s notes with you and let them rebook from the link in your bio.</p>
                            <p class="lp-rose mt-5 text-[12px] font-medium">Starter plan</p>
                        </div>
                    </article>

                    {{-- Barbershop --}}
                    <article class="lp-rv lp-lift flex flex-col overflow-hidden rounded-3xl border border-border bg-card" style="--i:1">
                        <svg viewBox="0 0 280 180" class="h-auto w-full" aria-hidden="true">
                            <defs><clipPath id="pole"><rect x="28" y="40" width="16" height="92" rx="8" /></clipPath></defs>
                            <rect width="280" height="180" fill="hsl(var(--secondary))" />
                            <rect x="24" y="30" width="24" height="12" rx="6" fill="hsl(var(--foreground))" />
                            <rect x="28" y="40" width="16" height="92" rx="8" fill="hsl(var(--card))" />
                            <g clip-path="url(#pole)">
                                <path d="M20 52l32-18M20 72l32-18M20 92l32-18M20 112l32-18M20 132l32-18M20 152l32-18" stroke="hsl(var(--primary))" stroke-width="7" />
                                <path d="M20 62l32-18M20 82l32-18M20 102l32-18M20 122l32-18M20 142l32-18" stroke="hsl(var(--pink))" stroke-width="7" />
                            </g>
                            <rect x="24" y="130" width="24" height="12" rx="6" fill="hsl(var(--foreground))" />
                            {!! $chairBack(92, 206, 0.5) !!}
                            {!! $chairFront(92, 206, 0.5) !!}
                            {!! $person(['x' => 128, 'y' => 30, 's' => 0.9, 'skin' => 'd', 'hair' => 'fade', 'beard' => true, 'top' => 'hsl(var(--primary))', 'outfit' => 'apron', 'prop' => 'clippers', 'blink' => 1.7]) !!}
                            {!! $chairBack(252, 206, 0.5) !!}
                            {!! $chairFront(252, 206, 0.5) !!}
                        </svg>
                        <div class="flex flex-1 flex-col p-6">
                            <p class="lp-label">A floor of chairs</p>
                            <h3 class="mt-2 text-[18px] font-semibold tracking-tight">Barbershops</h3>
                            <p class="mt-2 flex-1 text-[13.5px] leading-relaxed text-muted-foreground">Walk-ins and online bookings in the same diary, each barber on their own column, no public directory.</p>
                            <p class="lp-rose mt-5 text-[12px] font-medium">Shop plan</p>
                        </div>
                    </article>

                    {{-- Beauty studio --}}
                    <article class="lp-rv lp-lift flex flex-col overflow-hidden rounded-3xl border border-border bg-card" style="--i:2">
                        <svg viewBox="0 0 280 180" class="h-auto w-full" aria-hidden="true">
                            <rect width="280" height="180" fill="hsl(var(--accent))" />
                            <path d="M214 34c0 22 10 30 26 30" stroke="hsl(var(--foreground))" stroke-width="3" stroke-linecap="round" />
                            <path d="M228 64a14 10 0 0 1 28 0z" fill="hsl(var(--foreground))" />
                            <path d="M232 66l-8 30h40l-8-30" fill="hsl(var(--warning) / .12)" />
                            <rect x="150" y="118" width="124" height="18" rx="9" fill="hsl(var(--card))" stroke="hsl(var(--border))" stroke-width="2" />
                            <rect x="160" y="136" width="8" height="30" fill="#414755" />
                            <rect x="256" y="136" width="8" height="30" fill="#414755" />
                            <rect x="152" y="110" width="36" height="12" rx="6" fill="hsl(var(--primary) / .25)" />
                            {!! $person(['x' => 40, 'y' => 26, 's' => 0.9, 'skin' => 'b', 'hair' => 'hijab', 'hc' => 'hsl(var(--primary-deep))', 'top' => 'hsl(var(--foreground))', 'outfit' => 'tunic', 'prop' => 'tablet', 'blink' => 2.4]) !!}
                        </svg>
                        <div class="flex flex-1 flex-col p-6">
                            <p class="lp-label">Treatments &amp; beauty</p>
                            <h3 class="mt-2 text-[18px] font-semibold tracking-tight">Beauty studios</h3>
                            <p class="mt-2 flex-1 text-[13.5px] leading-relaxed text-muted-foreground">Longer services and client notes that make 90-minute treatments easy to schedule and easy to prepare for.</p>
                            <p class="lp-rose mt-5 text-[12px] font-medium">Shop plan</p>
                        </div>
                    </article>

                    {{-- Growing salon --}}
                    <article class="lp-rv lp-lift flex flex-col overflow-hidden rounded-3xl border border-border bg-card" style="--i:3">
                        <svg viewBox="0 0 280 180" class="h-auto w-full" aria-hidden="true">
                            <rect width="280" height="180" fill="hsl(var(--secondary))" />
                            @foreach ([20, 104, 188] as $mx)
                                <rect x="{{ $mx }}" y="26" width="72" height="92" rx="36" fill="hsl(var(--card))" stroke="hsl(var(--border))" stroke-width="2" />
                            @endforeach
                            <g transform="translate(26 44) scale(.5)">{!! $person(['skin' => 'c', 'hair' => 'curls', 'top' => 'hsl(var(--primary))', 'outfit' => 'apron']) !!}</g>
                            <g transform="translate(194 44) scale(.5)">{!! $person(['skin' => 'a', 'hair' => 'buzz', 'hc' => '#414755', 'top' => 'hsl(var(--primary-deep))', 'outfit' => 'apron', 'blink' => 1]) !!}</g>
                            @foreach ([20, 104, 188] as $mx)
                                <rect x="{{ $mx - 6 }}" y="118" width="84" height="62" fill="hsl(var(--secondary))" />
                                <rect x="{{ $mx - 6 }}" y="118" width="84" height="7" rx="3.5" fill="hsl(var(--foreground))" />
                            @endforeach
                            {!! $person(['x' => 86, 'y' => 34, 's' => 0.9, 'skin' => 'c', 'hair' => 'bun', 'top' => 'hsl(var(--foreground))', 'outfit' => 'jacket', 'glasses' => true, 'blink' => 3]) !!}
                        </svg>
                        <div class="flex flex-1 flex-col p-6">
                            <p class="lp-label">Growing team</p>
                            <h3 class="mt-2 text-[18px] font-semibold tracking-tight">Growing salons</h3>
                            <p class="mt-2 flex-1 text-[13.5px] leading-relaxed text-muted-foreground">More chairs, same system. Add stylists and assign chairs while every client relationship stays with the business.</p>
                            <p class="lp-rose mt-5 text-[12px] font-medium">Studio plan</p>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        {{-- 10. Pricing --}}
        <section id="pricing" class="py-20 sm:py-28">
            <div class="lp-wrap">
                <div class="lp-rv mx-auto max-w-3xl text-center">
                    <p class="lp-kicker"><b>09</b><i></i>Pricing</p>
                    <h2 class="lp-h2 mt-4">Simple pricing. No cut from your bookings.</h2>
                    <p class="lp-lede mx-auto mt-5">One flat monthly price for the shop. Marketplaces take a percentage of every visit — Cutcost never does.</p>
                </div>

                <div class="lp-rv mx-auto mt-12 grid max-w-4xl items-center gap-6 rounded-3xl border border-border bg-card p-5 sm:p-8 md:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)]" data-fee>
                    <div class="relative" aria-hidden="true">
                        <svg viewBox="0 0 420 210" class="h-auto w-full">
                            <path d="M30 186H410M30 186V14" stroke="hsl(var(--border))" stroke-width="2" />
                            <path class="lp-fee-line" pathLength="1" d="M30 180L410 30" stroke="hsl(var(--muted-foreground) / .6)" stroke-width="3" stroke-linecap="round" />
                            <path class="lp-fee-line" pathLength="1" d="M30 120H410" stroke="hsl(var(--primary))" stroke-width="3.5" stroke-linecap="round" style="transition-delay:.35s" />
                            <circle cx="410" cy="30" r="5" fill="hsl(var(--muted-foreground))" />
                            <circle cx="410" cy="120" r="5" fill="hsl(var(--primary))" />
                        </svg>
                        <span class="absolute bottom-0 right-0 text-[11px] font-medium text-muted-foreground">More bookings →</span>
                        <span class="absolute left-0 top-0 text-[11px] font-medium text-muted-foreground">What you pay</span>
                    </div>
                    <div class="space-y-4">
                        <div class="flex gap-3">
                            <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-muted-foreground/60"></span>
                            <div>
                                <p class="text-[14px] font-semibold">Marketplace percentage</p>
                                <p class="mt-0.5 text-[13px] leading-relaxed text-muted-foreground">Grows with every booking you take. You pay more the busier you get.</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-primary"></span>
                            <div>
                                <p class="text-[14px] font-semibold">Cutcost · Flat monthly · {{ $zeroPrice }} per booking</p>
                                <p class="mt-0.5 text-[13px] leading-relaxed text-muted-foreground">From {{ $fromPrice }}/month, however many bookings you take. The client stays yours.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-12 grid gap-5 lg:grid-cols-3 lg:items-center">
                    @foreach ($plans as $i => $plan)
                        <div class="lp-rv lp-lift relative flex flex-col rounded-3xl border bg-card p-6 sm:p-7 {{ $plan['featured'] ? 'lp-plan-featured border-primary/40 lg:py-9' : 'border-border' }}" style="--i:{{ $i }}">
                            @if ($plan['featured'])
                                <span class="absolute right-5 top-5 rounded-full px-2.5 py-1 text-[11px] font-semibold text-white" style="background:hsl(var(--pink-deep))">Best for busy shops</span>
                            @endif
                            <h3 class="text-[18px] font-semibold tracking-tight">{{ $plan['name'] }}</h3>
                            <p class="mt-1 text-[13.5px] text-muted-foreground">{{ $plan['description'] }}</p>
                            <p class="mt-6 flex items-end gap-1.5">
                                <span class="font-display text-[2.6rem] font-extrabold leading-none tracking-[-0.04em] tabular-nums">{{ $plan['price'] }}</span>
                                <span class="mb-1 text-[13px] text-muted-foreground">/ month</span>
                            </p>
                            <ul class="mt-6 flex-1 space-y-2.5 border-t border-border pt-6 text-[13.5px]">
                                @foreach ($plan['features'] as $feature)
                                    <li class="flex gap-2.5">
                                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-primary" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3.5 8.5l3 3 6-7" /></svg>
                                        {{ $feature }}
                                    </li>
                                @endforeach
                            </ul>
                            @auth
                                <a href="{{ route('dashboard') }}" class="{{ $plan['featured'] ? 'btn-primary' : 'btn-secondary' }} lp-btn mt-8 h-11 w-full justify-center">Go to dashboard</a>
                            @else
                                <a href="{{ route('register', ['plan' => $plan['slug']]) }}" class="{{ $plan['featured'] ? 'btn-primary' : 'btn-secondary' }} lp-btn mt-8 h-11 w-full justify-center">{{ $plan['cta'] }}</a>
                            @endauth
                        </div>
                    @endforeach
                </div>
                <p class="lp-rv mt-8 text-center text-[13px] text-muted-foreground">Every plan includes your private booking link. Cancel anytime. No marketplace.</p>
            </div>
        </section>

        {{-- 11. Trust --}}
        <section id="trust" class="border-t border-border bg-card py-20 sm:py-28">
            <div class="lp-wrap grid gap-12 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)] lg:gap-20">
                <div class="lp-rv lg:sticky lg:top-28 lg:self-start">
                    <p class="lp-kicker"><b>10</b><i></i>What you keep</p>
                    <h2 class="lp-h2 mt-4">The relationship stays in your shop.</h2>
                    <p class="lp-lede mt-5">Cutcost is the software you run your business on — not a platform your clients belong to.</p>
                </div>
                <ul class="divide-y divide-border border-y border-border">
                    @foreach ([
                        ['Private booking links', 'Clients land on your page, with your services and your stylists. There’s no directory of nearby shops to browse away to.', '<path d="M10 13a5 5 0 0 0 7.07 0l2.83-2.83a5 5 0 0 0-7.07-7.07L11.5 4.5" /><path d="M14 11a5 5 0 0 0-7.07 0L4.1 13.83a5 5 0 0 0 7.07 7.07l1.33-1.4" />'],
                        ['Client CRM', 'Contact details, notes and visit history sit in your account, so the next visit starts where the last one ended.', '<circle cx="9" cy="8" r="3.5" /><path d="M2.5 20c.6-3.6 3.2-5.6 6.5-5.6s5.9 2 6.5 5.6" /><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18.5 14.6c1.8.7 2.8 2.5 3 5.4" />'],
                        ['Staff scheduling', 'Each stylist has their own column and their own login. Owners see the whole floor.', '<rect x="3.5" y="5" width="17" height="15.5" rx="2.5" /><path d="M3.5 10h17M8 3v4M16 3v4M8 14h3M13 14h3M8 17h3" />'],
                        ['Workday visibility', 'Today’s book on one screen — who’s in, who’s next, who’s free — instead of rebuilt from messages.', '<rect x="3" y="4" width="18" height="13" rx="2" /><path d="M8 21h8M12 17v4M7 12l3-3 3 2 4-4" />'],
                        ['Direct customer relationships', 'You send the link and you keep the client. Cutcost doesn’t advertise other shops to them.', '<path d="M4 12h16M14 6l6 6-6 6" />'],
                    ] as $k => $t)
                        <li class="lp-rv flex gap-5 py-6 sm:py-7" style="--i:{{ $k }}">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-accent text-primary">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $t[2] !!}</svg>
                            </span>
                            <div>
                                <p class="text-[16px] font-semibold tracking-tight">{{ $t[0] }}</p>
                                <p class="mt-1.5 max-w-lg text-[14px] leading-relaxed text-muted-foreground">{{ $t[1] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        {{-- 12. Closing CTA --}}
        <section id="get-started" class="lp-close bg-sidebar text-white">
            <div class="lp-wrap grid items-center gap-12 py-20 sm:py-24 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.05fr)] lg:gap-10">
                <div class="lp-rv">
                    <h2 class="lp-h2 text-white">Spend less time managing. Spend more time behind the chair.</h2>
                    <p class="lp-lede mt-5 text-sidebar-foreground">Cutcost handles the admin so salon and beauty professionals can focus on the people in their chair.</p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn-primary lp-btn lp-btn-lg">Open dashboard</a>
                        @else
                            <a href="{{ route('register') }}" class="btn-primary lp-btn lp-btn-lg">Create your shop</a>
                        @endauth
                        <a href="#pricing" class="lp-btn lp-btn-lg lp-btn-ghost-light btn-base">See pricing</a>
                    </div>
                    <p class="mt-5 text-[13px] text-sidebar-foreground">From {{ $fromPrice }}/month · No marketplace fees · Cancel anytime.</p>
                </div>
                <div class="lp-rv relative" style="--i:1" role="img" aria-label="An organised salon: two stylists cut clients’ hair at calm, tidy stations while a tablet on the wall shows the day fully booked.">
                    <svg viewBox="0 0 560 420" class="h-auto w-full" aria-hidden="true">
                        <defs><clipPath id="close-room"><rect x="0" y="0" width="560" height="420" rx="32" /></clipPath></defs>
                        <g clip-path="url(#close-room)">
                            <rect width="560" height="420" fill="hsl(var(--accent))" />
                            <rect y="356" width="560" height="64" fill="hsl(var(--primary) / .08)" />
                            <path d="M0 356H560" stroke="hsl(var(--primary) / .14)" stroke-width="2" />
                            @foreach ([140, 410] as $lx)
                                <path d="M{{ $lx }} 0V34" stroke="hsl(var(--foreground))" stroke-width="2" />
                                <path d="M{{ $lx - 18 }} 50a18 16 0 0 1 36 0z" fill="hsl(var(--foreground))" />
                                <ellipse cx="{{ $lx }}" cy="52" rx="10" ry="3" fill="hsl(var(--warning) / .5)" />
                            @endforeach
                            @foreach ([140, 410] as $mx)
                                <path d="M{{ $mx - 62 }} 238V120A62 62 0 0 1 {{ $mx + 62 }} 120V238Z" fill="hsl(var(--card))" stroke="hsl(var(--border))" stroke-width="3" />
                                <path d="M{{ $mx - 52 }} 232V122A52 52 0 0 1 {{ $mx + 52 }} 122V232Z" fill="hsl(var(--secondary))" />
                                <rect x="{{ $mx - 76 }}" y="238" width="152" height="10" rx="5" fill="hsl(var(--foreground))" />
                            @endforeach
                            <g transform="translate(250 128)">
                                <rect width="60" height="84" rx="8" fill="hsl(var(--foreground))" />
                                <rect x="4" y="4" width="52" height="76" rx="5" fill="hsl(var(--card))" />
                                <rect x="10" y="11" width="22" height="4" rx="2" fill="hsl(var(--primary))" />
                                @foreach (range(0, 5) as $r)
                                    <rect x="10" y="{{ 21 + $r * 9 }}" width="40" height="6" rx="2" fill="hsl(var(--primary) / {{ $r < 5 ? '.28' : '.12' }})" />
                                @endforeach
                            </g>

                            {!! $chairBack(140, 402, 0.8) !!}
                            {!! $person(['x' => 98, 'y' => 206, 's' => 0.7, 'skin' => 'a', 'hair' => 'bob', 'hc' => '#5b3824', 'outfit' => 'cape', 'mood' => 'calm']) !!}
                            {!! $chairFront(140, 402, 0.8) !!}
                            {!! $person(['x' => 186, 'y' => 146, 's' => 0.82, 'full' => true, 'flip' => true, 'skin' => 'c', 'hair' => 'locs', 'top' => 'hsl(var(--primary-deep))', 'outfit' => 'apron', 'prop' => 'scissors', 'blink' => 0.9]) !!}

                            {!! $chairBack(410, 402, 0.8) !!}
                            {!! $person(['x' => 368, 'y' => 206, 's' => 0.7, 'skin' => 'd', 'hair' => 'buzz', 'outfit' => 'cape', 'mood' => 'calm']) !!}
                            {!! $chairFront(410, 402, 0.8) !!}
                            {!! $person(['x' => 456, 'y' => 146, 's' => 0.82, 'full' => true, 'flip' => true, 'skin' => 'd', 'hair' => 'fade', 'beard' => true, 'top' => 'hsl(var(--primary))', 'outfit' => 'apron', 'prop' => 'clippers', 'blink' => 2.2]) !!}
                        </g>
                    </svg>
                    <span class="lp-close-tag" style="left:6%;top:6%"><span class="h-1.5 w-1.5 rounded-full bg-success"></span>Chair 1 · Booked</span>
                    <span class="lp-close-tag" style="right:6%;top:6%"><span class="h-1.5 w-1.5 rounded-full bg-success"></span>Chair 2 · Booked</span>
                </div>
            </div>
        </section>
        </main>

        {{-- 13. Footer --}}
        @php
            $legalLinks = array_filter(
                ['privacy' => 'Privacy policy', 'terms' => 'Terms of service', 'cookies' => 'Cookie policy'],
                fn ($name) => \Illuminate\Support\Facades\Route::has($name),
                ARRAY_FILTER_USE_KEY,
            );
        @endphp
        <footer id="footer" class="bg-background">
            <div class="lp-wrap py-16 sm:py-20">
                <div class="grid gap-12 sm:grid-cols-2 lg:grid-cols-[minmax(0,1.4fr)_repeat(4,minmax(0,1fr))] lg:gap-8">
                    <div class="sm:col-span-2 lg:col-span-1">
                        <a href="{{ route('home') }}" class="brand-logo brand-logo-gradient" aria-label="Cutcost home">Cut<span class="brand-logo-accent">cost</span></a>
                        <p class="mt-6 font-display text-[28px] font-extrabold leading-[1.02] tracking-[-0.04em]">Run your shop.<br><span class="text-primary">Fill your <span class="lp-hl">chair.</span></span></p>
                        <p class="mt-4 max-w-xs text-[13px] leading-relaxed text-muted-foreground">The private CRM and booking platform for salons, barbershops, stylists and beauty professionals. No marketplace.</p>
                    </div>
                    <nav aria-label="Product">
                        <p class="lp-label">Product</p>
                        <ul class="mt-4 space-y-1 text-[13.5px]">
                            <li><a href="#features" class="lp-flink">Features</a></li>
                            <li><a href="#booking" class="lp-flink">Booking page</a></li>
                            <li><a href="#today" class="lp-flink">Dashboard</a></li>
                            <li><a href="#how-it-works" class="lp-flink">How it works</a></li>
                            <li><a href="#pricing" class="lp-flink">Pricing</a></li>
                        </ul>
                    </nav>
                    <nav aria-label="Account">
                        <p class="lp-label">Account</p>
                        <ul class="mt-4 space-y-1 text-[13.5px]">
                            @auth
                                <li><a href="{{ route('dashboard') }}" class="lp-flink">Dashboard</a></li>
                                <li><a href="{{ route('profile.edit') }}" class="lp-flink">Profile</a></li>
                            @else
                                <li><a href="{{ route('login') }}" class="lp-flink">Log in</a></li>
                                <li><a href="{{ route('register') }}" class="lp-flink">Create your shop</a></li>
                                <li><a href="{{ route('waitlist') }}" class="lp-flink">Join the waitlist</a></li>
                            @endauth
                        </ul>
                    </nav>
                    <nav aria-label="Company">
                        <p class="lp-label">Company</p>
                        <ul class="mt-4 space-y-1 text-[13.5px]">
                            <li><a href="#compare" class="lp-flink">Why not a marketplace</a></li>
                            <li><a href="#who" class="lp-flink">Who it’s for</a></li>
                            <li><a href="#trust" class="lp-flink">What you keep</a></li>
                        </ul>
                    </nav>
                    <nav aria-label="Legal">
                        <p class="lp-label">Legal</p>
                        <ul class="mt-4 space-y-1 text-[13.5px]">
                            @forelse ($legalLinks as $name => $label)
                                <li><a href="{{ route($name) }}" class="lp-flink">{{ $label }}</a></li>
                            @empty
                                <li class="py-1 text-muted-foreground">Privacy policy <span class="badge-muted ml-1">Soon</span></li>
                                <li class="py-1 text-muted-foreground">Terms of service <span class="badge-muted ml-1">Soon</span></li>
                            @endforelse
                        </ul>
                    </nav>
                </div>
                <div class="mt-14 flex flex-col gap-2 border-t border-border pt-6 text-[12.5px] text-muted-foreground sm:flex-row sm:items-center sm:justify-between">
                    <p>© {{ date('Y') }} Cutcost</p>
                    <p>Private booking. Your clients stay yours.</p>
                </div>
            </div>
        </footer>

        <script>
            (() => {
                const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                const $ = (sel, root = document) => root.querySelector(sel);
                const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];
                const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

                const nav = $('#lp-nav');
                const onScroll = () => nav.classList.toggle('is-solid', window.scrollY > 8);
                window.addEventListener('scroll', onScroll, { passive: true });
                onScroll();

                const menuBtn = $('#lp-menu-btn');
                const menu = $('#lp-menu');
                const setMenu = (open) => {
                    menu.classList.toggle('is-open', open);
                    menuBtn.classList.toggle('is-open', open);
                    menuBtn.setAttribute('aria-expanded', String(open));
                    menuBtn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
                };
                menuBtn.addEventListener('click', () => setMenu(!menu.classList.contains('is-open')));
                $$('a', menu).forEach((a) => a.addEventListener('click', () => setMenu(false)));

                const onVisible = (el, fn, threshold = 0.3) => {
                    if (!el) return;
                    const io = new IntersectionObserver((entries) => {
                        if (entries.some((e) => e.isIntersecting)) {
                            io.disconnect();
                            fn();
                        }
                    }, { threshold });
                    io.observe(el);
                };

                const revealables = $$('.lp-rv, [data-path]');
                if (reduce) {
                    revealables.forEach((el) => el.classList.add('is-in'));
                } else {
                    const io = new IntersectionObserver((entries) => {
                        entries.forEach((e) => {
                            if (!e.isIntersecting) return;
                            e.target.classList.add('is-in');
                            io.unobserve(e.target);
                        });
                    }, { threshold: 0.15, rootMargin: '0px 0px -6% 0px' });
                    revealables.forEach((el) => io.observe(el));
                }

                // Clone `from`, move it onto `to` inside `host`, then drop the clone.
                const fly = (from, to, host, duration = 1000) => {
                    const h = host.getBoundingClientRect();
                    const a = from.getBoundingClientRect();
                    const b = to.getBoundingClientRect();
                    const ghost = from.cloneNode(true);
                    ghost.removeAttribute('data-hero-appt');
                    ghost.removeAttribute('data-jcard');
                    ghost.classList.add('lp-ghost');
                    Object.assign(ghost.style, {
                        left: `${a.left - h.left}px`,
                        top: `${a.top - h.top}px`,
                        right: 'auto',
                        width: `${a.width}px`,
                        animation: 'none',
                        transition: 'none',
                    });
                    host.appendChild(ghost);
                    const dx = b.left + b.width / 2 - (a.left + a.width / 2);
                    const dy = b.top + b.height / 2 - (a.top + a.height / 2);
                    const scale = Math.min(1, (b.height / a.height) * 1.15);
                    return ghost.animate([
                        { transform: 'translate(0, 0) scale(1)', opacity: 1 },
                        { transform: `translate(${dx * 0.5}px, ${dy * 0.5 - 36}px) scale(0.94)`, opacity: 1, offset: 0.5 },
                        { transform: `translate(${dx}px, ${dy}px) scale(${scale})`, opacity: 0.2 },
                    ], { duration, easing: 'cubic-bezier(0.45, 0, 0.2, 1)', fill: 'forwards' }).finished.then(() => ghost.remove());
                };

                // Hero: phone → appointment → today's schedule → stylist confirmation.
                const hero = $('[data-hero]');
                if (hero && !reduce) {
                    const stage = $('[data-hero-stage]', hero);
                    hero.classList.add('is-pre');
                    (async () => {
                        await wait(1500);
                        hero.classList.add('is-appt');
                        await wait(1000);
                        await fly($('[data-hero-appt]', hero), $('[data-hero-slot]', hero), stage, 1050);
                        hero.classList.add('is-slot');
                        await wait(450);
                        hero.classList.add('is-ok');
                    })();
                }

                // Chaos: scroll-scrub on desktop. A phone cannot pin the copy and the
                // scene together, so the same collapse loops while the section is on screen.
                const chaos = $('[data-chaos]');
                if (chaos && !reduce) {
                    const setP = (p) => chaos.style.setProperty('--p', Math.min(1, Math.max(0, p)).toFixed(3));
                    const ease = (k) => (k < 0.5 ? 2 * k * k : 1 - ((-2 * k + 2) ** 2) / 2);
                    const scrubQuery = window.matchMedia('(min-width: 1024px) and (min-height: 640px)');
                    let scrubbing = false;
                    let looping = false;
                    let raf = 0;

                    const updateScrub = () => {
                        const r = chaos.getBoundingClientRect();
                        const total = r.height - window.innerHeight;
                        setP(total <= 0 ? 1 : (-r.top - total * 0.06) / (total * 0.74));
                    };

                    const startScrub = () => {
                        if (scrubbing) return;
                        scrubbing = true;
                        looping = false;
                        cancelAnimationFrame(raf);
                        chaos.classList.add('is-scrub');
                        let ticking = false;
                        const onScroll = () => {
                            if (ticking) return;
                            ticking = true;
                            requestAnimationFrame(() => {
                                ticking = false;
                                if (scrubbing) updateScrub();
                            });
                        };
                        window.addEventListener('scroll', onScroll, { passive: true });
                        window.addEventListener('resize', onScroll);
                        updateScrub();
                    };

                    const startLoop = () => {
                        if (looping) return;
                        looping = true;
                        scrubbing = false;
                        chaos.classList.remove('is-scrub');
                        const cycle = 6200;
                        const origin = performance.now();
                        const frame = (now) => {
                            if (!looping) return;
                            const t = ((now - origin) % cycle) / cycle;
                            let p = 0;
                            if (t < 0.46) p = ease(t / 0.46);
                            else if (t < 0.7) p = 1;
                            else if (t < 0.88) p = 1 - ease((t - 0.7) / 0.18);
                            setP(p);
                            raf = requestAnimationFrame(frame);
                        };
                        raf = requestAnimationFrame(frame);
                    };

                    const sync = () => (scrubQuery.matches ? startScrub() : startLoop());
                    scrubQuery.addEventListener('change', () => {
                        scrubbing = false;
                        looping = false;
                        cancelAnimationFrame(raf);
                        sync();
                    });

                    if (scrubQuery.matches) {
                        startScrub();
                    } else {
                        setP(0);
                        const watch = new IntersectionObserver((entries) => {
                            if (scrubQuery.matches) return;
                            if (entries.some((entry) => entry.isIntersecting)) startLoop();
                            else {
                                looping = false;
                                cancelAnimationFrame(raf);
                                setP(0);
                            }
                        }, { threshold: 0.2 });
                        watch.observe(chaos);
                    }
                }

                // Booking journey on the phone, then the booking lands in the calendar.
                const journey = $('[data-journey]');
                if (journey) {
                    const screens = $$('[data-jscreen]', journey);
                    const steps = $$('[data-jstep]', journey);
                    const slot = $('[data-jslot]', journey);
                    const card = $('[data-jcard]', journey);
                    const cta = $('[data-jcta]', journey);
                    const labels = ['Continue', 'Continue', 'Confirm booking', 'Add to calendar'];
                    let run = 0;

                    const show = (n) => {
                        screens.forEach((s, i) => s.classList.toggle('is-on', i === n));
                        steps.forEach((s, i) => {
                            s.classList.toggle('is-done', i < n || n === 3);
                            s.classList.toggle('is-now', i === n && n < 3);
                        });
                        cta.textContent = labels[n];
                    };

                    const play = async () => {
                        const id = ++run;
                        journey.classList.remove('lp-journey-done');
                        journey.classList.add('lp-journey-run');
                        slot.classList.remove('is-filled', 'is-landed');
                        for (let n = 0; n < screens.length; n++) {
                            if (id !== run) return;
                            show(n);
                            await wait(n === 3 ? 900 : 1700);
                        }
                        if (id !== run) return;
                        await fly(card, slot, journey, 1000);
                        if (id !== run) return;
                        slot.classList.add('is-filled', 'is-landed');
                        journey.classList.add('lp-journey-done');
                    };

                    show(3);
                    if (!reduce) {
                        onVisible($('.lp-phone', journey), play, 0.5);
                        $('[data-journey-replay]', journey).addEventListener('click', play);
                    }
                }

                // Day in the life: moments feed into the Cutcost hub one by one.
                const day = $('[data-day]');
                if (day && !reduce) {
                    const moments = $$('[data-moment]', day);
                    const feeds = $$('[data-feed]', day);
                    const clock = $('[data-day-clock]', day);
                    const bar = $('[data-day-bar]', day);
                    const times = ['08:45', '09:10', '10:00', '12:30', '15:00', '18:45'];
                    const set = (n) => {
                        moments.forEach((m) => m.classList.toggle('is-on', Number(m.dataset.moment) <= n));
                        feeds.forEach((f) => {
                            const i = Number(f.dataset.feed);
                            f.classList.toggle('is-on', i <= n);
                            f.classList.toggle('is-latest', i === n);
                        });
                        clock.textContent = n < 0 ? '08:00' : times[n];
                        bar.style.width = `${((n + 1) / times.length) * 100}%`;
                    };
                    set(-1);
                    onVisible($('.lp-hub', day), async () => {
                        for (let n = 0; n < times.length; n++) {
                            await wait(n === 0 ? 300 : 1000);
                            set(n);
                        }
                    }, 0.4);
                }

                // Dashboard: a new booking arrives once while it's on screen.
                const showcase = $('[data-showcase]');
                if (showcase && !reduce) {
                    const row = $('[data-showcase-new]', showcase);
                    row.classList.add('is-hold');
                    onVisible($('.lp-app', showcase), async () => {
                        await wait(1200);
                        row.classList.remove('is-hold');
                        row.classList.add('is-drop');
                    }, 0.35);
                }
            })();
        </script>
    </body>
</html>
