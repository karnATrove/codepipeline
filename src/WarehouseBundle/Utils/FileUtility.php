<?php
/**
 * Created by PhpStorm.
 * User: rove
 * Date: 2017-05-29
 * Time: 9:56 AM
 */

namespace WarehouseBundle\Utils;


use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class FileUtility
{
	/**
	 * @param string $dir
	 *
	 * @return bool|null
	 */
	public static function isDirEmpty(string $dir)
	{
		if (!is_readable($dir)) return NULL;
		$handle = opendir($dir);
		while (false !== ($entry = readdir($handle))) {
			if ($entry != "." && $entry != "..") {
				return FALSE;
			}
		}
		return TRUE;
	}

	/**
	 * @param $dir
	 * @param $zipDir
	 */
	public static function zip($dir, $zipDir)
	{
		$rootPath = realpath($dir);
		$zip = new ZipArchive();
		$zip->open($zipDir, ZipArchive::CREATE | ZipArchive::OVERWRITE);
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($rootPath),
			RecursiveIteratorIterator::LEAVES_ONLY
		);

		foreach ($files as $name => $file) {
			if (!$file->isDir()) {
				$filePath = $file->getRealPath();
				$relativePath = substr($filePath, strlen($rootPath) + 1);
				$zip->addFile($filePath, $relativePath);
			}
		}
		$zip->close();
	}

	/**
	 * @param $directory
	 */
	public static function recursiveRemoveDirectory($directory)
	{
		foreach(glob("{$directory}/*") as $file)
		{
			if(is_dir($file)) {
				self::recursiveRemoveDirectory($file);
			} else {
				unlink($file);
			}
		}
		rmdir($directory);
	}
}