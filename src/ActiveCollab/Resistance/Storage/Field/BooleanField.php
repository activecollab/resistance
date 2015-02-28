<?php
  namespace ActiveCollab\Resistance\Storage\Field;

  use ActiveCollab\Resistance\Error\Error;

  /**
   * @package ActiveCollab\Resistance\Storage\Field
   */
  class BooleanField extends Field
  {
    /**
     * @param  mixed $value
     * @return boolean
     */
    public function cast($value)
    {
      return (boolean) $value;
    }

    /**
     * Cast value so we can use it for map key
     *
     * @param  mixed  $value
     * @return string
     */
    public function castForMapKey($value)
    {
      return $this->cast($value) ? '1' : '0';
    }

    /**
     * @throws Error
     */
    public function unique()
    {
      throw new Error('Boolean fields can not be unique');
    }
  }