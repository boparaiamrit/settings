<?php namespace Boparaiamrit\Settings;


use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;

/**
 * Class Settings
 *
 * @package Boparaiamrit\Setting
 */
class Settings
{
	
	/**
	 * Registry config
	 *
	 * @var array
	 */
	protected $config;
	
	
	/**
	 * Database manager instance
	 *
	 * @var \Illuminate\Database\DatabaseManager
	 */
	protected $database;
	
	/**
	 * Cache
	 *
	 * @var \Boparaiamrit\Settings\Cache
	 */
	protected $cache;
	
	/**
	 * Constructor
	 *
	 * @param DatabaseManager|Connection $database
	 * @param array                      $config
	 */
	public function __construct(DatabaseManager $database, $config)
	{
		$this->database = $database;
		
		$this->config = $config;
		
		$this->cache = new Cache(array_get($config, 'path'), array_get($config, 'filename'));
	}
	
	/**
	 * Gets a value
	 *
	 * @param  string $key
	 * @param  string $default
	 *
	 * @return mixed
	 */
	public function get($key, $default = null)
	{
		$value = $this->fetch($key);
		
		if (!is_null($value))
			return $value;
		else if ($default != null)
			return $default;
		else if ($this->config['fallback'])
			return app('config')->get($key, null);
		else
			return $default;
	}
	
	/**
	 * @param $key
	 *
	 * @return mixed|null
	 */
	private function fetch($key)
	{
		if ($this->cache->hasKey($key)) {
			return $this->cache->get($key);
		}
		
		$row = $this->database->table($this->config['collection'])->where('key', $key)->first(['value']);
		
		return (!is_null($row)) ? $this->cache->set($key, unserialize($row['value'])) : null;
	}
	
	
	/**
	 * Checks if setting exists
	 *
	 * @param $key
	 *
	 * @return bool
	 */
	public function hasKey($key)
	{
		if ($this->cache->hasKey($key)) {
			return true;
		}
		$row = $this->database->table($this->config['collection'])->where('key', $key)->first(['value']);
		
		return !empty($row);
	}
	
	/**
	 * Store value into registry
	 *
	 * @param  string $key
	 * @param  mixed  $value
	 *
	 * @return mixed
	 */
	public function set($key, $value)
	{
		$value = serialize($value);
		
		$setting = $this->database->table($this->config['collection'])->where('key', $key)->first();
		
		if (is_null($setting)) {
			$this->database->table($this->config['collection'])
						   ->insert(['key' => $key, 'value' => $value]);
		} else {
			$this->database->table($this->config['collection'])
						   ->where('key', $key)
						   ->update(['value' => $value]);
		}
		
		$this->cache->set($key, unserialize($value));
		
		return $value;
	}
	
	
	/**
	 * Remove a setting
	 *
	 * @param  string $key
	 *
	 * @return void
	 */
	public function forget($key)
	{
		$this->database->table($this->config['collection'])->where('key', $key)->delete();
		$this->cache->forget($key);
	}
	
	/**
	 * Remove all settings
	 *
	 * @return bool
	 */
	public function flush()
	{
		$this->cache->flush();
		
		return $this->database->table($this->config['collection'])->delete();
	}
	
	/**
	 * Fetch all values
	 *
	 * @return mixed
	 */
	public function getAll()
	{
		return $this->cache->getAll();
	}
	
	
}
