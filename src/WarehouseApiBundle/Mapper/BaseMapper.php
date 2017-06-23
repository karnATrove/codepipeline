<?php

namespace WarehouseApiBundle\Mapper;

abstract class BaseMapper
{
	public abstract static function mapToDto($item);
	
	public static function mapToDtoList($list)
	{
		$dtoList = [];
		foreach ($list as $item) {
			$dtoList[] = self::mapToDto($item);
		}
		return $dtoList;
	}
}