@extends('layouts.app')

@section('title', 'Ontwikkeling | Jouw leeromgeving')

@if ($roleAssets['style'])
  @push('styles')
    <link rel="stylesheet" href="{{ asset('assets/'.$roleAssets['style']) }}?v={{ filemtime(public_path('assets/'.$roleAssets['style'])) }}">
  @endpush
@endif
@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/application-theme.css') }}">
@endpush

@section('content')
  <div class="app role-{{ $frontendUser['role'] }}" id="app" data-application-style="{{ $applicationSettings->application_style }}" style="--application-accent: {{ $applicationAccentColor }}">
    @include('components.sidebar')

    <main class="main">
      @include('components.topbar')

      @yield('role-content')

      <div id="hint" class="warning hidden" role="status"></div>
      @include('components.footer')
    </main>
  </div>
@endsection

@push('scripts')
  <script>
    window.LARAVEL_USER = {{ Illuminate\Support\Js::from($frontendUser) }};
    window.LARAVEL_APPLICATION_THEME = {{ Illuminate\Support\Js::from([
        'accentColor' => $applicationAccentColor,
        'globalAccentColor' => $applicationSettings->application_accent_color,
        'style' => $applicationSettings->application_style,
    ]) }};
    window.LARAVEL_STATE = {{ Illuminate\Support\Js::from($frontendState) }};
    window.LARAVEL_NOTIFICATION_SETTINGS = {{ Illuminate\Support\Js::from(array_replace($user->appState?->gegevens['notification-settings'] ?? [], array_intersect_key($user->appState?->gegevens['profile-settings'] ?? [], array_flip(['email_notifications','platform_notifications'])))) }};
    window.LARAVEL_DATA = {{ Illuminate\Support\Js::from($frontendData) }};
    window.LARAVEL_SAVE_URL = {{ Illuminate\Support\Js::from(route('state.save')) }};
    window.LARAVEL_ROLE_SETTINGS_URL = {{ Illuminate\Support\Js::from(route('dashboard-settings.update')) }};
    window.LARAVEL_COACH_ROUTES = {{ Illuminate\Support\Js::from([
        'overview' => route('coach.dashboard'),
        'groups' => route('coach.groups'),
        'activities' => route('coach.activities'),
        'goals' => route('coach.goals'),
        'projects' => route('coach.projects'),
        'feedback' => route('coach.feedback'),
        'student' => route('coach.student', ['student' => '__ID__']),
    ]) }};
    window.LARAVEL_COACH_PAGE = {{ Illuminate\Support\Js::from($coachPage ?? 'overview') }};
    window.LARAVEL_COACH_STUDENT_ID = {{ Illuminate\Support\Js::from($coachStudentId ?? null) }};
    window.LARAVEL_COACH_PROJECT_FILTER = {{ Illuminate\Support\Js::from(request()->query('status', '')) }};
    window.LARAVEL_API = {{ Illuminate\Support\Js::from([
        'challengeSync' => route('challenges.sync'),
        'notificationSettings' => route('profile.notifications'),
        'reflectionStore' => route('reflections.store'),
        'reflectionUpdate' => route('reflections.update', ['reflection' => '__ID__']),
        'reflectionRestore' => route('reflections.restore', ['id'=>'__ID__']),
        'reflectionDelete' => route('reflections.destroy', ['reflection' => '__ID__']),
        'feedbackStore' => route('feedback.store'),
        'feedbackDelete' => route('feedback.destroy', ['feedback' => '__ID__']),
        'goalStore' => route('goals.store'),
        'goalComplete' => route('goals.complete', ['goal'=>'__ID__']),
        'assignmentSubmit' => route('assignments.submit', ['assignment' => '__ID__']),
        'assignmentReopen' => route('assignments.reopen', ['assignment' => '__ID__']),
        'assignmentFileStore' => route('assignments.files.store', ['assignment' => '__ID__']),
        'assignmentFileDelete' => route('assignments.files.destroy', ['assignmentFile' => '__ID__']),
    ]) }};
    window.LARAVEL_CSRF = {{ Illuminate\Support\Js::from(csrf_token()) }};
  </script>
  @foreach ($roleAssets['scripts'] as $script)
    <script src="{{ asset('assets/'.$script) }}?v={{ filemtime(public_path('assets/'.$script)) }}"></script>
  @endforeach
@endpush
