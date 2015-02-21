<?php
  namespace ActiveCollab\Resistance\Storage\Field;

  /**
   * @package ActiveCollab\Resistance\Storage\Field
   */
  class IntegerField extends Field
  {
    /**
     * @param  mixed $value
     * @return mixed
     */
    public function cast($value)
    {
      return (integer) $value;
    }
  }