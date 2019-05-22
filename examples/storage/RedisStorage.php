<?php
/**
 * Created by PhpStorm.
 * User: miki
 * Date: 2019-03-25
 * Time: 15:27
 */

use InstagramAPI\Settings\StorageInterface;
use InstagramAPI\Exception\SettingsException;
use Predis\Client;

class RedisStorage implements StorageInterface
{
    /** @var string Human name of the backend, such as "MySQL" , "SQLite" or "Redis" */
    protected $_backendName;

    /** @var \Predis\Client Our connection to the database. */
    protected $_redis;

    /** @var array A cache of important columns from the user's database row. */
    protected $_cache;

    /** @var string Current Instagram username that all settings belong to. */
    protected $_username;

    /** @var string Current Instagram pk that all settings belong to. */
    protected $_pk;

    /** @var string Which table to store the settings in. */
    protected $_dbTableName;

    /**
     * Constructor.
     *
     * @param string $backendName Human name of the backend, such as "MySQL" , "SQLite" or "Redis".
     *
     * @throws SettingsException
     */
    public function __construct($backendName = 'Redis')
    {
        $this->_backendName = $backendName;
    }

    /**
     * Connect to a storage location and perform necessary startup preparations.
     *
     * @param array $locationConfig
     *
     * @throws SettingsException
     */
    public function openLocation(array $locationConfig)
    {
        $this->_dbTableName = (isset($locationConfig['dbtablename']) ? $locationConfig['dbtablename'] : 'account');

        if (!isset($locationConfig['redis_url'])) {
            throw new SettingsException('Missing redis_url variable');
        }

        $this->_redis = new Client($locationConfig['redis_url']);
    }

    public function hasUser($username)
    {
        $key = sprintf(RedisKeys::SEARCH_PK_KEY, $this->_dbTableName, $username);

        return $this->_redis->exists($key);
    }

    public function moveUser($oldUsername,
                             $newUsername)
    {

        if (!$this->hasUser($oldUsername)) {
            throw new SettingsException(sprintf('Cannot move non-existent user "%s".', $oldUsername));
        }

        // Verify that the new username does not exist.
        if ($this->hasUser($newUsername)) {
            throw new SettingsException(sprintf('Refusing to overwrite existing user "%s".', $newUsername));
        }

        $oldKey = sprintf(RedisKeys::SEARCH_PK_KEY, $this->_dbTableName, $oldUsername);

        $newKey = sprintf(RedisKeys::SEARCH_PK_KEY,  $this->_dbTableName, $newUsername);


        if (!$this->_redis->rename($oldKey, $newKey)) {
            throw new SettingsException($this->_backendName.' Error: moveUser from '.$oldKey.' to '.$newUsername);
        }
    }

    public function deleteUser($username)
    {
        $key = sprintf(RedisKeys::SEARCH_PK_KEY, $this->_dbTableName, $username);

        $this->_redis->del($key);
    }

    public function openUser($username, $pk)
    {

        $this->_username = $username;
        $this->_pk = $pk;

        $key = sprintf(RedisKeys::SEARCH_PK_KEY, $this->_dbTableName, $pk);

        $settings = $this->_redis->executeRaw(["JSON.GET", $key, ".settings"]);
        $cookies = $this->_redis->executeRaw(["JSON.GET", $key, ".cookies"]);

        if (!empty($settings) && !empty($cookies)) {
            $this->_cache['settings'] = $settings;
            $this->_cache['cookies'] = $cookies;
        } else {
            $this->_cache = [
                'id'       => null,
                'settings' => null,
                'cookies'  => null,
            ];
        }

    }

    public function loadUserSettings()
    {
        $userSettings = [];

        if (!empty($this->_cache['settings'])) {

            $userSettings = @json_decode($this->_cache['settings'], true, 512, JSON_BIGINT_AS_STRING);
            if (!is_array($userSettings)) {
                throw new SettingsException(sprintf('Failed to decode corrupt settings for account "%s".', $this->_pk));
            }
        }

        return $userSettings;
    }

    public function saveUserSettings(array $userSettings, $triggerKey)
    {
        // Store the settings as a JSON blob.
        $encodedData = json_encode($userSettings);
        $this->_setUserColumn('settings', $encodedData);
    }

    public function hasUserCookies()
    {
        return isset($this->_cache['cookies']) && !empty($this->_cache['cookies']);
    }

    public function getUserCookiesFilePath()
    {
        // NULL = We (the backend) will handle the cookie loading/saving.
        return null;
    }

    public function loadUserCookies()
    {
        return isset($this->_cache['cookies'])
            ? $this->_cache['cookies']
            : null;
    }

    public function saveUserCookies($rawData)
    {
        $this->_setUserColumn('cookies', $rawData);
    }

    public function closeUser()
    {
        $this->_username = null;
        $this->_pk = null;
        $this->_cache = null;
    }

    public function closeLocation()
    {
        // Delete our reference to the Redis object. If nobody else references
        // it, the Redis connection will now be terminated. In case of shared
        // objects, the original owner still has their reference (as intended).
        $this->_redis = null;
    }

    /**
     * Automatically writes to the correct user's row and caches the new value.
     *
     * @param string $column The database column.
     * @param string $data   Data to be written.
     *
     * @throws \InstagramAPI\Exception\SettingsException
     */
    protected function _setUserColumn($column, $data) {

        if ($column != 'settings' && $column != 'cookies') {
            throw new SettingsException(sprintf('Attempt to write to illegal database column "%s".', $column));
        }

        $key = sprintf(RedisKeys::SEARCH_PK_KEY, $this->_dbTableName, $this->_pk);

        if (!$this->_redis->exists($key)) {
            $this->_redis->executeRaw(["JSON.SET", $key, ".", '{"cookies":null,"settings":null}']);
        }

        $this->_redis->executeRaw(["JSON.SET", $key, ".$column", $data]);

        // Cache the new value.
        $this->_cache[$column] = $data;
    }
}
