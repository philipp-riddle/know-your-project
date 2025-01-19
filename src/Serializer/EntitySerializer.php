<?php

namespace App\Serializer;

use App\Entity\Interface\UserPermissionInterface;
use App\Entity\User;
use App\Serializer\Attribute\IgnoreWhenNested;
use Doctrine\Common\Proxy\Proxy;
use ReflectionClass;

final class EntitySerializer
{
    public function serialize(User $currentUser, UserPermissionInterface|iterable $entityOrIterable, int $maxDepth = 5): array
    {
        if (\is_iterable($entityOrIterable)) { // @todo move this to the recursive serializer to keep the depth in mind when serializing an iterable as well
            $serializedIterable = [];
            
            foreach ($entityOrIterable as $entity) {
                $serializedIterable[] = $this->recursiveSerialize($currentUser, $entity, $maxDepth, currentDepth: 1); // start at depth 1 has we went into the iterable
            }

            return $serializedIterable;
        }

        return $this->recursiveSerialize($currentUser, $entityOrIterable, $maxDepth);
    }

    /**
     * Recursively serializes the properties in the given object or class with the given max depth.
     * 
     * @param object|string $objectOrClass The object or class to serialize the content for.
     * @param int $maxDepth The maximum depth to go into the object graph.
     * @param int $currentDepth The current depth in the object graph.
     * @param string|null $propertyName The name of the property that is being processed.
     * @param array $allFoundTypesAndIds All found types and IDs in the object graph; this prevents infinite loops.
     * 
     * @return array The exclude normalize callbacks.
     */
    private function recursiveSerialize(User $currentUser, object $object, int $maxDepth, int $currentDepth = 0, array &$allFoundTypesAndIds = []): array|int
    {
        if ($object instanceof Proxy) {
            $object->__load(); // this makes sure all the properties are loaded if we handle a Doctrine entity proxy
        }

        $serializedObject = [];
        $reflection = new \ReflectionClass($object);
        $objectId = $this->getEntityIdValue($reflection, $object);

        // prevent infinite loops by checking if we already processed this type and ID
        // if yes we serialize the ID only
        if (null !== $objectId && \array_key_exists($objectId, $allFoundTypesAndIds[$reflection->getName()] ?? [])) {
            // var_dump('CIRCULAR', $reflection->getName(), $objectId);

            return $this->getMappedEntityValue($reflection, $object);
        }

        // add the object to the list of all found types and IDs to prevent serialising it multiple times
        $allFoundTypesAndIds[$reflection->getName()][$objectId ?? ''] = 1;

        // in this variable we store all the found nested types we can go into deeper.
        // if max depth is reached add them to the exclude list.
        $foundNestedTypes = [];

        // We need to check all getters of the property to see if it is attributed to a property of the class and is assigned the type of a object.
        // If it is an object we need to check if we reached the max depth.
        //    - if we reached max depth we only return the default for the object (object ID / empty array).
        //    - if not we need to go deeper recursively (by adding it to the $foundNestedTypes property)
        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $propertyMethod) {
            if (!$this->shouldSerializeMethod($propertyMethod)) {
                continue;
            }

            // now we determine through the return type whether or not we want to allow the normalization of this property in the given class and at the current depth
            $propertyName = \lcfirst(\substr($propertyMethod->getName(), 3));
            $nestedReflectionClass = $this->getNestedReflectionClass($currentUser, $object, $propertyMethod, $currentDepth);
            $nestedValue = $propertyMethod->invoke($object);

            if ($currentDepth >= $maxDepth || null === $nestedReflectionClass || null === $nestedValue) {
                $serializedObject[$propertyName] = $this->getMappedEntityValue($nestedReflectionClass, $nestedValue);
            } else {
                $foundNestedTypes[] = [$propertyName, $nestedValue];
            }
        }

        // let the recursion begin:
        // go into all the found nested types and all their values, serialise them, and add them to the object which we will return.
        foreach ($foundNestedTypes as $typeNestedData) {
            list ($propertyName, $nestedValue) = $typeNestedData;

            if (\is_iterable($nestedValue)) {
                $serializedObject[$propertyName] = []; // initialize the iterable property with an empty array

                foreach ($nestedValue as $nestedValueItem) {
                    $serializedObject[$propertyName][] = $this->recursiveSerialize(
                        currentUser: $currentUser,
                        object: $nestedValueItem,
                        maxDepth: $maxDepth,
                        currentDepth: $currentDepth + 1,
                        allFoundTypesAndIds: $allFoundTypesAndIds,
                    );
                }
            } else {
                $serializedObject[$propertyName] = $this->recursiveSerialize(
                    currentUser: $currentUser,
                    object: $nestedValue,
                    maxDepth: $maxDepth,
                    currentDepth: $currentDepth + 1,
                    allFoundTypesAndIds: $allFoundTypesAndIds,
                );
            }
        }

        return $serializedObject;
    }

    /**
     * Gets the nested reflection class for the given object and method.
     * 
     * @return \ReflectionClass|null The reflection class of the nested object or null if the object is not a nested entity we want to serialise.
     */
    public function getNestedReflectionClass(User $currentUser, object $object, \ReflectionMethod $method, int $currentDepth = 0): ?ReflectionClass
    {
        $ignoreWhenNestedAttribute = $method->getAttributes(IgnoreWhenNested::class)[0] ?? null;

        if ($ignoreWhenNestedAttribute !== null && $currentDepth > 0) {
            return null; // value is ignored if the 'nested' attribute is set
        }

        $methodReturnType = $method->getReturnType();
    
        if ($methodReturnType instanceof \ReflectionUnionType || $methodReturnType instanceof \ReflectionIntersectionType) {
            $types = $method->getReturnType()->getTypes();
        } else {
            $types = [$methodReturnType];
        }

        /**
         * @var \ReflectionNamedType $type
         */
        foreach ($types as $type) {
            $typeName = $type?->getName();
            $nestedTypeValue = $method->invoke($object);

            // special case if we find a collection:
            // we need to determine the types of the collection via the doc comment
            if (\str_contains($typeName, 'Collection')) {
                $nestedReflectionClass = $this->getReflectionEntityClassFromDocComment($method->getDocComment());
                $typeName = $nestedReflectionClass->getName();
            } else {
                try {
                    $nestedReflectionClass = (new \ReflectionClass($typeName));
                } catch (\ReflectionException $e) {
                    $nestedReflectionClass = null; // e.g. integer, string, ...
                }
            }

            // ignore classes that are not in the App namespace and have \\; that includes DateTime in the serialisation while skipping Doctrine proxies, collections, ...
            if (null !== $nestedReflectionClass && !\str_starts_with($typeName, 'App') && \str_contains($typeName, '\\')) {
                continue; // only interested in serialising entities 
            }

            // if the property does not contain '\' in its name we know it's not an object; can be skipped in this context.
            if (!\str_contains($typeName, '\\')) {
                continue;
            }

            if ($nestedReflectionClass?->implementsInterface(UserPermissionInterface::class)) {
                // this can happen if we serialise a collection; check all the entries in the collection whether the user has access
                if (\is_iterable($nestedTypeValue)) {
                    foreach ($nestedTypeValue as $nestedTypeValueItem) {
                        if (!$nestedTypeValueItem->hasUserAccess($currentUser)) {
                            continue 2;
                        }
                    }
                } else if ($nestedTypeValue !== null && !$nestedTypeValue->hasUserAccess($currentUser)) {
                    continue;
                }
            }

            return $nestedReflectionClass;
        }

        return null;
    }

    public function shouldSerializeMethod(\ReflectionMethod $method): bool
    {
        if (!\str_starts_with($method->getName(), 'get')) {
            return false;
        }

        foreach ($method->getParameters() as $parameter) {
            if (!$parameter->isOptional()) {
                return false; // if the method has any required parameters we cannot serialize it
            }
        }

        return true;
    }

    public function getMappedEntityValue(?\ReflectionClass $class, $nestedTypeValue)
    {
        if ($nestedTypeValue instanceof \Traversable) {
            $nestedTypeValue = [];
        } else if (null !== $class && null !== $nestedEntityId = $this->getEntityIdValue($class, $nestedTypeValue)) {
            $nestedTypeValue = $nestedEntityId;
        } elseif ($nestedTypeValue instanceof \DateTime) {
            $nestedTypeValue = $nestedTypeValue->format('Y-m-d').'T'.$nestedTypeValue->format('H:i:s');
        }

        return $nestedTypeValue;
    }

    public function getEntityIdValue(\ReflectionClass $class, $nestedTypeValue): ?int
    {
        if (\is_iterable($nestedTypeValue)) {
            return null; // arrays, collections, ... do not have an ID
        }

        if ($class?->hasMethod('getId')) {
            return $nestedTypeValue?->getId();
        }

        return null;
    }

    /**
     * Extracts the \ReflectionClass for the entity from the given doc comment.
     * 
     * @param string $docComment The doc comment to extract the entity class from.
     * 
     * @throws \RuntimeException If the type name could not be found in the doc comment.
     * @return \ReflectionClass The reflection class of the entity.
     */
    public function getReflectionEntityClassFromDocComment(string $docComment): \ReflectionClass
    {
        $entityClass = $this->getEntityClassFromDocComment($docComment);

        return new ReflectionClass($entityClass);
    }

    /**
     * Extracts the entity class from the given doc comment.
     * This is really tricky and requires us to handle many edge cases.
     * Thus, there are unit tests for this method.
     * 
     * @param string $docComment The doc comment to extract the entity class from.
     * 
     * @throws \RuntimeException If the type name could not be found in the doc comment.
     * @return string The entity class.
     */
    public function getEntityClassFromDocComment(string $docComment): string
    {
        // either the first or the second type is the type we are looking for
        $typeName = \explode('@return', $docComment)[1] ?? null;
        
        // first, split up the type names, e.g. 'Type1|Type2'
        $typeNameParts = @\explode('|', $typeName);

        foreach ($typeNameParts as $typeName) {
            if (\str_contains($typeName, ',')) {
                $typeNameCommaParts = \explode(',', $typeName);
                $typeName = $typeNameCommaParts[1] ?? null; // always use the second part, i.e. the type of the value, the first part is the key

                if ($typeName === null) {
                    throw new \RuntimeException(\sprintf('Malformed doc comment: "%s" ', $docComment));
                }
            }

            $typeName = \str_replace(' ', '', $typeName); // remove any white space

            if (\str_contains($typeName, '>')) {
                $typeName = \explode('>', $typeName)[0]; // e.g. 'PageSection>' => 'PageSection'
            } else {
                $typeName = \trim($typeName, '>/*'); // if there is no closing tag we assume the type is directly afterwards; trim any characters at the end
            }

            // final white space removal and character trimming
            $typeName = \str_replace(['<', '>', '[]'], '', $typeName);
            $typeName = \trim($typeName);

            if ($typeName === null) {
                throw new \RuntimeException(\sprintf('No type name part left to analyze in doc comment "%s" ', $docComment));
            }

            // if the type name does not contain a backslash we assume it is a relative path to the entity
            if (!\str_contains($typeName, '\\')) {
                $typeName = \sprintf('App\\Entity\\%s', \trim($typeName));
            }

            if (\class_exists($typeName)) {
                return $typeName;
            }
        }
        

        throw new \RuntimeException(\sprintf('Could not find type name in doc comment "%s" ', $docComment));
    }
}