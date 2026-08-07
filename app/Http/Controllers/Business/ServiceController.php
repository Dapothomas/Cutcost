<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $business = $request->user()->ownedBusiness;

        $services = $business->services()->latest()->paginate(15);

        return view('business.services.index', compact('services', 'business'));
    }

    public function create(Request $request): View
    {
        return view('business.services.create', [
            'business' => $request->user()->ownedBusiness,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $business = $request->user()->ownedBusiness;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $business->services()->create([
            'name' => $data['name'],
            'duration_minutes' => $data['duration_minutes'],
            'price_cents' => (int) round($data['price'] * 100),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('business.services.index')
            ->with('status', 'Service added.');
    }

    public function edit(Request $request, Service $service): View
    {
        $this->authorizeService($request, $service);

        return view('business.services.edit', [
            'service' => $service,
            'business' => $request->user()->ownedBusiness,
        ]);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $this->authorizeService($request, $service);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $service->update([
            'name' => $data['name'],
            'duration_minutes' => $data['duration_minutes'],
            'price_cents' => (int) round($data['price'] * 100),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('business.services.index')
            ->with('status', 'Service updated.');
    }

    public function destroy(Request $request, Service $service): RedirectResponse
    {
        $this->authorizeService($request, $service);

        $service->delete();

        return redirect()->route('business.services.index')
            ->with('status', 'Service removed.');
    }

    private function authorizeService(Request $request, Service $service): void
    {
        abort_unless($service->business_id === $request->user()->ownedBusiness?->id, 404);
    }
}
