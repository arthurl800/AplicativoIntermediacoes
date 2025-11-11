## Purpose
This project is a small PHP MVC-style app for importing "intermediações" data (CSV/XLSX) into a local MySQL database using DBeaver. Keep suggestions tightly focused on the existing patterns and files listed below.

## Big-picture architecture
- Entry point: `index.php` acts as a tiny router. It maps `?controller=...&action=...` to controller classes in `app/controller/`.
- Controllers (suffix `Controller`) orchestrate work and include views from `app/view/` and shared `includes/` header/footer.
- Models (suffix `Model`) encapsulate DB logic. DB access uses the `app/util/Database.php` singleton (MySQL database).
- Utilities live in `app/util/` and include `AuthManager.php` (session-based auth), `IFileProcessor.php`, `CsvProcessor.php`, and `XlsxProcessor.php` (uses PhpSpreadsheet via Composer).
- Views are PHP files in `app/view/` that expect controller-provided variables (e.g. `$users`, `$result`).

## Data flow examples (explicit)
- Upload flow: `app/controller/UploadController.php` -> selects `CsvProcessor` or `XlsxProcessor` -> `IntermediacaoModel::insertBatch()` -> `Database` (MySQL `INTERMEDIACOES` table).
- Admin user list: `app/controller/AdminController::users()` calls `UserModel::findAll()` and passes `$users` to `app/view/admin/user_list.php`.

## Project-specific conventions and gotchas
- Routing is query-string based: `index.php?controller=upload&action=processUpload`. Controllers are instantiated directly; methods are called if present.
- File inclusion uses relative paths with `dirname(dirname(__DIR__))` — keep that pattern when adding files or moving views/controllers.
- `Database.php` initializes a MySQL DB at runtime and creates tables if missing. Do not assume an external DB server.
- `CsvProcessor` and `XlsxProcessor` expect exactly 23 columns per row (see `$expectedColumns = 23`). They skip the header row.
- `XlsxProcessor` relies on `PhpOffice\PhpSpreadsheet`. Ensure `vendor/autoload.php` is required (already done in `index.php`). Run `composer install` when dependencies change.
- `AuthManager` stores `user_id`, `username`, `role`, and `logged_in` in `$_SESSION`. Controller constructors often call `new AuthManager()` and immediately check `isAdmin()` or `isLoggedIn()` to guard access.
- `UserModel` reads/writes the `USERS` table. Note column naming in code: password values are handled via `password_hash` in DB and mapped to `$user['password']` when returned by `findByUsername()` to satisfy existing `AuthController` expectations.

## Integration points & external dependencies
- PhpSpreadsheet (composer dependency in `composer.json`) — CSV/XLSX import and Excel date handling.
- No external APIs or services; persistence is local SQLite (`app_data.db`).

## Developer workflows & commands
- Install deps: `composer install` (composer.json requires `phpoffice/phpspreadsheet`).
- Start a quick dev server from project root: `php -S localhost:8000 -t .` and open `http://localhost:8000/`.
- The app will automatically create `app_data.db` and seed a default admin user (`username: admin`, `password: admin`) on first run.

## Testing and debugging tips
- There are no automated tests in the repo. Keep changes small and run the app locally.
- To debug: enable PHP error display or check web server/PHP error logs. Also inspect `app_data.db` with `sqlite3 app_data.db` to validate data.
- When editing DB code, be careful with column names (`password_hash` vs `password`) — UserModel performs a key-mapping to satisfy legacy expectations.

## How AI agents should make edits
- Preserve routing and include conventions. Prefer adding new controllers/models in `app/controller` and `app/model` respectively and follow existing naming patterns.
- When adding new views, include them using the same `dirname(dirname(__DIR__)) . '/app/view/...'` pattern so paths remain consistent.
- For file processing changes, keep the `IFileProcessor` interface contract: implement `read(string $filePath): array` and return rows skipping header.
- Avoid introducing background services or external DBs unless the change includes clear migration guidance and updates to `Database.php` and README.

## Useful files to reference (examples)
- Router/entry: `index.php`
- DB singleton and schema: `app/util/Database.php`
- Auth: `app/util/AuthManager.php`
- Upload flow & factory: `app/controller/UploadController.php`
- Processors: `app/util/CsvProcessor.php`, `app/util/XlsxProcessor.php`
- Models: `app/model/UserModel.php`, `app/model/IntermediacaoModel.php`
- Admin flows: `app/controller/AdminController.php`, `app/view/admin/user_list.php`

Ao clicar em "Negociações" no head da aplicação, abre a página de "Painel de Negociações", no entanto não estão buscando os dados corretos da INTERMEDIACOES_TABLE que é minha tabela na Database INTERMEDIACOES no banco de dados Mysql.
Ao clicar em "Negociar", deve-se abrir uma página correspondente na mesma linguagem visual da página de "painel de negociações", lá deve-se abrir os dados pré preenchidos da negociação selecionada na tabela anterior, respeitando as restrições dos dados que será trago do banco de dados. Exemplo, se nessa linha houver 6 títulos, eu posso vender no máximo 6 e no mínimo 1, e se eu vender, deve-se "dar baixa" na quantidade vendida, sobrando apenas o valor ainda não vendido. Esses dados devem ser tragos convertidos do Banco, pois lá as datas estão em padrão AAAA-MM-DD e quero que fiquem DD/MM/AAAA e os valores monetários devem ser apresentados em R$ e divididos por 100. Pois no banco, 5.167.367,00 é equivalente a 51.673,67.