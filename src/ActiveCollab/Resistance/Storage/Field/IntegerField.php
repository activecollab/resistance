<?php
  namespace ActiveCollab\Resistance\Storage\Field;

  /**
   * @package ActiveCollab\Resistance\Storage\Field
   */
  class IntegerField extends Field
  {
    /**
     * @param  mixed   $value
     * @return integer
     */
    public function cast($value)
    {
      return (integer) $value;
    }
  }