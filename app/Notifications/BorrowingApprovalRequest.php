<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BorrowingApprovalRequest extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Transaction $transaction
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $asset = $this->transaction->asset;

        return (new MailMessage)
            ->subject("[Persetujuan] Peminjaman {$asset?->kode_barang} oleh {$this->transaction->nama_peminjam}")
            ->greeting("Halo {$notifiable->name},")
            ->line("Terdapat permintaan peminjaman aset baru yang membutuhkan persetujuan Anda:")
            ->line("**Barang:** {$asset?->nama_barang} ({$asset?->kode_barang})")
            ->line("**Peminjam:** {$this->transaction->nama_peminjam}")
            ->line("**Keperluan:** {$this->transaction->keperluan}")
            ->line("**Tanggal Pinjam:** {$this->transaction->tanggal_pinjam->format('d/m/Y')}")
            ->action('Lihat & Setujui', url(route('transactions.index', ['status' => 'Menunggu Persetujuan'])))
            ->line("Silakan login untuk menyetujui atau menolak permintaan ini.");
    }
}
