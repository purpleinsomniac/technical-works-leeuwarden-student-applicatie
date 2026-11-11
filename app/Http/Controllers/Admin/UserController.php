<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    private const ROLES = ['student', 'coach', 'teamleider', 'beheerder'];

    public function downloadImportTemplate(): StreamedResponse
    {
        return response()->streamDownload(
            static function (): void {
                echo "naam;email;leerjaar;tijdelijk_wachtwoord\r\n";
            },
            'studenten-import-sjabloon.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'bestand' => ['required', 'file', 'extensions:csv', 'max:2048'],
        ]);

        $handle = fopen($request->file('bestand')->getRealPath(), 'rb');
        if ($handle === false) {
            throw ValidationException::withMessages([
                'bestand' => 'Het CSV-bestand kan niet worden gelezen.',
            ]);
        }

        $rows = [];
        $errors = [];
        try {
            $firstLine = fgets($handle);
            if ($firstLine === false) {
                throw ValidationException::withMessages([
                    'bestand' => 'Het CSV-bestand is leeg.',
                ]);
            }

            // Excel may save the template with commas or semicolons depending on regional settings.
            $delimiter = $this->csvDelimiter($firstLine);
            rewind($handle);
            $headers = fgetcsv($handle, null, $delimiter, '"', '');
            if ($headers === false) {
                throw ValidationException::withMessages([
                    'bestand' => 'De kolomkoppen konden niet worden gelezen.',
                ]);
            }

            $headers = array_map(
                static fn ($header): string => strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $header))),
                $headers,
            );
            $requiredHeaders = ['naam', 'email', 'leerjaar', 'tijdelijk_wachtwoord'];
            if (
                count($headers) !== count($requiredHeaders)
                || count(array_unique($headers)) !== count($requiredHeaders)
                || array_diff($requiredHeaders, $headers) !== []
            ) {
                throw ValidationException::withMessages([
                    'bestand' => 'Gebruik de kolomkoppen uit het importsjabloon: naam, email, leerjaar, tijdelijk_wachtwoord.',
                ]);
            }

            $rowNumber = 1;
            $seenEmails = [];
            while (($values = fgetcsv($handle, null, $delimiter, '"', '')) !== false) {
                $rowNumber++;
                if (collect($values)->every(static fn ($value): bool => trim((string) $value) === '')) {
                    continue;
                }

                if (count($values) !== count($headers)) {
                    $errors[] = "Rij {$rowNumber}: het aantal ingevulde waarden komt niet overeen met de kolomkoppen.";
                    continue;
                }

                $values = array_combine($headers, $values);
                $student = [
                    'naam' => trim((string) $values['naam']),
                    'email' => trim((string) $values['email']),
                    'leerjaar' => trim((string) $values['leerjaar']) ?: null,
                    'tijdelijk_wachtwoord' => (string) $values['tijdelijk_wachtwoord'],
                ];
                $validator = Validator::make($student, [
                    'naam' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'string', 'email', 'max:255'],
                    'leerjaar' => ['nullable', 'integer', 'between:1,4'],
                    'tijdelijk_wachtwoord' => ['required', 'string', 'min:8', 'max:255'],
                ]);

                if ($validator->fails()) {
                    foreach ($validator->errors()->all() as $message) {
                        $errors[] = "Rij {$rowNumber}: {$message}";
                    }

                    continue;
                }

                $normalizedEmail = strtolower($student['email']);
                if (isset($seenEmails[$normalizedEmail]) || User::whereRaw('LOWER(email) = ?', [$normalizedEmail])->exists()) {
                    $errors[] = "Rij {$rowNumber}: het e-mailadres {$student['email']} bestaat al of staat meerdere keren in het bestand.";
                    continue;
                }

                $seenEmails[$normalizedEmail] = true;
                $rows[] = $student;
            }
        } finally {
            fclose($handle);
        }

        if ($rows === [] && $errors === []) {
            $errors[] = 'Het bestand bevat geen studentgegevens.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['bestand' => $errors]);
        }

        // Validate every row before writing so an invalid file cannot create a partial batch.
        DB::transaction(function () use ($rows): void {
            $studentRole = Role::where('naam', 'student')->firstOrFail();

            foreach ($rows as $row) {
                $student = User::create([
                    'naam' => $row['naam'],
                    'email' => $row['email'],
                    'rol' => $studentRole->naam,
                    'hoofdrol_id' => $studentRole->id,
                    'leerjaar' => $row['leerjaar'],
                    'password' => $row['tijdelijk_wachtwoord'],
                ]);
                $student->forceFill(['studentnummer' => $this->studentNumberFor($student)])->save();
            }
        });

        $count = count($rows);

        return $this->usersPage()->with('status', "{$count} ".($count === 1 ? 'student is' : 'studenten zijn').' geïmporteerd.');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedUser($request, creating: true);
        $role = Role::where('naam', $data['rol'])->firstOrFail();

        $user = DB::transaction(function () use ($data, $role): User {
            $user = User::create([
                'naam' => $data['naam'],
                'email' => $data['email'],
                'password' => $data['password'],
                'rol' => $role->naam,
                'hoofdrol_id' => $role->id,
                'leerjaar' => $role->naam === 'student' ? ($data['leerjaar'] ?? null) : null,
            ]);

            if ($role->naam === 'student') {
                $user->forceFill(['studentnummer' => $this->studentNumberFor($user)])->save();
            }

            return $user;
        });

        $status = 'Het account is aangemaakt.';
        if ($role->naam === 'student') {
            $status .= ' Studentnummer: '.$user->studentnummer.'.';
        }

        return $this->usersPage()->with('status', $status);
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
            $user->leerjaar = $role->naam === 'student' ? ($data['leerjaar'] ?? null) : null;

            if (! empty($data['password'])) {
                $user->password = $data['password'];
            }

            $user->save();
            if ($role->naam === 'student' && ! $user->studentnummer) {
                $user->forceFill(['studentnummer' => $this->studentNumberFor($user)])->save();
            }

            if ($roleChanged) {
                $user->extraRoles()->sync([]);
                if ($currentRole === 'student') {
                    $user->groups()->detach();
                }
                if ($currentRole === 'coach') {
                    $user->coachedGroups()->update(['coach_id' => null]);
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
            'leerjaar' => $request->input('rol') === 'student'
                ? ['nullable', 'integer', 'between:1,4']
                : ['prohibited'],
            'password' => $creating
                ? ['required', 'string', 'min:8', 'confirmed']
                : ['nullable', 'string', 'min:8', 'confirmed'],
        ]);
    }

    private function studentNumberFor(User $user): string
    {
        return 'STU'.str_pad((string) $user->id, 6, '0', STR_PAD_LEFT);
    }

    private function csvDelimiter(string $firstLine): string
    {
        $counts = [
            ';' => substr_count($firstLine, ';'),
            ',' => substr_count($firstLine, ','),
            "\t" => substr_count($firstLine, "\t"),
        ];

        return array_search(max($counts), $counts, true);
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
