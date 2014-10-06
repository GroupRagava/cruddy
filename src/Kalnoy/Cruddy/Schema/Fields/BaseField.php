<?php

namespace Kalnoy\Cruddy\Schema\Fields;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Kalnoy\Cruddy\Schema\Attribute;
use Kalnoy\Cruddy\Schema\FieldInterface;

/**
 * A base class for all fields.
 *
 * @property string $label
 * @method $this label(string $value)
 * @property bool $required
 * @method $this required(bool $value = true)
 * @property bool $unique
 * @method $this unique(bool $value = true)
 * @property string $disable
 * @method $this disable(mixed $value = true)
 *
 * @since 1.0.0
 */
abstract class BaseField extends Attribute implements FieldInterface {

    /**
     * The filter type.
     *
     * @var string
     */
    protected $filterType = self::FILTER_NONE;

    /**
     * {@inheritdoc}
     */
    public function extract(Eloquent $model)
    {
        return $model->getAttribute($this->id);
    }

    /**
     * {@inheritdoc}
     */
    public function extractForColumn(Eloquent $model)
    {
        return $this->extract($model);
    }

    /**
     * {@inheritdoc}
     */
    public function process($value)
    {
        return $value;
    }

    /**
     * Get field label.
     *
     * @return string
     */
    public function getLabel()
    {
        if ($label = $this->get('label'))
        {
            return \Kalnoy\Cruddy\try_trans($label);
        }

        return $this->generateLabel();
    }

    /**
     * Generate a label.
     *
     * @return string
     */
    protected function generateLabel()
    {
        return $this->translate('fields') ?: parent::generateLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function filter(QueryBuilder $builder, $data)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterType()
    {
        return $this->filterType;
    }

    /**
     * @return bool|string
     */
    protected function isRequired()
    {
        $required = $this->get('required');

        if ($required !== null) return $required;

        return $this->entity->getValidator()->getRequiredState($this->id);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return
        [
            'required' => $this->isRequired(),
            'unique' => $this->get('unique'),
            'disabled' => $this->get('disable'),
            'label' => $this->getLabel(),
            'filter_type' => $this->getFilterType(),

        ] + parent::toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function keep($value)
    {
        return true;
    }

    /**
     * Get whether the field is disabled for specified action.
     *
     * @param string $action
     *
     * @return bool
     */
    public function isDisabled($action)
    {
        return $this->disabled === true or $this->disabled === $action;
    }

    /**
     * {@inheritdoc}
     */
    public function sendToRepository($action)
    {
        return ! $this->isDisabled($action);
    }

}