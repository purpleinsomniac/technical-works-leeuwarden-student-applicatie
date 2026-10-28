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

Testgebruikers (uitsluitend voor lokaal gebruik):

- student@example.test / Demo12345!
- coach@example.test / Demo12345!
- manager@example.test / Demo12345!
- admin@example.test / Demo12345!

**Verwijder de demo-accounts voordat je een echte omgeving publiceert.** Wijzig ook APP_DEBUG=false en stel een veilig productie-wachtwoordbeleid in.

## Wat werkt

- Laravel login/logout met gehashte wachtwoorden, CSRF bescherming en database sessies.
- Rollen worden server-side aan het account gekoppeld, niet gekozen in het inlogscherm.
- Bestaande schermen, filters, details, demo-overzichten en interacties in Blade + vanilla JS.
- Studenten slaan reflecties, feedback, archiveringen en gelezen-urenstatus op in hun eigen database-record. Nieuwe sessies laden deze gegevens weer in.
- Opslagendpoint vereist ingelogde student en valideert leeruitkomstcodes.

## Nog te bouwen voor productie

De applicatie bevat demonstratiedata. Uren en voortgang, autorisatie van coach/manager/admin, individuele leerlingdossiers, import en validatie van uren, auditlog, leerdoelen en formele berekeningsregels moeten op echte databasemodellen en bedrijfsregels worden aangesloten. Niet gebruiken met echte leerlinggegevens voordat beveiliging, AVG, privacy, objectautorisatie en tests zijn uitgewerkt. Demogegevens zijn zichtbaar in de huidige JavaScript en zijn geen echte organisatiegegevens.

Voor nu is `user_states.data` een JSON opslagmodel voor de persoonlijke invoer; dit is een pragmatische tussenstap en geen uitgewerkt domeinmodel. Browseropslag wordt alleen als lokale cache gebruikt (per account-ID); voor productie bij voorkeur geheel verwijderen en REST endpoints per entiteit gebruiken.
