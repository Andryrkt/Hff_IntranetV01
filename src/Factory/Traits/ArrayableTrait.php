<?php

namespace App\Factory\Traits;

trait ArrayableTrait
{
    /**=======================================================================
     * Hydrate l'objet à partir d'un tableau (transforme un array en objet)
     *======================================================================*/
    public function toObject(?array $data): self
    {
        // Si les données sont null, on retourne l'objet tel quel
        if ($data === null) {
            return $this;
        }

        foreach ($data as $key => $value) {
            $method = 'set' . ucfirst($key);

            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    /**==============================================================
     * Transforme l'objet en tableau en filtrant les valeurs vides
     *==============================================================*/
    public function toArray(array $onlyProperties = [], array $exceptProperties = []): array
    {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties();
        $result = [];

        foreach ($properties as $property) {
            $propertyName = $property->getName();

            // Appliquer les filtres
            if (!empty($onlyProperties) && !in_array($propertyName, $onlyProperties)) {
                continue;
            }

            if (!empty($exceptProperties) && in_array($propertyName, $exceptProperties)) {
                continue;
            }

            $property->setAccessible(true);
            $value = $property->getValue($this);

            if ($this->isValidArrayValue($value)) {
                $result[$propertyName] = $value;
            }
        }

        return $result;
    }

    private function isValidArrayValue($value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        if (is_array($value) && empty($value)) {
            return false;
        }

        if (is_object($value) && method_exists($value, 'isEmpty') && $value->isEmpty()) {
            return false;
        }

        return true;
    }
}
