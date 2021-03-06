<?php
/**
 * Process Library
 * @author Tao <taosikai@yeah.net>
 */
namespace Slince\Process\SystemV;

class SharedMemory
{
    use IpcKeyTrait;

    /**
     * The resource that be generated after call "shm_attach"
     * @var resource
     */
    protected $shmId;

    /**
     * The size of the shared memory
     * @var int
     */
    protected $size;

    public function __construct($pathname = __FILE__, $size = null, $permission = 0666)
    {
        if (!is_null($size)) {
            $this->size = static::humanReadableToBytes($size);
        } else {
            $this->size = (int)ini_get('sysvshm.init_mem') ?: 10000;
        }
        $ipcKey = $this->generateIpcKey($pathname);
        $this->shmId = shm_attach($ipcKey, $this->size, $permission);
    }

    /**
     * Gets a value from the shared memory
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return shm_get_var($this->shmId, $this->generateShmKey($key));
    }

    /**
     * Persists data in the shared memory
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function set($key, $value)
    {
        return shm_put_var($this->shmId, $this->generateShmKey($key), $value);
    }

    /**
     * Delete an item from the shared memory by its key
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        return shm_remove_var($this->shmId, $this->generateShmKey($key));
    }

    /**
     * Checks whether an item exists in the shared memory
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return shm_has_var($this->shmId, $this->generateShmKey($key));
    }

    /**
     * Deletes all items
     */
    public function clear()
    {
        return shm_remove($this->shmId);
    }

    /**
     * Checks whether the memory is enabled
     * @return bool
     */
    public function isEnabled()
    {
        return is_resource($this->shmId);
    }

    /**
     * Disconnects from shared memory
     */
    public function close()
    {
        is_resource($this->shmId) && shm_detach($this->shmId);
    }


    /**
     * Removes all items and disconnects from shared memory
     * @return void
     */
    public function destroy()
    {
        if (is_resource($this->shmId)) {
            shm_remove($this->shmId);
            shm_detach($this->shmId);
        }
    }

    /**
     * Generate the variable key
     * @param string $val
     * @return string int
     */
    protected function generateShmKey($val)
    {
        // enable all world langs and chars !
        return preg_replace("/[^0-9]/", "", (preg_replace("/[^0-9]/", "", md5($val))/35676248)/619876);
    }

    /**
     * Convert human readable file size (e.g. "10K" or "3M") into bytes
     * @link https://github.com/brandonsavage/Upload/blob/master/src/Upload/File.php#L446
     * @param  string $input
     * @return int
     */
    public static function humanReadableToBytes($input)
    {
        $number = (int)$input;
        $units = array(
            'b' => 1,
            'k' => 1024,
            'm' => 1048576,
            'g' => 1073741824
        );
        $unit = strtolower(substr($input, -1));
        if (isset($units[$unit])) {
            $number = $number * $units[$unit];
        }
        return $number;
    }
}
