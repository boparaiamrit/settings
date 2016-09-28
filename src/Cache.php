<?php namespace Boparaiamrit\Settings;


use Illuminate\Contracts\Filesystem\Filesystem;

/**
 * Class Cache
 *
 * @package Boparaiamrit\Setting
 */
class Cache
{
	protected $path;
	
	protected $filename;
	
	/**
	 * Path to cache file
	 *
	 * @var string
	 */
	protected $filepath;
	
	/**
	 * Cached Settings
	 *
	 * @var array
	 */
	protected $settings;
	
	/**
	 * @var Filesystem
	 */
	protected $files;
	
	
	/**
	 * Constructor
	 *
	 * @param $path
	 * @param $filename
	 */
	public function __construct($path, $filename)
	{
		$hostname = app('hostname');
		
		$this->path = $path . '/' . $hostname;
		
		$this->filename = $filename;
		
		$this->filepath = $this->path . '/' . $this->filename;
		
		$this->files = app('files');
		
		$this->checkCacheFile();
		
		$this->settings = $this->getAll();
	}
	
	/**
	 * Sets a value
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return mixed
	 */
	public function set($key, $value)
	{
		$this->settings[ $key ] = $value;
		$this->store();
		
		return $value;
	}
	
	/**
	 * Gets a value
	 *
	 * @param      $key
	 * @param null $default
	 *
	 * @return mixed
	 */
	public function get($key, $default = null)
	{
		return (array_key_exists($key, $this->settings) ? $this->settings[ $key ] : $default);
	}
	
	/**
	 * Checks if $key is cached
	 *
	 * @param $key
	 *
	 * @return bool
	 */
	public function hasKey($key)
	{
		return array_key_exists($key, $this->settings);
	}
	
	/**
	 * Gets all cached settings
	 *
	 * @return array
	 */
	public function getAll()
	{
		$values = json_decode($this->files->get($this->filepath), true);
		foreach ($values as $key => $value) {
			$values[ $key ] = unserialize($value);
		}
		
		return $values;
	}
	
	/**
	 * Stores all settings to the cache file
	 *
	 * @return void
	 */
	private function store()
	{
		$settings = [];
		foreach ($this->settings as $key => $value) {
			$settings[ $key ] = serialize($value);
		}
		$this->files->put($this->filepath, json_encode($settings));
	}
	
	/**
	 * Removes a value
	 *
	 * @param $key
	 */
	public function forget($key)
	{
		if (array_key_exists($key, $this->settings)) {
			unset($this->settings[ $key ]);
		}
		$this->store();
	}
	
	/**
	 * Removes all values
	 *
	 * @return void
	 */
	public function flush()
	{
		$this->files->put($this->filepath, json_encode([]));
		// fixed the set after immediately the flush, should be returned empty
		$this->settings = [];
	}
	
	/**
	 * Checks if the cache file exists and creates it if not
	 *
	 * @return void
	 */
	private function checkCacheFile()
	{
		if (!$this->files->isDirectory($this->path)) {
			$this->files->makeDirectory($this->path, 0777);
			
			$this->flush();
		}
	}
}
