<?php

class FormHeaderUnitTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Testing constructor
     */
    public function testConstructor()
    {
        // setup
        $field = new \Mezon\Gui\FormBuilder\FormHeader([
            'text' => 'name'
        ]);

        // test body
        $content = $field->html();

        // assertions
        $this->assertStringContainsString('<h3>name</h3>', $content, 'Header was not built');
    }
}
