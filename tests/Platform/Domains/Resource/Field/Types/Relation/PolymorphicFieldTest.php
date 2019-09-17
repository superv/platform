<?php

namespace Tests\Platform\Domains\Resource\Field\Types\Relation;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceConfig as Config;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class PolymorphicFieldTest extends ResourceTestCase
{
    function test__creates_resources()
    {
        $this->assertTrue(Resource::exists('platform.contacts_sms'));
        $this->assertTrue(Resource::exists('platform.contacts_email'));

        $this->assertTableExists('contacts_sms');
        $this->assertTableExists('contacts_email');
    }

    function test__creates_columns()
    {
        $this->assertColumnsExist('contacts', ['type_type', 'type_id']);
        $this->assertColumnsExist('contacts_sms', ['cell', 'contact_id']);
        $this->assertColumnsExist('contacts_email', ['email', 'contact_id']);
    }

    function __config()
    {
        $selectTypeField = sv_resource('platform.contacts')->getField('type');

//        $this->assertEquals([
//            'sms' => 'SMS Contacts',
//            'email' => 'Email Contacts',
//            'slack' => 'Slack Contacts',
//        ], $selectTypeField->getConfigValue('options'));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->create('contacts', function (Blueprint $table, Config $config) {
            $config->label('Contacts');

            $table->increments('id');
            $table->string('title');
            $table->belongsTo('users');

            $table->polymorph('typed')
                  ->add('sms', function (Blueprint $table, Config $config) {
                      $config->label('SMS Contacts');
                      $table->string('cell');
                  })
                  ->add('email', function (Blueprint $table, Config $config) {
                      $config->label('Email Contacts');
                      $table->email('email');
                  })
                  ->add('slack', function (Blueprint $table, Config $config) {
                      $config->label('Slack Contacts');
                      $table->email('slack_user');
                  });
        });
    }
}
