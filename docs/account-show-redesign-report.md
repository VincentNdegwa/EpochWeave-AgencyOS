# Account Show Page Redesign Report

## Current State

### Controller (`app/Http/Controllers/AccountController.php`)

The `show` method loads the account via `AccountService::getAccountById()` and then lazy-loads a subset of relationships:

```php
$account->load(['contacts', 'addresses', 'socialProfiles', 'industry', 'leadSource', 'companySize']);
```

`AccountService::getAccountById()` already loads `contacts`, so the controller is effectively performing a second round of relationship loading. This causes unnecessary N+1 risk and extra queries.

### Current UI (`resources/js/pages/account/show.vue`)

The page currently displays:

1. **Header** — company name, lifetime value, status badge, actions dropdown.
2. **Stat Bar** — lifetime value, website, contacts count, status label.
3. **Contacts Section** — full data table with add/edit actions.
4. **Addresses Section** — cards with type, primary badge, address lines, edit/delete actions.
5. **Social Profiles Section** — platform links with verified badges.
6. **Activity Timeline** — last 20 activity records.

## What Is Missing

### Account scalar fields not displayed

The `Account` model has these fields, most of which are never shown on the show page:

- `phone`
- `description`
- `founded_at`
- `annual_revenue`
- `employee_count`
- `token` (internal, probably not useful for UI)
- `industry` (relationship)
- `leadSource` (relationship)
- `companySize` (relationship)
- `created_at` / `updated_at` (metadata, optional)

### Relationships not loaded

The controller does **not** eager-load:

- `proposals` (with `proposalStatus`)
- `projects` (with `status`)
- `invoices` (with `invoiceStatus`)
- `engagements` (with `user`)
- `customFieldValues` (with `definition`)
- `portalInvitations` (with `accountContact`)

### Relationship data not displayed

Even the already-loaded relationships have unused fields:

- **Contacts**: `job_title`, `department`, `preferred_contact_method`, `date_of_birth`, `notes`, `receives_billing`, `is_verified`, `portalInvitation` status.
- **Social Profiles**: `handle`, `followers_count` are not shown.
- **Addresses**: `label`, `latitude`, `longitude` are not shown.

## Recommended Additions

### 1. Consolidated Account Overview Card

Create a top section (above or beside the stat bar) that surfaces the key account attributes:

- **Phone** — clickable `tel:` link.
- **Website** — external link (already in stat bar; move here or duplicate).
- **Industry** — badge/text.
- **Company Size** — badge/text.
- **Lead Source** — badge/text.
- **Founded Date** — formatted date.
- **Annual Revenue** — formatted currency.
- **Employees** — plain number.
- **Description** — collapsible/long text.

### 2. Financial Summary Cards

Replace the current stat bar with a more meaningful set of KPI cards:

- Lifetime Value (existing)
- Total Proposals (count + value)
- Total Invoices (count + value)
- Outstanding Invoice Balance
- Active Projects Count
- Open Engagements Count

### 3. Proposals Section

List recent proposals with columns:

- Proposal number
- Title
- Status badge
- Grand total
- Valid until
- Link to proposal detail

### 4. Projects Section

List projects with:

- Name
- Status badge
- Start / due dates
- Progress (tasks completed / total)
- Hours logged / budgeted

### 5. Invoices Section

List invoices with:

- Invoice number
- Status badge
- Grand total
- Amount paid / balance
- Issue / due dates

### 6. Engagements Section

Compact timeline/feed of engagements:

- Type + direction icons
- Subject
- Status
- Scheduled / completed dates
- Assigned user

### 7. Custom Fields Section

Display `customFieldValues` grouped by `definition` name:

- Label from `CustomFieldDefinition`
- Resolved value via `value` accessor

### 8. Richer Contact Cards

Enhance contacts to show:

- Primary / Billing / Verified badges
- Job title
- Email + phone
- Link to client portal invitation status

## Recommended Removals / Redesigns

1. **Remove the thin stat bar** or redesign it as KPI cards. The current bar duplicates the header (lifetime value, status) and shows low-density information.
2. **Remove redundant status display** in the header if the new overview card shows it clearly.
3. **Collapse empty sections** by default (e.g., if no proposals, show a compact empty state instead of a full section).
4. **Remove the hardcoded `router.delete('/addresses/${addressId}')`** if a Wayfinder action is available; otherwise keep for now.

## Implementation Plan

1. **Backend eager loading**
   - Update `AccountService::getAccountById()` to eager-load all relationships needed for the show page in one query.
   - Remove the separate `load()` call in `AccountController::show()`.

2. **TypeScript types**
   - Extend `Account` interface to include optional `proposals`, `projects`, `invoices`, `engagements`, `customFieldValues`, `portalInvitations`.
   - Add a minimal `Engagement` type if one does not exist.

3. **Vue page redesign**
   - Split the single-column layout into a two-column layout (overview + related records on the left, activity + meta on the right, or similar responsive grid).
   - Add new sections for proposals, projects, invoices, engagements, custom fields.
   - Improve the overview card and KPI cards.
   - Keep contacts, addresses, and social profiles but improve their density and empty states.

4. **Testing**
   - Verify the show page loads without N+1 queries.
   - Run the full test suite.
