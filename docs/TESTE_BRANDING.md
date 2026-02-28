# 🎨 Teste de Branding Dinâmico
## Logo, Cores e Nome do Tenant

---

## ✅ O que foi implementado:

1. **Logo Dinâmico** - Logo do tenant aparece no painel
2. **Cor Primária** - Cor principal do tema (botões, links, etc)
3. **Cor Secundária** - Cor secundária para elementos
4. **Nome da Empresa** - Aparece no topo do painel

---

## 🧪 Como Testar:

### Passo 1: Acessar Configurações

```
http://demo1.velozz.test/app/tenant-settings
```

### Passo 2: Configurar Logo

1. Na seção **Company Information**
2. Clique no campo **Logo**
3. Faça upload de uma imagem (PNG/JPG, max 2MB)
   - Sugestão: Logo da empresa (300x100px funciona bem)
4. Clique em **Save Settings**
5. ✅ Deve mostrar "Settings Saved"

**Verificar:**
1. Recarregue a página (F5)
2. ✅ O logo deve aparecer no canto superior esquerdo do painel
3. ✅ O logo substitui o texto "VELOZZ.DIGITAL"

### Passo 3: Configurar Cor Primária

1. Na seção **Company Information**
2. Clique no campo **Primary Color**
3. Escolha uma cor (exemplos):
   - Verde: `#10B981`
   - Azul: `#3B82F6`
   - Roxo: `#8B5CF6`
   - Vermelho: `#EF4444`
4. Clique em **Save Settings**

**Verificar:**
1. Recarregue a página (F5 ou Ctrl+Shift+R para hard refresh)
2. ✅ Botões devem ter a nova cor
3. ✅ Links devem ter a nova cor
4. ✅ Elementos de destaque devem usar a nova cor

### Passo 4: Configurar Cor Secundária

1. Na seção **Company Information**
2. Clique no campo **Secondary Color**
3. Escolha uma cor complementar
4. Clique em **Save Settings**

**Verificar:**
1. Recarregue a página (F5)
2. ✅ Elementos secundários usam a nova cor

### Passo 5: Alterar Nome da Empresa

1. Na seção **Company Information**
2. Altere **Company Name** para "Minha Empresa SA"
3. Clique em **Save Settings**

**Verificar:**
1. Recarregue a página (F5)
2. ✅ O nome "Minha Empresa SA" deve aparecer:
   - No topo do painel (ao lado ou no lugar do logo)
   - No título da página do navegador

---

## 🎨 Combinações de Cores Sugeridas:

### Profissional (Azul)
- **Primary**: `#2563EB` (Azul Royal)
- **Secondary**: `#64748B` (Cinza Azulado)

### Energia (Verde)
- **Primary**: `#10B981` (Verde Esmeralda)
- **Secondary**: `#F59E0B` (Âmbar)

### Moderno (Roxo)
- **Primary**: `#8B5CF6` (Roxo Vibrante)
- **Secondary**: `#EC4899` (Rosa)

### Elegante (Preto/Dourado)
- **Primary**: `#1F2937` (Cinza Escuro)
- **Secondary**: `#F59E0B` (Dourado)

---

## 🔍 Verificação Técnica:

### 1. Verificar no Banco de Dados

```sql
SELECT id, name, settings FROM tenants WHERE id = 1;
```

**Resultado esperado:**
```json
{
  "logo": "tenant-logos/abc123.jpg",
  "primary_color": "#10B981",
  "secondary_color": "#F59E0B"
}
```

### 2. Verificar Arquivo de Logo

```bash
ls -la storage/app/public/tenant-logos/
```

**Deve mostrar:**
```
-rw-r--r-- 1 sail sail 15234 Feb 26 22:30 abc123.jpg
```

### 3. Verificar Link Público

```
http://demo1.velozz.test/storage/tenant-logos/abc123.jpg
```

✅ Deve mostrar a imagem do logo

---

## 🐛 Troubleshooting:

### Problema: Logo não aparece

**Causas possíveis:**

1. **Link simbólico não existe**
   ```bash
   vendor/bin/sail artisan storage:link
   ```

2. **Permissões incorretas**
   ```bash
   vendor/bin/sail exec app chmod -R 775 storage/app/public
   vendor/bin/sail exec app chown -R sail:sail storage/app/public
   ```

3. **Arquivo não foi salvo**
   - Verifique `storage/app/public/tenant-logos/`
   - Tente fazer upload novamente

### Problema: Cores não mudam

**Soluções:**

1. **Hard refresh no navegador**
   - Chrome/Firefox: `Ctrl + Shift + R`
   - Mac: `Cmd + Shift + R`

2. **Limpar cache do navegador**
   - Abra DevTools (F12)
   - Clique com botão direito no refresh
   - Selecione "Empty Cache and Hard Reload"

3. **Verificar se as cores foram salvas**
   ```sql
   SELECT settings FROM tenants WHERE id = 1;
   ```

4. **Limpar cache do Filament**
   ```bash
   vendor/bin/sail artisan filament:clear-cached-components
   vendor/bin/sail artisan view:clear
   ```

### Problema: Nome não muda

**Solução:**
1. Verifique se salvou as configurações
2. Faça logout e login novamente
3. Limpe o cache:
   ```bash
   vendor/bin/sail artisan cache:clear
   ```

---

## 📸 Teste Visual Completo:

### 1. Estado Inicial (Antes)
- Logo: "VELOZZ.DIGITAL" (texto)
- Cor: Âmbar (padrão)
- Nome: "Demo Company 1"

### 2. Estado Customizado (Depois)
- Logo: Imagem personalizada
- Cor Primária: Verde `#10B981`
- Cor Secundária: Dourado `#F59E0B`
- Nome: "Minha Empresa SA"

### 3. Verificar em Múltiplas Páginas
- [ ] Dashboard - `/app`
- [ ] Leads - `/app/leads`
- [ ] Inbox - `/app/inbox`
- [ ] Settings - `/app/tenant-settings`
- [ ] Kanban - `/app/kanban`

**Todas as páginas devem mostrar:**
- ✅ Logo personalizado
- ✅ Cores personalizadas
- ✅ Nome personalizado

---

## 🎯 Teste com Múltiplos Tenants:

### Tenant 1 (demo1.velozz.test):
- Logo: logo-empresa-1.png
- Primary Color: `#10B981` (Verde)
- Nome: "Empresa 1"

### Tenant 2 (demo2.velozz.test):
- Logo: logo-empresa-2.png
- Primary Color: `#3B82F6` (Azul)
- Nome: "Empresa 2"

**Verificação:**
1. Faça login em demo1.velozz.test
   - ✅ Deve mostrar logo/cores da Empresa 1
2. Abra em aba anônima: demo2.velozz.test
   - ✅ Deve mostrar logo/cores da Empresa 2
3. **Isolamento confirmado!** Cada tenant tem seu próprio branding

---

## ✅ Checklist de Sucesso:

Para considerar o branding **100% funcional**:

- [ ] Logo pode ser feito upload
- [ ] Logo aparece no painel após salvar
- [ ] Logo é único por tenant
- [ ] Cor primária pode ser alterada
- [ ] Cor primária aplica em botões/links
- [ ] Cor secundária pode ser alterada
- [ ] Nome da empresa aparece no painel
- [ ] Configurações persistem após reload
- [ ] Configurações são isoladas por tenant
- [ ] Hard refresh aplica as mudanças de cor
- [ ] Link simbólico do storage funciona

---

## 🚀 Teste Rápido (2 minutos):

```bash
# 1. Acesse
http://demo1.velozz.test/app/tenant-settings

# 2. Upload logo (qualquer imagem)
# 3. Altere cor primária para #10B981
# 4. Clique "Save Settings"
# 5. Faça hard refresh (Ctrl+Shift+R)

# ✅ Resultado esperado:
# - Logo aparece no topo
# - Botões ficaram verdes
# - Configurações persistem
```

---

## 📝 Notas Importantes:

1. **Cache do navegador**: Cores podem demorar para aparecer se não fizer hard refresh
2. **Upload máximo**: 2MB para logos (configurado no FileUpload)
3. **Formatos suportados**: PNG, JPG, JPEG, GIF
4. **Dimensões recomendadas**: 300x100px (proporção 3:1)
5. **Cores**: Use formato hexadecimal (#RRGGBB)

---

Pronto para testar! 🎨
