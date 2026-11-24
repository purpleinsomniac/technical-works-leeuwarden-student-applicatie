@php
    $demoAccounts = \App\Models\User::query()
        ->whereIn('email', array_column(config('demo.accounts'), 'email'))
        ->get()->keyBy('email');
    $demoCurrentChoice = collect(config('demo.accounts'))->first(fn ($choice) => $choice['email'] === auth()->user()->email);
    $demoGroups = [
        'student'=>['label'=>'Studenten','icon'=>'<path d="m2 8 10-5 10 5-10 5Z"/><path d="M6 10v7q6 5 12 0v-7M22 8v8"/>'],
        'coach'=>['label'=>'Docent (coach)','icon'=>'<path d="M9 4h13v12H12M13 8h5"/><circle cx="6" cy="10" r="3"/><path d="M1 21v-2a5 5 0 0 1 10 0v2Z"/>'],
        'teamleider'=>['label'=>'Teamleider','icon'=>'<circle cx="12" cy="6" r="3"/><circle cx="4" cy="9" r="2"/><circle cx="20" cy="9" r="2"/><path d="M7 21v-4a5 5 0 0 1 10 0v4ZM1 20v-4q0-3 4-3M23 20v-4q0-3-4-3"/>'],
        'beheerder'=>['label'=>'Applicatiebeheerder (beheerder)','icon'=>'<path d="m10 3 4 0 1 3 3 1 3 0 1 4-2 2-1 3-1 3-4 1-2-2-3-1-3-1-1-4 2-2 1-3Z"/><circle cx="12" cy="12" r="3"/>'],
    ];
@endphp
<link rel="stylesheet" href="{{ asset('assets/css/demo-switcher.css') }}">
<details id="demoAccountSwitcher" class="demo-account-switcher">
    <summary aria-label="Demoaccount wisselen"><span aria-hidden="true">⇄</span><span>Demo: wissel account</span><small>{{ $demoCurrentChoice['label'] ?? ucfirst(auth()->user()->rol) }}</small></summary>
    <div class="demo-account-panel">
        <header><strong>Demonstratie</strong><small>Wissel naar een bestaand account</small></header>
        @foreach($demoGroups as $groupRole => $group)
        <section class="demo-role-group" aria-label="{{ $group['label'] }}">
            <h3><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $group['icon'] !!}</svg>{{ $group['label'] }}</h3>
        @foreach(config('demo.accounts') as $role => $demo)
            @continue(($demo['role'] ?? $role) !== $groupRole)
            @php($account = $demoAccounts->get($demo['email']))
            <form action="{{ route('demo.account-switch') }}" method="post">
                @csrf
                <input type="hidden" name="role" value="{{ $role }}">
                <button type="submit" @disabled(!$account || $account->rol !== ($demo['role'] ?? $role) || auth()->id() === $account->id) @if($account && auth()->id() === $account->id) aria-current="true" @endif>
                    <span class="demo-account-initial" aria-hidden="true">{{ mb_strtoupper(mb_substr($demo['label'],0,1)) }}</span>
                    <span><strong>{{ $demo['label'] }}</strong><small>{{ $account?->naam ?? 'Account niet beschikbaar' }}</small></span>
                    <span class="demo-account-selection" aria-hidden="true">{{ $account && auth()->id() === $account->id ? '✓' : '' }}</span>
                </button>
            </form>
        @endforeach
        </section>
        @endforeach
        <footer><span aria-hidden="true">ⓘ</span>Alleen beschikbaar in de lokale demo-omgeving.</footer>
    </div>
</details>
<script src="{{ asset('assets/js/demo-switcher.js') }}"></script>
