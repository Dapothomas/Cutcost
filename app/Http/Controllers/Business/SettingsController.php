<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Support\BrandColor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function edit(Request $request): Response
    {
        $business = $request->user()->ownedBusiness()->firstOrFail();

        return Inertia::render('Business/Settings/Edit', [
            'business' => [
                'name' => $business->name,
                'primary_color' => $business->primary_color,
            ],
            'presets' => BrandColor::presets(),
            'defaultColor' => BrandColor::DEFAULT_HEX,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $business = $request->user()->ownedBusiness()->firstOrFail();

        $data = $request->validate([
            'primary_color' => [
                'nullable',
                'string',
                'max:7',
                Rule::when(
                    filled($request->input('primary_color')),
                    ['regex:/^#?[0-9A-Fa-f]{6}$/'],
                ),
            ],
        ]);

        $business->update([
            'primary_color' => BrandColor::normalize($data['primary_color'] ?? null),
        ]);

        return back()->with('status', 'Brand colour saved.');
    }
}
