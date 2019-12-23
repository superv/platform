<?php

namespace SuperV\Platform\Domains\Resource\Field\Contracts;

use Closure;
use SuperV\Platform\Domains\Resource\Field\Composer\DefaultFieldComposer;
use SuperV\Platform\Support\Identifier;

interface FieldInterface
{
    public function getHandle();

    public function getLabel(): string;

    public function getIdentifier();

    public function identifier(): Identifier;

    public function getType(): string;

    public function getComponent(): ?string;

    public function setType(string $type): FieldInterface;

    public function getColumnName(): ?string;

    public function setLabel(string $label): FieldInterface;

    public function type(): FieldTypeInterface;

    public function getFieldType(): FieldTypeInterface;

    public function value(): FieldValueInterface;

    public function getValue(): FieldValueInterface;

    public function getDefaultValue();

    public function getComposer(): ComposerInterface;

    public function getConfig();

    public function getConfigValue($key, $default = null);

    public function setConfigValue($key, $value = null): FieldInterface;

    public function mergeConfig(array $config): FieldInterface;

    public function isHidden();

    public function isHiddenOnView();

    public function isUnique();

    public function isRequired();

    public function readOnly(): FieldInterface;

    public function setNotRequired();

    public function isUnbound();

    public function doesNotInteractWithTable();

    public function getAlterQueryCallback();

    public function getRules();

    public function removeRules(): FieldInterface;

    public function addRule($rule, $message = null): FieldInterface;

    public function getPlaceholder();

    public function beforeResolvingEntry(Closure $callback): FieldInterface;

    public function beforeResolvingRequest(Closure $callback): FieldInterface;

    public function beforeSaving(Closure $callback): FieldInterface;

    public function beforeCreating(Closure $callback): FieldInterface;

    public function beforeUpdating(Closure $callback): FieldInterface;

    public function beforeValidating(Closure $callback): FieldInterface;

    public function setCallback($trigger, $callback);

    public function getCallback($trigger);

//    public function resolveFromEntry($entry);

//    public function getComposer(): ComposerInterface;

    public function setPresenter(Closure $callback): FieldInterface;

    /**
     * Add a flag to hide the field on forms
     *
     * @return \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface
     */
    public function hide(): FieldInterface;

    /**
     * Add a flag to show the field on index table
     *
     * @return \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface
     */
    public function showOnIndex(): FieldInterface;

    /**
     * Generate filter from field and add to filters
     *
     * @param array $params
     * @return \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface
     */
    public function copyToFilters(array $params = []): FieldInterface;

    /**
     * Set the display order for the field
     *
     * @param $order
     * @return \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface
     */
    public function displayOrder($order): FieldInterface;

    /**
     * Add css class(es)
     *
     * @param string $class
     * @return \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface
     */
    public function addClass(string $class): FieldInterface;

    /**
     * Add a boolean flag
     *
     * @param string $flag
     * @return \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface
     */
    public function addFlag(string $flag): FieldInterface;

    /**
     * Determine if the field has given flag
     *
     * @param string $flag
     * @return bool
     */
    public function hasFlag(string $flag): bool;

    /**
     * Remove a flag from the field
     *
     * @param string $flag
     * @return \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface
     */
    public function removeFlag(string $flag): FieldInterface;

    public function isFilter();

    public function searchable(): FieldInterface;

    public function isVisible(): bool;
}
