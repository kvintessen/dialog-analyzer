# dialog-analyzer

## Главное правило

Все `php`/`composer`/`npm`/`node` команды — только в Docker, не на хосте. Compose: `docker/docker-compose.yml` (сервисы `php`, `nginx`, `db`). Node стоит в контейнере `php`, отдельного сервиса нет.

```bash
docker compose -f docker/docker-compose.yml exec php <команда>   # composer install / php artisan ... / npm run build / php artisan test
```

Приложение — http://localhost:8080. `npm run dev` (Vite HMR) наружу не проброшен (нет порта 5173 / `--host`) — сначала донастроить.

**TDD: сначала тест, потом реализация.** Без исключений — тест пишется до кода, должен упасть, затем код до зелёного.

## Стек

Laravel 13 (PHP 8.3) + Inertia v2 + Vue 3 (чистый JS, без TS) + shadcn-vue (`new-york`/`zinc`) + Tailwind. Тесты — PHPUnit (не Pest). БД — PostgreSQL в докере (`database.sqlite` не используется, только in-memory sqlite для тестов). Сейчас в проекте только скелет Breeze-auth + shadcn — своей бизнес-логики нет.

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
| `requesting-code-review` | По завершении заметной задачи, перед merge |

Вызываются автоматически по контексту задачи.

## Playwright MCP

MCP-сервер `playwright` (`.mcp.json`) — для проверки UI в браузере после фронтенд-изменений. Скриншоты — в `screenshots/` (в `.gitignore`, не коммитить).
