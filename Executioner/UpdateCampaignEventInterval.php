<?php

namespace MauticPlugin\SurgeExtendedCampaignBundle\Executioner;

use Mautic\CampaignBundle\Entity\Event;
use Mautic\CampaignBundle\Executioner\Scheduler\Mode\Interval;
use Psr\Log\LoggerInterface;

class UpdateCampaignEventInterval extends Interval
{
  const LOG_DATE_FORMAT = 'Y-m-d H:i:s T';
  private $logger;
  public function __construct(LoggerInterface $logger)
  {
    $this->logger = $logger;
  }

  public function getExecutionDateTime(Event $event, \DateTime $compareFromDateTime = null, \DateTime $comparedToDateTime = null)
  {
    if ($compareFromDateTime === null) {
      $compareFromDateTime = new \DateTime();
    }
    if ($comparedToDateTime === null) {
      $comparedToDateTime = clone $compareFromDateTime;
    }

    $interval = $event->getTriggerInterval();
    $intervalType = $event->getProperties('triggerIntervalType')['triggerIntervalType'];
    $intervalUnit = $event->getTriggerIntervalUnit();

    $this->logger->error('tsssypee',['interval' => $interval]);
    $this->logger->error('tsssypee',['type' => $intervalType]);
    $this->logger->error('tsssypee',['unit' => $intervalUnit]);


    if (!is_int($interval) || $interval < 1) {
      $interval = 0;
    }

    if (!empty($event->getTriggerHour())) {
      $getTriggerHour = $event->getTriggerHour();
    }

    if (!empty($event->getTriggerRestrictedStartHour())) {
      $triggerStartHour = $event->getTriggerRestrictedStartHour();
    }

    if (!empty($event->getTriggerRestrictedStopHour())) {
      $triggerStopHour = $event->getTriggerRestrictedStopHour();
    }

    $selectedDays = $event->getTriggerRestrictedDaysOfWeek();
    $values = array_values($selectedDays);

    $comparedToDateTime = $this->setEventTriggerUnitTime($comparedToDateTime, $intervalUnit);

    $this->logger->error('tsssypee',['beforexecutin typee' => $comparedToDateTime->format(self::LOG_DATE_FORMAT)]);
    
    $comparedToDateTime  = $this->setEventIntervalType($intervalType, $comparedToDateTime, $interval , $getTriggerHour);
    
    $this->logger->error('after type time',['after typee' => $comparedToDateTime->format(self::LOG_DATE_FORMAT)]);

    
    if (!empty($values)) {
      $day = $comparedToDateTime->format('w');
      if (!in_array($day, $values)) {
        $this->logger->error('typee');
        return null;
      }
    }
    if($intervalType == 'ne' && ($intervalUnit == 'd' || $intervalUnit == 'm')){
        // $event->setTriggerDate($comparedToDateTime->format('Y-m-d'));
        return $comparedToDateTime->format(self::LOG_DATE_FORMAT);
    }
    if (!empty($triggerStartHour) ) {
      $comparedToDateTime  = $this->setEventTriggerTime($triggerStartHour, $comparedToDateTime);
    }
    $this->logger->error('typee', ['beforeInetrunit' => $comparedToDateTime->format(self::LOG_DATE_FORMAT)]);

    return $comparedToDateTime->format(self::LOG_DATE_FORMAT);
  }



  public function setEventTriggerTime($getTriggerHour, $comparedToDateTime)
  {
    $hour = (int) $getTriggerHour->format('H');
    $minute = (int) $getTriggerHour->format('i');
    $second = (int) $getTriggerHour->format('s');
    $comparedToDateTime->setTime($hour, $minute, $second);
    return $comparedToDateTime;
  }

  public function setEventIntervalType($intervalType, \DateTime $comparedToDateTime, $interval, $getTriggerHour = null)
  {

    switch ($intervalType) {
      case 'ne':
        break;
      case 'i':
        $comparedToDateTime->setTime(0, 0);
        $comparedToDateTime->modify('+' . $interval . ' minute' . ($interval > 1 ? 's' : ''));
        break;
      case 'h':
        if (!empty($getTriggerHour)) {
          $comparedToDateTime  = $this->setEventTriggerTime($getTriggerHour, $comparedToDateTime);
          break;
        }
        $comparedToDateTime->setTime(0, 0);
        $comparedToDateTime->modify('+' . $interval . ' hour' . ($interval > 1 ? 's' : ''));
        break;
      case 'd':
        if (!empty($getTriggerHour)) {
          $comparedToDateTime  = $this->setEventTriggerTime($getTriggerHour, $comparedToDateTime);
          $comparedToDateTime->setDate($comparedToDateTime->format('Y'), $comparedToDateTime->format('n'), $interval);
          break;
        }
        $comparedToDateTime->setTime(0, 0, 0);
        $comparedToDateTime->setDate($comparedToDateTime->format('Y'), $comparedToDateTime->format('n'), $interval);
        break;
      case 'w':
        $comparedToDateTime->setDate($comparedToDateTime->format('Y'), $comparedToDateTime->format('m'), $interval * 7);
        break;

      case 'm':
        if (!empty($getTriggerHour)) {
          $comparedToDateTime  = $this->setEventTriggerTime($getTriggerHour, $comparedToDateTime);
          $comparedToDateTime->setDate($comparedToDateTime->format('Y'), $interval, 1);
          break;
        }
        $comparedToDateTime->setDate($comparedToDateTime->format('Y'), $interval, 1);
        break;        
    }

    return $comparedToDateTime;
  }

  public function setEventTriggerUnitTime($comparedToDateTime, $intervalUnit)
  {
    switch ($intervalUnit) {
      case 'd':
        $comparedToDateTime->modify('+1 day');
        break;
      case 'h':
        $comparedToDateTime->modify('+1 hour');
        break;
      case 'm':
        $comparedToDateTime->modify('+1 month');
        break;
      case 'i':
        $comparedToDateTime->modify('+1 minute');
        break;
      case 'y':
        $comparedToDateTime->modify('+1 year');
        break;
      default:
        throw new \InvalidArgumentException('Invalid interval unit: ' . $intervalUnit);
    }
    return $comparedToDateTime;
  }
}
