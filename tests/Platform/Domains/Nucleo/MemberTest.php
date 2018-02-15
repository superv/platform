<?php

namespace Tests\SuperV\Platform\Domains\Nucleo;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Nucleo\Member;
use SuperV\Platform\Domains\Nucleo\Value;
use Tests\SuperV\Platform\BaseTestCase;

class MemberTest extends BaseTestCase
{
    use RefreshDatabase;

    /** @test */
    function delete_values_when_a_member_is_deleted()
    {
        $member = Member::create(
            ['struct_id' => 1, 'field_id' => 1]
        );

        $member->values()->create(['value' => 'some value']);
        $member->values()->create(['value' =>'other value']);

        $this->assertEquals(2, Value::where('member_id', $member->id)->count());

        $member->delete();

        $this->assertEquals(0, Value::where('member_id', $member->id)->count());

    }
}