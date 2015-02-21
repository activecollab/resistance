<?php
  namespace ActiveCollab\Resistance\Storage\Field;

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
  }