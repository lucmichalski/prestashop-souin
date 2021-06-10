<?php

namespace PrestaShop\Module\Souin\Form;

class AbstractContainerFields
{
    /** @var self[]|AbstractField[] */
    protected $fields;

    /** @var string */
    protected $section;

    /** @var string */
    protected $title;

    /**
     * AbstractContainerFields constructor.
     * @param $fields self[]|AbstractField[]
     * @param $title string
     */
    public function __construct($fields, $section, $title)
    {
        $this->fields = $fields;
        $this->section = $section;
        $this->title = $title;
    }

    public function getName()
    {
        return $this->section;
    }

    public function getLabel()
    {
        return '';
    }

    public function listFields()
    {
        return $this->fields;
    }

    public function renderField(): string
    {
        $form = '';
        foreach ($this->fields as $field) {
            $form .= $field->renderField();
        }

        return $form;
    }
}
