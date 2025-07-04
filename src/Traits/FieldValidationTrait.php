<?php

declare(strict_types = 1);

namespace Namecheap\Traits;

trait FieldValidationTrait
{
    /**
     * Checks if required fields are present in the data array
     *
     * @param array $dataArray      The data array to check
     * @param array $requiredFields The list of required fields
     *
     * @return array
     */
    protected function checkRequiredFields(array $dataArray, array $requiredFields): array
    {
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (empty($dataArray[$field])) {
                $missingFields[] = $field;
            }
        }

        return $missingFields;
    }
}