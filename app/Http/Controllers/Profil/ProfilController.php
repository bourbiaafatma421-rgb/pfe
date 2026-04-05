<?php

namespace App\Http\Controllers\Profil;

use App\Http\Controllers\Controller;
use App\Services\ProfilService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfilController extends Controller
{
    public function __construct(protected ProfilService $service) {}

    // ─── GET /profil ──────────────────────────────────────────────────────────

    public function show()
    {
        $profil = $this->service->getMonProfil();

        return response()->json([
            'message'  => 'Profil récupéré avec succès',
            'profil'   => [
                'id'           => $profil->id,
                'first_name'   => $profil->first_name,
                'last_name'    => $profil->last_name,
                'email'        => $profil->email,
                'phone_number' => $profil->phone_number,
                'date_of_hire' => $profil->date_of_hire,
                'active'       => $profil->active,
                'role'         => $profil->role?->name,
                'signature_path' => $profil->signature_path,
                'avatar_url'   => $profil->avatar_path
                    ? asset('storage/' . $profil->avatar_path)
                    : null,
            ],
        ]);
    }

    // ─── PATCH /profil/telephone ──────────────────────────────────────────────

    public function updateTelephone(Request $request)
    {
        $request->validate([
            'phone_number' => ['required', 'string', 'regex:/^\+?[0-9\s\-]{8,15}$/'],
        ], [
            'phone_number.regex' => 'Numéro invalide (ex: +21612345678)',
        ]);

        /** @var \App\Models\User $user */
        $user    = Auth::user();
        $updated = $this->service->updateTelephone($user, $request->phone_number);

        return response()->json([
            'message'      => 'Téléphone mis à jour',
            'phone_number' => $updated->phone_number,
        ]);
    }

    // ─── POST /profil/avatar ──────────────────────────────────────────────────

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        /** @var \App\Models\User $user */
        $user    = Auth::user();
        $updated = $this->service->updateAvatar($user, $request->file('avatar'));

        return response()->json([
            'message'    => 'Avatar mis à jour',
            'avatar_url' => asset('storage/' . $updated->avatar_path),
        ]);
    }

    // ─── DELETE /profil/avatar ────────────────────────────────────────────────

    public function deleteAvatar()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $this->service->deleteAvatar($user);

        return response()->json(['message' => 'Avatar supprimé']);
    }
}