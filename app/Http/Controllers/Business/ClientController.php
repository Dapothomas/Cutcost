<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function index(Request $request): Response
    {
        $business = $request->user()->ownedBusiness;

        $clients = $business->clients()
            ->withCount('bookings')
            ->latest()
            ->paginate(15);

        return Inertia::render('Business/Clients/Index', [
            'clients' => $clients,
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Business/Clients/Create');
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

    public function edit(Request $request, Client $client): Response
    {
        $this->authorizeClient($request, $client);

        return Inertia::render('Business/Clients/Edit', [
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
                'notes' => $client->notes,
            ],
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
