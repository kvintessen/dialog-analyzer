# dialog-analyzer

## Главное правило

Все `php`/`composer`/`npm`/`node` команды — только в Docker, не на хосте. Compose: `docker/docker-compose.yml` (сервисы `php`, `nginx`, `db`). Node стоит в контейнере `php`, отдельного сервиса нет.

```bash
docker compose -f docker/docker-compose.yml exec php <команда>   # composer install / php artisan ... / npm run build / php artisan test
```

Приложение — http://localhost:8080. `npm run dev` (Vite HMR) наружу не проброшен (нет порта 5173 / `--host`) — сначала донастроить.

**TDD: сначала тест, потом реализация.** Без исключений — тест пишется до кода, должен упасть, затем код до зелёного.

**Проверка изменений: тестов недостаточно.** Если изменения касаются UI (`.vue`, layout, стили, что угодно во `resources/js`), после того как тесты зелёные — обязательно проверить их живьём в браузере через Playwright MCP (см. раздел ниже), а не только полагаться на автотесты.

## Стек

Laravel 13 (PHP 8.3) + Inertia v2 + Vue 3 (чистый JS, без TS) + shadcn-vue (`new-york`/`zinc`) + Tailwind. Тесты — PHPUnit (не Pest). БД — PostgreSQL в докере (`database.sqlite` не используется). Для тестов — отдельная БД `laravel_test` в том же контейнере `db` (не sqlite): создаётся автоматически из `docker/db/init/01-create-test-db.sql` при первом старте контейнера на чистом volume, конфиг — в `phpunit.xml`. Сброс состояния между тестами — через `RefreshDatabase` в фиче-тестах (транзакция + rollback), схема мигрируется один раз и кешируется. Сейчас в проекте только скелет Breeze-auth + shadcn — своей бизнес-логики нет.

При первом запуске скопировать `.env.testing.example` → `.env.testing` (файл в `.gitignore`, как и `.env`). Без него артизан-команды с `--env=testing` (например, ручной `migrate:fresh --env=testing`) тихо откатятся на обычный `.env` и заденут dev-БД вместо `laravel_test` — `php artisan test` при этом не пострадает, конфиг для него берётся из `phpunit.xml` напрямую.

## Структура

- `resources/js/Pages/*.vue` — Inertia-страницы, `Layouts/*` — layout'ы (Breeze legacy).
- `resources/js/Components/*` — **старый** Breeze UI (PascalCase), используется в 11 auth/profile файлах — не удалять без замены, не расширять.
- `resources/js/components/ui/*` — shadcn-vue, весь новый UI — сюда.
- Алиас `@/*` → `resources/js/*`.
- `.agents/skills/*` — локальные копии скиллов из `skills-lock.json`.

## Как писать код

- Backend: тонкие контроллеры, валидация во `FormRequest`, логика в моделях/сервисах, Eloquent без N+1. Порядок: миграция → модель → контроллер/роут → Inertia-страница. Следовать скиллу `laravel-best-practices`.
- Frontend: только Composition API + `<script setup>`, новый UI — через `components/ui`, стили — Tailwind + `cn()` из `lib/utils.js`. Без TS без явного запроса.
- Тесты: PHPUnit-классы (`extends TestCase`), как в `tests/`. Тест до кода (скилл `laravel-tdd`, примеры на Pest — адаптировать под PHPUnit).

## Экономия токенов

- Не читать/грепать `vendor/`, `node_modules/`, lock-файлы целиком; искать `grep`/`find` по конкретному пути.
- Широкий/неопределённый поиск — делегировать subagent'у (`Explore`), не перебирать файлы вручную.
- После `Edit`/`Write` файл не перечитывать — ошибка была бы видна сразу.
- Тесты гонять точечно (`--filter=...`), не весь набор без нужды.
- Ответ — итог и следствия, без пересказа диффа/файла/вывода команды; ссылки вида `путь:строка`.
- `git diff --unified=0`, `git log --oneline -10`, `gh issue list --json number,title`.
- Скриншоты — в `screenshots/`, не вставлять в контекст без необходимости.

## Скиллы

| Скилл | Когда |
|---|---|
| `git-commit` | Коммит / `/commit` |
| `laravel-best-practices` | Любой PHP-код Laravel |
| `laravel-tdd` | Перед новой фичей/багфиксом — тест сначала |
| `vue-best-practices` | Любая работа с `.vue` |
| `shadcn` | Добавление/кастомизация shadcn-vue компонентов |
| `ui-ux-pro-max` | Любое изменение UI (новые страницы/компоненты, цвет, типографика, layout, доступность, анимации) — обязателен ревью-чеклист перед сдачей |

Вызываются автоматически по контексту задачи.

## Playwright MCP

MCP-сервер `playwright` (`.mcp.json`) — обязателен для проверки UI в браузере после любых фронтенд-изменений, в дополнение к тестам (не вместо них). Открыть нужную страницу, проверить основной сценарий и то, что задели изменения. Скриншоты — в `screenshots/` (в `.gitignore`, не коммитить).
