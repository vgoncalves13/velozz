<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TestSmtpConfiguration extends Command
{
    protected $signature = 'mail:test-smtp
                            {email? : Destination email address (defaults to MAIL_FROM_ADDRESS)}
                            {--driver= : Override the mail driver (smtp, log, etc.)}';

    protected $description = 'Test the SMTP configuration by sending a test email and logging the result';

    public function handle(): int
    {
        $to = $this->argument('email') ?? config('mail.from.address');
        $driver = $this->option('driver');

        $this->info("Testing SMTP configuration...");
        $this->line("  Driver  : ".($driver ?? config('mail.default')));
        $this->line("  Host    : ".config('mail.mailers.smtp.host'));
        $this->line("  Port    : ".config('mail.mailers.smtp.port'));
        $this->line("  From    : ".config('mail.from.address'));
        $this->line("  To      : {$to}");
        $this->newLine();

        try {
            $mailer = $driver ? Mail::mailer($driver) : Mail::mailer();

            $mailer->raw(
                'This is a test email sent by the mail:test-smtp command to verify the SMTP configuration.',
                function ($message) use ($to): void {
                    $message->to($to)->subject('SMTP Test — '.config('app.name'));
                }
            );

            $this->info('Email sent successfully.');

            Log::info('mail:test-smtp — email sent successfully', [
                'driver' => $driver ?? config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'from' => config('mail.from.address'),
                'to' => $to,
            ]);

            return self::SUCCESS;
        } catch (Exception $e) {
            $this->error('Failed to send email: '.$e->getMessage());
            $this->newLine();
            $this->line('<fg=yellow>Check the log for full details.</>');

            Log::error('mail:test-smtp — failed to send email', [
                'driver' => $driver ?? config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'from' => config('mail.from.address'),
                'to' => $to,
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            return self::FAILURE;
        }
    }
}
