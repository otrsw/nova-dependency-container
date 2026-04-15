<?php

declare(strict_types=1);

namespace Iamgerwin\NovaDependencyContainer;

if (! class_exists('Laravel\Nova\Fields\Field')) {
    class_alias('Iamgerwin\NovaDependencyContainer\Stubs\Field', 'Laravel\Nova\Fields\Field');
}

if (! class_exists('Laravel\Nova\Http\Requests\NovaRequest')) {
    class_alias('Illuminate\Http\Request', 'Laravel\Nova\Http\Requests\NovaRequest');
}

use Laravel\Nova\Fields\Field;

class NovaDependencyContainer extends Field
{
    public $component = 'nova-dependency-container';

    public $showOnIndex = false;

    public $showOnDetail = false;

    protected array $dependencies = [];

    protected array $fields = [];

    protected bool $applyToFields = false;

    public function __construct($fields = [])
    {
        parent::__construct('', null, null);

        if (is_callable($fields)) {
            $fields = call_user_func($fields);
        }

        $this->fields = is_array($fields) ? $fields : [$fields];

        $this->withMeta([
            'fields' => $this->fields,
            'dependencies' => $this->dependencies,
        ]);
    }

    public function dependsOn(string $field, $value): self
    {
        $dependency = [
            'field' => $field,
            'value' => $value,
        ];

        if (! in_array($dependency, $this->dependencies, true)) {
            $this->dependencies[] = $dependency;
        }

        return $this->withMeta(['dependencies' => $this->dependencies]);
    }

    public function dependsOnIn(string $field, array $values): self
    {
        foreach ($values as $value) {
            $this->dependsOn($field, $value);
        }

        return $this;
    }

    public function dependsOnNotIn(string $field, array $values): self
    {
        $dependency = [
            'field' => $field,
            'notIn' => $values,
        ];

        if (! in_array($dependency, $this->dependencies, true)) {
            $this->dependencies[] = $dependency;
        }

        return $this->withMeta(['dependencies' => $this->dependencies]);
    }

    public function dependsOnNot(string $field, $value): self
    {
        $dependency = [
            'field' => $field,
            'not' => $value,
        ];

        if (! in_array($dependency, $this->dependencies, true)) {
            $this->dependencies[] = $dependency;
        }

        return $this->withMeta(['dependencies' => $this->dependencies]);
    }

    public function dependsOnNotEmpty(string $field): self
    {
        $dependency = [
            'field' => $field,
            'notEmpty' => true,
        ];

        if (! in_array($dependency, $this->dependencies, true)) {
            $this->dependencies[] = $dependency;
        }

        return $this->withMeta(['dependencies' => $this->dependencies]);
    }

    public function dependsOnEmpty(string $field): self
    {
        $dependency = [
            'field' => $field,
            'empty' => true,
        ];

        if (! in_array($dependency, $this->dependencies, true)) {
            $this->dependencies[] = $dependency;
        }

        return $this->withMeta(['dependencies' => $this->dependencies]);
    }

    public function dependsOnNullOrZero(string $field): self
    {
        $dependency = [
            'field' => $field,
            'nullOrZero' => true,
        ];

        if (! in_array($dependency, $this->dependencies, true)) {
            $this->dependencies[] = $dependency;
        }

        return $this->withMeta(['dependencies' => $this->dependencies]);
    }

    public function applyToFields(): self
    {
        $this->applyToFields = true;

        return $this->withMeta(['applyToFields' => true]);
    }

    public function jsonSerialize(): array
    {
        return array_merge([
            'component' => $this->component(),
            'prefixComponent' => true,
            'indexName' => '',
            'name' => '',
            'attribute' => '',
            'value' => null,
            'panel' => $this->panel,
            'sortable' => false,
            'nullable' => false,
            'readonly' => false,
            'textAlign' => 'left',
            'visible' => true,
            'withLabel' => false,
            'fields' => $this->fields,
            'dependencies' => $this->dependencies,
            'applyToFields' => $this->applyToFields,
        ], $this->meta());
    }

    public function fill($request, $model): void
    {
        $callbacks = [];

        foreach ($this->fields as $field) {
            if (! $field instanceof Field) {
                continue;
            }

            if (! empty($field->attribute)) {
                $callbacks[] = $field->fill($request, $model);
            }
        }

        foreach ($callbacks as $callback) {
            if (is_callable($callback)) {
                $callback();
            }
        }
    }

    public function resolve($resource, ?string $attribute = null)
    {
        foreach ($this->fields as $field) {
            if ($field instanceof Field) {
                $field->resolve($resource, $field->attribute);
            }
        }
    }

    public function resolveForDisplay($resource, ?string $attribute = null)
    {
        foreach ($this->fields as $field) {
            if ($field instanceof Field) {
                $field->resolveForDisplay($resource, $field->attribute);
            }
        }
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getDependencies(): array
    {
        return $this->dependencies;
    }
}
