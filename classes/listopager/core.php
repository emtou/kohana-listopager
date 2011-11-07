<?php
/**
 * Declares ListoPager_Core helper
 *
 * PHP version 5
 *
 * @group listopager
 *
 * @category  ListoPager
 * @package   ListoPager
 * @author    mtou <mtou@charougna.com>
 * @copyright 2011 mtou
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      https://github.com/emtou/kohana-listopager/tree/master/classes/listopager/core.php
 */

defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Provides ListoPager_Core helper
 *
 * PHP version 5
 *
 * @group listopager
 *
 * @category  ListoPager
 * @package   ListoPager
 * @author    mtou <mtou@charougna.com>
 * @copyright 2011 mtou
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      https://github.com/emtou/kohana-listopager/tree/master/classes/listopager/core.php
 */
abstract class ListoPager_Core
{
  public $alias       = '';   /** Alias of the ListoPager */
  public $listo       = NULL; /** Listo instance */
  public $number_rows = 0;    /** Number of total rows */
  public $pagination  = NULL; /** Pagination instance */


  /**
   * Instanciate inner Listo with alias
   *
   * @return ListoPager instance
   *
   * @throws ListoPager_Exception Can't instanciate ListoPager: not alias has been set
   */
  public function __construct()
  {
    if ($this->alias == '')
    {
      throw new ListoPager_Exception(
        'Can\'t instanciate ListoPager: not alias has been set'
      );
    }
    $this->listo = Listo::factory($this->alias);
  }
}