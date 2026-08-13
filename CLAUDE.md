# dialog-analyzer

## Главное правило

Все `php`/`composer`/`npm`/`node` команды — только в Docker (`docker/docker-compose.yml`, сервисы `php`/`nginx`/`db`, node — в контейнере `php`):

```bash
docker compose -f docker/docker-compose.yml exec php <команда>
```

Приложение — http://localhost:8080. `npm run dev` (Vite HMR) наружу не проброшен.

**TDD без исключений**: тест до кода, должен упасть, затем код до зелёного.

**После зелёных тестов на UI** (`.vue`, layout, стили) — обязательно проверить живьём в браузере через Playwright MCP, не только автотестами.

## Стек

Laravel 13 (PHP 8.3) + Inertia v2 + Vue 3 (JS, без TS) + shadcn-vue (`new-york`/`zinc`) + Tailwind. Тесты — PHPUnit. БД — PostgreSQL в докере, для тестов отдельная БД `laravel_test` (конфиг в `phpunit.xml`, схема мигрируется один раз и кешируется, сброс между тестами — `RefreshDatabase`).

Первый запуск: скопировать `.env.testing.example` → `.env.testing` (в `.gitignore`). Без него `--env=testing`-команды тихо бьют по dev-БД вместо `laravel_test` (`php artisan test` не задет — берёт конфиг из `phpunit.xml` напрямую).

## Структура

- `resources/js/Pages/*.vue`, `Layouts/*` — Inertia-страницы/layout'ы (Breeze legacy).
- `resources/js/Components/*` — старый Breeze UI (PascalCase, 11 auth/profile файлов) — не удалять, не расширять.
- `resources/js/components/ui/*` — shadcn-vue, весь новый UI.
- `@/*` → `resources/js/*`.
- `.agents/skills/*` — копии скиллов из `skills-lock.json`.

## Домен: анализ диалогов

`Dialog` → `Message` (`manager`/`client`, хронология по `sent_at`), `AnalysisRule` (`severity`/`enabled`/`config` jsonb) → `AnalysisEvent` (`evidence` jsonb).

- Доступ: `manager` — только свои диалоги, `analyst` — все + редактирование правил (`DialogPolicy`, `AnalysisRulePolicy`, роль в `UserRole`).
- Правила — plugin-архитектура: класс реализует `App\Analysis\AnalysisRule`, регистрируется в `config/analysis_rules.php`. `RuleRegistry` резолвит key→класс, `AnalysisRunner` прогоняет включённые правила в транзакции (старые `events` удаляются и пересоздаются). Новое правило = класс в `app/Analysis/Rules/` + строка в конфиге, UI/контроллеры не трогать — форма рендерится по `configSchema()` динамически.
- Пересчёт асинхронный: создание `Message` → `MessageObserver` → `AnalyzeDialogJob` в очередь (`database`, `--tries=3`; в тестах `sync`).
- Исключение в правиле откатывает весь анализ целиком, старые `events` остаются как есть — намеренный fail-safe, закреплён тестом `test_a_throwing_rule_rolls_back_and_keeps_previous_events` (`tests/Unit/Analysis/AnalysisRunnerTest.php`).

## Как писать код

- Backend: тонкие контроллеры, валидация в `FormRequest`, логика в моделях/сервисах, без N+1. Порядок: миграция → модель → контроллер/роут → Inertia-страница (скилл `laravel-best-practices`).
- Frontend: только Composition API + `<script setup>`, новый UI — через `components/ui`, стили — Tailwind + `cn()`. Без TS без явного запроса.
- Тесты: PHPUnit-классы, тест до кода (скилл `laravel-tdd`).

## Экономия токенов

- Не читать/грепать `vendor/`, `node_modules/`, lock-файлы целиком.
- Широкий/неопределённый поиск — subagent'у (`Explore`), не вручную.
- После `Edit`/`Write` файл не перечитывать.
- Тесты гонять точечно (`--filter=...`).
- Ответ — итог, без пересказа диффа/вывода; ссылки вида `путь:строка`.
- Скриншоты — в `screenshots/`, не вставлять в контекст без необходимости.

## Скиллы

| Скилл | Когда |
|---|---|
| `git-commit` | Коммит / `/commit` |
| `laravel-best-practices` | Любой PHP-код Laravel |
| `laravel-tdd` | Перед новой фичей/багфиксом |
| `vue-best-practices` | Работа с `.vue` |
| `shadcn` | shadcn-vue компоненты |
| `ui-ux-pro-max` | Изменение UI — обязателен ревью-чеклист |

## Playwright MCP

`playwright` MCP (`.mcp.json`) — обязателен для проверки UI в браузере после фронтенд-изменений, в дополнение к тестам. Скриншоты — в `screenshots/` (в `.gitignore`).
