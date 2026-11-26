<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:','session.driver'=>'array','cache.default'=>'array','demo.account_switcher'=>true]);
Illuminate\Support\Facades\DB::purge('sqlite');
Illuminate\Support\Facades\Artisan::call('migrate',['--database'=>'sqlite','--force'=>true]);
$app->instance('env','local');
$current = App\Models\User::create(['naam'=>'Student','email'=>'student@example.test','rol'=>'student','password'=>'Test12345!']);
$coach = App\Models\User::create(['naam'=>'Coach','email'=>'coach@example.test','rol'=>'coach','password'=>'Test12345!']);
$session = app('session')->driver();
$session->start();
Illuminate\Support\Facades\Auth::login($current);
$request = Illuminate\Http\Request::create('/demo/account-wisselen','POST',['role'=>'coach']);
$request->setLaravelSession($session);
$request->setUserResolver(fn () => Illuminate\Support\Facades\Auth::user());
$controller = new App\Http\Controllers\DemoAccountController();
$previousSession = $session->getId();
$previousToken = $session->token();
$response = $controller->switchAccount($request);
if (Illuminate\Support\Facades\Auth::id() !== $coach->id || $previousSession === $session->getId() || $previousToken === $session->token()) throw new RuntimeException('Accountwisseling moet een nieuwe sessie en CSRF-token gebruiken.');
if ($response->getTargetUrl() !== route('home')) throw new RuntimeException('Verkeerde doelpagina.');
$wesley = App\Models\User::create(['naam'=>'Wesley Rinsma','email'=>'student2@example.test','rol'=>'student','password'=>'Demo12345!']);
$request->merge(['role'=>'student2']);
$controller->switchAccount($request);
if (Illuminate\Support\Facades\Auth::id() !== $wesley->id) throw new RuntimeException('Tweede student is geen aparte accountkeuze.');
$adminSeeder = new Database\Seeders\DemoAdminSeeder();
$adminSeeder->run();
$admin = App\Models\User::where('email', config('demo.accounts.beheerder.email'))->firstOrFail();
$adminSeeder->run();
if ($admin->password !== $admin->fresh()->password) throw new RuntimeException('De seeder heeft een bestaand account gewijzigd.');
$request->merge(['role'=>'beheerder']);
$controller->switchAccount($request);
if (Illuminate\Support\Facades\Auth::id() !== $admin->id || ! $admin->canUseRole('beheerder')) throw new RuntimeException('Het demobeheeraccount kan niet worden gebruikt.');
foreach (['production','testing'] as $environment) {
    $app->instance('env',$environment);
    try { $controller->switchAccount($request); throw new RuntimeException('Wisselen mag niet buiten local.'); }
    catch (Symfony\Component\HttpKernel\Exception\HttpException $error) { if ($error->getStatusCode() !== 404) throw $error; }
}
$app->instance('env','local');
config(['demo.account_switcher'=>false]);
try { $controller->switchAccount($request); throw new RuntimeException('Uitgeschakelde demo moet weigeren.'); }
catch (Symfony\Component\HttpKernel\Exception\HttpException $error) { if ($error->getStatusCode() !== 404) throw $error; }
config(['demo.account_switcher'=>true]);
$request->merge(['role'=>'willekeurig-account']);
try { $controller->switchAccount($request); throw new RuntimeException('Onbekende accountkeuze moet worden geweigerd.'); }
catch (Illuminate\Validation\ValidationException $error) {}
echo "OK: account wisselen, sessievernieuwing, allowlist en uitschakeling buiten local.\n";
