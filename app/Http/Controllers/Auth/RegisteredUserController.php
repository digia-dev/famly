<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        return \DB::transaction(function() use ($request) {
             // 1. Create a new Family
            $family = \App\Models\Family::create([
                'family_name' => "Keluarga " . explode(' ', $request->name)[0]
            ]);

            // 2. Create User as 'dins' (Owner/Head) linked to Family
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'dins',
                'family_id' => $family->id
            ]);

            // 3. Seed basic categories for this family so the app isn't empty
            $jenisMasuk = \App\Models\KategoriJenisTabungan::firstOrCreate(['jenis' => 'Pemasukan']);
            $jenisKeluar = \App\Models\KategoriJenisTabungan::firstOrCreate(['jenis' => 'Pengeluaran']);

            \App\Models\KategoriNamaTabungan::create([
                'nama' => 'Dompet Utama',
                'icon' => 'account_balance_wallet',
                'family_id' => $family->id,
                'wallet_type' => 'wallet'
            ]);

            event(new Registered($user));

            Auth::login($user);

            return redirect(RouteServiceProvider::HOME);
        });
    }
}
