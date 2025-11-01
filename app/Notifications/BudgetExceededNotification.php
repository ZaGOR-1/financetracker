<?php

namespace App\Notifications;

use App\Models\Budget;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BudgetExceededNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Budget $budget,
        public string $alertType // 'warning' or 'exceeded'
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $categoryName = $this->budget->category 
            ? $this->budget->category->name 
            : 'Загальний бюджет';

        $percentage = number_format((float) $this->budget->percentage, 1);
        $spent = number_format($this->budget->spent, 2, ',', ' ');
        $amount = number_format((float) $this->budget->amount, 2, ',', ' ');

        $message = (new MailMessage)
            ->subject($this->getSubject())
            ->greeting('Вітаємо, ' . $notifiable->name . '!');

        if ($this->alertType === 'exceeded') {
            $message->error();
        }

        $message->line($this->getLine())
            ->line("**{$categoryName}**")
            ->line("Бюджет: {$amount} ₴")
            ->line("Витрачено: {$spent} ₴ ({$percentage}%)")
            ->action('Переглянути бюджети', url('/budgets'))
            ->line('Рекомендуємо переглянути ваші витрати та, можливо, скоригувати бюджет.');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'budget_id' => $this->budget->id,
            'category_name' => $this->budget->category?->name ?? 'Загальний бюджет',
            'alert_type' => $this->alertType,
            'percentage' => $this->budget->percentage,
            'spent' => $this->budget->spent,
            'amount' => $this->budget->amount,
        ];
    }

    /**
     * Get the notification subject.
     */
    private function getSubject(): string
    {
        return $this->alertType === 'exceeded'
            ? '🚨 Бюджет перевищено!'
            : '⚠️ Попередження про бюджет';
    }

    /**
     * Get the notification line.
     */
    private function getLine(): string
    {
        return $this->alertType === 'exceeded'
            ? 'Ваш бюджет було перевищено:'
            : 'Ваш бюджет досяг порогу попередження:';
    }
}

