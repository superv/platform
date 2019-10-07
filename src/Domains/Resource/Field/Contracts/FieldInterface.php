<?php

namespace SuperV\Platform\Domains\Resource\Field\Contracts;

use Closure;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Support\Identifier;

interface FieldInterface
{
    public function getName();

//    public function getResource(): Resource;

    public function getIdentifier();

    public function identifier(): Identifier;

    public function getType(): string;

    public function setType(string $type): FieldInterface;

    public function getColumnName(): ?string;

    public function getLabel(): string;

    public function setLabel(string $label): FieldInterface;

    public function getFieldType(): FieldTypeInterface;

    public function getValue();

    public function setValue($value): void;

    public function getDefaultValue();

    public function setDefaultValue($defaultValue): void;

    public function getConfig();

    public function getConfigValue($key, $default = null);

    public function setConfigValue($key, $value = null): FieldInterface;

    public function mergeConfig(array $config): FieldInterface;

    public function isHidden();

    public function isUnique();

    public function isRequired();

    public function setNotRequired();

    public function isUnbound();

    public function doesNotInteractWithTable();

    public function getAlterQueryCallback();

    public function getRules();

    public function removeRules(): FieldInterface;

    public function addRule($rule, $message = null): FieldInterface;

    public function getPlaceholder();

    public function fillFromEntry(EntryContract $entry);

    public function beforeResolvingEntry(Closure $callback): FieldInterface;

    public function beforeResolvingRequest(Closure $callback): FieldInterface;

    public function beforeSaving(Closure $callback): FieldInterface;

    public function setCallback($trigger, $callback);

    public function getCallback($trigger);

//    public function resolveRequest(Request $request, ?EntryContract $entry = null);

    public function resolveFromEntry($entry);

    public function getComposer($for);

    public function getMutator($for);

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

    public function revisionId(): ?string;

    public function isFilter();

    public function searchable(): FieldInterface;

    public function isVisible(): bool;
}
