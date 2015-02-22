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
     * @throws Error
     */
    public function unique()
    {
      throw new Error('Boolean fields can not be unique');
    }
  }