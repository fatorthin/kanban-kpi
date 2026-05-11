<?php

namespace Tests\Feature;

use App\Livewire\ActivityNotifications;
use App\Livewire\Auth\Login;
use App\Livewire\Kanban\Board;
use App\Livewire\Kanban\TaskSlideOver;
use App\Models\ActivityReadStatus;
use App\Models\Client;
use App\Models\Division;
use App\Models\Task;
use App\Models\TaskMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApplicationFlowTest extends TestCase
{
    use RefreshDatabase;

    private Division $division;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('director', 'web');
        Role::findOrCreate('manager', 'web');
        Role::findOrCreate('staff', 'web');

        $this->division = Division::create(['name' => 'Tax']);
    }

    public function test_guest_is_redirected_to_login_and_login_page_is_accessible(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Sign in to your account', false);
    }

    public function test_user_can_login_through_livewire_flow(): void
    {
        $user = $this->createUser('staff', [
            'email' => 'staff@example.test',
            'password' => bcrypt('password'),
        ]);

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('authenticate')
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_staff_route_access_matches_application_flow(): void
    {
        $staff = $this->createUser('staff');

        $this->actingAs($staff);

        $this->get(route('dashboard'))->assertOk();
        $this->get(route('kanban'))->assertOk();
        $this->get(route('recurring-tasks'))->assertOk();
        $this->get(route('kpi-reports'))->assertOk();

        $this->get(route('task-library'))->assertForbidden();
        $this->get(route('clients'))->assertForbidden();
        $this->get(route('staff'))->assertForbidden();
        $this->get(route('activity-logs'))->assertForbidden();
    }

    public function test_manager_route_access_matches_application_flow(): void
    {
        $manager = $this->createUser('manager');

        $this->actingAs($manager);

        $this->get(route('dashboard'))->assertOk();
        $this->get(route('kanban'))->assertOk();
        $this->get(route('recurring-tasks'))->assertOk();
        $this->get(route('kpi-reports'))->assertOk();
        $this->get(route('task-library'))->assertOk();
        $this->get(route('clients'))->assertOk();
        $this->get(route('staff'))->assertOk();

        $this->get(route('activity-logs'))->assertForbidden();
    }

    public function test_director_can_access_all_application_pages(): void
    {
        $director = $this->createUser('director');

        $this->actingAs($director);

        $this->get(route('dashboard'))->assertOk();
        $this->get(route('kanban'))->assertOk();
        $this->get(route('recurring-tasks'))->assertOk();
        $this->get(route('task-library'))->assertOk();
        $this->get(route('clients'))->assertOk();
        $this->get(route('staff'))->assertOk();
        $this->get(route('kpi-reports'))->assertOk();
        $this->get(route('activity-logs'))->assertOk();
    }

    public function test_kanban_search_filters_tasks_using_real_board_flow(): void
    {
        $manager = $this->createUser('manager', ['name' => 'Manager Tax']);
        $staff = $this->createUser('staff', ['name' => 'Alice Tax']);
        $otherStaff = $this->createUser('staff', ['name' => 'Bob Tax']);
        $client = Client::create(['name' => 'PT Maju Jaya', 'code' => 'MJ', 'grade' => 'A']);

        Task::create([
            'title' => 'Audit Pajak Tahunan',
            'description' => 'Perlu follow up dokumen',
            'task_type' => 'Client',
            'status' => 'New',
            'difficulty_points' => 30,
            'deadline' => now()->addDays(3),
            'client_id' => $client->id,
            'pic_id' => $staff->id,
            'manager_id' => $manager->id,
        ]);

        Task::create([
            'title' => 'Rekonsiliasi Internal',
            'description' => 'Task internal tim',
            'task_type' => 'Internal',
            'status' => 'New',
            'difficulty_points' => 10,
            'deadline' => now()->addDays(2),
            'pic_id' => $otherStaff->id,
            'manager_id' => $manager->id,
        ]);

        Livewire::actingAs($manager)
            ->test(Board::class)
            ->set('filterSearch', 'Maju Jaya')
            ->assertSee('Reset Search')
            ->assertSee('Audit Pajak Tahunan')
            ->assertDontSee('Rekonsiliasi Internal')
            ->set('filterSearch', '')
            ->assertDontSee('Reset Search')
            ->assertSee('Audit Pajak Tahunan')
            ->assertSee('Rekonsiliasi Internal');
    }

    public function test_kanban_board_uses_polling_for_automatic_updates(): void
    {
        $manager = $this->createUser('manager');

        $this->actingAs($manager)
            ->get(route('kanban'))
            ->assertOk()
            ->assertSee('wire:poll.5s.visible', false);
    }

    public function test_revision_flow_counts_revisions_and_allows_staff_rework(): void
    {
        $manager = $this->createUser('manager');
        $staff = $this->createUser('staff');
        $task = Task::create([
            'title' => 'Review SPT',
            'description' => 'Task review',
            'task_type' => 'Client',
            'status' => 'Review',
            'difficulty_points' => 20,
            'deadline' => now()->addDays(1),
            'pic_id' => $staff->id,
            'manager_id' => $manager->id,
        ]);

        Livewire::actingAs($manager)
            ->test(Board::class)
            ->call('moveTask', $task->id, 'Revision');

        $task->refresh();
        $this->assertSame('Revision', $task->status);
        $this->assertSame(1, $task->revision_count);

        Livewire::actingAs($staff)
            ->test(Board::class)
            ->call('moveTask', $task->id, 'In_Progress');

        $task->refresh();
        $this->assertSame('In_Progress', $task->status);
        $this->assertSame(1, $task->revision_count);
    }

    public function test_task_message_creates_notification_that_can_be_marked_read(): void
    {
        $manager = $this->createUser('manager', ['name' => 'Manager Tax']);
        $staff = $this->createUser('staff', ['name' => 'Staff Tax']);
        $task = Task::create([
            'title' => 'Chat Task Pajak',
            'description' => 'Task dengan diskusi',
            'task_type' => 'Client',
            'status' => 'In_Progress',
            'difficulty_points' => 15,
            'deadline' => now()->addDays(2),
            'pic_id' => $staff->id,
            'manager_id' => $manager->id,
        ]);

        Activity::query()->delete();

        Livewire::actingAs($manager)
            ->test(TaskSlideOver::class)
            ->call('open', $task->id)
            ->set('newMessage', 'Mohon update progress terbaru.')
            ->call('sendMessage');

        $message = TaskMessage::query()->where('task_id', $task->id)->first();

        $this->assertNotNull($message);

        $activity = Activity::query()
            ->where('description', 'Pesan baru pada tugas: "' . $task->title . '"')
            ->first();

        $this->assertNotNull($activity);

        Livewire::actingAs($staff)
            ->test(ActivityNotifications::class)
            ->assertSee('Pesan baru pada tugas: "' . $task->title . '"')
            ->assertSee('1 Baru')
            ->call('markAsRead', $activity->id)
            ->assertSee('0 Baru');

        $this->assertDatabaseHas('activity_read_statuses', [
            'activity_id' => $activity->id,
            'user_id' => $staff->id,
        ]);

        Livewire::actingAs($staff)
            ->test(Board::class)
            ->assertSee('1 pesan baru');

        Livewire::actingAs($staff)
            ->test(TaskSlideOver::class)
            ->call('open', $task->id);

        $this->assertDatabaseHas('message_read_statuses', [
            'message_id' => $message->id,
            'user_id' => $staff->id,
        ]);

        Livewire::actingAs($staff)
            ->test(Board::class)
            ->assertDontSee('1 pesan baru');
    }

    public function test_notification_dropdown_can_mark_all_items_as_read(): void
    {
        $manager = $this->createUser('manager');
        $staff = $this->createUser('staff');
        $task = Task::create([
            'title' => 'Task Notifikasi',
            'description' => 'Task testing',
            'task_type' => 'Internal',
            'status' => 'New',
            'difficulty_points' => 10,
            'deadline' => now()->addDays(1),
            'pic_id' => $staff->id,
            'manager_id' => $manager->id,
        ]);

        Activity::query()->delete();

        activity()->useLog('task_activity')->causedBy($manager)->performedOn($task)->log('Pesan baru pada tugas: "Task Notifikasi"');
        activity()->useLog('task_activity')->causedBy($manager)->performedOn($task)->log('Status tugas "Task Notifikasi" diperbarui menjadi: Siap Direview');

        Livewire::actingAs($staff)
            ->test(ActivityNotifications::class)
            ->assertSee('2 Baru')
            ->call('markAllAsRead')
            ->assertSee('0 Baru');

        $this->assertSame(2, ActivityReadStatus::query()->where('user_id', $staff->id)->count());
    }

    private function createUser(string $role, array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'division_id' => $this->division->id,
            'base_point_rate' => 20000,
        ], $attributes));

        $user->assignRole($role);

        return $user;
    }
}
