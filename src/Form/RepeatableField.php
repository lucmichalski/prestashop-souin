<?php

namespace PrestaShop\Module\Souin\Form;

class RepeatableField extends AbstractContainerFields
{
    protected $fields;
    protected $initialValues;

    /**
     * RepeatableField constructor.
     * @param string $name
     * @param string $label
     * @param mixed[] $initialValue
     * @param AbstractField[] $fields
     */
    public function __construct($name, $label, $initialValue, $fields = [])
    {
        parent::__construct([], $name, $label);
        $this->fields = $fields;
        $this->initialValues = $initialValue;
    }

    public function sanitizeFields($initialValue = null)
    {
        $fields = '';
        foreach ($this->fields as $f) {
            if (!is_null($initialValue)) {
                $n = $f->getName();
                preg_match_all('/\[([^\]]+)\]/', $n, $matches);
                $match = $matches[1][1];
                $f->setInitialValue($initialValue->$match);
            }
            $fields .= <<<HTML
<div>
  {$f->getLabel()}
  {$f->getField()}
</div>
HTML;
        }

        return $fields;
    }

    public function generateFields($classname = '', $initialValue = null)
    {
        $style = $classname !== '' ? ' style="display: none"' : 'style="padding: 0.5rem 0 1rem 0;"';

        return <<<HTML
            <div class="row {$classname}" {$style}>
                <div style="margin: auto;">
                    <span class="move">Move Row</span>
                    <span class="remove">Remove</span>
                </div>
                {$this->sanitizeFields($initialValue)}
            </div>
HTML;
    }

    public function getField()
    {
        $initialFields = '';
        foreach ($this->initialValues as $value) {
            $initialFields .= $this->generateFields('', $value);
        }

        if ($initialFields === '') {
            $initialFields .= $this->generateFields('');
        }

        return <<<HTML
<div class="repeat" style="padding-top: 1rem">
	<div class="wrapper">
		<span class="add">Add $this->title</span>
		<div class="container" style="padding: 0.5rem 0 1rem 1rem;">
            {$this->generateFields('template')}
            {$initialFields}
		</div>
	</div>
</div>
HTML;
    }

    public function renderField(): string
    {
        return $this->getField();
    }
}
