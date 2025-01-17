<?php

namespace App\Serializer;

class NormalizeDepthHandler
{
    public function generateNormalizeCallbacks(object|string $objectOrClass, int $maxDepth = 2): array
    {
        $maxDepthPropertiesAndClasses = $this->getRecursiveMaxDepthPropertiesAndClasses($objectOrClass, $maxDepth);
        $normalizeCallbacks = [];

        foreach ($maxDepthPropertiesAndClasses as $propertyName => $excludeAttributeClasses) {
            $normalizeCallbacks[$propertyName] = function (mixed $value, object|string $object) use ($excludeAttributeClasses) {
                if (!\is_object($value)) {
                    return $value;
                }

                if (\in_array(\get_class($value), \array_keys($excludeAttributeClasses), true)) {
                    // var_dump('exclude');
                    return $value->getId();
                }

                return $value;
            };
        }

        return $normalizeCallbacks;
    }

    /**
     * Recursively generates the properties to exclude in the given object or class with the given max depth.
     * 
     * @param object|string $objectOrClass The object or class to generate the exclude normalize callbacks for.
     * @param int $maxDepth The maximum depth to go into the object graph.
     * @param int $currentDepth The current depth in the object graph.
     * @param string|null $propertyName The name of the property that is being processed.
     * @param array $excludeNormalizeCallbacks The exclude normalize callbacks to add to.
     * @param array $allFoundTypes All found types in the object graph; this prevents infinite loops.
     * 
     * @return array The exclude normalize callbacks.
     */
    public function getRecursiveMaxDepthPropertiesAndClasses(object|string $objectOrClass, int $maxDepth, int $currentDepth = 0, array &$excludeNormalizePropertiesAndClasses = [], array &$allFoundTypes = []): array
    {
        // var_dump($currentDepth);

        $className = \is_object($objectOrClass) ? \get_class($objectOrClass) : $objectOrClass;
        $reflection = new \ReflectionClass($objectOrClass);
        $properties = $reflection->getProperties();

        // in this variable we store all the found nested types we can go into deeper.
        // if max depth is reached add them to the exclude list.
        $foundNestedTypes = [];

        // We need to check all getters of the property to see if it is attributed to a property of the class and is assigned the type of a object.
        // If it is an object we need to check if we reached the max depth.
        //    - if we reached max depth we only return the object's ID later.
        //    - if not we need to go deeper recursively.
        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $propertyMethod) {
            // we are only interested in getters
            if (!\str_starts_with($propertyMethod->getName(), 'get')) {
                continue;
            }

            // now we determine through the return type whether or not we want to allow the normalization of this property in the given class and at the current depth
            $propertyName = \lcfirst(\substr($propertyMethod->getName(), 3));
            $propertyMethodReturnType = $propertyMethod->getReturnType();
        
            if ($propertyMethodReturnType instanceof \ReflectionUnionType || $propertyMethodReturnType instanceof \ReflectionIntersectionType) {
                $typeNames = \array_map(fn(\ReflectionNamedType $type) => $type->getName(), $propertyMethod->getReturnType()->getTypes());
            } else {
                $typeNames = [$propertyMethodReturnType->getName()];
            }

            foreach ($typeNames as $typeName) {
                // prevent infinite loops by checking if we already processed this type
                if (\in_array($typeName, $allFoundTypes, true)) {
                    continue;
                }

                // if the property contains 'App\Entity' in its name we know it's an object we need to limit ('\' needs to be escaped in a PHP string, thus '\\')
                if (\str_contains($typeName, '\\')) {
                    if ($currentDepth >= $maxDepth) {
                        $excludeNormalizePropertiesAndClasses[$propertyName][$className] = 1;
                    } else {
                        $foundNestedTypes[] = $typeName;
                    }
                } else {
                    var_dump($propertyName, $typeName);
                }
            }
        }

        var_dump(\array_keys($excludeNormalizePropertiesAndClasses));
        die();

        // We need to check all types of the property to see if it is a object.
        // If it is one we need to check if we reached the max depth.
        //    - if we reached max depth we only return the entity's ID later.
        //    - if not we need to go deeper recursively.
        // foreach ($properties as $property) {
        //     $property->setAccessible(true);
        //     $propertyType = $property->getType();
            
        //     if ($propertyType instanceof \ReflectionUnionType || $propertyType instanceof \ReflectionIntersectionType) {
        //         $typeNames = \array_map(fn(\ReflectionNamedType $type) => $type->getName(), $property->getType()->getTypes());
        //     } else {
        //         $typeNames = [$propertyType->getName()];
        //     }

        //     foreach ($typeNames as $typeName) {
        //         // prevent infinite loops by checking if we already processed this type
        //         // @todo BIG PROBLEM: Recur
        //         if (\in_array($typeName, $allFoundTypes, true)) {
        //             continue;
        //         }

        //         // if the property contains 'App\Entity' in its name we know it's an object we need to limit ('\' needs to be escaped in a PHP string, thus '\\')
        //         if (\str_contains($typeName, '\\')) {
        //             if ($currentDepth >= $maxDepth) {
        //                 $excludeNormalizePropertiesAndClasses[$property->getName()][$className] = 1;
        //             } else {
        //                 $foundNestedTypes[] = $typeName;
        //             }
        //         } else {
        //             var_dump($typeName);
        //         }
        //     }
        // }

        var_dump($excludeNormalizePropertiesAndClasses);
        die();

        foreach ($foundNestedTypes as $foundNestedType) {
            $allFoundTypes[] = $foundNestedType;

            $recursiveExcludeNormalizeCallbacks = $this->getRecursiveMaxDepthPropertiesAndClasses(
                objectOrClass: $foundNestedType,
                maxDepth: $maxDepth,
                currentDepth: $currentDepth + 1,
                excludeNormalizePropertiesAndClasses: $excludeNormalizePropertiesAndClasses,
                allFoundTypes: $allFoundTypes,
            );

            foreach ($recursiveExcludeNormalizeCallbacks as $propertyName => $excludeAttributeClasses) {
                $excludeNormalizePropertiesAndClasses[$propertyName] = \array_unique([
                    ...$excludeAttributeClasses,
                    ...$excludeNormalizePropertiesAndClasses[$propertyName] ?? [],
                ]);
            }
        }

        return $excludeNormalizePropertiesAndClasses;
    }
}