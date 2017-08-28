<?php
namespace Webuntis\Collection;

use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;
use ReflectionClass;
use ReflectionException;
use Traversable;
use Webuntis\Model\ModelInterface;

class Collection implements CollectionInterface, IteratorAggregate
{
  // Variables
  protected $map = [];
  
  // Constructor
  public function __construct(array $elements = [])
  {
    // Add all elements to the map
    foreach ($elements as $element)
    {
      // Check if the element is a model interface
      if (!is_a($element, ModelInterface::class))
        throw new InvalidArgumentException('All elements must be a ' . ModelInterface::class);
      
      // Add the element to the map
      $this->add($element);
    }
  }
  
  // Check if an element satisfies a condition
  private function filter(ModelInterface $element, array $condition)
  {
    // Create a reflection class
    $classReflector = new ReflectionClass($element);
      
    // Iterate over the condition array
    foreach ($condition as $name => $value)
    {
      try
      {
        // Check if the property exists and has the same value in the element
        $propertyReflector = $classReflector->getProperty($name);
        $propertyReflector->setAccessible(true);
        if ($propertyReflector->getValue($element) != $value)
          return false;
      }
      catch (ReflectionException $ex)
      {
        return false;
      }
      
      // All conditions match
      return true;
    }
  }
  
  // Return the element with the specified identifier
  public function get(int $id)
  {
    return $this->map[$id];
  }
  
  // Return all elements that satisfies the condition
  public function findAll(array $condition = []): array
  {
    // If no condition, then return everything
    if (empty($condition))
      return $this->map;
    
    // Return all elements that satisfies the condition
    return array_filter($this->map,function($element) use ($condition) {
      return $this->filter($element,$condition);
    });
  }
  
  // Return the first element that satisfies the condition
  public function find(array $condition): ModelInterface
  {
    $matches = $this->findAll($condition);
    return array_shift($matches);
  }
  
  // Add an element to the collection
  public function add(ModelInterface $element): self
  {
    $this->map[$element->getId()] = $element;
    return $this;
  }
  
  // Remove an element from the collection
  public function remove(ModelInterface $element): self
  {
    unset($this->map[$element->getId()]);
    return $this;
  }
  
  // Remove an element by identifier from the collection
  public function removeId(int $elementId): self
  {
    unset($this->map[$elementId]);
    return $this;
  }

  // Return the iterator
  public function getIterator(): Traversable
  {
    return new ArrayIterator($this->map);
  }

  // Return the number of elements
  public function count(): int
  {
    return count($this->map);
  }
}
