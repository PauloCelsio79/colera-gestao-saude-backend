<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class UsuarioLogou extends Notification
{
    use Queueable;

    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Novo login no sistema')
            ->greeting('Olá, Administrador!')
            ->line('O usuário ' . $this->user->name . ' ('.$this->user->email.') acabou de iniciar sessão.')
            ->line('Tipo de usuário: ' . $this->user->tipo)
            ->line('Data/Hora: ' . now()->format('d/m/Y H:i:s'));
    }
}

