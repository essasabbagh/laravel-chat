<?php

namespace Essasabbagh\LaravelChat\Tests\Feature;

use Essasabbagh\LaravelChat\Models\Conversation;
use Essasabbagh\LaravelChat\Tests\Models\TestAgent;
use Essasabbagh\LaravelChat\Tests\Models\TestCustomer;
use Essasabbagh\LaravelChat\Tests\TestCase;

class GroupsTest extends TestCase
{
    private TestCustomer $admin;

    private TestCustomer $member;

    private TestAgent $agent;

    private Conversation $group;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = TestCustomer::create(['name' => 'Admin']);
        $this->member = TestCustomer::create(['name' => 'Member']);
        $this->agent = TestAgent::create(['name' => 'Agent']);

        $this->group = Conversation::factory()->group()->create();
        $this->group->participants()->create([
            'participantable_type' => TestCustomer::class,
            'participantable_id' => $this->admin->id,
            'role' => 'admin',
        ]);
        $this->group->participants()->create([
            'participantable_type' => TestCustomer::class,
            'participantable_id' => $this->member->id,
            'role' => 'member',
        ]);
    }

    /** @test */
    public function can_add_member_to_group()
    {
        $response = $this->postJson(
            "/api/chat/conversations/{$this->group->id}/members",
            [
                'participantable_type' => TestAgent::class,
                'participantable_id' => $this->agent->id,
            ]
        );

        $response->assertStatus(201);
        $this->assertDatabaseHas('chat_participants', [
            'conversation_id' => $this->group->id,
            'participantable_type' => TestAgent::class,
            'participantable_id' => $this->agent->id,
        ]);
    }

    /** @test */
    public function cannot_add_duplicate_member()
    {
        $response = $this->postJson(
            "/api/chat/conversations/{$this->group->id}/members",
            [
                'participantable_type' => TestCustomer::class,
                'participantable_id' => $this->admin->id,
            ]
        );

        $response->assertStatus(409);
    }

    /** @test */
    public function can_remove_member_from_group()
    {
        $participant = $this->group->participants()
            ->where('participantable_id', $this->member->id)
            ->first();

        $response = $this->deleteJson(
            "/api/chat/conversations/{$this->group->id}/members/{$participant->id}"
        );

        $response->assertStatus(204);
        $this->assertModelMissing($participant);
    }

    /** @test */
    public function can_update_member_role()
    {
        $participant = $this->group->participants()
            ->where('participantable_id', $this->member->id)
            ->first();

        $response = $this->putJson(
            "/api/chat/conversations/{$this->group->id}/members/{$participant->id}/role",
            ['role' => 'admin']
        );

        $response->assertStatus(200);
        $this->assertDatabaseHas('chat_participants', [
            'id' => $participant->id,
            'role' => 'admin',
        ]);
    }

    /** @test */
    public function cannot_add_member_to_direct_conversation()
    {
        $direct = Conversation::factory()->create(['type' => 'direct']);

        $response = $this->postJson(
            "/api/chat/conversations/{$direct->id}/members",
            [
                'participantable_type' => TestAgent::class,
                'participantable_id' => $this->agent->id,
            ]
        );

        $response->assertStatus(422);
    }

    /** @test */
    public function anyone_can_create_group_when_config_is_any()
    {
        config(['chat.groups.who_can_create' => 'any']);

        $response = $this->postJson('/api/chat/conversations', [
            'type' => 'group',
            'name' => 'Test Group',
            'created_by_type' => TestCustomer::class,
            'created_by_id' => $this->member->id,
            'participants' => [
                ['type' => TestCustomer::class, 'id' => $this->member->id],
            ],
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function only_admins_can_create_group_when_config_is_admin_role()
    {
        config(['chat.groups.who_can_create' => 'admin_role']);

        $response = $this->postJson('/api/chat/conversations', [
            'type' => 'group',
            'name' => 'Admin Group',
            'created_by_type' => TestCustomer::class,
            'created_by_id' => $this->admin->id,
            'participants' => [
                ['type' => TestCustomer::class, 'id' => $this->admin->id],
            ],
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function non_admin_cannot_create_group_when_config_is_admin_role()
    {
        config(['chat.groups.who_can_create' => 'admin_role']);

        $response = $this->postJson('/api/chat/conversations', [
            'type' => 'group',
            'name' => 'Member Group',
            'created_by_type' => TestCustomer::class,
            'created_by_id' => $this->member->id,
            'participants' => [
                ['type' => TestCustomer::class, 'id' => $this->member->id],
            ],
        ]);

        $response->assertStatus(403);
    }
}
