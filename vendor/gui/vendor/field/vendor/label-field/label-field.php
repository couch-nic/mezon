<?php
namespace Mezon\GUI\Field;

/**
 * Class LabelField
 *
 * @package Field
 * @subpackage LabelField
 * @author Dodonov A.A.
 * @version v.1.0 (2019/09/04)
 * @copyright Copyright (c) 2019, aeon.org
 */

/**
 * Form header control
 */
class LabelField extends TextField
{

    /**
     * Generating input feld
     *
     * @return string HTML representation of the input field
     */
    public function html(): string
    {
        $Content = '<div class="form-group col-md-12">';
        $Content .= '<label class="control-label">' . $this->Text . '</label>';
        $Content .= '</div>';

        return ($Content);
    }

    /**
     * Getting field type
     *
     * @return string Field type
     */
    public function getType(): string
    {
        return ('label');
    }

    /**
     *
     * {@inheritdoc}
     * @see \Mezon\GUI\Control::fillAllRow()
     */
    public function fillAllRow(): bool
    {
        return (true);
    }
}

?>