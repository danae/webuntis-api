<?php
namespace Webuntis\Utils;

use DateTime;

class DateTimeUtils
{
  // Parse a date
  public static function parseDate(int $date): DateTime
  {
    $dateTime = DateTime::createFromFormat('Ymd',(string)$date);
    $dateTime->setTime(0,0);
    return $dateTime;
  }
  
  // Parse a time
  public static function parseTime(DateTime $date, int $time): DateTime
  {
    $hours = $time / 100;
    $minutes = $time % 100;
    
    $dateTime = clone $date;
    $dateTime->setTime($hours,$minutes);
    return $dateTime;
  }
  
  // Format a date
  public static function formatDate(DateTime $dateTime): int
  {
    return intval($dateTime->format('Ymd'));
  }
  
  // Format a time
  public static function formatTime(DateTime $dateTime): int
  {
    return intval($dateTime->format('Hi'));
  }
}
