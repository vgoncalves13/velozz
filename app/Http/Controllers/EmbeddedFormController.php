<?php

namespace App\Http\Controllers;

use App\Enums\LeadSource;
use App\Models\EmbeddedForm;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class EmbeddedFormController extends Controller
{
    public function show(string $slug): View|Response
    {
        $form = EmbeddedForm::withoutGlobalScopes()
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        return view('forms.show', compact('form'));
    }

    public function preview(string $slug): View|Response
    {
        $form = EmbeddedForm::withoutGlobalScopes()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('forms.preview', compact('form'));
    }

    public function script(int $id): Response
    {
        $form = EmbeddedForm::withoutGlobalScopes()
            ->where('id', $id)
            ->where('status', 'active')
            ->firstOrFail();

        $content = view('embed.form-script', compact('form'))->render();

        return response($content, 200, ['Content-Type' => 'application/javascript']);
    }

    public function submit(Request $request, string $slug): JsonResponse
    {
        $form = EmbeddedForm::withoutGlobalScopes()
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $fields = $form->fields ?? [];
        $rules = [];

        foreach ($fields as $field) {
            $fieldRules = [];

            if (! empty($field['required'])) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            if ($field['type'] === 'email') {
                $fieldRules[] = 'email';
            } elseif ($field['type'] === 'number') {
                $fieldRules[] = 'numeric';
            }

            if (! empty($field['validation']['min'])) {
                $fieldRules[] = 'min:'.$field['validation']['min'];
            }
            if (! empty($field['validation']['max'])) {
                $fieldRules[] = 'max:'.$field['validation']['max'];
            }

            $rules[$field['name']] = $fieldRules;
        }

        $validated = $request->validate($rules);

        $fullName = $validated['full_name'] ?? $validated['name'] ?? $validated['nome'] ?? null;
        $email = $validated['email'] ?? null;
        $phone = $validated['phone'] ?? $validated['whatsapp'] ?? $validated['telefone'] ?? null;

        $knownFields = ['full_name', 'name', 'nome', 'email', 'phone', 'whatsapp', 'telefone'];
        $customFields = array_filter(
            $validated,
            fn ($key) => ! in_array($key, $knownFields),
            ARRAY_FILTER_USE_KEY
        );

        Lead::create([
            'tenant_id' => $form->tenant_id,
            'full_name' => $fullName ?? 'Unknown',
            'email' => $email,
            'phones' => $phone ? [$phone] : null,
            'source' => LeadSource::EmbeddedForm,
            'custom_fields' => ! empty($customFields) ? $customFields : null,
        ]);

        return response()->json([
            'success' => true,
            'redirect_url' => $form->redirect_url,
        ]);
    }
}
