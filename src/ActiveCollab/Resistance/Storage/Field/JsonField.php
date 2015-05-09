<?php
  namespace ActiveCollab\Resistance\Storage\Field;

  use JsonSerializable, ActiveCollab\Resistance\Error\Error;

  /**
   * @package ActiveCollab\Resistance\Storage\Field
   */
  class JsonField extends Field
  {
    /**
     * Convert $value to the native PHP type that is handled by this field
     *
     * @param  mixed $value
     * @return mixed
     * @throws Error
     */
    public function cast($value)
    {
      if (is_array($value) || $value instanceof JsonSerializable) {
        return $value;
      } else {
        throw new Error('Value is expected to be an array or an instance that implements JsonSerializable interface, ' . gettype($value) . ' given');
      }
    }

    /**
     * Prepare value for the storage
     *
     * @param  mixed  $value
     * @return string
     */
    public function serialize($value)
    {
      return json_encode($value);
    }

    /**
     * Conver stored value to the native PHP type
     *
     * @param  string $value
     * @return mixed
     */
    public function unserialize($value)
    {
      return json_decode($value, true);
    }
  }