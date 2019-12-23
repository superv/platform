<?php

namespace SuperV\Platform\Domains\Resource\Jobs;

class AccessEntryAttributes
{
//    public function handle(EntryRetrievedEvent $event)
//    {
//        $entry = $event->entry;
//
//        if (starts_with($entry->getTable(), 'sv_')) {
//            return;
//        }
//
//        if (! Platform::isInstalled()) {
//            return;
//        }
//
//        if (! Resource::exists($entry)) {
//            return;
//        }
//
//        $resource = ResourceFactory::make($entry);
//
//        $resource->getFields()->map(function (FieldInterface $field) use ($entry) {
//
//            $value =   $field->getValue()->setEntry($entry)->resolve()->get();
//            if ($value instanceof Closure) {
//                return;
//            }
//            $entry->setAttribute($field->getColumnName(), $value);
//
////            if ($field->getFieldType() instanceof HasAccessor) {
////                $value = (new Accessor($field->getFieldType()))
////                    ->get([
////                        'entry' => $entry,
////                        'value' => $entry->getAttribute($field->getColumnName()),
////                    ]);
////
////                if ($value instanceof Closure) {
////                    return;
////                }
////
////                $entry->setAttribute($field->getColumnName(), $value);
////            }
//        });
//    }
}