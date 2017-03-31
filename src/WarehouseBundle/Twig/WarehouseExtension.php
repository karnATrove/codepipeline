<?php

namespace WarehouseBundle\Twig;

class WarehouseExtension extends \Twig_Extension
{
	public function getFilters() {
		return array(
            new \Twig_SimpleFilter('date_difference', array($this, 'dateDifference')),
            new \Twig_SimpleFilter('days_since', array($this, 'daysSince')),
        );
	}

	public function daysSince(\DateTime $date) {
		$now = new \DateTime("now");
		return $date->diff($now)->format('%R%a');
	}

	public function dateDifference(\DateTime $startDate, \DateTime $endDate) {
		return abs($startDate->diff($endDate)->format());
		$x1 = $this->getDays($startDate);
	    $x2 = $this->getDays($endDate);
	    
	    if ($x1 && $x2) {
	        return abs($x1 - $x2);
	    }
	}

	public function getDays(\DateTime $date) {
		$y = $date->format('Y') - 1;
		$days = $y * 365;
		$z = (int)($y / 4);
		$days += $z;
		$z = (int)($y / 100);
		$days -= $z;
		$z = (int)($y / 400);
		$days += $z;
		$days += $date->format('z');

		return $days;
	}

	public function getName()
    {
        return 'warehouse_extension';
    }

    static function datetimeDiff(\DateTime $dt1, \DateTime $dt2){
        $t1 = strtotime($dt1);
        $t2 = strtotime($dt2);

        $dtd = new stdClass();
        $dtd->interval = $t2 - $t1;
        $dtd->total_sec = abs($t2-$t1);
        $dtd->total_min = floor($dtd->total_sec/60);
        $dtd->total_hour = floor($dtd->total_min/60);
        $dtd->total_day = floor($dtd->total_hour/24);

        $dtd->day = $dtd->total_day;
        $dtd->hour = $dtd->total_hour -($dtd->total_day*24);
        $dtd->min = $dtd->total_min -($dtd->total_hour*60);
        $dtd->sec = $dtd->total_sec -($dtd->total_min*60);
        return $dtd;
    }
}