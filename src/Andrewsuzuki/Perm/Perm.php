<?php namespace Andrewsuzuki\Perm;

use Illuminate\Filesystem\Filesystem;

class Perm {

	/**
	 * Current configuration file
	 * @var string
	 */
	protected $filename;

	/**
	 * Current configuration file's array value
	 * @var array
	 */
	protected $config;

	/**
	 * Illuminate filesystem helper
	 * @var object
	 */
	protected $filesystem;

	/**
	 * Constructor
	 */
	public function __construct(Filesystem $filesystem)
	{
		$this->filesystem = $filesystem;
		$this->config = array();
	}

	/**
	 * Load a configuration file
	 * 
	 * @return $this
	 */
	public function load($filename)
	{
		// check if file already exists
		if ($this->filesystem->exists($filename))
		{
			// require file and check that it returns an array
			try
			{
				if (is_array($config = $this->filesystem->getRequire($filename)))
					$this->config = $config;
				else
					throw new \Exception('Existing configuration file could not be loaded (not valid array).');
			}
			catch (\Exception $e)
			{
				throw new \Exception($e->getMessage());
			}
		}

		$this->filename = $filename;

		return $this;
	}

	/**
	 * Gets all config values
	 * 
	 * @return array
	 */
	public function getAll()
	{
		return $this->config;
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
			return array_only($this->config, $keyOrArray);
		else
			return array_get($this->config, $keyOrArray); // get value, with laravel dot-notation helper
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

		array_set($this->config, $keyOrArray, $value); // set value, with laravel dot-notation helper

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
		array_forget($this->config, $key);
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
			$contents = var_export($this->config, true); // export php value
			$contents = '<?php return '.$contents.'; ?>';

			// make directory (recursively) if it doesn't exist
			$this->filesystem->makeDirectory(dirname($this->filename), 511, true);

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