<?php
namespace Webuntis\Model;

interface ModelInterface
{
  // Return the identifier
  public function getId(): int;
  
  // Set the identifier
  public function setId(int $id);
}
