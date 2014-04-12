<?php namespace Andrewsuzuki\Perm;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Config\Repository;

class Perm {

	/**
	 * Current configuration file
	 * @var string
	 */
	protected $filename;

	/**
	 * Current configuration file's values in array
	 * @var array
	 */
	protected $configValues;

	/**
	 * Base path for loading dot-notation filenames
	 * @var string
	 */
	protected $basepath;

	/**
	 * Illuminate filesystem
	 * @var object
	 */
	protected $filesystem;

	/**
	 * Illuminate config repository
	 * @var object
	 */
	protected $config;

	/**
	 * Constructor
	 */
	public function __construct(Filesystem $filesystem, Repository $config, $basepath = null)
	{
		$this->filesystem = $filesystem;
		$this->config     = $config;

		// get dot-file basepath from config if not set in constructor
		if (!$basepath) $basepath = $this->config->get('perm::basepath');
		$this->basepath = $basepath;

		$this->configValues = array();
	}

	/**
	 * Load a configuration file
	 *
	 * @param  $dotOrFilePath Absolute path to config file, or dot-notation path from basepath (default: app/config) (existing, or to-be)
	 * @return $this
	 */
	public function load($dotOrFilePath)
	{
		$this->setFilename($dotOrFilePath);

		// check if file already exists
		if ($this->filesystem->exists($this->filename))
		{
			// require file and check that it returns an array
			try
			{
				if (is_array($config = $this->filesystem->getRequire($this->filename)))
					$this->configValues = $config;
				else
					throw new \Exception('Existing configuration file could not be loaded (not valid array).');
			}
			catch (\Exception $e)
			{
				throw new \Exception($e->getMessage());
			}
		}

		return $this;
	}

	/**
	 * Update current loaded filename (will NOT update loaded config)
	 *
	 * @param  $dotOrFilePath Absolute path to config file (no extension), or dot-notation path from basepath (default: app/config) (existing, or to-be)
	 * @return $this
	 */
	public function setFilename($dotOrFilePath)
	{
		// determine if absolute path was given
		if (strpos($dotOrFilePath, '/') !== false)
		{
			// make sure file basename has no dots
			if (strpos(basename($dotOrFilePath), '.'))
				throw new \InvalidArgumentException('Absolute file path basename cannot have an extension.');

			$filename = $dotOrFilePath;
		}
		else
		{
			// parse dot notation as absolute path
			$filename = rtrim($this->basepath, '/').'/'.trim(implode('/', explode('.', $dotOrFilePath)), '/');
		}

		$this->filename = $filename.'.php';

		return $this;
	}

	/**
	 * Return current loaded filename
	 * 
	 * @return string Path to file (doesn't have to exist)
	 */
	public function getFilename()
	{
		return substr($this->filename, 0, -4);
	}

	/**
	 * Gets all config values
	 * 
	 * @return array
	 */
	public function getAll()
	{
		return $this->configValues;
	}

	/**
	 * Gets a config value (can use laravel dot notation)
	 *
	 * @param  mixed $key String key, or array of keys
	 * @return mixed      Set/saved config value, array of values (if passed array of keys), or null/nulls if dne
	 */
	public function get($keyOrArray)
	{
		if (is_array($keyOrArray))
			return array_only($this->configValues, $keyOrArray);
		else
			return array_get($this->configValues, $keyOrArray); // get value, with laravel dot-notation helper
	}

	/**
	 * Sets a config value
	 * 
	 * @param  array|string $keyOrArray Array of key=>values, or key string (can use laravel dot notation)
	 * @param  mixed        $value      Config value to set
	 * @return              $this
	 */
	public function set($keyOrArray, $value = null)
	{
		// ensure value is not an object/closure
		if (is_object($value))
		{
			// determine exception message (if it's a closure or not) and throw
			$type = ($value instanceof \Closure) ? 'a closure' : 'an object';
			throw new \InvalidArgumentException('Config value cannot be '.$type.'.');
		}

		// handle arrays passed as first argument
		if (is_array($keyOrArray))
		{
			// make recursive calls while retaining method chaining
			
			$perm = $this;

			foreach ($keyOrArray as $key => $value)
			{
				$perm = $perm->set($key, $value);
			}

			return $perm;
		}
		// ensure key is a string
		elseif (!is_string($keyOrArray))
		{
			throw new \InvalidArgumentException('Config key must be a string.');
		}

		array_set($this->configValues, $keyOrArray, $value); // set value, with laravel dot-notation helper

		return $this;
	}

	/**
	 * Forgets a config value
	 *
	 * @param  string Key string (can use laravel dot notation)
	 * @return $this
	 */
	public function forget($key)
	{
		array_forget($this->configValues, $key);
		return $this;
	}

	/**
	 * Saves a configuration file
	 *
	 * @return $this
	 */
	public function save()
	{
		try
		{
			// make sure a filename was loaded/set
			if (!$this->filename)
				throw new \Exception('A filename was not loaded/set.');

			$contents = var_export($this->configValues, true); // export php value
			$contents = '<?php return '.$contents.'; /* Config file generated by andrewsuzuki/perm at '.date('c').' */ ?>';

			// make directory (recursively) if it doesn't exist
			$dir = dirname($this->filename);
			if (!$this->filesystem->isDirectory($dir)) $this->filesystem->makeDirectory($dir, 0777);

			// save file
			$this->filesystem->put($this->filename, $contents);

			return $this;
		}
		catch (\Exception $e)
		{
			throw new \Exception('Configuration file could not be saved: '.$e->getMessage());
		}
	}
}