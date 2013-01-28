<?php

namespace LiteCQRS\Plugin\CRUD\Updater;

class ReflectionBasedUpdater implements PropertyUpdater
{
    /**
     * {@inheritDoc}
     */
    public function update($domain, array $data)
    {
        $objectRef = new \ReflectionObject($domain);
        foreach ($data as $key => $value) {
            $propertyRef = $objectRef->getProperty($key);
            $protected   = $propertyRef->isProtected() || $propertyRef->isPrivate();

            if ($protected) {
                $propertyRef->setAccessible(true);
            }

            $propertyRef->setValue($domain, $value);

            if ($protected) {
                $propertyRef->setAccessible(false);
            }
        }
    }
}
