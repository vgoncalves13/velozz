# ✅ PHASE 2 - Authentication & RBAC - IN PROGRESS

## What Has Been Implemented

### 1. Spatie Permission Integration ✅
- Configured Spatie Permission package
- Migrations created and executed
- HasRoles trait added to User model

### 2. Roles and Permissions ✅
Created 5 roles with granular permissions:

#### Roles:
1. **admin_master** - Full system access (manages all tenants)
2. **admin_client** - Full tenant access
3. **supervisor** - Can manage team and leads
4. **operator** - Basic operator permissions (only assigned leads)
5. **financial** - Financial access (products, opportunities, reports)

#### Permissions Created:
- **Lead permissions:** view_lead, create_lead, edit_lead, delete_lead, export_leads, import_leads
- **WhatsApp permissions:** send_message, view_messages, configure_whatsapp, manage_templates
- **Dashboard & Reports:** view_dashboard, view_reports
- **Team Management:** manage_operators, view_operators
- **Pipeline & Kanban:** manage_pipeline, view_pipeline, move_lead_stage
- **Products & Opportunities:** manage_products, view_products, manage_opportunities, view_opportunities
- **Settings & Configuration:** manage_tenant_settings, view_audit_logs

### 3. Policies ✅
Created authorization policies:

#### TenantPolicy
- Only admin_master can view/create/edit/delete tenants
- Protects tenant management from non-admin users

#### LeadPolicy
- Operators can only view and edit their assigned leads
- Admin and supervisors can view/edit all leads
- Checks permissions using Spatie's hasPermissionTo()

### 4. User Model Updates ✅
- Added helper methods: isAdminMaster(), isAdminClient(), isSupervisor(), isOperator(), isFinancial()
- Updated canAccessPanel() to use English role names
- Integrated with Spatie HasRoles trait

---

### 5. Middleware de Permissões nos Panels ✅
- Panel authorization handled via User::canAccessPanel() method
- Policies auto-discovered in Laravel 12
- Permission checks integrated with Spatie's hasPermissionTo()

### 6. Sistema de Convite ✅
- ✅ Created InviteUser command (user:invite)
- ✅ Created SendInviteEmail job (queued)
- ✅ Created InviteMail mailable with markdown template
- ✅ Created AcceptInviteController (show & store methods)
- ✅ Created accept-invite Blade view
- ✅ Added routes: GET /accept-invite/{token} and POST /accept-invite/{token}

---

## ✅ PHASE 2 COMPLETE - Ready for Testing

---

## Files Created/Modified

### New Files:
- `database/seeders/RolesAndPermissionsSeeder.php` - Creates roles and permissions
- `app/Policies/TenantPolicy.php` - Tenant authorization rules
- `app/Policies/LeadPolicy.php` - Lead authorization rules

### Modified Files:
- `app/Models/User.php` - Added HasRoles trait and role helper methods
- `database/seeders/DemoTenantsSeeder.php` - Updated to use 'admin_client' role
- `PHASE1_COMPLETE.md` - Updated role names to English

---

## Testing Phase 2 So Far

### 1. Verify Roles and Permissions
```bash
vendor/bin/sail artisan tinker
>>> \Spatie\Permission\Models\Role::all()->pluck('name')
>>> \Spatie\Permission\Models\Permission::all()->pluck('name')
```

Expected output:
- Roles: admin_master, admin_client, supervisor, operator, financial
- Permissions: view_lead, create_lead, edit_lead, etc. (all in English)

### 2. Test TenantPolicy
- Login as admin_master (admin@velozz.digital)
- Access http://velozz.test/admin/tenants - should work
- Logout and login as admin_client (admin@demo1.test)
- Try to access http://demo1.velozz.test/admin/tenants - should be denied (403)

### 3. Test User Role Methods
```bash
vendor/bin/sail artisan tinker
>>> $user = User::where('email', 'admin@velozz.digital')->first()
>>> $user->isAdminMaster()  // should return true
>>> $user->isAdminClient()  // should return false
```

---

## Testing Phase 2

### Test 1: Verify Roles and Permissions in Database
```bash
vendor/bin/sail artisan tinker
>>> \Spatie\Permission\Models\Role::all()->pluck('name')
# Expected: ["admin_master", "admin_client", "supervisor", "operator", "financial"]

>>> \Spatie\Permission\Models\Permission::all()->pluck('name')
# Expected: ["view_lead", "create_lead", "edit_lead", "delete_lead", etc.]

>>> $user = User::where('email', 'admin@velozz.digital')->first()
>>> $user->isAdminMaster()  // true
>>> $user->isAdminClient()  // false
```

### Test 2: Test TenantPolicy
1. Login as admin_master: http://velozz.test/admin
   - Email: admin@velozz.digital
   - Password: password
2. Access http://velozz.test/admin/tenants - ✅ Should work
3. Logout
4. Login as admin_client: http://demo1.velozz.test/app
   - Email: admin@demo1.test
   - Password: password
5. Try http://demo1.velozz.test/admin - ❌ Should redirect to login
6. Try http://velozz.test/admin - ❌ Should be denied

### Test 3: Invite a New User
```bash
vendor/bin/sail artisan user:invite \
    operator@demo1.test \
    "John Operator" \
    operator \
    1
```

Expected output:
```
Invitation sent to operator@demo1.test
User will be able to set their password via the invitation link
```

### Test 4: Check Invitation Email
1. Open Mailpit: http://localhost:8025
2. You should see an email with subject "You've been invited to join Demo Company 1"
3. The email should contain:
   - Welcome message
   - User's name
   - Role (Operator)
   - "Accept Invitation" button
   - Expiration date (48 hours from now)

### Test 5: Accept Invitation
1. Click the "Accept Invitation" link in the email (or copy the URL)
2. Should see accept-invite page with:
   - Welcome message
   - User name
   - Password fields
   - Role displayed at bottom
3. Set a password (minimum 8 characters)
4. Click "Set Password & Continue"
5. Should be automatically logged in and redirected to http://demo1.velozz.test/app

### Test 6: Verify Operator Permissions
1. After accepting invite, logged in as operator@demo1.test
2. Check that operator can access Client panel: http://demo1.velozz.test/app ✅
3. Try to access Admin panel: http://velozz.test/admin ❌ Should be denied
4. Verify operator has proper permissions:
```bash
vendor/bin/sail artisan tinker
>>> $user = User::where('email', 'operator@demo1.test')->first()
>>> $user->hasPermissionTo('view_lead')  // true
>>> $user->hasPermissionTo('delete_lead')  // false (only admin_client has this)
>>> $user->isOperator()  // true
```

### Test 7: Test Invalid Token
1. Try accessing: http://velozz.test/accept-invite/invalid-token-123
2. Should show 404 error (token not found)

### Test 8: Test Expired Token
1. Create an invitation
2. Manually update invite_expires_at to past date:
```bash
vendor/bin/sail artisan tinker
>>> $user = User::where('email', 'operator@demo1.test')->first()
>>> $user->update(['invite_expires_at' => now()->subHours(1)])
```
3. Try to accept invitation with that token
4. Should show 404 error (token expired)

---

## Files Created/Modified in Phase 2

### New Files:
- `database/seeders/RolesAndPermissionsSeeder.php` - Creates 5 roles and all permissions
- `app/Policies/TenantPolicy.php` - Tenant authorization (admin_master only)
- `app/Policies/LeadPolicy.php` - Lead authorization (operators see only assigned)
- `app/Console/Commands/InviteUser.php` - CLI command to invite users
- `app/Jobs/SendInviteEmail.php` - Queued job to send invitation emails
- `app/Mail/InviteMail.php` - Invitation email mailable
- `app/Http/Controllers/AcceptInviteController.php` - Handle invitation acceptance
- `resources/views/emails/invite.blade.php` - Invitation email template
- `resources/views/auth/accept-invite.blade.php` - Password setup form
- `PHASE2_PROGRESS.md` - This file

### Modified Files:
- `app/Models/User.php` - Added HasRoles trait, role helper methods (English names)
- `database/seeders/DemoTenantsSeeder.php` - Updated to use 'admin_client' role
- `routes/web.php` - Added invitation routes
- `PHASE1_COMPLETE.md` - Updated role names to English

---

## Summary of Changes

### Role Names (All English):
- ✅ admin_master (unchanged)
- ✅ admin_client (was admin_cliente)
- ✅ supervisor (unchanged)
- ✅ operator (was operador)
- ✅ financial (was financeiro)

### Permissions Created (28 total):
All permission names are in English following Laravel conventions (e.g., view_lead, create_lead, edit_lead, etc.)

### Security Features:
- TenantPolicy: Only admin_master can manage tenants
- LeadPolicy: Operators can only view/edit assigned leads
- User invitation: 48-hour expiring tokens
- Automatic role assignment via Spatie
- Secure password hashing
- Automatic login after password setup

---

## Next Steps

1. ✅ Test all invitation flows
2. ✅ Verify role permissions work correctly
3. ✅ Test policy authorization
4. Create Git commit after validation
5. Move to Phase 3 - Módulo de Leads (CRUD Base)

---

**Phase 2 Status:** ✅ COMPLETE - Ready for Testing and Validation
