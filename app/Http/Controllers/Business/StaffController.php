<?php

namespace App\Http\Controllers\Business;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ShopNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
{
    public function index(Request $request): Response
    {
        $business = $request->user()->ownedBusiness;
        $search = trim((string) $request->query('search', ''));

        $barbers = $business->barbers()
            ->when($search !== '', function ($query) use ($search) {
                $term = '%'.$search.'%';

                $query->where(fn ($q) => $q
                    ->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Business/Staff/Index', [
            'barbers' => $barbers,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Business/Staff/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $business = $request->user()->ownedBusiness;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $stylist = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => Role::Barber,
            'business_id' => $business->id,
            'email_verified_at' => now(),
        ]);

        ShopNotifier::stylistAdded($business, $stylist);

        return redirect()->route('business.staff.index')
            ->with('status', 'Barber added to your team.');
    }

    public function destroy(Request $request, User $staff): RedirectResponse
    {
        $business = $request->user()->ownedBusiness;

        abort_unless(
            $staff->business_id === $business?->id && $staff->isBarber(),
            404
        );

        $staff->delete();

        return redirect()->route('business.staff.index')
            ->with('status', 'Barber removed.');
    }
}
