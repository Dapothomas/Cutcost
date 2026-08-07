<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $business = $request->user()->ownedBusiness;

        $clients = $business->clients()
            ->withCount('bookings')
            ->latest()
            ->paginate(15);

        return view('business.clients.index', compact('clients', 'business'));
    }

    public function create(Request $request): View
    {
        return view('business.clients.create', [
            'business' => $request->user()->ownedBusiness,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $business = $request->user()->ownedBusiness;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $business->clients()->create($data);

        return redirect()->route('business.clients.index')
            ->with('status', 'Client added.');
    }

    public function edit(Request $request, Client $client): View
    {
        $this->authorizeClient($request, $client);

        return view('business.clients.edit', [
            'client' => $client,
            'business' => $request->user()->ownedBusiness,
        ]);
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $this->authorizeClient($request, $client);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $client->update($data);

        return redirect()->route('business.clients.index')
            ->with('status', 'Client updated.');
    }

    public function destroy(Request $request, Client $client): RedirectResponse
    {
        $this->authorizeClient($request, $client);

        $client->delete();

        return redirect()->route('business.clients.index')
            ->with('status', 'Client removed.');
    }

    private function authorizeClient(Request $request, Client $client): void
    {
        abort_unless($client->business_id === $request->user()->ownedBusiness?->id, 404);
    }
}
