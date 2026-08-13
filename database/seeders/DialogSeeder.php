<?php

namespace Database\Seeders;

use App\Enums\DialogResult;
use App\Enums\MessageSender;
use App\Models\Dialog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DialogSeeder extends Seeder
{
    public function run(): void
    {
        $ivan = User::where('email', 'ivan@example.com')->firstOrFail();
        $anna = User::where('email', 'anna@example.com')->firstOrFail();
        $igor = User::where('email', 'igor@example.com')->firstOrFail();

        $this->successfulSale($ivan);
        $this->explicitRefusal($ivan);
        $this->handledObjection($anna);
        $this->ghostingAfterFollowUps($igor);
        $this->slowManagerResponses($ivan);
        $this->longDialogWithoutSale($anna);
    }

    /** @param  array<int, array{0: MessageSender, 1: string, 2: Carbon}>  $messages */
    private function seedDialog(int $managerId, string $clientName, DialogResult $result, array $messages): void
    {
        $dialog = Dialog::create([
            'manager_id' => $managerId,
            'client_name' => $clientName,
            'result' => $result,
        ]);

        foreach ($messages as [$sender, $body, $sentAt]) {
            $dialog->messages()->create([
                'sender' => $sender,
                'body' => $body,
                'sent_at' => $sentAt,
            ]);
        }
    }

    private function successfulSale(User $manager): void
    {
        $base = Carbon::parse('2026-08-06 10:00:00');

        $this->seedDialog($manager->id, 'Мария Волкова', DialogResult::Purchased, [
            [MessageSender::Client, 'Добрый день! Подскажите, у вас есть тариф для команды из 10 человек?', $base->copy()],
            [MessageSender::Manager, 'Добрый день, Мария! Да, есть тариф «Команда» — 10 мест, включена приоритетная поддержка.', $base->copy()->addMinutes(4)],
            [MessageSender::Client, 'А сколько это будет стоить в месяц?', $base->copy()->addMinutes(6)],
            [MessageSender::Manager, '25 000 ₽/мес при оплате за год, есть скидка 15% на первый год.', $base->copy()->addMinutes(9)],
            [MessageSender::Client, 'Отлично, это укладывается в бюджет. Как оформить?', $base->copy()->addMinutes(12)],
            [MessageSender::Manager, 'Отправлю вам ссылку на оплату и договор прямо сейчас.', $base->copy()->addMinutes(14)],
            [MessageSender::Client, 'Спасибо, оформляю заказ!', $base->copy()->addMinutes(20)],
        ]);
    }

    private function explicitRefusal(User $manager): void
    {
        $base = Carbon::parse('2026-08-07 11:00:00');

        $this->seedDialog($manager->id, 'Дмитрий Орлов', DialogResult::NotPurchased, [
            [MessageSender::Client, 'Здравствуйте! Расскажите про тариф для малого бизнеса.', $base->copy()],
            [MessageSender::Manager, 'Добрый день! Тариф «Старт» — 3 места, 9 000 ₽/мес.', $base->copy()->addMinutes(5)],
            [MessageSender::Client, 'А можно без годовой оплаты?', $base->copy()->addMinutes(8)],
            [MessageSender::Manager, 'Да, помесячная оплата тоже доступна, но без скидки.', $base->copy()->addMinutes(11)],
            [MessageSender::Client, 'Понял, спасибо. К сожалению, руководитель решил остаться на текущем решении — переходить не будем.', $base->copy()->addMinutes(25)],
        ]);
    }

    private function handledObjection(User $manager): void
    {
        $base = Carbon::parse('2026-08-08 09:30:00');

        $this->seedDialog($manager->id, 'Ольга Романова', DialogResult::Purchased, [
            [MessageSender::Client, 'Добрый день! Смотрела ваш сервис, но как-то дорого показалось.', $base->copy()],
            [MessageSender::Manager, 'Добрый день, Ольга! Понимаю. При оплате за год стоимость на 20% ниже — выходит 2 000 ₽/мес.', $base->copy()->addMinutes(6)],
            [MessageSender::Client, 'Хорошо, но мне надо подумать, обсужу с партнёром.', $base->copy()->addMinutes(10)],
            [MessageSender::Manager, 'Конечно! Могу выслать сравнение тарифов, чтобы было проще принять решение.', $base->copy()->addMinutes(14)],
            [MessageSender::Client, 'Да, пришлите, пожалуйста. Кстати, у конкурентов вроде дешевле похожий пакет.', $base->copy()->addMinutes(40)],
            [MessageSender::Manager, 'У нас есть функции, которых нет у конкурентов — например, интеграция с CRM и поддержка 24/7. Отправил сравнение.', $base->copy()->addMinutes(45)],
            [MessageSender::Client, 'Посмотрела, впечатляет. Хорошо, беру годовой тариф.', $base->copy()->addMinutes(70)],
            [MessageSender::Manager, 'Отлично! Оформляю договор и отправляю на подпись.', $base->copy()->addMinutes(72)],
            [MessageSender::Client, 'Спасибо, жду документы!', $base->copy()->addMinutes(80)],
        ]);
    }

    private function ghostingAfterFollowUps(User $manager): void
    {
        $base = Carbon::parse('2026-08-09 10:00:00');

        $this->seedDialog($manager->id, 'Сергей Титов', DialogResult::Undecided, [
            [MessageSender::Client, 'Здравствуйте! Ищу CRM для отдела продаж из 5 человек.', $base->copy()],
            [MessageSender::Manager, 'Добрый день, Сергей! Расскажите, какие задачи сейчас не закрывает текущая система?', $base->copy()->addMinutes(5)],
            [MessageSender::Client, 'Нет автоматических напоминаний и сложно строить отчёты.', $base->copy()->addMinutes(9)],
            [MessageSender::Manager, 'У нас есть автонапоминания и конструктор отчётов без кода. Показать демо?', $base->copy()->addMinutes(13)],
            [MessageSender::Client, 'Да, было бы полезно.', $base->copy()->addMinutes(18)],
            [MessageSender::Manager, 'Отправил ссылку на запись демо и калькулятор стоимости.', $base->copy()->addMinutes(22)],
            [MessageSender::Client, 'А есть интеграция с почтой и телефонией?', $base->copy()->addMinutes(26)],
            [MessageSender::Manager, 'Да, обе есть, настраивается за 10 минут.', $base->copy()->addMinutes(30)],
            [MessageSender::Client, 'Хорошо, обсужу с командой и вернусь.', $base->copy()->addMinutes(34)],
            [MessageSender::Manager, 'Добрый день! Актуально ли ещё предложение? Готов ответить на вопросы.', $base->copy()->addDay()->setTime(12, 0)],
            [MessageSender::Manager, 'Сергей, добрый день! Если демо было полезно — дайте знать, продлим доступ к калькулятору.', $base->copy()->addDays(2)->setTime(9, 0)],
        ]);
    }

    private function slowManagerResponses(User $manager): void
    {
        $base = Carbon::parse('2026-08-10 09:00:00');

        $this->seedDialog($manager->id, 'Наталья Ежова', DialogResult::NotPurchased, [
            [MessageSender::Client, 'Здравствуйте! У нас горит дедлайн, нужно быстро подключить оплату. Поможете сегодня?', $base->copy()],
            [MessageSender::Manager, 'Здравствуйте! Да, поможем, направьте, пожалуйста, реквизиты компании.', $base->copy()->addMinutes(50)],
            [MessageSender::Client, 'Отправила на почту. Когда будет готово?', $base->copy()->addMinutes(55)],
            [MessageSender::Manager, 'Извините за паузу, всё ещё уточняю у биллинга сроки подключения.', $base->copy()->addMinutes(155)],
            [MessageSender::Client, 'Это уже третий час ожидания, у нас действительно горит время.', $base->copy()->addMinutes(160)],
            [MessageSender::Manager, 'Понимаю, прошу прощения за задержку — сейчас подключаем, осталось 10 минут.', $base->copy()->addMinutes(165)],
            [MessageSender::Client, 'Хорошо, будем ждать.', $base->copy()->addMinutes(170)],
            [MessageSender::Manager, 'Оплата пока не поступила, готовы продлить бронь ещё на час.', $base->copy()->addMinutes(176)],
        ]);
    }

    private function longDialogWithoutSale(User $manager): void
    {
        $base = Carbon::parse('2026-08-11 13:00:00');

        $this->seedDialog($manager->id, 'Павел Игнатов', DialogResult::NotPurchased, [
            [MessageSender::Client, 'Добрый день! Рассматриваем несколько решений, можно подробнее про ваш продукт?', $base->copy()],
            [MessageSender::Manager, 'Конечно! Что для вас сейчас особенно важно — цена, функциональность, поддержка?', $base->copy()->addMinutes(4)],
            [MessageSender::Client, 'В первую очередь — интеграции. У нас Bitrix24 и Telegram-бот.', $base->copy()->addMinutes(8)],
            [MessageSender::Manager, 'Обе интеграции есть из коробки, настраиваются без разработчиков.', $base->copy()->addMinutes(12)],
            [MessageSender::Client, 'Хорошо. А сколько стоит на 15 пользователей?', $base->copy()->addMinutes(16)],
            [MessageSender::Manager, '15 мест — 32 000 ₽/мес, при годовой оплате скидка 15%.', $base->copy()->addMinutes(20)],
            [MessageSender::Client, 'Это дорого относительно того, что мы видели у других.', $base->copy()->addMinutes(24)],
            [MessageSender::Manager, 'Понимаю. Могу предложить индивидуальные условия, если закрепим годовой контракт.', $base->copy()->addMinutes(28)],
            [MessageSender::Client, 'Какие именно условия?', $base->copy()->addMinutes(32)],
            [MessageSender::Manager, 'Скидка 25% и бесплатное внедрение в первый месяц.', $base->copy()->addMinutes(36)],
            [MessageSender::Client, 'Уже лучше. А есть тестовый период?', $base->copy()->addMinutes(40)],
            [MessageSender::Manager, 'Да, 14 дней бесплатно, без привязки карты.', $base->copy()->addMinutes(44)],
            [MessageSender::Client, 'Спасибо, воздержимся пока — обсудим внутри команды и вернёмся, если решим двигаться дальше.', $base->copy()->addMinutes(50)],
        ]);
    }
}
