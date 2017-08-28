<?php
namespace Webuntis\Collection;

use Countable;
use Webuntis\Model\ModelInterface;

interface CollectionInterface extends Countable
{
  // Return the element with the specified identifier
  public function get(int $id);
  
  // Return all elements that satisfies the condition
  public function findAll(array $condition = []): array;
  
  // Return the first element that satisfies the condition
  public function find(array $condition);
  
  // Add an element to the collection
  public function add(ModelInterface $element);
  
  // Remove an element from the collection
  public function remove(ModelInterface $element);
  
  // Remove an element by identifier from the collection
  public function removeId(int $elementId);
}
