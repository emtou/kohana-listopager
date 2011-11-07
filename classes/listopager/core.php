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
  public $alias                 = '';   /** Alias of the ListoPager */
  public $listo                 = NULL; /** Listo instance */
  public $number_rows           = 0;    /** Number of total rows */
  public $number_items_per_page = 10;   /** Number of shown items per page */
  public $pagination            = NULL; /** Pagination instance */


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


  /**
   * Counts number of total items which could be displayed by the inner Listo
   *
   * Does nothing. Should be overloaded if not directly read from $this->number_rows variable
   *
   * @return null
   */
  protected function _count_total_items()
  {
  }


  /**
   * Instanciates inner Pagination
   *
   * @return null
   */
  protected function _init_pagination()
  {
    $this->pagination = Pagination::factory(
        array(
          'total_items'    => $this->number_rows,
          'items_per_page' => $this->number_items_per_page,
        )
    );
  }

}