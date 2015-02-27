<?php
  namespace ActiveCollab\Resistance\Storage\Field;

  use ActiveCollab\Resistance\Error\Error, ActiveCollab\Resistance\Error\ValidationError;

  /**
   * @package ActiveCollab\Resistance\Storage\Field
   */
  abstract class Field
  {
    /**
     * Construct new field instance
     */
    public function __construct()
    {
      $this->default_value = $this->cast(null);
    }

    /**
     * @param  mixed $value
     * @return mixed
     */
    abstract public function cast($value);

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
      $this->default_value = $this->cast($value);
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
     * @param  boolean $value
     * @return $this
     */
    public function &required($value = true)
    {
      if ($value) {
        $this->setValidator('required', function($field_name, $value) {
          if (!$value) {
            throw new ValidationError("Value of '$field_name' is required");
          }
        });
      } else {
        $this->setValidator('required', null);
      }

      return $this;
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