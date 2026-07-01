# CRM Data Model Enhancement Plan

## Overview

This plan implements the missing CRM data architecture for EpochWeave. The goal is to move from a minimal account/contact model to a full CRM-grade data layer using **workspace-scoped lookup tables**, **polymorphic relationships**, and an **EAV custom fields system**.

## Architectural Principles

1. **Workspace-scoped lookup tables** — Not global, not hardcoded enums. Every workspace owns its own taxonomy.
2. **Polymorphic relationships** — One model serves multiple parents (Address belongs to Account, Contact, Invoice).
3. **Seeded defaults** — When a workspace is created, sensible defaults are injected. Users can edit, add, or delete later.
4. **No free text for categorization** — Industry, lead source, and company size are dropdowns, not text fields.
5. **JSON only for truly unstructured data** — Everything else gets a proper table.

---

## Phase 1: Addresses

### Why
Every CRM needs address management. Accounts have HQ, billing, and shipping addresses. Contacts have personal addresses. Invoices need a "billed to" address. A flat `address` column on `accounts` breaks immediately.

### Schema
```php
// addresses table
table->id();
table->morphs('addressable');           // account, account_contact, invoice
$table->string('type');                  // 'billing' | 'shipping' | 'primary' | 'office'
table->string('label')->nullable();      // user-defined name, e.g. "Nairobi HQ"
table->string('street_1');
$table->string('street_2')->nullable();
$table->string('city');
$table->string('state')->nullable();
$table->string('postal_code')->nullable();
$table->string('country');
$table->decimal('latitude', 10, 8)->nullable();
$table->decimal('longitude', 11, 8)->nullable();
$table->boolean('is_primary')->default(false);
$table->timestamps();

table->index(['addressable_type', 'addressable_id', 'type']);
```

### Model
- `Address` model with `addressable()` morphTo relationship.
- `Account` gets `morphMany(Address::class, 'addressable')`.
- `AccountContact` gets the same.
- Helper methods: `primaryAddress()`, `billingAddresses()`, `shippingAddresses()`.

### Seeding
**No seeded defaults.** Addresses are user-created per entity.

### UI Impact
- Address section in `AccountFormDialog.vue` (add/remove/edit addresses).
- Address selector in `InvoiceFormDialog.vue` and `ProposalFormDialog.vue`.
- Address display on `AccountShow.vue`.

### Deliverables
| File | Purpose |
|---|---|
| `database/migrations/…_create_addresses_table.php` | Schema |
| `app/Models/Address.php` | Model + casts |
| `database/factories/AddressFactory.php` | Factory |
| Update `app/Models/Account.php` | `addresses()` morphMany |
| Update `app/Models/AccountContact.php` | `addresses()` morphMany |
| Update `app/Models/Invoice.php` | `addresses()` morphMany |
| `app/Http/Controllers/AddressController.php` | CRUD |
| `routes/web.php` | Resource routes for addresses |
| UI components | Inline address management on account/contact forms |
| Tests | `tests/Feature/AddressTest.php` |

---

## Phase 2: Industries (Workspace-Scoped Lookup)

### Why
Segmentation, ideal customer profile analysis, and regional flexibility. "Tech" vs "AgriTech" vs "Fintech" varies by market.

### Schema
```php
// industries table
table->id();
table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
$table->string('name');                  // e.g. "Software & IT"
$table->string('color')->nullable();     // UI badge color
$table->text('description')->nullable();
$table->unsignedTinyInteger('sort_order')->default(0);
$table->boolean('is_default')->default(false);  // pre-selected in dropdowns
$table->timestamps();

table->unique(['workspace_id', 'name']);
```

```php
// accounts.industry_id (nullable FK)
```

### Model
- `Industry` belongsTo `Workspace`, hasMany `Account`.
- `Account` belongsTo `Industry` (nullable).

### Seeding (on Workspace Creation)
A `WorkspaceIndustrySeeder` injects ~15 default industries:
- Software & IT
- Financial Services
- Healthcare & Pharmaceuticals
- Manufacturing
- Retail & E-commerce
- Education
- Real Estate
- Hospitality & Tourism
- Agriculture
- Construction
- Energy & Utilities
- Media & Entertainment
- Government & Public Sector
- Non-profit
- Professional Services

### UI Impact
- Dropdown in `AccountFormDialog.vue`.
- Filter in account index.
- Badge on `AccountShow.vue`.
- Settings page: `/workspace/settings/industries` (table + add/edit/delete).

### Deliverables
| File | Purpose |
|---|---|
| Migration | `industries` table + `accounts.industry_id` |
| `app/Models/Industry.php` | Model |
| `database/factories/IndustryFactory.php` | Factory |
| Update `app/Models/Account.php` | `industry()` relationship + fillable |
| Update `app/Services/WorkspaceService.php` | Call seeder on workspace creation |
| `app/Actions/SeedWorkspaceDefaults.php` | Encapsulated seeding action |
| `app/Http/Controllers/IndustryController.php` | CRUD |
| Settings UI | `/workspace-settings/Industry.vue` |
| Tests | `tests/Feature/IndustryTest.php` |

---

## Phase 3: Lead Sources (Workspace-Scoped Lookup)

### Why
Attribution is the #1 question in sales. "Where did this deal come from?" Without a structured taxonomy, reporting is guesswork.

### Schema
```php
// lead_sources table
table->id();
table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
$table->string('name');                  // e.g. "Google Ads"
$table->string('color')->nullable();
$table->text('description')->nullable();
$table->string('category')->nullable();  // 'paid' | 'organic' | 'referral' | 'event' | 'outbound'
$table->unsignedTinyInteger('sort_order')->default(0);
$table->boolean('is_default')->default(false);
$table->timestamps();

table->unique(['workspace_id', 'name']);
```

```php
// accounts.lead_source_id (nullable FK)
```

### Model
- `LeadSource` belongsTo `Workspace`, hasMany `Account`.
- `Account` belongsTo `LeadSource` (nullable).

### Seeding (on Workspace Creation)
Default lead sources:
- Organic Search
- Google Ads
- Social Media (Organic)
- Social Media (Paid)
- LinkedIn Outreach
- Email Campaign
- Referral — Customer
- Referral — Partner
- Webinar / Event
- Cold Call
- Trade Show
- Content Download
- Direct Traffic
- Other

### UI Impact
- Dropdown in `AccountFormDialog.vue`.
- Filter and column in account index.
- Settings page: `/workspace/settings/lead-sources`.

### Deliverables
| File | Purpose |
|---|---|
| Migration | `lead_sources` table + `accounts.lead_source_id` |
| `app/Models/LeadSource.php` | Model |
| `database/factories/LeadSourceFactory.php` | Factory |
| Update `app/Models/Account.php` | `leadSource()` relationship + fillable |
| Update `WorkspaceService` / `SeedWorkspaceDefaults` | Add lead sources to seeding |
| `app/Http/Controllers/LeadSourceController.php` | CRUD |
| Settings UI | `/workspace-settings/LeadSource.vue` |
| Tests | `tests/Feature/LeadSourceTest.php` |

---

## Phase 4: Social Profiles (Polymorphic)

### Why
Sales reps research prospects on LinkedIn and Twitter before calls. Storing these centrally prevents scattered browser bookmarks and manual lookups.

### Schema
```php
// social_profiles table
table->id();
table->morphs('profileable');              // account, account_contact
$table->string('platform');                // 'linkedin' | 'twitter' | 'facebook' | 'instagram' | 'github' | 'youtube' | 'tiktok' | 'other'
$table->string('url');
$table->string('handle')->nullable();      // @username
$table->unsignedBigInteger('followers_count')->nullable();
$table->boolean('is_verified')->default(false);
$table->timestamps();

table->index(['profileable_type', 'profileable_id', 'platform']);
```

### Model
- `SocialProfile` with `profileable()` morphTo.
- `Account` and `AccountContact` get `socialProfiles()` morphMany.

### Seeding
**None.** User-created per entity.

### UI Impact
- Inline add/edit on `AccountShow.vue` and contact forms.
- Icon links in account and contact cards.

### Deliverables
| File | Purpose |
|---|---|
| Migration | `social_profiles` table |
| `app/Models/SocialProfile.php` | Model |
| Update `app/Models/Account.php` | `socialProfiles()` |
| Update `app/Models/AccountContact.php` | `socialProfiles()` |
| `app/Http/Controllers/SocialProfileController.php` | CRUD |
| UI components | Inline social profile manager |
| Tests | `tests/Feature/SocialProfileTest.php` |

---

## Phase 5: Company Sizes (Workspace-Scoped Lookup)

### Why
Pricing tiers, qualification rules, and ICP scoring. "Enterprise" is 500+ in some markets and 10,000+ in others.

### Schema
```php
// company_sizes table
table->id();
table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
$table->string('label');                   // "1-10", "11-50", "51-200"
$table->unsignedInteger('min_employees')->nullable();
$table->unsignedInteger('max_employees')->nullable();
$table->unsignedTinyInteger('sort_order')->default(0);
$table->boolean('is_default')->default(false);
$table->timestamps();

table->unique(['workspace_id', 'label']);
```

```php
// accounts.company_size_id (nullable FK)
```

### Alternative: `employees_count` integer column
If you prefer simplicity, a nullable `integer employees_count` on `accounts` allows dynamic bucketing in reports. The lookup table gives cleaner UI dropdowns but is slightly more rigid. **Recommendation: use the lookup table.** It aligns with the workspace-scoped pattern and prevents users from typing random numbers.

### Seeding (on Workspace Creation)
- 1-10
- 11-50
- 51-200
- 201-500
- 501-1,000
- 1,001-5,000
- 5,001-10,000
- 10,001+

### UI Impact
- Dropdown in `AccountFormDialog.vue`.
- Filter in account index.
- Settings page: `/workspace/settings/company-sizes`.

### Deliverables
| File | Purpose |
|---|---|
| Migration | `company_sizes` table + `accounts.company_size_id` |
| `app/Models/CompanySize.php` | Model |
| Update `app/Models/Account.php` | `companySize()` relationship + fillable |
| Update `WorkspaceService` / `SeedWorkspaceDefaults` | Add company sizes to seeding |
| `app/Http/Controllers/CompanySizeController.php` | CRUD |
| Settings UI | `/workspace-settings/CompanySize.vue` |
| Tests | `tests/Feature/CompanySizeTest.php` |

---

## Phase 6: Account Contact Enhancements

### Schema Additions to `account_contacts`
```php
$table->string('mobile')->nullable();     // separate from desk phone
$table->string('department')->nullable();  // Marketing, Engineering, Finance
$table->string('linkedin_url')->nullable();
$table->string('preferred_contact_method')->nullable();  // 'email' | 'phone' | 'linkedin'
$table->text('notes')->nullable();
$table->date('birthday')->nullable();
$table->string('language')->nullable();    // ISO 639-1, e.g. 'en', 'sw'
```

### UI Impact
- Additional fields in `ContactFormDialog.vue`.
- Display on `AccountShow.vue` contact cards.

### Deliverables
| File | Purpose |
|---|---|
| Migration | Add columns to `account_contacts` |
| Update `app/Models/AccountContact.php` | Fillable + casts |
| Update `database/factories/AccountContactFactory.php` | New fields |
| Update `ContactFormDialog.vue` | New form fields |
| Tests | Update `AccountContactTest.php` |

---

## Phase 7: Account Enhancements (Simple Columns)

### Schema Additions to `accounts`
```php
$table->string('phone')->nullable();           // main company line
$table->string('tax_id')->nullable();            // VAT / EIN / KRA PIN
$table->string('currency')->default('USD');      // ISO 4217
$table->string('timezone')->nullable();          // e.g. 'Africa/Nairobi'
$table->text('description')->nullable();         // free-form company bio
$table->year('founded_year')->nullable();
$table->foreignId('parent_company_id')->nullable()->constrained('accounts')->nullOnDelete();  // account hierarchy
$table->string('logo')->nullable();              // path to uploaded logo
```

### UI Impact
- Additional fields in `AccountFormDialog.vue`.
- Parent company dropdown (recursive, filtered to same workspace).
- Logo upload in account form.
- Description display on `AccountShow.vue`.

### Deliverables
| File | Purpose |
|---|---|
| Migration | Add columns to `accounts` |
| Update `app/Models/Account.php` | Fillable + casts + `parentCompany()` / `subCompanies()` |
| Update `database/factories/AccountFactory.php` | New fields |
| Update `AccountFormDialog.vue` | New fields + logo upload |
| Tests | Update `AccountTest.php` |

---

## Phase 8: Custom Fields (EAV — Entity-Attribute-Value)

### Why
This is the unlock. No matter how many lookup tables you build, users will ask for fields you didn't anticipate: "Contract Renewal Month", "GDPR Consent Date", "Partner Tier", "NPS Score". Custom fields turn EpochWeave from a fixed app into a CRM platform.

### Schema
```php
// custom_fields table
table->id();
table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
$table->string('name');                      // "Contract Renewal Month"
$table->string('type');                      // 'text' | 'number' | 'date' | 'datetime' | 'select' | 'multiselect' | 'boolean' | 'textarea' | 'url' | 'email'
$table->json('options')->nullable();         // ["January", "February", …] for select/multiselect
$table->string('model_type');                // 'Account' | 'AccountContact' | 'Proposal' | 'Invoice' | 'Project'
$table->boolean('required')->default(false);
$table->unsignedTinyInteger('sort_order')->default(0);
$table->timestamps();

table->index(['workspace_id', 'model_type', 'sort_order']);
```

```php
// custom_field_values table
table->id();
table->foreignId('custom_field_id')->constrained()->cascadeOnDelete();
table->morphs('model');                      // the actual Account, Proposal, etc.
$table->text('value');                       // stringified, cast based on custom_fields.type
$table->timestamps();

table->unique(['custom_field_id', 'model_type', 'model_id']);
```

### Model
- `CustomField` belongsTo `Workspace`.
- `CustomFieldValue` belongsTo `CustomField`, morphs to the target model.
- Target models (`Account`, `Proposal`, etc.) get a `morphMany(CustomFieldValue::class, 'model')` relationship.
- A `HasCustomFields` trait can be added to any model to provide:
  - `customFields()` — the field definitions for this model type.
  - `customFieldValues()` — the stored values.
  - `getCustomFieldValue(string $name)` — convenience accessor.
  - `setCustomFieldValue(string $name, mixed $value)` — convenience mutator.

### Seeding
**None.** Custom fields are user-created per workspace via settings.

### Validation
Validation rules are generated dynamically based on `custom_fields.type`:
- `text` → `string|max:255`
- `number` → `numeric`
- `date` → `date`
- `datetime` → `date`
- `select` → `in:option1,option2`
- `multiselect` → `array|in:option1,option2`
- `boolean` → `boolean`
- `textarea` → `string|max:5000`
- `url` → `url`
- `email` → `email`

### UI Impact
- **Settings page**: `/workspace/settings/custom-fields`. Table showing fields per model type. Add/edit/delete with live preview of field type.
- **Forms**: `AccountFormDialog.vue`, `ContactFormDialog.vue`, `ProposalFormDialog.vue` dynamically render custom field inputs based on the workspace's configured fields.
- **Show pages**: Display custom fields in a dedicated section.
- **Index / Table views**: Optionally show custom fields as columns.

### Implementation Complexity
| Aspect | Effort | Notes |
|---|---|---|
| Backend models + migrations | Low | Straightforward EAV |
| Dynamic validation | Medium | Build a `CustomFieldValidator` service |
| Dynamic form rendering (Vue) | Medium | Map `type` → component (TextInput, Select, DatePicker, etc.) |
| Index table columns | High | Requires flexible column definitions |
| Reporting / filtering | High | EAV filtering is notoriously slow; consider materialized views or JSON columns for heavily filtered fields |

### Performance Note
For heavy filtering ("show all accounts where Partner Tier = Gold"), EAV joins are slow. Two mitigation strategies:
1. **JSON column on the parent table** — Store a `custom_attributes` JSONB column on `accounts` that mirrors the EAV values. Query the JSON column for filters, use the EAV table for strict data integrity.
2. **Elasticsearch / Meilisearch** — If search becomes a bottleneck, index custom fields.

For now, start with pure EAV. Optimize only when performance is proven to be a problem.

### Deliverables
| File | Purpose |
|---|---|
| Migrations | `custom_fields` + `custom_field_values` |
| `app/Models/CustomField.php` | Model |
| `app/Models/CustomFieldValue.php` | Model |
| `app/Traits/HasCustomFields.php` | Reusable trait for any model |
| `app/Services/CustomFieldValidator.php` | Dynamic validation rule generator |
| `app/Http/Controllers/CustomFieldController.php` | CRUD for field definitions |
| `app/Http/Controllers/CustomFieldValueController.php` | CRUD for values (or merge into parent controllers) |
| Settings UI | `/workspace-settings/CustomFields.vue` |
| Dynamic form components | `CustomFieldInput.vue`, `CustomFieldRenderer.vue` |
| Tests | `tests/Feature/CustomFieldTest.php` |

---

## Workspace Creation Seeding Strategy

### Current State
Workspace creation happens in `app/Services/WorkspaceService.php` or via an action. We need a central place to seed defaults.

### New Action: `SeedWorkspaceDefaults`
```php
class SeedWorkspaceDefaults
{
    public function execute(Workspace $workspace): void
    {
        $this->seedIndustries($workspace);
        $this->seedLeadSources($workspace);
        $this->seedCompanySizes($workspace);
        // Future: seed proposal statuses, invoice statuses, task statuses if not already there
    }
}
```

### Seed Data Files
```php
// database/seeders/data/default_industries.php
return [
    ['name' => 'Software & IT', 'color' => '#3b82f6'],
    ['name' => 'Financial Services', 'color' => '#10b981'],
    // ...
];

// database/seeders/data/default_lead_sources.php
return [
    ['name' => 'Organic Search', 'color' => '#6366f1', 'category' => 'organic'],
    ['name' => 'Google Ads', 'color' => '#ef4444', 'category' => 'paid'],
    // ...
];

// database/seeders/data/default_company_sizes.php
return [
    ['label' => '1-10', 'min_employees' => 1, 'max_employees' => 10],
    ['label' => '11-50', 'min_employees' => 11, 'max_employees' => 50],
    // ...
];
```

### Hook into Workspace Creation
Update `app/Actions/CreateWorkspace.php` or `WorkspaceService::create()` to call `SeedWorkspaceDefaults::execute($workspace)` after the workspace record is persisted.

---

## Settings Page Architecture

### New Settings Pages
| Route | Component | Data Managed |
|---|---|---|
| `/workspace/settings/industries` | `workspace-settings/Industry.vue` | Industries |
| `/workspace/settings/lead-sources` | `workspace-settings/LeadSource.vue` | Lead Sources |
| `/workspace/settings/company-sizes` | `workspace-settings/CompanySize.vue` | Company Sizes |
| `/workspace/settings/custom-fields` | `workspace-settings/CustomField.vue` | Custom Fields |

### Pattern
Each settings page follows the same pattern as existing workspace settings (`General.vue`, `Automation.vue`, etc.):
- Data table with sortable rows.
- Inline add/edit via a small form row or dialog.
- Color picker for industries and lead sources.
- Delete with confirmation.

---

## Phased Implementation Order

| Phase | Feature | Effort | Value |
|---|---|---|---|
| 1 | **Addresses** | Medium | High — unblock invoicing, shipping, portal |
| 2 | **Industries** | Low | Medium — segmentation & reporting |
| 3 | **Lead Sources** | Low | High — attribution is core CRM value |
| 4 | **Social Profiles** | Low | Low — nice-to-have, quick win |
| 5 | **Company Sizes** | Low | Medium — qualification & pricing |
| 6 | **Contact Enhancements** | Low | Medium — richer contact data |
| 7 | **Account Enhancements** | Low | Medium — basic fields (phone, tax, currency, description) |
| 8 | **Custom Fields** | High | Very High — platform unlock |

**Recommended sprint order:**
1. **Sprint 1**: Phases 1–3 (Addresses, Industries, Lead Sources). These are independent, high-value, and establish the workspace-scoped lookup pattern.
2. **Sprint 2**: Phases 4–7 (Social Profiles, Company Sizes, Contact & Account enhancements). These are additive columns and simple tables.
3. **Sprint 3**: Phase 8 (Custom Fields). This is the capstone. By now, the patterns (lookup tables, polymorphic, seeding, settings pages) are well established.

---

## Testing Strategy

Each phase gets a feature test:

```php
// Pattern per phase
test_it_can_list_workspace_scoped_records();
test_it_can_create_a_record();
test_it_can_update_a_record();
test_it_can_delete_a_record();
test_it_validates_required_fields();
test_it_prevents_duplicate_names_in_same_workspace();
test_it_seeds_defaults_on_workspace_creation();
test_it_scopes_records_to_current_workspace();
```

For custom fields:
```php
test_it_can_store_text_custom_field_value();
test_it_can_store_select_custom_field_value();
test_it_validates_select_against_defined_options();
test_it_rejects_custom_fields_for_wrong_model_type();
test_custom_fields_are_included_in_model_serialization();
```

---

## Migration Strategy

### For Existing Workspaces
When these migrations run on an existing database, existing accounts will have `null` for `industry_id`, `lead_source_id`, and `company_size_id`. This is fine — they become "Uncategorized" in the UI.

### Optional: Backfill Script
If you want to populate `lead_source_id` based on existing activity data (e.g., if an account came from a proposal sent via email, infer "Email Campaign"), write a one-time artisan command. Don't block the schema work on this.

---

## Summary of New Files

| Phase | Files |
|---|---|
| 1 (Addresses) | `Address.php`, `AddressFactory.php`, `AddressController.php`, `…create_addresses_table.php`, inline UI, tests |
| 2 (Industries) | `Industry.php`, `IndustryFactory.php`, `IndustryController.php`, `…create_industries_table.php`, `SeedWorkspaceDefaults.php`, settings UI, tests |
| 3 (Lead Sources) | `LeadSource.php`, `LeadSourceFactory.php`, `LeadSourceController.php`, `…create_lead_sources_table.php`, settings UI, tests |
| 4 (Social Profiles) | `SocialProfile.php`, `SocialProfileController.php`, `…create_social_profiles_table.php`, inline UI, tests |
| 5 (Company Sizes) | `CompanySize.php`, `CompanySizeFactory.php`, `CompanySizeController.php`, `…create_company_sizes_table.php`, settings UI, tests |
| 6 (Contacts) | Migration for `account_contacts` columns, updated factory + form, tests |
| 7 (Accounts) | Migration for `accounts` columns, updated factory + form, tests |
| 8 (Custom Fields) | `CustomField.php`, `CustomFieldValue.php`, `HasCustomFields.php`, `CustomFieldController.php`, `CustomFieldValidator.php`, 2 migrations, settings UI, dynamic form components, tests |

---

## Open Questions

1. **Should `company_size` be a lookup table or just `employees_count` integer?**
   - *Recommendation*: Lookup table for UI consistency, but expose `employees_count` on accounts as an override or future addition.

2. **Should addresses support validation per country?**
   - *Recommendation*: Start simple. Add country-specific postal code validation later if needed.

3. **Should custom fields support conditional visibility?**
   - *Recommendation*: Not in v1. Add a `conditions` JSON column to `custom_fields` in v2 (e.g., "show this field only if Industry = Software").

4. **Should we import a global industry taxonomy (NAICS, ISIC)?**
   - *Recommendation*: No. Workspace-scoped defaults are simpler and more flexible. Users can align to local standards.

5. **Should parent_company_id enforce same workspace?**
   - *Yes.* Add a validation rule or foreign key constraint that prevents cross-workspace hierarchies.

---

## Next Step

Review this plan. If approved, I will begin with **Phase 1 (Addresses)** and create the migration, model, controller, and UI integration.
