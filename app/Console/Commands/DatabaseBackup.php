<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DatabaseBackup extends Command
{
    protected $signature = 'app:database-backup
                            {--disk=local : Disk tujuan penyimpanan backup}
                            {--keep=7 : Jumlah backup yang akan disimpan}';

    protected $description = 'Backup database MySQL ke file SQL';

    public function handle(): int
    {
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');
        $disk = $this->option('disk');
        $keep = (int) $this->option('keep');

        $filename = sprintf(
            'backup-%s-%s.sql',
            $dbName,
            now()->format('Y-m-d_H-i-s')
        );

        $backupDir = 'backups';
        $backupPath = storage_path("app/{$disk}/{$backupDir}/{$filename}");

        if (!is_dir(dirname($backupPath))) {
            mkdir(dirname($backupPath), 0755, true);
        }

        $command = sprintf(
            'mysqldump --host=%s --user=%s --password=%s --routines --single-transaction %s > %s 2>&1',
            escapeshellarg($dbHost),
            escapeshellarg($dbUser),
            escapeshellarg($dbPass),
            escapeshellarg($dbName),
            escapeshellarg($backupPath)
        );

        $output = null;
        $resultCode = null;
        exec($command, $output, $resultCode);

        if ($resultCode !== 0) {
            $this->error("Backup gagal: " . implode("\n", $output));
            return Command::FAILURE;
        }

        $this->info("Backup berhasil: {$filename}");

        $files = collect(Storage::disk($disk)->files("{$backupDir}"))
            ->filter(fn($f) => str_starts_with(basename($f), 'backup-'))
            ->sort()
            ->values();

        if ($files->count() > $keep) {
            $toDelete = $files->take($files->count() - $keep);
            foreach ($toDelete as $file) {
                Storage::disk($disk)->delete($file);
                $this->warn("Backup lama dihapus: {$file}");
            }
        }

        return Command::SUCCESS;
    }
}
