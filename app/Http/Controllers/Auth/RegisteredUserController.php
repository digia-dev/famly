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
             // 1. Create a new Group (formerly Family)
            $group = \App\Models\Group::create([
                'name' => "Keluarga " . explode(' ', $request->name)[0],
                'type' => 'Family',
                'invite_code' => \Illuminate\Support\Str::random(8),
            ]);

            // 2. Create User as 'dins' (Owner) linked to Group
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'dins',
                'group_id' => $group->id,
                'current_group_id' => $group->id,
            ]);

            // Set admin_id for the group now that user exists
            $group->update(['admin_id' => $user->id]);

            // Add user to group_members table
            \DB::table('group_members')->insert([
                'group_id' => $group->id,
                'user_id' => $user->id,
                'role' => 'admin',
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Seed basic categories for this group
            $jenisMasuk = \App\Models\KategoriJenisTabungan::firstOrCreate(['jenis' => 'Pemasukan']);
            $jenisKeluar = \App\Models\KategoriJenisTabungan::firstOrCreate(['jenis' => 'Pengeluaran']);

            \App\Models\KategoriNamaTabungan::create([
                'nama' => 'Dompet Utama',
                'icon' => 'account_balance_wallet',
                'group_id' => $group->id,
                'wallet_type' => 'wallet',
                'user_id' => $user->id, // Owner
            ]);

            event(new Registered($user));

            Auth::login($user);

            return redirect(RouteServiceProvider::HOME);
        });
    }
}
