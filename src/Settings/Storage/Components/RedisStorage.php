<?php
/**
 * Created by PhpStorm.
 * User: miljanrakita
 * Date: 2/5/19
 * Time: 2:08 AM
 */

namespace InstagramAPI\Settings\Storage\Components;

use InstagramAPI\Exception\SettingsException;
use InstagramAPI\Settings\StorageInterface;
use InstagramAPI\Settings\Util\RedisKeys;
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
        if (!isset($locationConfig['redis_host']))  {
            throw new SettingsException('Missing redis_host variable');
        }

        if (!isset($locationConfig['redis_port'])) {
            throw new SettingsException('Missing redis_port variable');
        }

        $this->_dbTableName = (isset($locationConfig['dbtablename']) ? $locationConfig['dbtablename'] : 'account');

        $this->_redis = new Client([
            'scheme' => 'tcp',
            'host'   => $locationConfig['redis_host'],
            'port'   => $locationConfig['redis_port'],
        ]);
    }

    public function hasUser($username)
    {
        $key = sprintf(RedisKeys::SEARCH_USERNAME_KEY, $this->_dbTableName, $username);

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

        $oldKey = sprintf(RedisKeys::SEARCH_USERNAME_KEY, $this->_dbTableName, $oldUsername);

        $newKey = sprintf(RedisKeys::SEARCH_USERNAME_KEY,  $this->_dbTableName, $newUsername);


        if (!$this->_redis->rename($oldKey, $newKey)) {
            throw new SettingsException($this->_backendName.' Error: moveUser from '.$oldKey.' to '.$newUsername);
        }
    }

    public function deleteUser($username)
    {
        $key = sprintf(RedisKeys::SEARCH_USERNAME_KEY, $this->_dbTableName, $username);

        $this->_redis->del($key);
    }

    public function openUser($username)
    {

        $this->_username = $username;

        $key = sprintf(RedisKeys::SEARCH_USERNAME_KEY, $this->_dbTableName, $username);

        $result = $this->_redis->hGetAll($key);

        if (!empty($result)) {
            $this->_cache = $result;
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
                throw new SettingsException(sprintf('Failed to decode corrupt settings for account "%s".', $this->_username));
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

        $key = sprintf(RedisKeys::SEARCH_USERNAME_KEY, $this->_dbTableName, $this->_username);

        $this->_redis->hMSet($key, [
            $column => $data
        ]);

        // Cache the new value.
        $this->_cache[$column] = $data;
    }

}