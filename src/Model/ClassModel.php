<?php
namespace Webuntis\Model;

use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizableInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Webuntis\Model\Traits\ActiveTrait;
use Webuntis\Model\Traits\LongNameTrait;
use Webuntis\Model\Traits\NameTrait;
use Webuntis\WebuntisInterface;

class ClassModel extends Model implements DenormalizableInterface
{
  use NameTrait;
  use LongNameTrait;
  use ActiveTrait;
  
  // Variables
  protected $department;
  
  // Return the department id
  public function getDepartment()
  {
    return $this->department;
  }
  
  // Set the department id
  public function setDepartment($department): self
  {
    $this->department = $department;
    return $this;
  }

  // Denormalize the class
  public function denormalize(DenormalizerInterface $denormalizer, $data, $format = null, array $context = []): self
  {
    if (!is_array($data))
      throw new InvalidArgumentException("Data must be an array");
    
    // Check if we can access the database
    if (!is_a($context['database'],WebuntisInterface::class))
      throw new InvalidArgumentException("No database is defined");
    
    // Get the department if there is one
    if (isset($data['did']))
      $department = $context['database']->getDepartments()->get($data['did']);
    
    // Return the object
    return $this
      ->setId($data['id'])
      ->setName($data['name'])
      ->setLongName($data['longName'])
      ->setActive($data['active'])
      ->setDepartment($department);
  }
  
  // Convert to string
  public function __toString()
  {
    return $this->getLongName();
  }
}
