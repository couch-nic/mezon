<?php

class RowsFieldTest extends PHPUnit\Framework\TestCase
{

    /**
     * Testing constructor
     */
    public function testConstructor()
    {
        // setup
        $Field = new \Mezon\GUI\FormBuilder\RowsField([
            'text' => 'name'
        ], 'author');

        // test body
        $Content = $Field->html();

        // assertions
        $this->assertContains('add_element_by_template', $Content, 'Necessary JavaScripts were not found');
    }
}

?>