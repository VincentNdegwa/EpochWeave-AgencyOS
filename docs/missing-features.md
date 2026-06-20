# EpochWeave — Missing Features & Implementation Plan

> This document tracks major functionality gaps in the AgencyOS application, prioritized by business impact and effort. Each section includes what exists today, what's missing, and proposed implementation scope.

---

## 1. Real Dashboard

**Priority:** High  
**Effort:** Medium

### Current State
`resources/js/pages/Dashboard.vue` renders placeholder cards — no actual data. The page is the first thing users see after login.

### What's Missing
- KPI cards: open proposals, outstanding invoices, overdue tasks, monthly revenue
- Recent activity feed (proposals signed, invoices sent, tasks completed)
- Revenue chart (monthly pipeline vs. actual)
- Upcoming deadlines (proposals expiring, tasks due, invoices past due)

### Proposed Scope
- New backend endpoint or Inertia page props aggregating counts from Proposals, Invoices, Tasks, and TimeEntries
- Frontend cards with real numbers and trend indicators
- Optional: lightweight charting library for revenue visualization

---

## 2. Time Tracking (Frontend & Routes)

**Priority:** High  
**Effort:** Medium

### Current State
- `time_entries` migration, model, and `TimeEntryService` exist
- `TaskController::show` loads `timeEntries` relationship
- Task show page has a "Time Entries" tab that says *"Time entries integration coming soon"*

### What's Missing
- `TimeEntryController` with routes for CRUD
- Timer UI (start/stop/reset) in the task detail page
- Time entry list with editing, deletion, and billable toggle
- "Convert to invoice" action for unbilled time

### Proposed Scope
- `TimeEntryController` (index, store, update, destroy) scoped to workspace
- `resources/js/pages/tasks/TimeEntriesPanel.vue` integrated into task show tabs
- Timer component with real-time duration display
- Filtered views: by user, by project, by date range

---

## 3. Task Comments & Attachments

**Priority:** High  
**Effort:** Medium

### Current State
- `CommentService`, `AttachmentService`, `StoreCommentRequest`, and `StoreAttachmentRequest` exist
- `TaskController::show` eagerly loads `comments.user` and `attachments.uploader`
- Task show page does **not** render either relationship

### What's Missing
- Comment thread UI (add, edit, delete) in the task detail page
- File upload & download UI for attachments
- Real-time feel without WebSocket dependency (Inertia reload on submit)

### Proposed Scope
- `resources/js/pages/tasks/components/CommentThread.vue`
- `resources/js/pages/tasks/components/AttachmentList.vue`
- Quill-lite or plain textarea for comments
- Drag-and-drop or button upload for attachments with progress

---

## 4. Subtasks

**Priority:** Medium  
**Effort:** Medium

### Current State
- `tasks` table has a self-referential parent/child relationship
- `TaskController::show` loads `children` with status
- Task show page has a "Subtasks" tab with a placeholder message

### What's Missing
- Subtask creation inline or via dialog
- Subtask status update and reordering
- Progress bar showing completion ratio

### Proposed Scope
- Reuse `TaskFormDialog` with a `parent_id` field
- Kanban or checklist-style subtask list within the parent task
- Auto-complete parent when all subtasks are done (optional)

---

## 5. Client Portal (Post-Setup)

**Priority:** High  
**Effort:** High

### Current State
- `PortalSetupController` handles invitation token validation and password creation
- `portal/setup.vue` is the only portal-facing page
- `ClientProfile` model exists

### What's Missing
- **Portal login page** for clients (separate from staff login)
- **Portal dashboard** showing assigned proposals and invoices per client
- **Client-side proposal view & sign** (public show exists but not behind authenticated portal session)
- **Client-side invoice view & payment status**

### Proposed Scope
- New `ClientAuthController` (login/logout using `ClientProfile` credentials)
- `resources/js/pages/portal/Dashboard.vue` — list of proposals + invoices
- Reuse public `show.vue` components but gate them behind `client` guard
- Email notifications when new proposals/invoices are shared

---

## 6. Team & Role Management

**Priority:** Medium  
**Effort:** Medium–High

### Current State
- Laratrust is installed (`roles`, `permissions`, `role_user`, `permission_role`, `permission_user` tables)
- `Permission` and `Role` models exist
- No UI for inviting users, managing roles, or viewing workspace members

### What's Missing
- Invite user flow (email + role assignment)
- Workspace members list with roles and status
- Role-based permission editor (optional advanced)
- Deactivate / remove member

### Proposed Scope
- `WorkspaceMemberController` or extend `WorkspaceController`
- `resources/js/pages/workspace-settings/Team.vue` added to business settings sidebar
- Invitation email with acceptance link
- Enforce role checks in backend where `canUpdateWorkspaceSettings` is currently boolean

---

## 7. Reports & Analytics

**Priority:** Medium  
**Effort:** High

### Current State
No reporting layer exists. No charts library installed.

### What's Missing
- **Financial:** revenue by month, outstanding invoices, paid vs. overdue
- **Time:** billable hours by project / user / date range, unbilled time summary
- **Sales:** proposal conversion rate, average deal size, sales cycle length
- **Project health:** budget burn rate, task completion velocity

### Proposed Scope
- New `ReportController` with filtered aggregation queries
- `resources/js/pages/reports/Index.vue` with date-range picker and report selector
- Install lightweight charting library (e.g., `vue-chartjs` or `recharts` if React migration ever happens)
- Export to CSV/Excel for accountant handoff

---

## 8. In-App Notifications

**Priority:** Medium  
**Effort:** Medium

### Current State
- `WorkspaceSetting::SUBMODULE_NOTIFICATIONS` stores toggle settings for proposal emails
- `NotificationController` exists for workspace settings
- No notification bell or dropdown in the app header

### What's Missing
- Database `notifications` table (Laravel's built-in) or custom in-app notification model
- Notification bell in `AppHeader.vue` with unread count
- Mark-as-read / dismiss actions
- Event-driven notifications: proposal viewed, signed, invoice paid, task assigned

### Proposed Scope
- `UserNotification` model or leverage Laravel's `Illuminate\Notifications\DatabaseNotification`
- `NotificationController` for fetching and marking read
- Event listeners firing on key business events (e.g., `ProposalSigned`, `InvoicePaid`)
- Reuse existing `NotificationSettings` toggles to respect user preferences

---

## 9. Invoice Payments

**Priority:** Medium  
**Effort:** Medium

### Current State
- Invoices can be created, edited, sent, and publicly viewed
- Status changes (`updateStatus`) exist but only swap enum values
- `invoice_items` exist but no `payments` table

### What's Missing
- `payments` table and model (amount, method, date, transaction reference, notes)
- Payment recording UI on invoice show page
- Partial payment support
- Outstanding balance display
- Auto-mark-paid when balance reaches zero

### Proposed Scope
- Migration + `Payment` model (`invoice_id`, `amount`, `method`, `paid_at`, `reference`, `notes`)
- `PaymentController` (store, destroy)
- `resources/js/pages/invoices/components/PaymentPanel.vue`
- Update `InvoiceDocument` to show balance and payment history

---

## 10. Activity / Audit Log

**Priority:** Low–Medium  
**Effort:** Medium

### Current State
No centralized audit trail.

### What's Missing
- History of who changed what and when (proposal edits, invoice status changes, task assignments)
- `ProposalView` model exists but no UI to see *when* a client viewed a proposal

### Proposed Scope
- `activities` table (polymorphic `subject`, `causer`, `description`, `properties` JSON)
- `ActivityService` recording key events
- Optional activity timeline on proposal, invoice, and task show pages

---

## 11. Minor / Polish Gaps

| Feature | Gap |
|---------|-----|
| Product show page | Exists but may be thin — no sales history or linked proposals |
| Bulk actions on list pages | Most datatables lack multi-select bulk delete / status update |
| Proposal email sending | `send` endpoint exists but no email template preview or custom message |
| Invoice email sending | Same as above — no custom subject/body before sending |
| File manager / central asset library | Attachments are task-scoped; no reusable asset gallery for proposals |
| Recurring invoices | No scheduler or template for repeating monthly retainer invoices |
| Multi-currency | `useCurrency` exists but only one base currency per workspace |
| Tax handling | Invoice line items have no tax rate / tax amount fields |

---

## Suggested Implementation Order

1. **Dashboard** — immediate user value, low risk
2. **Task Comments & Attachments** — high daily usage, backend already ready
3. **Time Tracking UI** — backend ready, unlocks billing workflow
4. **Invoice Payments** — closes the core financial loop
5. **Subtasks** — quick win after comments pattern is established
6. **In-App Notifications** — improves team coordination
7. **Client Portal** — highest effort but unlocks client self-service
8. **Team & Roles** — scales the product to multi-user agencies
9. **Reports** — requires data from all preceding features
10. **Activity Log** — nice-to-have polish

---

*Last updated: 2026-06-19*
