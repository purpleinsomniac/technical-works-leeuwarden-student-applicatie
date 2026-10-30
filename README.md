# Ontwikkeling — Laravel

Laravel 12-starterproject voor de persoonlijke leeromgeving Ontwikkeling. De aangeleverde interface, rollen, navigatie en voorbeeldgegevens zijn hierin verwerkt.

## Installatie

Vereisten: PHP 8.2+, Composer, SQLite extensie.

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

Open http://127.0.0.1:8000.

## MVC-verdeling voor het team

Iedere rol heeft een eigen controllermap en een eigen viewmap. Zo kunnen vier
teamleden tegelijk werken zonder dat alle code in één groot bestand terechtkomt.

| Rol | Controllers | Views |
| --- | --- | --- |
| Student | `app/Http/Controllers/Student` | `resources/views/student` |
| Coach / SLB'er | `app/Http/Controllers/Coach` | `resources/views/coach` |
| Teamleider | `app/Http/Controllers/Teamleader` | `resources/views/teamleader` |
| Beheerder | `app/Http/Controllers/Admin` | `resources/views/admin` |

De modellen in `app/Models` blijven gedeeld: een gebruiker, opdracht of
leeruitkomst is namelijk hetzelfde gegeven voor alle rollen. Gedeelde onderdelen
zoals de zijbalk, header en footer staan in `resources/views/components`. Routes
staan centraal in `routes/web.php` en zijn per rol beveiligd met middleware.

De JavaScript-code van de student staat per onderdeel in
`public/assets/js/roles/student`:

- `core.js`: opslag en API-verzoeken;
- `data.js`: omzetting van databasegegevens naar de interface;
- `app.js`: navigatie en algemene interface;
- `dashboard.js`: dashboard en instellingen;
- `assignments.js`: opdrachten;
- `learning-outcomes.js`: leeruitkomsten en leerdoelen;
- `hours.js`: uren;
- `reflections.js`: reflecties;
- `feedback.js`: feedback;
- `init.js`: opstarten van de onderdelen.

De volgorde waarin deze bestanden in de studentcontroller staan is belangrijk:
`init.js` wordt als laatste geladen nadat alle functies beschikbaar zijn.

De fictieve ontwikkelgegevens worden aangemaakt door
`database/seeders/DatabaseSeeder.php`. De 48 leeruitkomsten staan als invoer voor
de seeder in `database/outcomes.json`. Na het seeden leest de applicatie alle
leeruitkomsten, opdrachten, voortgang, reflecties, feedback en leerdoelen uit de
database; JavaScript bevat hiervan geen tweede hardcoded kopie.

Maak databasewijzigingen altijd samen: stem migraties en modelrelaties eerst af,
zodat twee teamleden niet tegelijk dezelfde tabel aanpassen.

Testgebruikers (uitsluitend voor lokaal gebruik):

- student@example.test / Demo12345!
- coach@example.test / Demo12345!
- manager@example.test / Demo12345!
- admin@ITIG.edu / Demo12345!

**Verwijder de demo-accounts voordat je een echte omgeving publiceert.** Wijzig ook APP_DEBUG=false en stel een veilig productie-wachtwoordbeleid in.

## Wat werkt

- Laravel login/logout met gehashte wachtwoorden, CSRF bescherming en database sessies.
- Rollen worden server-side aan het account gekoppeld, niet gekozen in het inlogscherm.
- Bestaande schermen, filters, details en interacties in Blade + vanilla JS.
- Studenten slaan reflecties, feedback, archiveringen en gelezen-urenstatus op in hun eigen database-record. Nieuwe sessies laden deze gegevens weer in.
- Opslagendpoint vereist ingelogde student en valideert leeruitkomstcodes.

## Nog te bouwen voor productie

De applicatie bevat fictieve seeddata in de database. Leeruitkomsten,
opdrachten, voortgang, reflecties, feedback en leerdoelen worden via Laravel uit
MySQL geladen. Import en formele validatie van uren, een auditlogboek en de
definitieve berekeningsregels moeten nog worden uitgewerkt. Niet gebruiken met
echte leerlinggegevens voordat beveiliging, AVG, privacy, objectautorisatie en
tests volledig zijn beoordeeld.

Voor nu is `user_states.data` een JSON opslagmodel voor de persoonlijke invoer; dit is een pragmatische tussenstap en geen uitgewerkt domeinmodel. Browseropslag wordt alleen als lokale cache gebruikt (per account-ID); voor productie bij voorkeur geheel verwijderen en REST endpoints per entiteit gebruiken.
