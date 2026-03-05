<?php

namespace App\Http\Controllers;

use App\Enums\LeadSource;
use App\Models\Lead;
use App\Models\WhatsAppInstance;
use App\Models\WhatsAppWidget;
use App\Services\ZApi\ZApiServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class WhatsAppWidgetController extends Controller
{
    public function __construct(private readonly ZApiServiceInterface $zApi) {}

    public function preview(int $id): View|Response
    {
        $widget = WhatsAppWidget::withoutGlobalScopes()
            ->where('id', $id)
            ->firstOrFail();

        return view('embed.whatsapp-preview', compact('widget'));
    }

    public function script(int $id): Response
    {
        $widget = WhatsAppWidget::withoutGlobalScopes()
            ->where('id', $id)
            ->where('status', 'active')
            ->firstOrFail();

        $content = view('embed.whatsapp-script', compact('widget'))->render();

        return response($content, 200, ['Content-Type' => 'application/javascript']);
    }

    public function submit(Request $request, int $id): JsonResponse
    {
        $widget = WhatsAppWidget::withoutGlobalScopes()
            ->where('id', $id)
            ->where('status', 'active')
            ->firstOrFail();

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'telefone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $lead = Lead::create([
            'tenant_id' => $widget->tenant_id,
            'full_name' => $validated['nome'],
            'email' => $validated['email'] ?? null,
            'phones' => [$validated['telefone']],
            'whatsapps' => [$validated['telefone']],
            'source' => LeadSource::WhatsappWidget,
        ]);

        $instance = WhatsAppInstance::withoutGlobalScopes()
            ->where('tenant_id', $widget->tenant_id)
            ->where('status', 'connected')
            ->first();

        if ($instance) {
            $message = str_replace('{{nome}}', $validated['nome'], $widget->auto_message);
            $this->zApi->sendMessage($instance->instance_id, $validated['telefone'], $message);
        }

        return response()->json(['success' => true]);
    }
}
