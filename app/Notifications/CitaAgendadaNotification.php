<?php

namespace App\Notifications;

use App\Models\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CitaAgendadaNotification extends Notification
{
    use Queueable;

    public function __construct(public Cita $cita) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nueva Cita Agendada')
            ->line('Tiene una nueva cita médica asignada.')
            ->line('Paciente: '.$this->cita->patient->name)
            ->line('Especialidad: '.$this->cita->especialidad)
            ->line('Fecha: '.$this->cita->fecha_hora->format('d/m/Y H:i'));
    }
}
