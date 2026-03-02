# Filament v4/v5 — Regras para AI

> Este projeto usa **Filament v5** (que é idêntico ao v4 em API, mas requer Livewire v4+).
> Sempre gere código compatível com Filament v4/v5. NUNCA use sintaxe do Filament v3.

---

## ❌ Métodos REMOVIDOS no v3 → NÃO USE JAMAIS

| ❌ v3 (proibido)                      | ✅ v4/v5 (correto)                                      |
|--------------------------------------|--------------------------------------------------------|
| `->form()` em Actions/Filters         | `->schema()`                                           |
| `->mutateFormDataUsing()`             | `->mutateDataUsing()`                                  |
| `Placeholder::make()`                 | `TextEntry::make()->state()` (import de Infolists)     |
| `->label('')` para esconder label     | `->hiddenLabel()`                                      |
| `->reactive()`                        | `->live()`                                             |
| `protected static string $view`       | `protected string $view` (não-estático em Pages!)      |
| `route('filament.admin.resources...')` | `MinhaResource::getUrl('index')`                      |

---

## ✅ Regras Gerais

### Resources
- **NUNCA** gere a página `View` ou `Infolist` a menos que seja explicitamente solicitado
- Ao criar um Resource, gere também os smoke tests (ver seção de Testes)
- Scaffold sempre via artisan: `php artisan make:filament-resource NomeModel`

### Formulários (Forms)
- Reatividade: usar `->live()`, nunca `->reactive()`
- Validação `unique()` já tem `ignoreRecord: true` por padrão no v4/v5 — não especifique
- Para campos condicionais use `->visible()` ou `->hidden()` com closure
- Calculos automáticos: usar `->live()` + `->afterStateUpdated()`

### Enums
- Sempre implementar `HasLabel`, `HasColor`, `HasIcon` nos Enums usados em Models
- Retornos de `getIcon()` devem ser `string|BackedEnum|Htmlable|null` — não substitua por tipos mais específicos
- Ao definir valor padrão com Enum, **nunca** adicionar `->value`
- Usar `Filament\Support\Icons\Heroicon` (enum) para ícones, nunca string hardcoded
- Nunca hardcode strings onde existe Enum — sempre referencie o Enum

### Actions
- Para autorização, usar `->authorize('ability')` na action — não usar `Gate::authorize()` manualmente
- Actions com modal: configurar via `->modalHeading()`, `->modalDescription()`, `->modalSubmitActionLabel()`

### Rotas
- Referenciar rotas do Filament com `MinhaResource::getUrl('index')`, nunca com helper `route()`
- Sempre especificar o nome exato do Resource, nunca usar `getResource()`

### Blade / Temas customizados
- Se criar Blade files com classes Tailwind fora dos diretórios padrão do Filament, registrar o caminho no `theme.css` do tema customizado

---

## 🧪 Testes

- Ao criar um Resource: **obrigatório** gerar smoke tests
- Ao alterar um Resource: **obrigatório** rodar os testes existentes (ou gerá-los se não existirem)
- Sintaxe correta com Pest: `Livewire::test(MinhaPage::class)` — **nunca** `livewire(MinhaPage::class)`
- Em factories/testes, usar o Enum diretamente quando o campo é castado para Enum — nunca string hardcoded

---

## 📦 Requisitos do Filament v5

- PHP 8.2+
- Laravel 11.28+
- Livewire 4.0+
- Tailwind CSS 4.0+

---

## 🔗 Documentação oficial

- Resources: https://filamentphp.com/docs/5.x/resources/overview
- Forms: https://filamentphp.com/docs/5.x/forms/overview
- Tables: https://filamentphp.com/docs/5.x/tables/overview
- Infolists: https://filamentphp.com/docs/5.x/infolists/overview
- Actions: https://filamentphp.com/docs/5.x/actions/overview
- Enums: https://filamentphp.com/docs/5.x/advanced/enums
- Upgrade guide: https://filamentphp.com/docs/5.x/upgrade-guide
