<?php

namespace App\Console\Commands;

use App\Http\Controllers\ZApiWebhookController;
use App\Models\Lead;
use App\Models\WhatsAppInstance;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SimulateIncomingMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simulate:incoming-message
                            {--lead= : The lead ID to send message to}
                            {--phone= : The phone number to send message from (if no lead specified)}
                            {--message= : The message content}
                            {--instance= : The WhatsApp instance ID (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate an incoming WhatsApp message for testing';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Get parameters
        $leadId = $this->option('lead');
        $phone = $this->option('phone');
        $message = $this->option('message') ?? 'Test message from simulation';
        $instanceId = $this->option('instance');

        // Validate input
        if (! $leadId && ! $phone) {
            $this->error('Either --lead or --phone must be specified');

            return Command::FAILURE;
        }

        // Get lead if specified
        if ($leadId) {
            $lead = Lead::find($leadId);
            if (! $lead) {
                $this->error("Lead with ID {$leadId} not found");

                return Command::FAILURE;
            }

            $phone = $lead->primary_whatsapp ?? $lead->first_whatsapp;
            if (! $phone) {
                $this->error('Lead does not have a WhatsApp number');

                return Command::FAILURE;
            }

            $tenantId = $lead->tenant_id;
        } else {
            // If no lead, we need instance ID
            if (! $instanceId) {
                $this->error('--instance must be specified when using --phone without --lead');

                return Command::FAILURE;
            }
        }

        // Get WhatsApp instance
        if ($instanceId) {
            $instance = WhatsAppInstance::where('instance_id', $instanceId)->first();
        } else {
            $instance = WhatsAppInstance::where('tenant_id', $tenantId)->first();
        }

        if (! $instance) {
            $this->error('No WhatsApp instance found');

            return Command::FAILURE;
        }

        // Create simulated Z-API webhook payload
        $payload = [
            'instanceId' => $instance->instance_id,
            'message' => [
                'id' => 'mock_'.Str::uuid()->toString(),
                'fromMe' => false,
                'from' => $phone.'@c.us',
                'body' => $message,
                'timestamp' => now()->timestamp,
            ],
        ];

        $this->info('Simulating incoming message...');
        $this->info("Instance: {$instance->instance_id}");
        $this->info("From: {$phone}");
        $this->info("Message: {$message}");

        // Create request and call webhook controller
        $request = Request::create(
            '/api/webhook/zapi',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $controller = new ZApiWebhookController;
        $response = $controller->handle($request);

        if ($response->getStatusCode() === 200) {
            $this->info('✓ Message simulated successfully!');
            $data = json_decode($response->getContent(), true);
            $this->info("Message ID: {$data['message_id']}");

            return Command::SUCCESS;
        } else {
            $this->error('Failed to simulate message');
            $this->error($response->getContent());

            return Command::FAILURE;
        }
    }
}
