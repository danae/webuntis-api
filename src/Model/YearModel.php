<?php
namespace Webuntis\Model;

use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizableInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Webuntis\Model\Traits\NameTrait;
use Webuntis\Model\Traits\DatePeriodTrait;
use Webuntis\Utils\DateTimeUtils;

class YearModel extends Model implements DenormalizableInterface
{
  use NameTrait;
  use DatePeriodTrait;
  
  // Covnert to string
  public function __toString()
  {
    return $this->getName();
  }
  
  // Denormalize this year
  public function denormalize(DenormalizerInterface $denormalizer, $data, $format = null, array $context = []): self
  {
    if (!is_array($data))
      throw new InvalidArgumentException("Data must be an array");
    
    return $this
      ->setId($data['id'])
      ->setName($data['name'])
      ->setStartDate(DateTimeUtils::parseDate($data['startDate']))
      ->setEndDate(DateTimeUtils::parseDate($data['endDate']));
  }
  
  // Comparator function
  public static function compare($a, $b): int
  {
    if ($a == null)
      return 1;
    else if ($b == null)
      return -1;
    
    return $a->getStartDate()->getTimestamp() - $b->getStartDate()->getTimestamp();
  }
}
