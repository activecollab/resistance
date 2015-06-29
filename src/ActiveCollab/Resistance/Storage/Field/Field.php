<?php
  namespace ActiveCollab\Resistance\Storage\Field;

  use ActiveCollab\Resistance\Error\Error, ActiveCollab\Resistance\Error\ValidationError;

  /**
   * @package ActiveCollab\Resistance\Storage\Field
   */
  abstract class Field
  {
    /**
     * Convert $value to the native PHP type that is handled by this field
     *
     * @param  mixed $value
     * @return mixed
     */
    public function cast($value)
    {
      return $value;
    }

    /**
     * Prepare value for the storage
     *
     * @param  mixed  $value
     * @return string
     */
    public function serialize($value)
    {
      return (string) $this->cast($value);
    }

    /**
     * Conver stored value to the native PHP type
     *
     * @param  string $value
     * @return mixed
     */
    public function unserialize($value)
    {
      return $this->cast($value);
    }

    /**
     * Cast value so we can use it for map key
     *
     * @param  mixed  $value
     * @return string
     * @throws Error
     */
    public function castForMapKey($value)
    {
      $map_key = mb_strtolower(trim((string) $this->cast($value)));

      if (mb_strlen($map_key) > 1024) {
        throw new Error('Values longer than 1024 characters cannot be mapped');
      }

      return $map_key;
    }

    /**
     * @param  string $value
     * @return string
     * @throws Error
     */
    public function castForUniqueCheck($value)
    {
      $value_to_check = mb_strtolower(trim((string) $this->cast($value)));

      if (mb_strlen($value_to_check) > 1024) {
        throw new Error('Values longer than 1024 characters cannot be checked for uniqueness');
      }

      return $value_to_check;
    }

    /**
     * Indicator whether we have a custom default value
     *
     * @var bool
     */
    private $custom_default_value_is_set = false;

    /**
     * @var mixed
     */
    protected $custom_default_value;

    /**
     * Return value that is used when field value is not provided
     *
     * @return mixed
     */
    public function getDefaultValue()
    {
      return $this->custom_default_value_is_set ? $this->custom_default_value : $this->cast(null);
    }

    /**
     * Set value that should be used by default when field value is not provided
     *
     * @param  mixed $value
     * @return $this
     */
    public function &setDefaultValue($value)
    {
      $this->custom_default_value = $this->cast($value);
      $this->custom_default_value_is_set = true;

      return $this;
    }

    /**
     * @var callable[]
     */
    private $validators = [];

    /**
     * @param  string   $name
     * @param  callable $validator
     * @throws Error
     */
    protected function setValidator($name, callable $validator = null)
    {
      if ($validator === null) {
        if (isset($this->validators[$name])) {
          unset($this->validators[$name]);
        }
      } else if (is_callable($validator)) {
        $this->validators[$name] = $validator;
      } else {
        throw new Error('Invalid validator');
      }
    }

    /**
     * Check if value is valid
     *
     * @param  string          $field_name
     * @param  mixed           $value
     * @throws ValidationError
     */
    public function validate($field_name, $value)
    {
      foreach ($this->validators as $validator) {
        call_user_func($validator, $field_name, $value);
      }
    }

    /**
     * Set if field value is required to evaluate to TRUE
     *
     * @return $this
     */
    public function &required()
    {
      if (empty($this->validators['required'])) {
        $this->setValidator('required', function ($field_name, $value) {
          if (!$value) {
            throw new ValidationError("Value of '$field_name' is required");
          }
        });
      }

      return $this;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
      return isset($this->validators['required']) && is_callable($this->validators['required']);
    }

    /**
     * @var bool
     */
    private $is_mapped = false;

    /**
     * @return $this
     */
    public function &map()
    {
      $this->is_mapped = true;

      return $this;
    }

    /**
     * @return bool
     */
    public function isMapped()
    {
      return $this->is_mapped;
    }

    /**
     * @var bool
     */
    private $is_unique = false;

    /**
     * @return $this
     */
    public function &unique()
    {
      $this->is_unique = true;

      if (!$this->isRequired()) {
        $this->required();
      }

      return $this;
    }

    /**
     * @return bool
     */
    public function isUnique()
    {
      return $this->is_unique;
    }

    /**
     * @var bool
     */
    private $is_protected = false;

    /**
     * @return $this
     */
    public function &protect()
    {
      $this->is_protected = true;

      return $this;
    }

    /**
     * @return bool
     */
    public function isProtected()
    {
      return $this->is_protected;
    }
  }