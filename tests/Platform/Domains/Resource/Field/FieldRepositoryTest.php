<?php

namespace Tests\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Auth\Access\Action;
use SuperV\Platform\Domains\Resource\Field\FieldRepository;
use SuperV\Platform\Exceptions\ValidationException;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FieldRepositoryTest extends ResourceTestCase
{
    /** @var FieldRepository */
    protected $repo;

    function test__creates_field_with_valid_params()
    {
        $field = $this->repo->create($attributes = $this->makeValidFieldAttributes());

        $this->assertNotNull($field);

        $this->assertArrayContains($attributes, $field->toArray());
    }

    function test__identifier_is_required()
    {
        $this->expectException(ValidationException::class);
        $this->repo->create([
            'name' => 'title',
            'type' => 'text',
        ]);
    }

    function test__validates_identifier()
    {
        $this->expectException(ValidationException::class);
        $this->repo->create($attributes = [
            'identifier' => 'testing.core.title',
            'name'       => 'title',
            'type'       => 'text',
        ]);
    }

    function test__creates_auth_action_entries()
    {
        $field = $this->repo->create($attributes = $this->makeValidFieldAttributes());

        $action = Action::withSlug($field->getIdentifier());
        $this->assertNotNull($action);
        $this->assertEquals('testing_module.core.fields', $action->namespace);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->repo = FieldRepository::resolve();
    }

    /**
     * @return array
     */
    protected function makeValidFieldAttributes(): array
    {
        return [
            'identifier' => 'testing_module.core.fields:title',
            'name'       => 'title',
            'type'       => 'text',
        ];
    }
}
