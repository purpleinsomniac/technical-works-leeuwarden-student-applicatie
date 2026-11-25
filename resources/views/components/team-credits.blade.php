{{-- Gebruik een dialoogvenster voor focusbeheer en sluiten met Escape. --}}
<dialog id="teamCreditsDialog" class="team-credits-dialog" aria-labelledby="teamCreditsTitle">
  <form method="dialog">
    <button class="team-credits-close" aria-label="Venster sluiten" autofocus>×</button>
  </form>
  <svg class="team-credits-wave team-credits-wave-top" viewBox="0 0 500 150" preserveAspectRatio="none" aria-hidden="true"><path d="M0 0h500v150C270 0 210 200 0 0" fill="currentColor" opacity=".13"/><path d="M190 0h310v130C340 60 320 120 190 0" fill="currentColor" opacity=".18"/></svg>
  <svg class="team-credits-wave team-credits-wave-bottom" viewBox="0 0 500 150" preserveAspectRatio="none" aria-hidden="true"><path d="M0 150h500V0C300 0 250 120 0 150" fill="currentColor" opacity=".13"/><path d="M240 150h260V0C400 40 390 120 240 150" fill="currentColor" opacity=".2"/></svg>
  <span class="team-credits-mark" aria-hidden="true"><svg viewBox="0 0 64 64" fill="none"><path d="m32 5 27 27-27 27L5 32Z" stroke="currentColor" stroke-width="6"/><path d="m32 20 12 12-12 12-12-12Z" fill="currentColor"/></svg></span>
  <h2 id="teamCreditsTitle">Technical Works Leeuwarden</h2>
  <p>Deze applicatie is gemaakt door:</p>
  <ul>
    @foreach ([
      ['name' => 'Wesley Rinsma', 'role' => 'Studentenrol', 'github' => 'Wesley058'],
      ['name' => 'Gerben Harder', 'role' => "SLB'er", 'github' => 'Gurbun'],
      ['name' => 'Gilano Nash', 'role' => 'Teamleider', 'github' => 'GilanoNash'],
      ['name' => 'Ruben Huizenga', 'role' => 'Applicatiebeheerder', 'github' => 'purpleinsomniac'],
    ] as $maker)
      <li>
        <details class="team-credits-person" @if ($loop->first) open @endif>
          <summary>
            <span class="team-credits-avatar" aria-hidden="true">
              <svg viewBox="0 0 32 32" fill="currentColor">
                <circle cx="16" cy="10" r="6"/>
                <path d="M4 29v-4c0-6 5-9 12-9s12 3 12 9v4Z"/>
              </svg>
            </span>
            <span>{{ $maker['name'] }}</span>
            <span class="team-credits-chevron" aria-hidden="true"></span>
          </summary>
          <div class="team-credits-contribution">
            <p>Gewerkt aan: <strong>{{ $maker['role'] }}</strong></p>
            <a href="https://github.com/{{ $maker['github'] }}" target="_blank" rel="noopener noreferrer" aria-label="GitHub-profiel van {{ $maker['name'] }} (opent in een nieuw tabblad)">GitHub · {{ $maker['github'] }} <span aria-hidden="true">↗</span></a>
          </div>
        </details>
      </li>
    @endforeach
  </ul>
</dialog>
@once
  <script src="{{ asset('assets/js/team-credits.js') }}" defer></script>
@endonce
