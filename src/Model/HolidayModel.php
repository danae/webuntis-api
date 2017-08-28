<?php
namespace Webuntis\Model;

use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizableInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Webuntis\Model\Traits\LongNameTrait;
use Webuntis\Model\Traits\NameTrait;
use Webuntis\Model\Traits\DatePeriodTrait;
use Webuntis\Utils\DateTimeUtils;

class HolidayModel extends Model implements DenormalizableInterface
{
  use NameTrait;
  use LongNameTrait;
  use DatePeriodTrait;
  
  // Denormalize this holiday
  public function denormalize(DenormalizerInterface $denormalizer, $data, $format = null, array $context = []): self
  {
    if (!is_array($data))
      throw new InvalidArgumentException("Data must be an array");
    
    return $this
      ->setId($data['id'])
      ->setName($data['name'])
      ->setLongName($data['longName'])
      ->setStartDate(DateTimeUtils::parseDate($data['startDate']))
      ->setEndDate(DateTimeUtils::parseDate($data['endDate']));
  }
  
  // Convert to string
  public function __toString()
  {
    return $this->getLongName();
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
