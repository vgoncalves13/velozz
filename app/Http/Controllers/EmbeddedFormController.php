<?php

namespace App\Http\Controllers;

use App\Enums\LeadField;
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

        $leadData = [
            'tenant_id' => $form->tenant_id,
            'source' => LeadSource::EmbeddedForm,
        ];
        $phones = [];
        $whatsapps = [];
        $customFields = [];

        foreach ($fields as $field) {
            $value = $validated[$field['name']] ?? null;
            $mapsTo = ! empty($field['maps_to']) ? $field['maps_to'] : null;

            if ($mapsTo === null) {
                if ($value !== null) {
                    $customFields[$field['name']] = $value;
                }

                continue;
            }

            if ($mapsTo === LeadField::Phone->value) {
                if ($value) {
                    $phones[] = $value;
                }
            } elseif ($mapsTo === LeadField::Whatsapp->value) {
                if ($value) {
                    $whatsapps[] = $value;
                }
            } else {
                $leadData[$mapsTo] = $value;
            }
        }

        if (! empty($phones)) {
            $leadData['phones'] = $phones;
        }

        if (! empty($whatsapps)) {
            $leadData['whatsapps'] = $whatsapps;
        }

        if (! empty($customFields)) {
            $leadData['custom_fields'] = $customFields;
        }

        if (empty($leadData['full_name'])) {
            $leadData['full_name'] = 'Unknown';
        }

        Lead::create($leadData);

        return response()->json([
            'success' => true,
            'redirect_url' => $form->redirect_url,
        ]);
    }
}
