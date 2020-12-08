<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ContactTest extends TestCase
{
    /**
     * test on getting contacts
     * @test
     * @return void
     */
    public function getContacts()
    {
        $response = $this->call('GET', '/contacts?pageSize=10&page=1');

        $this->assertEquals(200, $response->status());

        $response->assertJsonStructure([
          'contacts',
          'customAttributes'
       ]);
          
    }

    /**
     * test on importing contacts
     * @test
     * @return void
     */
    public function importContacts()
    {
        $response = $this->call('POST', '/contacts/import', [
          'data' => [
            [
              'team_id' => 1,
              'name' => 'Unit Testing',
              'phone' => 123456,
              'email' => 'testingImport@test.com',
              'sticky_phone_number_id' => 123
            ]
          ],
          'keys' => ['team_id', 'name', 'phone', 'email', 'sticky_phone_number_id']
        ]);

        $this->seeInDatabase('contacts', ['email' => 'testingImport@test.com']);

        $this->assertEquals(200, $response->status());

        $response->assertJsonStructure([
          'success',
          'description'
       ]);
    }

    /**
     * test on importing contacts with custom attributes
     * @test
     * @return void
     */
    public function importContactsWithCustomAttribute()
    {
        $response = $this->call('POST', '/contacts/import', [
          'data' => [
            [
              'team_id' => 1,
              'name' => 'Unit Testing With Custom Attribute',
              'phone' => 123456,
              'email' => 'testingImportWithCustom@test.com',
              'sticky_phone_number_id' => 123,
              'unit_testing' => 'passed'
            ]
          ],
          'keys' => ['team_id', 'name', 'phone', 'email', 'sticky_phone_number_id', 'unit_testing']
        ]);

        $this->seeInDatabase('contacts', ['email' => 'testingImportWithCustom@test.com']);
        $this->seeInDatabase('custom_attributes', ['key' => 'unit_testing', 'value' => 'passed']);

        $this->assertEquals(200, $response->status());

        $response->assertJsonStructure([
          'success',
          'description'
       ]);
    }
    
}
