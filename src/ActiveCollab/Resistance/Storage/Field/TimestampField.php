<?php
  namespace ActiveCollab\Resistance\Storage\Field;

  /**
   * @package ActiveCollab\Resistance\Storage\Field
   */
  class TimestampField extends IntegerField
  {
    /**
     * @param  mixed   $value
     * @return integer
     */
    public function cast($value)
    {
      if (is_int($value)) {
        return $value;
      } else if (is_numeric($value)) {
        return (integer) $value;
      } else {
        return strtotime($value);
      }
    }
  }
