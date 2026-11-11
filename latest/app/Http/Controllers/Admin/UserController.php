<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    private const ROLES = ['student', 'coach', 'teamleider', 'beheerder'];

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedUser($request, creating: true);
        $role = Role::where('naam', $data['rol'])->firstOrFail();

        User::create([
            'naam' => $data['naam'],
            'email' => $data['email'],
            'password' => $data['password'],
            'rol' => $role->naam,
            'hoofdrol_id' => $role->id,
        ]);

        return $this->usersPage()->with('status', 'Het account is aangemaakt.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validatedUser($request, $user);
        $role = Role::where('naam', $data['rol'])->firstOrFail();
        $currentRole = $user->primaryRole?->naam ?? $user->rol;
        $roleChanged = $currentRole !== $role->naam;

        if ($roleChanged && $request->user()->is($user)) {
            throw ValidationException::withMessages([
                'rol' => 'Je kunt je eigen beheerdersrol niet wijzigen.',
            ]);
        }

        if ($currentRole === 'beheerder' && $roleChanged && $this->administratorCount() <= 1) {
            throw ValidationException::withMessages([
                'rol' => 'De laatste beheerder kan niet worden gedegradeerd.',
            ]);
        }

        DB::transaction(function () use ($data, $role, $roleChanged, $user): void {
            $user->naam = $data['naam'];
            $user->email = $data['email'];
            $user->rol = $role->naam;
            $user->hoofdrol_id = $role->id;

            if (! empty($data['password'])) {
                $user->password = $data['password'];
            }

            $user->save();

            if ($roleChanged) {
                $user->extraRoles()->sync([]);
                if ($currentRole === 'student') {
                    $user->classGroups()->detach();
                }
                if ($currentRole === 'coach') {
                    $user->coachedClassGroups()->update(['coach_id' => null]);
                }
            }
        });

        return $this->usersPage()->with('status', 'Het account is bijgewerkt.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            throw ValidationException::withMessages([
                'account' => 'Je kunt je eigen account niet verwijderen.',
            ]);
        }

        $role = $user->primaryRole?->naam ?? $user->rol;
        if ($role === 'beheerder' && $this->administratorCount() <= 1) {
            throw ValidationException::withMessages([
                'account' => 'De laatste beheerder kan niet worden verwijderd.',
            ]);
        }

        $user->delete();

        return $this->usersPage()->with('status', 'Het account is verwijderd.');
    }

    private function validatedUser(Request $request, ?User $user = null, bool $creating = false): array
    {
        return $request->validate([
            'naam' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('gebruikers', 'email')->ignore($user),
            ],
            'rol' => ['required', 'string', Rule::in(self::ROLES), Rule::exists('rollen', 'naam')],
            'password' => $creating
                ? ['required', 'string', 'min:8', 'confirmed']
                : ['nullable', 'string', 'min:8', 'confirmed'],
        ]);
    }

    private function administratorCount(): int
    {
        $administratorRoleId = Role::where('naam', 'beheerder')->value('id');

        return User::query()
            ->where(function ($query) use ($administratorRoleId): void {
                if ($administratorRoleId !== null) {
                    $query->where('hoofdrol_id', $administratorRoleId)
                        ->orWhere(fn ($fallback) => $fallback
                            ->whereNull('hoofdrol_id')
                            ->where('rol', 'beheerder'));
                } else {
                    $query->where('rol', 'beheerder');
                }
            })
            ->count();
    }

    private function usersPage(): RedirectResponse
    {
        return redirect()->route('admin.dashboard', ['page' => 'gebruikers']);
    }
}
