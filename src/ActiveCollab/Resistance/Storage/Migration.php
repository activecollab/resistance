<?php
  namespace ActiveCollab\Resistance\Storage;

  /**
   * @package ActiveCollab\Resistance\Storage
   */
  abstract class Migration
  {
    /**
     * Migrate up
     */
    abstract public function up();
  }