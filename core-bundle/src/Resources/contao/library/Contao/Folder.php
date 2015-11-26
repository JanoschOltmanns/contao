<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao;


/**
 * Creates, reads, writes and deletes folders
 *
 * Usage:
 *
 *     $folder = new Folder('test');
 *
 *     if (!$folder->isEmpty())
 *     {
 *         $folder->purge();
 *     }
 *
 * @property string  $hash     The MD5 hash
 * @property string  $name     The folder name
 * @property string  $basename Alias of $name
 * @property string  $path     The folder path
 * @property string  $value    Alias of $path
 * @property integer $size     The folder size
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class Folder extends \System
{

	/**
	 * Folder name
	 * @var string
	 */
	protected $strFolder;

	/**
	 * Files model
	 * @var FilesModel
	 */
	protected $objModel;


	/**
	 * Check whether the folder exists
	 *
	 * @param string $strFolder The folder path
	 *
	 * @throws \Exception If $strFolder is not a folder
	 */
	public function __construct($strFolder)
	{
		// No parent::__construct() here

		// Handle open_basedir restrictions
		if ($strFolder == '.')
		{
			$strFolder = '';
		}

		// Check whether it is a directory
		if (is_file(TL_ROOT . '/' . $strFolder))
		{
			throw new \Exception(sprintf('File "%s" is not a directory', $strFolder));
		}

		$this->import('Files');
		$this->strFolder = $strFolder;

		// Create the folder if it does not exist
		if (!is_dir(TL_ROOT . '/' . $this->strFolder))
		{
			$strPath = '';
			$arrChunks = explode('/', $this->strFolder);

			// Create the folder
			foreach ($arrChunks as $strFolder)
			{
				$strPath .= ($strPath ? '/' : '') . $strFolder;
				$this->Files->mkdir($strPath);
			}

			// Update the database
			if (\Dbafs::shouldBeSynchronized($this->strFolder))
			{
				$this->objModel = \Dbafs::addResource($this->strFolder);
			}
		}
	}


	/**
	 * Return an object property
	 *
	 * @param string $strKey The property name
	 *
	 * @return mixed The property value
	 */
	public function __get($strKey)
	{
		switch ($strKey)
		{
			case 'hash':
				return $this->getHash();
				break;

			case 'name':
			case 'basename':
				return basename($this->strFolder);
				break;

			case 'path':
			case 'value':
				return $this->strFolder;
				break;

			case 'size':
				return $this->getSize();
				break;

			default:
				return parent::__get($strKey);
				break;
		}
	}


	/**
	 * Return true if the folder is empty
	 *
	 * @return boolean True if the folder is empty
	 */
	public function isEmpty()
	{
		return (count(scan(TL_ROOT . '/' . $this->strFolder, true)) < 1);
	}


	/**
	 * Purge the folder
	 */
	public function purge()
	{
		$this->Files->rrdir($this->strFolder, true);

		// Update the database
		if (\Dbafs::shouldBeSynchronized($this->strFolder))
		{
			$objFiles = \FilesModel::findMultipleByBasepath($this->strFolder . '/');

			if ($objFiles !== null)
			{
				while ($objFiles->next())
				{
					$objFiles->delete();
				}
			}

			\Dbafs::updateFolderHashes($this->strFolder);
		}
	}


	/**
	 * Purge the folder
	 *
	 * @deprecated Deprecated since Contao 4.0, to be removed in Contao 5.0.
	 *             Use $this->purge() instead.
	 */
	public function clear()
	{
		@trigger_error('Using Folder->clear() has been deprecated and will no longer work in Contao 5.0. Use Folder->purge() instead.', E_USER_DEPRECATED);

		$this->purge();
	}


	/**
	 * Delete the folder
	 */
	public function delete()
	{
		$this->Files->rrdir($this->strFolder);

		// Update the database
		if (\Dbafs::shouldBeSynchronized($this->strFolder))
		{
			\Dbafs::deleteResource($this->strFolder);
		}
	}


	/**
	 * Set the folder permissions
	 *
	 * @param string $intChmod The CHMOD settings
	 *
	 * @return boolean True if the operation was successful
	 */
	public function chmod($intChmod)
	{
		return $this->Files->chmod($this->strFolder, $intChmod);
	}


	/**
	 * Rename the folder
	 *
	 * @param string $strNewName The new path
	 *
	 * @return boolean True if the operation was successful
	 */
	public function renameTo($strNewName)
	{
		$strParent = dirname($strNewName);

		// Create the parent folder if it does not exist
		if (!is_dir(TL_ROOT . '/' . $strParent))
		{
			new \Folder($strParent);
		}

		$return = $this->Files->rename($this->strFolder, $strNewName);

		// Update the database AFTER the folder has been renamed
		$syncSource = \Dbafs::shouldBeSynchronized($this->strFolder);
		$syncTarget = \Dbafs::shouldBeSynchronized($strNewName);

		// Synchronize the database
		if ($syncSource && $syncTarget)
		{
			$this->objModel = \Dbafs::moveResource($this->strFolder, $strNewName);
		}
		elseif ($syncSource)
		{
			$this->objModel = \Dbafs::deleteResource($this->strFolder);
		}
		elseif ($syncTarget)
		{
			$this->objModel = \Dbafs::addResource($strNewName);
		}

		// Reset the object AFTER the database has been updated
		if ($return != false)
		{
			$this->strFolder = $strNewName;
		}

		return $return;
	}


	/**
	 * Copy the folder
	 *
	 * @param string $strNewName The target path
	 *
	 * @return boolean True if the operation was successful
	 */
	public function copyTo($strNewName)
	{
		$strParent = dirname($strNewName);

		// Create the parent folder if it does not exist
		if (!is_dir(TL_ROOT . '/' . $strParent))
		{
			new \Folder($strParent);
		}

		$this->Files->rcopy($this->strFolder, $strNewName);

		// Update the database AFTER the folder has been renamed
		$syncSource = \Dbafs::shouldBeSynchronized($this->strFolder);
		$syncTarget = \Dbafs::shouldBeSynchronized($strNewName);

		if ($syncSource && $syncTarget)
		{
			\Dbafs::copyResource($this->strFolder, $strNewName);
		}
		elseif ($syncTarget)
		{
			\Dbafs::addResource($strNewName);
		}

		return true;
	}


	/**
	 * Protect the folder by removing the .public file
	 */
	public function protect()
	{
		if (file_exists(TL_ROOT . '/' . $this->strFolder . '/.public'))
		{
			$objFile = new \File($this->strFolder . '/.public');
			$objFile->delete();
		}
	}


	/**
	 * Unprotect the folder by adding a .public file
	 */
	public function unprotect()
	{
		if (!file_exists(TL_ROOT . '/' . $this->strFolder . '/.public'))
		{
			\File::putContent($this->strFolder . '/.public', '');
		}
	}


	/**
	 * Return the files model
	 *
	 * @return FilesModel The files model
	 */
	public function getModel()
	{
		if ($this->objModel === null && \Dbafs::shouldBeSynchronized($this->strFolder))
		{
			$this->objModel = \FilesModel::findByPath($this->strFolder);
		}

		return $this->objModel;
	}


	/**
	 * Return the MD5 hash of the folder
	 *
	 * @return string The MD5 has
	 */
	protected function getHash()
	{
		$arrFiles = array();

		/** @var \SplFileInfo[] $it */
		$it = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator(
				TL_ROOT . '/' . $this->strFolder,
				\FilesystemIterator::UNIX_PATHS|\FilesystemIterator::FOLLOW_SYMLINKS|\FilesystemIterator::SKIP_DOTS
			), \RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ($it as $i)
		{
			if (strncmp($i->getFilename(), '.', 1) !== 0)
			{
				$arrFiles[] = str_replace(TL_ROOT . '/' . $this->strFolder . '/', '', $i->getPathname());
			}
		}

		return md5(implode('-', $arrFiles));
	}


	/**
	 * Return the size of the folder
	 *
	 * @return integer The folder size in bytes
	 */
	protected function getSize()
	{
		$intSize = 0;

		foreach (scan(TL_ROOT . '/' . $this->strFolder, true) as $strFile)
		{
			if (strncmp($strFile, '.', 1) === 0)
			{
				continue;
			}

			if (is_dir(TL_ROOT . '/' . $this->strFolder . '/' . $strFile))
			{
				$objFolder = new \Folder($this->strFolder . '/' . $strFile);
				$intSize += $objFolder->size;
			}
			else
			{
				$objFile = new \File($this->strFolder . '/' . $strFile);
				$intSize += $objFile->size;
			}
		}

		return $intSize;
	}


	/**
	 * Check if the folder should be synchronized with the database
	 *
	 * @return bool True if the folder needs to be synchronized with the database
	 *
	 * @deprecated Use Dbafs::shouldBeSynchronized() instead
	 */
	public function shouldBeSynchronized()
	{
		return \Dbafs::shouldBeSynchronized($this->strFolder);
	}
}
