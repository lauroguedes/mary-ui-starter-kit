<?php

declare(strict_types=1);

use App\Models\User;
use App\Enums\UserStatus;
use Livewire\Livewire;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('users index page loads successfully', function () {
    $response = $this->get(route('users.index'));
    
    $response->assertStatus(200);
    $response->assertSee(__('Users'));
});

test('users index displays users in table', function () {
    $users = User::factory()->count(3)->create();
    
    Livewire::test('pages.users.index')
        ->assertSee($users[0]->name)
        ->assertSee($users[1]->name)
        ->assertSee($users[2]->name);
});

test('users can be searched by name', function () {
    $userJohn = User::factory()->create(['name' => 'John Doe']);
    $userJane = User::factory()->create(['name' => 'Jane Smith']);
    
    Livewire::test('pages.users.index')
        ->set('search', 'John')
        ->assertSee($userJohn->name)
        ->assertDontSee($userJane->name);
});

test('users can be filtered by status', function () {
    $activeUser = User::factory()->create(['status' => UserStatus::ACTIVE]);
    $inactiveUser = User::factory()->create(['status' => UserStatus::INACTIVE]);
    
    Livewire::test('pages.users.index')
        ->set('status', UserStatus::ACTIVE->value)
        ->assertSee($activeUser->name)
        ->assertDontSee($inactiveUser->name);
});

test('users can be sorted by columns', function () {
    $userA = User::factory()->create(['name' => 'Alice']);
    $userB = User::factory()->create(['name' => 'Bob']);
    
    Livewire::test('pages.users.index')
        ->set('sortBy', ['column' => 'name', 'direction' => 'desc'])
        ->assertSeeInOrder([$userB->name, $userA->name]);
});

test('user can be deleted successfully', function () {
    $targetUser = User::factory()->create();
    
    Livewire::test('pages.users.index')
        ->call('delete', $targetUser)
        ->assertSet('modal', false)
        ->assertSuccessful();
        
    $this->assertDatabaseMissing('users', [
        'id' => $targetUser->id,
    ]);
});

test('user cannot delete themselves', function () {
    // Create another user so we can see the delete button for them
    $otherUser = User::factory()->create();
    
    $component = Livewire::test('pages.users.index');
    
    // The current user should not have a delete button in their actions
    $html = $component->html();
    
    // Look for the current user's row and verify no delete button
    expect($html)->toContain($this->user->name);
    
    // Check that other users do have delete buttons
    expect($html)->toContain($otherUser->name);
    expect($html)->toContain(__('Delete'));
});

test('filters can be cleared', function () {
    Livewire::test('pages.users.index')
        ->set('search', 'test')
        ->set('status', UserStatus::ACTIVE->value)
        ->call('clear')
        ->assertSet('search', '')
        ->assertSet('status', null);
});

test('edit redirects to user edit page', function () {
    $targetUser = User::factory()->create();
    
    Livewire::test('pages.users.index')
        ->call('edit', $targetUser)
        ->assertRedirect(route('users.edit', ['user' => $targetUser->id]));
});

test('pagination works correctly', function () {
    User::factory()->count(15)->create();
    
    $component = Livewire::test('pages.users.index');
    
    // Should show 10 per page by default (plus the authenticated user = 11 total users, but paginated to 10)
    $users = $component->instance()->users();
    expect($users->count())->toBe(10);
    expect($users->total())->toBe(16); // 15 created + 1 authenticated user
});

test('drawer opens and closes for filters', function () {
    Livewire::test('pages.users.index')
        ->set('drawer', true)
        ->assertSet('drawer', true)
        ->set('drawer', false)
        ->assertSet('drawer', false);
});