<?php

namespace MauticPlugin\SurgeExtendedCampaignBundle\Executioner;

use DateInterval;
use Psr\Log\LoggerInterface;
use Mautic\CampaignBundle\Entity\Event;
use Mautic\CoreBundle\Helper\DateTimeHelper;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CampaignBundle\Executioner\Scheduler\Mode\Interval;
use Mautic\CampaignBundle\Executioner\Scheduler\Exception\NotSchedulableException;

class CustomInterval extends Interval
{
  const LOG_DATE_FORMAT = 'Y-m-d H:i:s T';

  /**
   * @var LoggerInterface
   */
  private $logger;

  /**
   * @var CoreParametersHelper
   */
  private $coreParametersHelper;

  /**
   * @var \DateTimeZone
   */
  private $defaultTimezone;

  /**
   * Interval constructor.
   */
  public function __construct(LoggerInterface $logger, CoreParametersHelper $coreParametersHelper)
  {
      $this->logger               = $logger;
      $this->coreParametersHelper = $coreParametersHelper;

      parent::__construct($logger, $coreParametersHelper);
  }

	public function getExecutionDateTime(Event $event, \DateTime $compareFromDateTime = null, \DateTime $comparedToDateTime = null)
	{
		$intervalType = $event->getProperties()['triggerIntervalType'] ?? 'na';
    $intervalStatus = $event->getProperties()['triggerIntervalStatus'] ?? 'wait';
    $intervalUnit     = $event->getTriggerIntervalUnit();

		if ($intervalStatus === 'wait' || $intervalType === 'na') {
			$interval = $event->getTriggerInterval();
		} else {
			$interval = 1;
		}

		try {
			$this->logger->debug(
				'CAMPAIGN: (' . $event->getId() . ') Adding interval of ' . $interval . $intervalUnit . ' to ' . $comparedToDateTime->format(self::LOG_DATE_FORMAT)
			);

			$comparedToDateTime->add((new DateTimeHelper())->buildInterval($interval, $intervalUnit));
		} catch (\Exception $exception) {
			$this->logger->error('CAMPAIGN: Determining interval scheduled failed with "' . $exception->getMessage() . '"');

			throw new NotSchedulableException();
		}

		if ($comparedToDateTime > $compareFromDateTime) {
			$this->logger->debug(
				'CAMPAIGN: (' . $event->getId() . ') ' . $comparedToDateTime->format(self::LOG_DATE_FORMAT) . ' is later than '
					. $compareFromDateTime->format(self::LOG_DATE_FORMAT) . ' and thus returning ' . $comparedToDateTime->format(self::LOG_DATE_FORMAT)
			);

			//the event is to be scheduled based on the time interval
			$compareFromDateTime = $comparedToDateTime;
		}

		$this->logger->debug(
			'CAMPAIGN: (' . $event->getId() . ') ' . $comparedToDateTime->format(self::LOG_DATE_FORMAT) . ' is earlier than '
				. $compareFromDateTime->format(self::LOG_DATE_FORMAT) . ' and thus returning ' . $compareFromDateTime->format(self::LOG_DATE_FORMAT)
		);

    $interval = $event->getTriggerInterval();

		return $this->setEventIntervalType($compareFromDateTime, $interval, $intervalType, $intervalUnit);
	}

	public function setEventIntervalType(\DateTime $dateTime, $interval, $intervalType, $intervalUnit)
	{
		if ($intervalType === 'na') {
			return $dateTime;
		}
    
		switch ($intervalType) {
			case 'i':
        if($intervalUnit == 'h') {
          $dateTime->setTime($dateTime->format('H'), 0, 0);
        }else{
          $dateTime->setTime(0, 0, 0);
        }

				$dateTime->modify('+' . $interval . ' minute' . ($interval > 1 ? 's' : ''));
				break;
			case 'h':
				$dateTime->setTime(0, 0, 0);
				$dateTime->modify('+' . $interval . ' hour' . ($interval > 1 ? 's' : ''));
				break;
			case 'd':
				$dateTime->setTime(0, 0, 0);
				if ($intervalUnit == 'm') {
					$dateTime->modify('first day of this month');
				} else if ($intervalUnit == 'y') {
					$dateTime->modify('first day of January ' . $dateTime->format('Y'));
				}
				$daysToAdd = $interval - 1;
				if ($daysToAdd > 0)
					$dateTime->modify('+' . $daysToAdd . ' day' . ($daysToAdd > 1 ? 's' : ''));
				break;
			case 'w':
				$dateTime = $this->setWeekDate($dateTime, $intervalUnit, $interval);
				break;
			case 'm':
				$dateTime->setDate($dateTime->format('Y'), $interval, 1);
				$dateTime->setTime(0, 0, 0);
				break;
			default:
				throw new \InvalidArgumentException("Invalid interval type provided");
		}

		return $dateTime;
	}

	private function setWeekDate($dateTime, $intervalUnit, $weekNumber)
	{
		$dateTimeClone = clone $dateTime;

    // Ensure the time is reset to 00:00:00
		$dateTimeClone->setTime(0, 0, 0);

		if ($intervalUnit == 'm') {
			$dateTimeClone->modify('first day of this month');
		} else if($intervalUnit == 'y') {
			$dateTimeClone->modify('first day of January ' . $dateTimeClone->format('Y'));
		}
		$firstDayWeekday = $dateTimeClone->format('N'); // 1 is Monday, 7 is Sunday

		// If the first day of the month/year is not Monday and weekNumber = 1 return the first day of the month/year
		if ($firstDayWeekday != 1 && $weekNumber == 1) {
			return $dateTimeClone;
		}

    // If the first day of the month/year is not Monday, adjust to the next Monday
		if ($firstDayWeekday != 1) {
			$dateTimeClone->modify('last monday');
		}

		// For weekNumber = 1, we stay on this Monday, for higher weeks, we add weeks
		if ($weekNumber > 1) {
			$dateTimeClone->modify('+' . ($weekNumber - 1) . ' weeks');
		}

		return $dateTimeClone;
	}
}
