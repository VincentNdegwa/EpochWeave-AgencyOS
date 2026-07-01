<?php

namespace Tests\Feature\Engagement;

use App\Enums\EngagementDirection;
use App\Enums\EngagementOutcome;
use App\Enums\EngagementStatus;
use App\Enums\EngagementType;
use App\Models\Account;
use App\Models\Engagement;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EngagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_engagement_can_be_stored(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);

        $response = $this
            ->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post(route('engagements.store'), [
                'account_id' => $account->id,
                'type' => EngagementType::Call->value,
                'direction' => EngagementDirection::Outbound->value,
                'status' => EngagementStatus::Completed->value,
                'subject' => 'Cold call follow-up',
                'content' => 'Discussed website redesign timeline.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('engagements', [
            'workspace_id' => $workspace->id,
            'account_id' => $account->id,
            'type' => EngagementType::Call->value,
            'subject' => 'Cold call follow-up',
        ]);
    }

    public function test_engagement_can_include_related_records(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $proposal = Proposal::factory()->create([
            'workspace_id' => $workspace->id,
            'account_id' => $account->id,
        ]);
        $invoice = Invoice::factory()->create([
            'workspace_id' => $workspace->id,
            'account_id' => $account->id,
        ]);
        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
            'account_id' => $account->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post(route('engagements.store'), [
                'account_id' => $account->id,
                'type' => EngagementType::Email->value,
                'direction' => EngagementDirection::Outbound->value,
                'status' => EngagementStatus::Replied->value,
                'subject' => 'Proposal follow-up',
                'proposal_id' => $proposal->id,
                'invoice_id' => $invoice->id,
                'project_id' => $project->id,
                'outcome' => EngagementOutcome::Positive->value,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('engagements', [
            'proposal_id' => $proposal->id,
            'invoice_id' => $invoice->id,
            'project_id' => $project->id,
            'outcome' => EngagementOutcome::Positive->value,
        ]);
    }

    public function test_engagement_can_be_updated(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $engagement = Engagement::factory()->create([
            'workspace_id' => $workspace->id,
            'account_id' => $account->id,
            'type' => EngagementType::Call->value,
            'subject' => 'Original subject',
        ]);

        $response = $this
            ->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->put(route('engagements.update', $engagement), [
                'type' => EngagementType::Email->value,
                'direction' => EngagementDirection::Inbound->value,
                'status' => EngagementStatus::Replied->value,
                'subject' => 'Updated subject',
                'content' => 'Updated content',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $engagement->refresh();
        $this->assertEquals(EngagementType::Email, $engagement->type);
        $this->assertEquals(EngagementDirection::Inbound, $engagement->direction);
        $this->assertEquals('Updated subject', $engagement->subject);
    }

    public function test_engagement_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);
        $engagement = Engagement::factory()->create([
            'workspace_id' => $workspace->id,
            'account_id' => $account->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->delete(route('engagements.destroy', $engagement));

        $response->assertRedirect();

        $this->assertDatabaseMissing('engagements', [
            'id' => $engagement->id,
        ]);
    }

    public function test_invalid_engagement_type_is_rejected(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create();
        $account = Account::factory()->create(['workspace_id' => $workspace->id]);

        $response = $this
            ->actingAs($user)
            ->withSession(['current_workspace_id' => $workspace->id])
            ->post(route('engagements.store'), [
                'account_id' => $account->id,
                'type' => 'invalid_type',
                'direction' => EngagementDirection::Outbound->value,
                'status' => EngagementStatus::Completed->value,
            ]);

        $response->assertSessionHasErrors('type');
    }
}
